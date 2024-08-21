<div class="discoverer">
    <div class="discoverer-form">
        $SearchForm
    </div>

    <% if $QuerySuggestions %>
        <div class="discoverer-suggestions">
            <h2><%t SilverStripe\DiscovererSearchUI\Page\SearchResults.SearchSuggestions 'Search suggestions' %></h2>

            $QuerySuggestions
        </div>
    <% end_if %>

    $SearchResults
</div>
