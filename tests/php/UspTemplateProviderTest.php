<?php

declare(strict_types=1);

namespace SilverShop\Usp\Tests;

use SilverShop\Usp\Usp;
use SilverShop\Usp\UspTemplateProvider;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * The TemplateGlobalProvider API: $USPs('location'), $AnnouncementBar and $PaymentBadges, including the bar's
 * enable / empty / rotate / dismiss-hash logic. Core-free.
 */
class UspTemplateProviderTest extends SapphireTest
{
    protected $usesDatabase = true;

    private function config(array $data = []): SiteConfig
    {
        $config = SiteConfig::current_site_config();
        $config->update($data);
        $config->write();

        return $config;
    }

    private function announcement(string $title, int $sort = 0): Usp
    {
        $usp = Usp::create(['Title' => $title, 'Placements' => 'announcement', 'Active' => 1, 'Sort' => $sort]);
        $usp->write();

        return $usp;
    }

    public function testUSPsDelegatesToPlacementQuery(): void
    {
        $usp = Usp::create(['Title' => 'Grid item', 'Placements' => 'home', 'Active' => 1]);
        $usp->write();

        $this->assertSame(['Grid item'], UspTemplateProvider::USPs('home')->column('Title'));
        $this->assertCount(0, UspTemplateProvider::USPs('footer'));
    }

    public function testAnnouncementBarNullWhenDisabled(): void
    {
        $this->config(['AnnouncementBarEnabled' => 0]);
        $this->announcement('Hello');

        $this->assertNull(UspTemplateProvider::AnnouncementBar());
    }

    public function testAnnouncementBarNullWhenNoMessages(): void
    {
        $this->config(['AnnouncementBarEnabled' => 1]);

        $this->assertNull(UspTemplateProvider::AnnouncementBar());
    }

    public function testAnnouncementBarReturnsMessagesAndFlags(): void
    {
        $this->config([
            'AnnouncementBarEnabled' => 1,
            'AnnouncementDismissible' => 1,
            'AnnouncementRotate' => 1,
            'AnnouncementRotateSeconds' => 8,
        ]);
        $this->announcement('First', 1);
        $this->announcement('Second', 2);

        $bar = UspTemplateProvider::AnnouncementBar();
        $this->assertNotNull($bar);
        $this->assertTrue((bool) $bar->HasMessages);
        $this->assertSame(2, (int) $bar->Count);
        $this->assertTrue((bool) $bar->Multiple);
        $this->assertTrue((bool) $bar->Dismissible);
        $this->assertTrue((bool) $bar->Rotate);
        $this->assertSame(8, (int) $bar->RotateSeconds);
        $this->assertSame(8000, (int) $bar->RotateMs);
        $this->assertSame('First', $bar->FirstMessage->Title);
        $this->assertNotEmpty($bar->Hash);
    }

    public function testRotateRequiresMultipleMessages(): void
    {
        $this->config(['AnnouncementBarEnabled' => 1, 'AnnouncementRotate' => 1]);
        $this->announcement('Only one');

        $bar = UspTemplateProvider::AnnouncementBar();
        $this->assertNotNull($bar);
        $this->assertFalse((bool) $bar->Rotate);
        $this->assertFalse((bool) $bar->Multiple);
    }

    public function testRotateSecondsFloorsToSix(): void
    {
        $this->config(['AnnouncementBarEnabled' => 1, 'AnnouncementRotateSeconds' => 0]);
        $this->announcement('One');

        $bar = UspTemplateProvider::AnnouncementBar();
        $this->assertSame(6, (int) $bar->RotateSeconds);
    }

    public function testDismissHashChangesWithMessageSet(): void
    {
        $this->config(['AnnouncementBarEnabled' => 1]);
        $a = $this->announcement('Alpha', 1);
        $hashOne = UspTemplateProvider::AnnouncementBar()->Hash;

        $this->announcement('Beta', 2);
        $hashTwo = UspTemplateProvider::AnnouncementBar()->Hash;
        $this->assertNotSame($hashOne, $hashTwo, 'A new message changes the dismiss hash.');

        // Removing the new message restores the original hash (stable for the same set).
        $a->Title = 'Alpha';
        Usp::get()->filter('Title', 'Beta')->first()->delete();
        $hashThree = UspTemplateProvider::AnnouncementBar()->Hash;
        $this->assertSame($hashOne, $hashThree);
    }

    public function testPaymentBadgesNullWhenNothingEnabled(): void
    {
        $this->config(['EnabledPaymentBadges' => null, 'ShowSecurityBadge' => 0]);

        $this->assertNull(UspTemplateProvider::PaymentBadges());
    }

    public function testPaymentBadgesReturnsEnabledMarks(): void
    {
        $this->config(['EnabledPaymentBadges' => 'ideal,visa', 'ShowSecurityBadge' => 1]);

        $badges = UspTemplateProvider::PaymentBadges();
        $this->assertNotNull($badges);
        $this->assertTrue((bool) $badges->HasBadges);
        $this->assertTrue((bool) $badges->ShowSecurity);
        $this->assertSame(['ideal', 'visa'], $badges->Badges->column('Key'));
        $this->assertStringContainsString('<svg', (string) $badges->SecuritySVG->getValue());
    }

    public function testPaymentBadgesSecurityOnlyStillRenders(): void
    {
        $this->config(['EnabledPaymentBadges' => null, 'ShowSecurityBadge' => 1]);

        $badges = UspTemplateProvider::PaymentBadges();
        $this->assertNotNull($badges);
        $this->assertFalse((bool) $badges->HasBadges);
        $this->assertTrue((bool) $badges->ShowSecurity);
    }
}
