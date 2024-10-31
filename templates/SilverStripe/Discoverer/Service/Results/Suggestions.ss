<ul class="discoverer-suggestions__list">
    <% loop $Me %>
        <% if $Up.TargetQueryUrl && $Up.TargetQueryStringField %>
            <li class="discoverer-suggestion">
                <a href="{$Up.TargetQueryUrl}?{$Up.TargetQueryStringField}={$Me}"
                   class="discoverer-suggestion__link"
                >$Me.Raw</a>
            </li>
        <% else %>
            <li class="discoverer-suggestion">$Me.Raw</li>
        <% end_if %>
    <% end_loop %>
</ul>
