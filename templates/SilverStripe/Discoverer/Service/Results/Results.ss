<div class="discoverer-results__container">
    <% if $Facets %>
        <div class="discoverer-facets">
            <h2>Facet results:</h2>

            $Facets
        </div>
    <% end_if %>

    <div class="discoverer-results">
        <% if $Records %>
            <h2>Search results:</h2>

            <% with $Records %>
                <% include SilverStripe\Discoverer\Includes\Summary %>

                $Me

                <% if $MoreThanOnePage %>
                    <% include SilverStripe\Discoverer\Includes\Pagination %>
                <% end_if %>
            <% end_with %>
        <% else %>
            <p class="error discoverer-results__message">No search results.</p>
        <% end_if %>
    </div>
</div>
