<ul class="discoverer-results__list">
    <% loop $Me %>
        <li class="discoverer-result">
            <h5 class="discoverer-result__title">
                <a href="$Link" class="link link--transparent-underline discoverer-result__link">$Title</a>
            </h5>

            <% if $Body %>
                $Body
            <% end_if %>

            <% if $Content %>
                $Content
            <% end_if %>
        </li>
    <% end_loop %>
</ul>
