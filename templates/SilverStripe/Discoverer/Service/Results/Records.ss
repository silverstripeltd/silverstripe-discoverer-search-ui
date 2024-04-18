<ul class="search-ui-results__list">
    <% loop $Me %>
        <li class="search-ui-result">
            <h5 class="search-ui-result__title">
                <a href="$Link" class="link link--transparent-underline search-ui-result__link">
                    <span class="link-text">$Title</span>
                </a>
            </h5>

            <% if $Body %>
                <div class="search-ui-result__body">
                    $Body
                </div>
            <% end_if %>

            <% if $Content %>
                <div class="search-ui-result__content">
                    $Content
                </div>
            <% end_if %>
        </li>
    <% end_loop %>
</ul>
