<?php

declare(strict_types=1);

namespace SilverShop\Usp;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Seeds a small set of demo USP items (the storefront mockup's value props + an announcement) and enables a few
 * payment badges, so a fresh install shows something immediately. Safe to run once: it does nothing when USP items
 * already exist.
 */
class PopulateUspTask extends BuildTask
{
    protected string $title = 'SilverShop USP: seed demo content';

    protected static string $description =
        'Creates demo USP items (value props + an announcement) and enables sample payment badges. No-op if any USP exists.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        if (Usp::get()->exists()) {
            $output->writeln('USP items already exist — nothing seeded.');
            return Command::SUCCESS;
        }

        $items = [
            ['Title' => 'Free shipping from €75', 'Text' => 'On all orders over €75', 'IconKey' => 'truck', 'Placements' => 'home,pdp,footer'],
            ['Title' => '30-day returns', 'Text' => 'No-questions-asked returns', 'IconKey' => 'return', 'Placements' => 'home,pdp,cart'],
            ['Title' => 'Repair-first warranty', 'Text' => 'We fix before we replace', 'IconKey' => 'shield', 'Placements' => 'home,pdp'],
            ['Title' => 'Made responsibly', 'Text' => 'Durable, sustainable materials', 'IconKey' => 'leaf', 'Placements' => 'home,footer'],
            ['Title' => 'Secure payment', 'Text' => 'Encrypted checkout', 'IconKey' => 'lock', 'Placements' => 'footer,checkout'],
            ['Title' => 'Open studio day — Saturday 10:00–16:00', 'Text' => '', 'IconKey' => 'clock', 'Placements' => 'announcement'],
            ['Title' => 'Free shipping on orders over €75', 'Text' => '', 'IconKey' => 'truck', 'Placements' => 'announcement'],
        ];

        $sort = 0;
        foreach ($items as $data) {
            $usp = Usp::create($data);
            $usp->Active = true;
            $usp->Sort = ++$sort;
            $usp->write();
        }

        $output->writeln('Seeded ' . count($items) . ' demo USP item(s).');

        $config = SiteConfig::current_site_config();
        if (trim((string) $config->EnabledPaymentBadges) === '') {
            $config->EnabledPaymentBadges = 'ideal,bancontact,visa,mastercard,paypal';
            $config->ShowSecurityBadge = true;
            $config->write();
            $output->writeln('Enabled sample payment badges on SiteConfig.');
        }

        return Command::SUCCESS;
    }
}
