<div class="discoverer">
    <div class="discoverer-form">
        $SearchForm
    </div>

    <% if $SpellingSuggestions %>
        <div class="discoverer-suggestions">
            <h2><%t SilverStripe\DiscovererSearchUI\Page\SearchResults.SearchSuggestions 'Search suggestions' %></h2>

            $SpellingSuggestions
        </div>
    <% end_if %>

    $SearchResults
</div>
