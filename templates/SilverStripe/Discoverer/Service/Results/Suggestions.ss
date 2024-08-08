<ul class="discoverer-suggestions__list">
    <% loop $Me %>
        <% if $Up.DesiredUrl && $Up.DesiredQueryField %>
            <li class="discoverer-suggestion">
                <a href="{$Up.DesiredUrl}?{$Up.DesiredQueryField}={$Me}"
                   class="discoverer-suggestion__link"
                >$Me</a>
            </li>
        <% else %>
            <li class="discoverer-suggestion">$Me</li>
        <% end_if %>
    <% end_loop %>
</ul>
