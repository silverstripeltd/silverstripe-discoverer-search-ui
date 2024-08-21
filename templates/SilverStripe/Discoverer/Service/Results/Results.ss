<div class="discoverer-results__container">
    <% if $Facets %>
        <div class="discoverer-facets">
            <h2><%t SilverStripe\Discoverer\Service\Results\Results.FacetResults 'Facet results' %></h2>

            $Facets
        </div>
    <% end_if %>

    <div class="discoverer-results">
        <% if $Records %>
            <h2><%t SilverStripe\Discoverer\Service\Results\Results.SearchResults 'Search results' %></h2>

            <% with $Records %>
                <% include SilverStripe\Discoverer\Includes\Summary %>

                $Me

                <% if $MoreThanOnePage %>
                    <% include SilverStripe\Discoverer\Includes\Pagination %>
                <% end_if %>
            <% end_with %>
        <% else %>
            <p class="error discoverer-results__message"><%t SilverStripe\Discoverer\Service\Results\Results.NoResults 'No search results.' %></p>
        <% end_if %>
    </div>
</div>
