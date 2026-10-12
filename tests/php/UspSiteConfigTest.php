<?php

declare(strict_types=1);

namespace SilverShop\Usp\Tests;

use SilverShop\Usp\Usp;
use SilverShop\Usp\UspPaymentBadges;
use SilverShop\Usp\UspSiteConfigExtension;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * The SiteConfig behaviour extension: the knobs persist, the CMS fields are added, the fallback accessors work, and
 * the payment-badge library resolves enabled marks.
 */
class UspSiteConfigTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testExtensionApplied(): void
    {
        $this->assertTrue(SiteConfig::has_extension(UspSiteConfigExtension::class));
    }

    public function testKnobsPersist(): void
    {
        $config = SiteConfig::current_site_config();
        $config->AnnouncementBarEnabled = 1;
        $config->AnnouncementDismissible = 0;
        $config->AnnouncementRotate = 1;
        $config->AnnouncementRotateSeconds = 9;
        $config->EnabledPaymentBadges = 'ideal,klarna';
        $config->ShowSecurityBadge = 1;
        $config->write();

        $fresh = SiteConfig::get()->byID($config->ID);
        $this->assertTrue((bool) $fresh->AnnouncementBarEnabled);
        $this->assertFalse((bool) $fresh->AnnouncementDismissible);
        $this->assertSame(9, (int) $fresh->AnnouncementRotateSeconds);
        $this->assertSame('ideal,klarna', $fresh->EnabledPaymentBadges);
        $this->assertTrue((bool) $fresh->ShowSecurityBadge);
    }

    public function testCMSFieldsAdded(): void
    {
        $config = SiteConfig::current_site_config();
        $fields = $config->getCMSFields();

        $this->assertNotNull($fields->dataFieldByName('AnnouncementBarEnabled'));
        $this->assertNotNull($fields->dataFieldByName('EnabledPaymentBadges'));
        $this->assertNotNull($fields->dataFieldByName('ShowSecurityBadge'));
    }

    public function testFallbackAccessorsOnSiteConfig(): void
    {
        $config = SiteConfig::current_site_config();
        $config->AnnouncementBarEnabled = 1;
        $config->write();

        $usp = Usp::create(['Title' => 'Footer item', 'Placements' => 'footer', 'Active' => 1]);
        $usp->write();

        $this->assertSame(['Footer item'], $config->USPs('footer')->column('Title'));
        $this->assertCount(0, $config->USPs('home'));
    }

    public function testPaymentBadgeLibraryEnabled(): void
    {
        $badges = UspPaymentBadges::singleton()->enabled('visa,paypal,bogus');

        // Returns the known marks, in the library's order, skipping unknown keys.
        $this->assertSame(['visa', 'paypal'], $badges->column('Key'));
        foreach ($badges as $badge) {
            $this->assertStringContainsString('<svg', (string) $badge->SVG->getValue());
        }
    }

    public function testPaymentBadgeLibraryEmpty(): void
    {
        $this->assertCount(0, UspPaymentBadges::singleton()->enabled(''));
        $this->assertCount(0, UspPaymentBadges::singleton()->enabled(null));
    }
}
