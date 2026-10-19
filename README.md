# silvershop/usp

CMS-managed **USPs, trust signals and an announcement / promo bar** for SilverStripe. It lifts the shop's value
propositions and promo messaging out of the theme and into the CMS, so a merchant edits them without touching
templates. One backend — a `Usp` DataObject set plus a thin SiteConfig behaviour knob — feeds **multiple storefront
placements** through small, overridable `.ss` includes: the topbar announcement bar, the home USP grid, the product
page / cart / checkout trust strips, and the footer (payment + security badges).

- **Works on any SilverStripe site.** `silvershop/core` is a *soft* dependency: the USP items, the announcement bar,
  and the home / footer placements work everywhere. The shop-specific placements (product page / cart / checkout) are
  simply regions a shop theme drops the strip include into — nothing here hard-requires core.
- **The theme hosts and styles; the module owns the content and behaviour.** The includes are minimal and self-styled
  (plain BEM classes, `currentColor` icons); your theme restyles the classes or overrides the files.

## Requirements

- PHP 8.3+
- `silverstripe/framework` ^6, `silverstripe/cms` ^6, `silverstripe/admin` ^3, `silverstripe/siteconfig` ^6,
  `silverstripe/asset-admin` ^3

Optional (feature-detected, never required):

- `silvershop/core` — the product-page / cart / checkout placements.
- `symbiote/silverstripe-gridfieldextensions` — drag-and-drop ordering of the USP list (ships with silvershop/core).
- `tractorcow/silverstripe-fluent` — localises the USP content fields.

Already using Elemental? The globals and includes work inside any Elemental block template today (e.g. drop
`<% include SilverShop/Usp/UspStrip Location='home' %>` into a block). A dedicated USP *block* is on the roadmap
(see below).

## Install

```bash
composer require silvershop/usp
```

Run `dev/build?flush=1`, then manage items under **CMS → USPs** and the bar behaviour under
**CMS → Settings → USPs**. To drop in some demo content, run the seed task once:

```bash
vendor/bin/sake tasks:SilverShop-Usp-PopulateUspTask
```

## Managing content

Each **USP** item has a title / message, optional supporting text, an icon (a themeable inline icon chosen from the
set, or a custom uploaded image), an optional link, a set of **placements**, an **Active** flag, a sort order, and an
optional **Visible from / until** schedule window.

The announcement-bar *behaviour* lives on SiteConfig (CMS → Settings → USPs): enable the bar, allow dismissal, rotate
between messages (and the interval), plus which payment badges and the secure-checkout badge show.

## Theme integration contract

The module exposes two globals in **any** template (including page-less header / footer shells), plus a SiteConfig
fallback (`$SiteConfig.USPs(...)`, `$SiteConfig.AnnouncementBar`, `$SiteConfig.PaymentBadges`):

| Global | Returns |
|---|---|
| `$USPs('<placement>')` | the active, in-schedule items for a placement, in sort order |
| `$AnnouncementBar` | a view model for the ribbon (messages + behaviour flags + a message-set hash), or nothing |
| `$PaymentBadges` | the enabled payment marks + optional security badge, or nothing |

Placement keys: `announcement`, `home`, `pdp`, `cart`, `checkout`, `footer`.

Drop the includes where the theme wants each region. Every include renders **nothing** when its data is empty, so an
un-populated region simply disappears:

| Surface | Drop in the theme | Degrades to |
|---|---|---|
| Topbar announcement | `<% include SilverShop/Usp/AnnouncementBar %>` | no ribbon |
| Home USP grid | `<% include SilverShop/Usp/UspGrid %>` | empty region |
| Product page strip | `<% include SilverShop/Usp/UspStrip Location='pdp' %>` | no strip |
| Cart / checkout | `<% include SilverShop/Usp/UspStrip Location='cart' %>` / `Location='checkout'` | no strip |
| Footer | `<% include SilverShop/Usp/UspStrip Location='footer' %>` + `<% include SilverShop/Usp/PaymentBadges %>` | static footer |

`UspStrip` is one parametric include reused across pdp / cart / checkout / footer; `UspItem` is the shared
single-item partial. Override any of them by placing a file at the same path in your theme.

### Announcement bar behaviour

Progressive enhancement throughout:

- **Scheduling** — items outside their *Visible from / until* window are filtered out.
- **Dismissible** — remembered per visitor in `localStorage`, keyed to a hash of the active message set, so a *new*
  announcement re-shows after an old one was dismissed. No server state, consent-safe.
- **Multi / rotating** — several active announcement items rotate when rotation is on, honouring
  `prefers-reduced-motion`. With no JavaScript the first message always shows.

## Icons

Icons are a configurable named set of inline SVGs that inherit `currentColor` (no sprite, no extra request). Add or
replace entries via YAML:

```yaml
SilverShop\Usp\UspIconSet:
  icons:
    my-icon: '<svg class="usp-icon__svg" viewBox="0 0 24 24" ...>...</svg>'
```

Each USP may instead upload a custom icon image, which takes precedence over the chosen key.

## Payment & security badges

The shipped payment marks are **neutral placeholders** (a bordered chip with the scheme name), not the official brand
logos. To ship official artwork, override the relevant key (or the whole map) via YAML:

```yaml
SilverShop\Usp\UspPaymentBadges:
  badges:
    ideal:
      label: 'iDEAL'
      svg: '<svg ...>...official mark...</svg>'
```

## Roadmap

- **Dedicated Elemental USP block.** Planned as a small companion package (`silvershop/usp-elemental`) that requires
  both this module and `dnadesign/silverstripe-elemental`. It cannot live in this module: a `BaseElement` subclass
  shipped here would either break sites that do *not* have Elemental (the config manifest reflects on every class and
  fatals on the missing `BaseElement` parent) or, if hidden from the manifest to avoid that, would be invisible to
  Elemental's block discovery. A companion package keeps this module Elemental-optional. In the meantime the includes
  above work inside any Elemental block template.

## License

BSD-3-Clause.

## Credits

Built and maintained by **Remy Vaartjes** (<remy@xd.nl>) — part of the **SilverShop-Suite**.
