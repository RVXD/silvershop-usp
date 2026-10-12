<?php

declare(strict_types=1);

namespace SilverShop\Usp;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * A known-marks library of payment-method badges plus a security/SSL badge, rendered inline so they inherit
 * `currentColor`. The shipped marks are deliberately NEUTRAL wordmark placeholders (a bordered chip with the scheme
 * name), not the official brand logos — a project that wants the real artwork overrides the relevant key via YAML on
 * {@link UspPaymentBadges} (or swaps the whole `badges` map). Which badges show is a SiteConfig multi-select
 * ({@link UspSiteConfigExtension::$db} `EnabledPaymentBadges`); the comma-separated enum there must stay in step with
 * the keys below.
 */
class UspPaymentBadges
{
    use Injectable;
    use Configurable;

    /**
     * The payment-badge enum values, in display order. Mirror this in the SiteConfig MultiEnum column.
     */
    public const KEYS = 'ideal,bancontact,visa,mastercard,amex,paypal,applepay,googlepay,klarna';

    /**
     * Map of badge key => ['label' => string, 'svg' => string]. Override via YAML to ship official artwork.
     *
     * @var array<string, array{label: string, svg: string}>
     */
    private static array $badges = [
        'ideal' => ['label' => 'iDEAL', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="iDEAL"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><text x="24" y="19" text-anchor="middle" font-family="system-ui,sans-serif" font-size="9" font-weight="700" fill="currentColor">iDEAL</text></svg>'],
        'bancontact' => ['label' => 'Bancontact', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="Bancontact"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><text x="24" y="18" text-anchor="middle" font-family="system-ui,sans-serif" font-size="6" font-weight="600" fill="currentColor">Bancontact</text></svg>'],
        'visa' => ['label' => 'Visa', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="Visa"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><text x="24" y="19" text-anchor="middle" font-family="system-ui,sans-serif" font-size="9" font-weight="700" font-style="italic" fill="currentColor">VISA</text></svg>'],
        'mastercard' => ['label' => 'Mastercard', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="Mastercard"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><circle cx="20" cy="15" r="6" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.8"/><circle cx="28" cy="15" r="6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>'],
        'amex' => ['label' => 'American Express', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="American Express"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><text x="24" y="18" text-anchor="middle" font-family="system-ui,sans-serif" font-size="8" font-weight="700" fill="currentColor">AMEX</text></svg>'],
        'paypal' => ['label' => 'PayPal', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="PayPal"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><text x="24" y="19" text-anchor="middle" font-family="system-ui,sans-serif" font-size="8" font-weight="700" font-style="italic" fill="currentColor">PayPal</text></svg>'],
        'applepay' => ['label' => 'Apple Pay', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="Apple Pay"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><text x="24" y="19" text-anchor="middle" font-family="system-ui,sans-serif" font-size="7.5" font-weight="600" fill="currentColor">Apple Pay</text></svg>'],
        'googlepay' => ['label' => 'Google Pay', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="Google Pay"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><text x="24" y="19" text-anchor="middle" font-family="system-ui,sans-serif" font-size="7" font-weight="600" fill="currentColor">Google Pay</text></svg>'],
        'klarna' => ['label' => 'Klarna', 'svg' => '<svg class="usp-badge__svg" viewBox="0 0 48 30" role="img" aria-label="Klarna"><rect x="1" y="1" width="46" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><text x="24" y="19" text-anchor="middle" font-family="system-ui,sans-serif" font-size="8" font-weight="700" fill="currentColor">Klarna</text></svg>'],
    ];

    /**
     * The security/SSL reassurance badge (a padlock + "Secure" wording). Shown via SiteConfig.ShowSecurityBadge.
     */
    private static string $security_svg = '<svg class="usp-badge__svg usp-badge__svg--security" viewBox="0 0 64 30" role="img" aria-label="Secure checkout"><rect x="1" y="1" width="62" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><rect x="8" y="13" width="9" height="7" rx="1.2" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M10 13v-2a2.5 2.5 0 0 1 5 0v2" fill="none" stroke="currentColor" stroke-width="1.5"/><text x="38" y="19" text-anchor="middle" font-family="system-ui,sans-serif" font-size="8" font-weight="600" fill="currentColor">Secure</text></svg>';

    /**
     * @return array<string, array{label: string, svg: string}>
     */
    public function badges(): array
    {
        return (array) $this->config()->get('badges');
    }

    /**
     * Dropdown/checkbox options: key => label.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];
        foreach ($this->badges() as $key => $badge) {
            $options[$key] = _t(self::class . '.badge_' . $key, $badge['label'] ?? $key);
        }

        return $options;
    }

    /**
     * The enabled badges (in the library's defined order) as a render-ready list of ArrayData {Key, Label, SVG}.
     *
     * @param string|null $enabledCsv The SiteConfig MultiEnum value (comma-separated keys).
     */
    public function enabled(?string $enabledCsv): ArrayList
    {
        $wanted = array_filter(array_map('trim', explode(',', (string) $enabledCsv)));
        $list = ArrayList::create();
        if ($wanted === []) {
            return $list;
        }

        foreach ($this->badges() as $key => $badge) {
            if (!in_array($key, $wanted, true)) {
                continue;
            }
            $list->push(ArrayData::create([
                'Key' => $key,
                'Label' => _t(self::class . '.badge_' . $key, $badge['label'] ?? $key),
                'SVG' => DBHTMLText::create()->setValue($badge['svg'] ?? ''),
            ]));
        }

        return $list;
    }

    public function securitySvg(): DBHTMLText
    {
        return DBHTMLText::create()->setValue((string) $this->config()->get('security_svg'));
    }
}
