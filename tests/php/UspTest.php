<?php

declare(strict_types=1);

namespace SilverShop\Usp\Tests;

use SilverShop\Usp\Usp;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * The Usp model: placement filtering, the schedule window, sort order and the small accessors. Core-free and
 * deterministic (a plain DataObject; mocked "now").
 */
class UspTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected function setUp(): void
    {
        parent::setUp();
        DBDatetime::set_mock_now('2026-06-15 12:00:00');
    }

    protected function tearDown(): void
    {
        DBDatetime::clear_mock_now();
        parent::tearDown();
    }

    private function makeUsp(array $data): Usp
    {
        $usp = Usp::create($data);
        $usp->write();

        return $usp;
    }

    public function testActiveForPlacementFiltersSortsAndSchedules(): void
    {
        $second = $this->makeUsp(['Title' => 'Second', 'Placements' => 'home', 'Active' => 1, 'Sort' => 2]);
        $first = $this->makeUsp(['Title' => 'First', 'Placements' => 'home', 'Active' => 1, 'Sort' => 1]);
        // Other placement only — excluded from home.
        $this->makeUsp(['Title' => 'PDP only', 'Placements' => 'pdp', 'Active' => 1, 'Sort' => 0]);
        // Inactive — excluded.
        $this->makeUsp(['Title' => 'Inactive', 'Placements' => 'home', 'Active' => 0, 'Sort' => 0]);
        // Not started yet — excluded.
        $this->makeUsp(['Title' => 'Future', 'Placements' => 'home', 'Active' => 1, 'Sort' => 3, 'StartDate' => '2026-07-01 00:00:00']);
        // Already ended — excluded.
        $this->makeUsp(['Title' => 'Past', 'Placements' => 'home', 'Active' => 1, 'Sort' => 4, 'EndDate' => '2026-06-01 00:00:00']);
        // In window — included.
        $inWindow = $this->makeUsp(['Title' => 'In window', 'Placements' => 'home', 'Active' => 1, 'Sort' => 5, 'StartDate' => '2026-06-01 00:00:00', 'EndDate' => '2026-07-01 00:00:00']);

        $titles = Usp::activeForPlacement('home')->column('Title');

        $this->assertSame(['First', 'Second', 'In window'], $titles);
        $this->assertSame([$first->ID, $second->ID, $inWindow->ID], array_map('intval', Usp::activeForPlacement('home')->column('ID')));
    }

    public function testMultiPlacementItemAppearsInEachPlacement(): void
    {
        $this->makeUsp(['Title' => 'Everywhere', 'Placements' => 'home,footer,pdp', 'Active' => 1, 'Sort' => 1]);

        $this->assertSame(['Everywhere'], Usp::activeForPlacement('home')->column('Title'));
        $this->assertSame(['Everywhere'], Usp::activeForPlacement('footer')->column('Title'));
        $this->assertSame(['Everywhere'], Usp::activeForPlacement('pdp')->column('Title'));
        $this->assertCount(0, Usp::activeForPlacement('cart'));
    }

    public function testHasPlacementAndList(): void
    {
        $usp = $this->makeUsp(['Title' => 'X', 'Placements' => 'home,footer']);

        $this->assertTrue($usp->hasPlacement('home'));
        $this->assertTrue($usp->hasPlacement('footer'));
        $this->assertFalse($usp->hasPlacement('pdp'));
        $this->assertSame(['home', 'footer'], $usp->getPlacementsList());
    }

    public function testIsScheduledNow(): void
    {
        $open = $this->makeUsp(['Title' => 'Open', 'Active' => 1]);
        $this->assertTrue($open->getIsScheduledNow());

        $future = $this->makeUsp(['Title' => 'Future', 'Active' => 1, 'StartDate' => '2026-07-01 00:00:00']);
        $this->assertFalse($future->getIsScheduledNow());

        $past = $this->makeUsp(['Title' => 'Past', 'Active' => 1, 'EndDate' => '2026-06-01 00:00:00']);
        $this->assertFalse($past->getIsScheduledNow());

        $window = $this->makeUsp(['Title' => 'Window', 'Active' => 1, 'StartDate' => '2026-06-01 00:00:00', 'EndDate' => '2026-07-01 00:00:00']);
        $this->assertTrue($window->getIsScheduledNow());
    }

    public function testStatusLabel(): void
    {
        $this->assertSame('Inactive', $this->makeUsp(['Title' => 'A', 'Active' => 0])->getStatusLabel());
        $this->assertSame('Active', $this->makeUsp(['Title' => 'B', 'Active' => 1])->getStatusLabel());
        $this->assertSame('Scheduled', $this->makeUsp(['Title' => 'C', 'Active' => 1, 'StartDate' => '2026-07-01 00:00:00'])->getStatusLabel());
        $this->assertSame('Expired', $this->makeUsp(['Title' => 'D', 'Active' => 1, 'EndDate' => '2026-06-01 00:00:00'])->getStatusLabel());
    }

    public function testIconSvgResolution(): void
    {
        $withIcon = $this->makeUsp(['Title' => 'A', 'IconKey' => 'truck']);
        $svg = $withIcon->getIconSVG();
        $this->assertNotNull($svg);
        $this->assertStringContainsString('<svg', (string) $svg->getValue());
        $this->assertStringContainsString('currentColor', (string) $svg->getValue());

        $noIcon = $this->makeUsp(['Title' => 'B']);
        $this->assertNull($noIcon->getIconSVG());

        $badKey = $this->makeUsp(['Title' => 'C', 'IconKey' => 'does-not-exist']);
        $this->assertNull($badKey->getIconSVG());
    }

    public function testPlacementsSummary(): void
    {
        $usp = $this->makeUsp(['Title' => 'A', 'Placements' => 'home,footer']);
        $this->assertSame('Home USP grid, Footer', $usp->getPlacementsSummary());
    }

    public function testCanViewIsPublic(): void
    {
        $this->assertTrue($this->makeUsp(['Title' => 'A'])->canView());
    }
}
