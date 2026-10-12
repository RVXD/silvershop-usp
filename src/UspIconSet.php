<?php

declare(strict_types=1);

namespace SilverShop\Usp;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

/**
 * A named set of inline SVG icons for USP items. Each icon is a self-contained `<svg>` using `currentColor`, so it
 * inherits the surrounding text colour and theme tokens — no sprite sheet, no extra request, no hard-coded size
 * (the includes size it in em). The set is config-driven: a project can add or replace entries (or override a label)
 * via YAML against {@link UspIconSet}.
 *
 * Icons are simple, original geometric marks — not brand logos. For a per-item custom mark, upload an image on the
 * Usp record instead (that takes precedence over the chosen key).
 */
class UspIconSet
{
    use Injectable;
    use Configurable;

    /**
     * Map of icon key => inline SVG markup. Override or extend via YAML.
     *
     * @var array<string, string>
     */
    private static array $icons = [
        'truck' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 6h11v9H3z"/><path d="M14 9h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg>',
        'return' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 8h10a5 5 0 0 1 0 10H9"/><path d="M7 5 4 8l3 3"/></svg>',
        'shield' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3 5 6v6c0 4 3 7 7 9 4-2 7-5 7-9V6z"/><path d="m9 12 2 2 4-4"/></svg>',
        'leaf' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 19c0-8 6-14 14-14 0 8-6 14-14 14Z"/><path d="M5 19C8 15 12 12 16 10"/></svg>',
        'lock' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>',
        'headset' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/><path d="M20 19a4 4 0 0 1-4 4h-3"/></svg>',
        'star' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m12 3 2.7 5.6 6.1.9-4.4 4.4 1 6.1L12 17.8 6.6 20l1-6.1L3.2 9.5l6.1-.9z"/></svg>',
        'check' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>',
        'gift' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="4" y="9" width="16" height="11" rx="1"/><path d="M4 13h16M12 9v11"/><path d="M12 9S10 3 7.5 4.5 12 9 12 9Zm0 0s2-6 4.5-4.5S12 9 12 9Z"/></svg>',
        'clock' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        'tag' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 4h7l9 9-7 7-9-9z"/><circle cx="8.5" cy="8.5" r="1.4"/></svg>',
        'heart' => '<svg class="usp-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 20S4 15 4 9a4 4 0 0 1 8-1 4 4 0 0 1 8 1c0 6-8 11-8 11Z"/></svg>',
    ];

    /**
     * Default English labels for the shipped keys. Any key missing here falls back to a humanised key.
     *
     * @var array<string, string>
     */
    private static array $default_labels = [
        'truck' => 'Shipping',
        'return' => 'Returns',
        'shield' => 'Warranty',
        'leaf' => 'Sustainable',
        'lock' => 'Secure payment',
        'headset' => 'Support',
        'star' => 'Rating',
        'check' => 'Guarantee',
        'gift' => 'Gift / promo',
        'clock' => 'Fast delivery',
        'tag' => 'Price / deal',
        'heart' => 'Loved',
    ];

    /**
     * @return array<string, string>
     */
    public function icons(): array
    {
        return (array) $this->config()->get('icons');
    }

    /**
     * The raw inline SVG for a key, or null when the key is unknown.
     */
    public function svg(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        return $this->icons()[$key] ?? null;
    }

    /**
     * Dropdown options: key => human label.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];
        foreach (array_keys($this->icons()) as $key) {
            $options[$key] = $this->label($key);
        }
        asort($options);

        return $options;
    }

    public function label(string $key): string
    {
        $labels = (array) $this->config()->get('default_labels');
        $fallback = $labels[$key] ?? ucwords(str_replace(['-', '_'], ' ', $key));

        return _t(self::class . '.icon_' . $key, $fallback);
    }
}
