<%-- A single USP item: optional icon (uploaded image wins over the named inline SVG), title, optional supporting
     line, optional link. Shared by the grid and the strips. Style lives in the container include so it is not
     repeated per item. Override this file in your theme to restyle. Expects a Usp in scope (a loop iteration). --%>
<div class="usp-item">
    <% if $Icon.Exists %>
        <span class="usp-item__icon"><img class="usp-item__icon-img" src="$Icon.URL" alt="" aria-hidden="true" /></span>
    <% else_if $IconSVG %>
        <span class="usp-item__icon">$IconSVG.RAW</span>
    <% end_if %>
    <span class="usp-item__body">
        <% if $LinkURL %><a class="usp-item__link" href="$LinkURL.ATT"<% if $OpenInNew %> target="_blank" rel="noopener"<% end_if %>><% end_if %>
            <span class="usp-item__title">$Title</span>
            <% if $Text %><span class="usp-item__text">$Text</span><% end_if %>
        <% if $LinkURL %></a><% end_if %>
    </span>
</div>
