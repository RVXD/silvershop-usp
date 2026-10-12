<%-- Parametric USP strip: a horizontal row of icon+text items for one placement. Reused across the PDP, cart,
     checkout and footer. Pass the placement key as Location, e.g.
         <% include SilverShop/Usp/UspStrip Location='pdp' %>
     The pdp/cart/checkout placements are only reachable when silvershop/core hosts their include; this file works
     the same regardless. Renders nothing when there are no active items for the placement. --%>
<% if $USPs($Location).Count %>
<style>
    .usp-strip { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 1.25em; }
    .usp-strip__cell { margin: 0; padding: 0; }
    .usp-strip .usp-item { display: flex; align-items: center; gap: 0.5em; }
    .usp-strip .usp-item__icon { flex: 0 0 auto; display: inline-flex; width: 1.4em; height: 1.4em; }
    .usp-strip .usp-item__icon svg, .usp-strip .usp-item__icon-img { width: 100%; height: 100%; }
    .usp-strip .usp-item__body { display: inline-flex; flex-direction: column; }
    .usp-strip .usp-item__title { font-weight: 600; }
    .usp-strip .usp-item__text { opacity: 0.8; font-size: 0.9em; }
    .usp-strip .usp-item__link { color: inherit; text-decoration: none; }
    .usp-strip .usp-item__link:hover .usp-item__title { text-decoration: underline; }
</style>
<ul class="usp-strip usp-strip--$Location">
    <% loop $USPs($Location) %>
        <li class="usp-strip__cell"><% include SilverShop/Usp/UspItem %></li>
    <% end_loop %>
</ul>
<% end_if %>
