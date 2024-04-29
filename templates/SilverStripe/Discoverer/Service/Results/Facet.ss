<li class="discoverer-facets__item">Facet field: $Name ($FieldName)
    <% if $Data %>
        <ul>
            <% loop $Data %>
                <li>$Value: $Count</li>
            <% end_loop %>
        </ul>
    <% end_if %>
</li>

