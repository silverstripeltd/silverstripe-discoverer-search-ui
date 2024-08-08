<div class="discoverer">
    <div class="discoverer-form">
        $SearchForm
    </div>

    <% if $QuerySuggestions %>
        <div class="discoverer-suggestions">
            <h2>Search suggestions:</h2>

            $QuerySuggestions
        </div>
    <% end_if %>

    $SearchResults
</div>
