<%-- Home USP grid: the active "home" USP items as an icon+text grid. Drop it where the theme wants the grid, e.g.
     <% include SilverShop/Usp/UspGrid %>. Minimal self-styled (em units); override the file or just restyle the
     BEM classes from your theme. Renders nothing when there are no active items. --%>
<% if $USPs('home').Count %>
<style>
    .usp-grid { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(14em, 1fr)); gap: 1.5em; }
    .usp-grid__cell { margin: 0; padding: 0; }
    .usp-grid .usp-item { display: flex; align-items: flex-start; gap: 0.75em; }
    .usp-grid .usp-item__icon { flex: 0 0 auto; display: inline-flex; width: 1.75em; height: 1.75em; }
    .usp-grid .usp-item__icon svg, .usp-grid .usp-item__icon-img { width: 100%; height: 100%; }
    .usp-grid .usp-item__body { display: block; }
    .usp-grid .usp-item__title { display: block; font-weight: 600; }
    .usp-grid .usp-item__text { display: block; opacity: 0.8; font-size: 0.9em; }
    .usp-grid .usp-item__link { color: inherit; text-decoration: none; }
    .usp-grid .usp-item__link:hover .usp-item__title { text-decoration: underline; }
</style>
<ul class="usp-grid">
    <% loop $USPs('home') %>
        <li class="usp-grid__cell"><% include SilverShop/Usp/UspItem %></li>
    <% end_loop %>
</ul>
<% end_if %>
