<?php

declare(strict_types=1);

namespace SilverShop\Usp;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Security\Permission;

/**
 * A single unique selling point / trust signal / announcement message. One model covers every surface: a value prop
 * in the home grid, a trust row on the PDP, a message in the topbar announcement bar, a footer reassurance, … — the
 * {@link $Placements} multi-select decides where it shows. Scheduling ({@link $StartDate}/{@link $EndDate}) and the
 * {@link $Active} flag gate visibility; {@link $Sort} orders items within a placement.
 *
 * Managed under CMS → USPs ({@link UspAdmin}). Rendered through {@link UspTemplateProvider} (`$USPs('home')`,
 * `$AnnouncementBar`) and the overridable includes under templates/SilverShop/Usp/Includes. A plain DataObject on
 * purpose — no Versioned, no Page — so it is site-agnostic and works with or without silvershop/core.
 *
 * @property string $Title
 * @property string $Text
 * @property string $IconKey
 * @property string $LinkURL
 * @property string $LinkText
 * @property bool $OpenInNew
 * @property string $Placements
 * @property bool $Active
 * @property int $Sort
 * @property string $StartDate
 * @property string $EndDate
 * @property int $IconID
 * @method Image Icon()
 */
class Usp extends DataObject
{
    /**
     * Placement keys. An announcement is just a USP placed in the bar; the shop placements (pdp/cart/checkout) only
     * render when silvershop/core hosts their include.
     */
    public const PLACEMENTS = 'announcement,home,pdp,cart,checkout,footer';

    private static string $table_name = 'SilverShop_Usp';

    private static array $db = [
        'Title' => 'Varchar(255)',
        'Text' => 'Varchar(255)',
        'IconKey' => 'Varchar(50)',
        'LinkURL' => 'Varchar(2048)',
        'LinkText' => 'Varchar(255)',
        'OpenInNew' => 'Boolean',
        'Placements' => 'MultiEnum("announcement,home,pdp,cart,checkout,footer")',
        'Active' => 'Boolean(1)',
        'Sort' => 'Int',
        'StartDate' => 'Datetime',
        'EndDate' => 'Datetime',
    ];

    private static array $has_one = [
        'Icon' => Image::class,
    ];

    private static array $owns = [
        'Icon',
    ];

    private static string $default_sort = '"Sort" ASC, "ID" ASC';

    private static array $searchable_fields = ['Title', 'Active'];

    /**
     * Active items for a placement, within their schedule window, in sort order. DB-agnostic: the schedule window is
     * filtered in SQL; the MultiEnum placement match is done in PHP (a SET column has no portable "contains" filter).
     */
    public static function activeForPlacement(string $location): ArrayList
    {
        $now = DBDatetime::now()->getValue();

        $candidates = static::get()
            ->filter('Active', true)
            ->filterAny([
                'StartDate' => null,
                'StartDate:LessThanOrEqual' => $now,
            ])
            ->filterAny([
                'EndDate' => null,
                'EndDate:GreaterThanOrEqual' => $now,
            ])
            ->sort('Sort', 'ASC');

        $out = ArrayList::create();
        foreach ($candidates as $usp) {
            if ($usp->hasPlacement($location)) {
                $out->push($usp);
            }
        }

        return $out;
    }

