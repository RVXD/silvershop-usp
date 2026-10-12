<?php

declare(strict_types=1);

namespace SilverShop\Usp\Tests;

use SilverShop\Usp\Usp;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\TemplateEngine\SSTemplateEngine;
use SilverStripe\View\ViewLayerData;

/**
 * Live-equivalent check: render the shipped includes through the real template engine and the auto-registered
 * global accessors, and assert the markup (and the degrade-to-nothing behaviour). This is the same path the theme
 * uses on the storefront, exercised headlessly.
 */
class UspRenderTest extends SapphireTest
{
    protected $usesDatabase = true;

    private function render(string $template): string
    {
        $engine = SSTemplateEngine::create();
        $model = ViewLayerData::create(SiteConfig::current_site_config());

        return $engine->renderString($template, $model, [], false);
    }

    private function makeUsp(array $data): Usp
    {
        $usp = Usp::create($data);
        $usp->write();

        return $usp;
    }

    public function testGridRendersActiveItems(): void
    {
        $this->makeUsp(['Title' => 'Free shipping', 'Text' => 'Over €75', 'IconKey' => 'truck', 'Placements' => 'home', 'Active' => 1, 'Sort' => 1]);
        $this->makeUsp(['Title' => '30-day returns', 'Placements' => 'home', 'Active' => 1, 'Sort' => 2]);

        $html = $this->render('<% include SilverShop/Usp/UspGrid %>');

        $this->assertStringContainsString('usp-grid', $html);
        $this->assertStringContainsString('Free shipping', $html);
        $this->assertStringContainsString('30-day returns', $html);
        // Inline, themeable icon.
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('currentColor', $html);
    }

    public function testGridRendersNothingWhenEmpty(): void
    {
        $html = $this->render('<% include SilverShop/Usp/UspGrid %>');

        $this->assertStringNotContainsString('usp-grid', $html);
        $this->assertSame('', trim($html));
    }

    public function testStripRendersForFooterPlacement(): void
    {
        $this->makeUsp(['Title' => 'Secure payment', 'Placements' => 'footer', 'Active' => 1]);
        $this->makeUsp(['Title' => 'Home only', 'Placements' => 'home', 'Active' => 1]);

        $html = $this->render("<% include SilverShop/Usp/UspStrip Location='footer' %>");

        $this->assertStringContainsString('usp-strip', $html);
        $this->assertStringContainsString('Secure payment', $html);
        $this->assertStringNotContainsString('Home only', $html);
    }

    public function testAnnouncementBarRendersWhenEnabled(): void
    {
        $config = SiteConfig::current_site_config();
        $config->AnnouncementBarEnabled = 1;
        $config->AnnouncementDismissible = 1;
        $config->write();
        $this->makeUsp(['Title' => 'Open studio day', 'Placements' => 'announcement', 'Active' => 1]);

        $html = $this->render('<% include SilverShop/Usp/AnnouncementBar %>');

        $this->assertStringContainsString('usp-announcement', $html);
        $this->assertStringContainsString('Open studio day', $html);
        $this->assertStringContainsString('data-usp-dismiss', $html);
        $this->assertStringContainsString('data-hash', $html);
    }

    public function testAnnouncementBarRendersNothingWhenDisabled(): void
    {
        $config = SiteConfig::current_site_config();
        $config->AnnouncementBarEnabled = 0;
        $config->write();
        $this->makeUsp(['Title' => 'Hidden', 'Placements' => 'announcement', 'Active' => 1]);

        $html = $this->render('<% include SilverShop/Usp/AnnouncementBar %>');

        $this->assertStringNotContainsString('usp-announcement', $html);
        $this->assertSame('', trim($html));
    }

    public function testPaymentBadgesRender(): void
    {
        $config = SiteConfig::current_site_config();
        $config->EnabledPaymentBadges = 'ideal,visa';
        $config->ShowSecurityBadge = 1;
        $config->write();

        $html = $this->render('<% include SilverShop/Usp/PaymentBadges %>');

        $this->assertStringContainsString('usp-badges', $html);
        $this->assertStringContainsString('aria-label="iDEAL"', $html);
        $this->assertStringContainsString('aria-label="Secure checkout"', $html);
    }
}
