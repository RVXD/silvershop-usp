<%-- Topbar announcement ribbon. Drop it in the theme's topbar: <% include SilverShop/Usp/AnnouncementBar %>.
     Renders the active "announcement" USP items subject to the SiteConfig behaviour knobs. Progressive enhancement:
     with no JavaScript it shows the first message (others are hidden); JS adds dismiss (remembered per message set)
     and, when enabled, rotation that honours prefers-reduced-motion. Minimal self-styled (em units) — restyle the
     BEM classes from your theme, or override this file. --%>
<% with $AnnouncementBar %>
<% if $HasMessages %>
<style>
    .usp-announcement { display: flex; align-items: center; justify-content: center; gap: 1em; padding: 0.5em 1em; font-size: 0.95em; position: relative; }
    .usp-announcement__viewport { display: flex; align-items: center; justify-content: center; text-align: center; min-height: 1.4em; }
    .usp-announcement__msg { display: inline-flex; align-items: center; gap: 0.5em; }
    .usp-announcement__msg[hidden] { display: none; }
    .usp-announcement__icon { display: inline-flex; width: 1.2em; height: 1.2em; }
    .usp-announcement__icon svg { width: 100%; height: 100%; }
    .usp-announcement__link { color: inherit; text-decoration: underline; }
    .usp-announcement__text { opacity: 0.85; }
    .usp-announcement__close { position: absolute; right: 0.5em; top: 50%; transform: translateY(-50%); background: none; border: 0; color: inherit; font-size: 1.2em; line-height: 1; cursor: pointer; padding: 0.2em 0.4em; }
    .usp-announcement[hidden] { display: none; }
</style>
<div class="usp-announcement" data-usp-announcement data-hash="$Hash"<% if $Dismissible %> data-dismissible="1"<% end_if %><% if $Rotate %> data-rotate="$RotateMs"<% end_if %>>
    <div class="usp-announcement__viewport">
        <% loop $Messages %>
            <div class="usp-announcement__msg<% if $First %> is-active<% end_if %>"<% if not $First %> hidden<% end_if %>>
                <% if $IconSVG %><span class="usp-announcement__icon">$IconSVG.RAW</span><% end_if %>
                <% if $LinkURL %><a class="usp-announcement__link" href="$LinkURL.ATT"<% if $OpenInNew %> target="_blank" rel="noopener"<% end_if %>><% end_if %>
                    <span class="usp-announcement__title">$Title</span>
                    <% if $Text %> <span class="usp-announcement__text">$Text</span><% end_if %>
                <% if $LinkURL %></a><% end_if %>
            </div>
        <% end_loop %>
    </div>
    <% if $Dismissible %><button type="button" class="usp-announcement__close" data-usp-dismiss aria-label="<%t SilverShop\Usp\UspTemplateProvider.Dismiss 'Dismiss' %>">&times;</button><% end_if %>
</div>
<script>
(function () {
    var script = document.currentScript;
    var bar = script && script.previousElementSibling;
    if (!bar || !bar.hasAttribute('data-usp-announcement')) { return; }

    var hash = bar.getAttribute('data-hash') || '';
    var storageKey = 'usp-announcement-dismissed';

    // Remembered dismissal, keyed by the active message set: a new set (new hash) shows again.
    try {
        if (bar.getAttribute('data-dismissible') && window.localStorage.getItem(storageKey) === hash) {
            bar.setAttribute('hidden', '');
            return;
        }
    } catch (e) {}

    var closeBtn = bar.querySelector('[data-usp-dismiss]');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            bar.setAttribute('hidden', '');
            try { window.localStorage.setItem(storageKey, hash); } catch (e) {}
        });
    }

    var interval = parseInt(bar.getAttribute('data-rotate'), 10);
    var messages = bar.querySelectorAll('.usp-announcement__msg');
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (interval && messages.length > 1 && !reduce) {
        var current = 0;
        setInterval(function () {
            messages[current].setAttribute('hidden', '');
            messages[current].classList.remove('is-active');
            current = (current + 1) % messages.length;
            messages[current].removeAttribute('hidden');
            messages[current].classList.add('is-active');
        }, interval);
    }
})();
</script>
<% end_if %>
<% end_with %>