    /**
     * @return string[]
     */
    public function getPlacementsList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->Placements))));
    }

    public function hasPlacement(string $location): bool
    {
        return in_array($location, $this->getPlacementsList(), true);
    }

    /**
     * True when now is inside the (optional) StartDate/EndDate window.
     */
    public function getIsScheduledNow(): bool
    {
        $now = DBDatetime::now()->getValue();
        $start = $this->StartDate;
        $end = $this->EndDate;

        if ($start && $start > $now) {
            return false;
        }
        if ($end && $end < $now) {
            return false;
        }

        return true;
    }

    /**
     * True when the item would render somewhere right now (active + in schedule).
     */
    public function getIsLive(): bool
    {
        return (bool) $this->Active && $this->getIsScheduledNow();
    }

    /**
     * The inline SVG for the chosen icon key (raw, for `$IconSVG.RAW`), or null when none/unknown. An uploaded
     * {@link $Icon} image takes precedence and is rendered by the template instead.
     */
    public function getIconSVG(): ?DBHTMLText
    {
        $svg = UspIconSet::singleton()->svg((string) $this->IconKey);
        if ($svg === null) {
            return null;
        }

        return DBHTMLText::create()->setValue($svg);
    }

    public function hasIconImage(): bool
    {
        return (bool) $this->IconID && $this->Icon()->exists();
    }

    public function LinkTarget(): string
    {
        return $this->OpenInNew ? '_blank' : '';
    }

    public function hasLink(): bool
    {
        return trim((string) $this->LinkURL) !== '';
    }

    /**
     * Human summary of the placements, for the admin list column.
     */
    public function getPlacementsSummary(): string
    {
        $labels = static::placementLabels();
        $names = [];
        foreach ($this->getPlacementsList() as $key) {
            $names[] = $labels[$key] ?? $key;
        }

        return implode(', ', $names);
    }

    public function getStatusLabel(): string
    {
        if (!$this->Active) {
            return _t(self::class . '.status_inactive', 'Inactive');
        }
        $now = DBDatetime::now()->getValue();
        if ($this->StartDate && $this->StartDate > $now) {
            return _t(self::class . '.status_scheduled', 'Scheduled');
        }
        if ($this->EndDate && $this->EndDate < $now) {
            return _t(self::class . '.status_expired', 'Expired');
        }

        return _t(self::class . '.status_active', 'Active');
    }

    /**
     * @return array<string, string>
     */
    public static function placementLabels(): array
    {
        return [
            'announcement' => _t(self::class . '.placement_announcement', 'Announcement bar'),
            'home' => _t(self::class . '.placement_home', 'Home USP grid'),
            'pdp' => _t(self::class . '.placement_pdp', 'Product page'),
            'cart' => _t(self::class . '.placement_cart', 'Cart'),
            'checkout' => _t(self::class . '.placement_checkout', 'Checkout'),
            'footer' => _t(self::class . '.placement_footer', 'Footer'),
        ];
    }

    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Title'] = _t(self::class . '.db_Title', 'Title / message');
        $labels['Text'] = _t(self::class . '.db_Text', 'Supporting text');
        $labels['IconKey'] = _t(self::class . '.db_IconKey', 'Icon');
        $labels['Icon'] = _t(self::class . '.db_Icon', 'Custom icon image');
        $labels['LinkURL'] = _t(self::class . '.db_LinkURL', 'Link URL');
        $labels['LinkText'] = _t(self::class . '.db_LinkText', 'Link text');
        $labels['OpenInNew'] = _t(self::class . '.db_OpenInNew', 'Open link in a new tab');
        $labels['Placements'] = _t(self::class . '.db_Placements', 'Show in');
        $labels['Active'] = _t(self::class . '.db_Active', 'Active');
        $labels['Sort'] = _t(self::class . '.db_Sort', 'Sort');
        $labels['StartDate'] = _t(self::class . '.db_StartDate', 'Visible from');
        $labels['EndDate'] = _t(self::class . '.db_EndDate', 'Visible until');

        return $labels;
    }

    public function summaryFields(): array
    {
        return [
            'Title' => _t(self::class . '.col_Title', 'Title / message'),
            'PlacementsSummary' => _t(self::class . '.col_Placements', 'Shown in'),
            'StatusLabel' => _t(self::class . '.col_Status', 'Status'),
        ];
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Drag-sort (when gridfieldextensions is installed) owns the order; otherwise keep an editable Sort field.
        if (class_exists(\Symbiote\GridFieldExtensions\GridFieldOrderableRows::class)) {
            $fields->removeByName('Sort');
        }

        $fields->dataFieldByName('Title')
            ?->setDescription(_t(self::class . '.TitleDesc', 'The headline, trust claim or announcement message.'));

        $fields->dataFieldByName('Text')
            ?->setDescription(_t(self::class . '.TextDesc', 'Optional supporting line shown under the title.'));

        // Icon key -> a dropdown from the named icon set.
        $fields->replaceField(
            'IconKey',
            DropdownField::create('IconKey', $this->fieldLabel('IconKey'), UspIconSet::singleton()->options())
                ->setEmptyString(_t(self::class . '.NoIcon', '(no icon)'))
                ->setDescription(_t(self::class . '.IconKeyDesc', 'A themeable inline icon. Leave empty for none.'))
        );

        // Optional uploaded icon overrides the chosen key.
        $fields->replaceField(
            'Icon',
            UploadField::create('Icon', $this->fieldLabel('Icon'))
                ->setFolderName('usp')
                ->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])
                ->setDescription(_t(self::class . '.IconDesc', 'Optional. A custom image used instead of the icon above.'))
        );

        $fields->dataFieldByName('LinkURL')
            ?->setDescription(_t(self::class . '.LinkURLDesc', 'Optional. Makes the item a link (e.g. /sale or https://…).'));

        $placements = $fields->dataFieldByName('Placements');
        if ($placements instanceof CheckboxSetField) {
            $placements->setSource(static::placementLabels());
            $placements->setDescription(
                _t(self::class . '.PlacementsDesc', 'The storefront regions this item appears in.')
            );
        }

        $fields->dataFieldByName('StartDate')
            ?->setDescription(_t(self::class . '.StartDateDesc', 'Optional. Hidden before this moment.'));
        $fields->dataFieldByName('EndDate')
            ?->setDescription(_t(self::class . '.EndDateDesc', 'Optional. Hidden after this moment.'));

        return $fields;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canEdit($member = null)
    {
        return Permission::check('CMS_ACCESS', 'any', $member);
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('CMS_ACCESS', 'any', $member);
    }

    public function canDelete($member = null)
    {
        return Permission::check('CMS_ACCESS', 'any', $member);
    }
}
