<?php

declare(strict_types=1);

namespace SilverShop\Usp;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;

/**
 * Applied to {@link \SilverStripe\SiteConfig\SiteConfig}. Holds the genuinely site-wide BEHAVIOUR of the announcement
 * bar (enabled / dismissible / rotate + interval) and which payment / security badges show — the knobs that are
 * singletons rather than per-item (the items themselves are {@link Usp} records in {@link UspAdmin}).
 *
 * Also exposes the accessors on SiteConfig as a zero-extra-global fallback: $SiteConfig.USPs('home'),
 * $SiteConfig.AnnouncementBar, $SiteConfig.PaymentBadges (the clean route is the globals from
 * {@link UspTemplateProvider}).
 *
 * @property \SilverStripe\SiteConfig\SiteConfig $owner
 * @property bool $AnnouncementBarEnabled
 * @property bool $AnnouncementDismissible
 * @property bool $AnnouncementRotate
 * @property int $AnnouncementRotateSeconds
 * @property string $EnabledPaymentBadges
 * @property bool $ShowSecurityBadge
 */
class UspSiteConfigExtension extends Extension
{
    private static array $db = [
        'AnnouncementBarEnabled' => 'Boolean(1)',
        'AnnouncementDismissible' => 'Boolean(1)',
        'AnnouncementRotate' => 'Boolean(0)',
        'AnnouncementRotateSeconds' => 'Int',
        'EnabledPaymentBadges' => 'MultiEnum("ideal,bancontact,visa,mastercard,amex,paypal,applepay,googlepay,klarna")',
        'ShowSecurityBadge' => 'Boolean(0)',
    ];

    private static array $defaults = [
        'AnnouncementRotateSeconds' => 6,
    ];

    protected function updateCMSFields(FieldList $fields): void
    {
        $announcement = [
            HeaderField::create('UspAnnouncementHeader', _t(self::class . '.AnnouncementHeader', 'Announcement bar')),
            CheckboxField::create('AnnouncementBarEnabled', _t(self::class . '.Enabled', 'Show the announcement bar'))
                ->setDescription(_t(
                    self::class . '.EnabledDesc',
                    'The bar renders the active "Announcement bar" USP items.'
                )),
            CheckboxField::create('AnnouncementDismissible', _t(self::class . '.Dismissible', 'Let visitors dismiss it')),
            CheckboxField::create('AnnouncementRotate', _t(self::class . '.Rotate', 'Rotate between multiple messages')),
            NumericField::create('AnnouncementRotateSeconds', _t(self::class . '.RotateSeconds', 'Seconds per message'))
                ->setDescription(_t(self::class . '.RotateSecondsDesc', 'Used when rotation is on (minimum 2).')),
        ];

        $badges = [
            HeaderField::create('UspBadgesHeader', _t(self::class . '.BadgesHeader', 'Payment & security badges')),
            CheckboxSetField::create(
                'EnabledPaymentBadges',
                _t(self::class . '.PaymentBadges', 'Payment methods to show'),
                UspPaymentBadges::singleton()->options()
            )->setDescription(_t(
                self::class . '.PaymentBadgesDesc',
                'Rendered by the footer / checkout payment-badges include.'
            )),
            CheckboxField::create('ShowSecurityBadge', _t(self::class . '.SecurityBadge', 'Show a secure-checkout badge')),
        ];

        $tab = $fields->fieldByName('Root.Shop') ? 'Root.Shop.ShopTabs.Usp' : 'Root.Usp';
        $fields->addFieldsToTab($tab, array_merge($announcement, $badges));
        $fields->findOrMakeTab($tab)->setTitle(_t(self::class . '.TabTitle', 'USPs'));
    }

    /**
     * Fallback accessor: $SiteConfig.USPs('home').
     */
    public function USPs(string $location): ArrayList
    {
        return Usp::activeForPlacement($location);
    }

    /**
     * Fallback accessor: $SiteConfig.AnnouncementBar.
     */
    public function AnnouncementBar(): ?ArrayData
    {
        return UspTemplateProvider::AnnouncementBar();
    }

    /**
     * Fallback accessor: $SiteConfig.PaymentBadges.
     */
    public function PaymentBadges(): ?ArrayData
    {
        return UspTemplateProvider::PaymentBadges();
    }
}
