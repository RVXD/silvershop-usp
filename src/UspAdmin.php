<?php

declare(strict_types=1);

namespace SilverShop\Usp;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * CMS admin for managing USP / trust-signal / announcement items. One screen, drag-sortable when
 * symbiote/silverstripe-gridfieldextensions is installed (it ships with silvershop/core); otherwise the Sort field
 * on each record keeps the order editable.
 */
class UspAdmin extends ModelAdmin
{
    private static string $url_segment = 'usps';

    private static string $menu_title = 'USPs';

    private static string $menu_icon_class = 'font-icon-check-mark-circle';

    private static int $menu_priority = 0;

    private static array $managed_models = [
        Usp::class,
    ];

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();

        // Drag-and-drop ordering, only when the component is available (soft dependency).
        if (class_exists(GridFieldOrderableRows::class)) {
            $config->addComponent(GridFieldOrderableRows::create('Sort'));
        }

        return $config;
    }
}
