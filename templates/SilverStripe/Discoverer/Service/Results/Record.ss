<li class="discoverer-result">
    <h3 class="discoverer-result__title">
        <a href="{$Link}<% if $AnalyticsData %>?$AnalyticsData<% end_if %>"
           class="discoverer-result__link"
        >$Title</a>
    </h3>

    <% if $Content %>
        $Content
    <% end_if %>

    <% if $Body %>
        $Body
    <% end_if %>
</li>
