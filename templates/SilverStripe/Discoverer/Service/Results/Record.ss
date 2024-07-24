<li class="discoverer-result">
    <h5 class="discoverer-result__title">
        <a href="{$Link}<% if $AnalyticsData %>?$AnalyticsData<% end_if %>" class="link link--transparent-underline discoverer-result__link">$Title</a>
    </h5>

    <% if $Content %>
        $Content
    <% end_if %>

    <% if $Body %>
        $Body
    <% end_if %>
</li>
