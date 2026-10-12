<%-- Payment-method + security badges (footer / checkout). Drop it where the theme wants the marks:
     <% include SilverShop/Usp/PaymentBadges %>. Which marks show is set in CMS → Settings → USPs. The shipped
     marks are neutral placeholders (not brand logos) — override UspPaymentBadges config to ship official artwork.
     Minimal self-styled (em units); renders nothing when nothing is enabled. --%>
<% with $PaymentBadges %>
<style>
    .usp-badges { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5em; }
    .usp-badges__list { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; align-items: center; gap: 0.5em; }
    .usp-badges__item { display: inline-flex; align-items: center; opacity: 0.75; }
    .usp-badges__item svg { height: 1.9em; width: auto; }
    .usp-badges__security { display: inline-flex; align-items: center; opacity: 0.75; }
    .usp-badges__security svg { height: 1.9em; width: auto; }
</style>
<div class="usp-badges">
    <% if $HasBadges %>
    <ul class="usp-badges__list">
        <% loop $Badges %>
            <li class="usp-badges__item" title="$Label.ATT">$SVG.RAW</li>
        <% end_loop %>
    </ul>
    <% end_if %>
    <% if $ShowSecurity %><div class="usp-badges__security">$SecuritySVG.RAW</div><% end_if %>
</div>
<% end_with %>
