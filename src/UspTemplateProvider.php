<?php

declare(strict_types=1);

namespace SilverShop\Usp;

use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Exposes the USP accessors in ANY template — including the theme's page-less header/footer shells, which are not
 * bound to a page model. Auto-registered by implementing {@link TemplateGlobalProvider} (no YAML needed).
 *
 *   $USPs('home')          the active, in-schedule items for a placement, in sort order
 *   $AnnouncementBar       a view model for the topbar ribbon (messages + behaviour flags + a message-set hash), or
 *                          nothing when the bar is disabled or empty
 *   $PaymentBadges         the enabled payment marks + optional security badge, or nothing
 *
 * The same data is also reachable as $SiteConfig.USPs('home') / $SiteConfig.AnnouncementBar via
 * {@link UspSiteConfigExtension} for themes that prefer the zero-extra-global route.
 */
class UspTemplateProvider implements TemplateGlobalProvider
{
    public static function get_template_global_variables(): array
    {
        return [
            'USPs' => 'USPs',
            'AnnouncementBar' => 'AnnouncementBar',
            'PaymentBadges' => 'PaymentBadges',
        ];
    }

    /**
     * Active, in-schedule items for a placement, in sort order.
     */
    public static function USPs(string $location): ArrayList
    {
        return Usp::activeForPlacement($location);
    }

    /**
     * A view model for the announcement bar, or null when it should not render (disabled, or no active messages).
     */
    public static function AnnouncementBar(): ?ArrayData
    {
        $config = SiteConfig::current_site_config();
        if (!$config->AnnouncementBarEnabled) {
            return null;
        }

        $messages = Usp::activeForPlacement('announcement');
        if ($messages->count() === 0) {
            return null;
        }

        $seconds = (int) $config->AnnouncementRotateSeconds;
        if ($seconds < 2) {
            $seconds = 6;
        }

        return ArrayData::create([
            'Messages' => $messages,
            'FirstMessage' => $messages->first(),
            'HasMessages' => true,
            'Multiple' => $messages->count() > 1,
            'Count' => $messages->count(),
            'Dismissible' => (bool) $config->AnnouncementDismissible,
            'Rotate' => (bool) $config->AnnouncementRotate && $messages->count() > 1,
            'RotateSeconds' => $seconds,
            'RotateMs' => $seconds * 1000,
            'Hash' => self::messageSetHash($messages),
        ]);
    }

    /**
     * A view model for the payment/security badges, or null when there is nothing to show.
     */
    public static function PaymentBadges(): ?ArrayData
    {
        $config = SiteConfig::current_site_config();
        $library = UspPaymentBadges::singleton();
        $badges = $library->enabled((string) $config->EnabledPaymentBadges);
        $showSecurity = (bool) $config->ShowSecurityBadge;

        if ($badges->count() === 0 && !$showSecurity) {
            return null;
        }

        return ArrayData::create([
            'Badges' => $badges,
            'HasBadges' => $badges->count() > 0,
            'ShowSecurity' => $showSecurity,
            'SecuritySVG' => $library->securitySvg(),
        ]);
    }

    /**
     * A short, stable hash of the active message set, so a dismissed bar re-shows when the messages change.
     */
    private static function messageSetHash(ArrayList $messages): string
    {
        $parts = [];
        foreach ($messages as $message) {
            $parts[] = $message->ID . ':' . $message->Title;
        }

        return substr(sha1(implode('|', $parts)), 0, 12);
    }
}
