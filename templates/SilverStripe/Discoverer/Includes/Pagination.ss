<% if $MoreThanOnePage %>
    <div class="discoverer-pagination">
        <% if $NotFirstPage %>
            <a
                class="discoverer-pagination__arrow discoverer-pagination__arrow--prev"
                href="$PrevLink"
                {$Attributes}
                aria-label="Previous page"
            ></a>
        <% else %>
            <span class="discoverer-pagination__arrow discoverer-pagination__arrow--prev discoverer-pagination__arrow--disabled"></span>
        <% end_if %>

        <% loop $PaginationSummary(2) %>
            <% if $CurrentBool %>
                <span class="discoverer-pagination__number discoverer-pagination__number--current">$PageNum</span>
            <% else %>
                <% if $Link %>
                    <a class="discoverer-pagination__number"
                       href="$Link"
                       {$Up.Attributes}
                    >$PageNum</a>
                <% else %>
                    <span class="discoverer-pagination__dots">...</span>
                <% end_if %>
            <% end_if %>
        <% end_loop %>

        <% if $NotLastPage %>
            <a
                class="discoverer-pagination__arrow discoverer-pagination__arrow--next"
                href="$NextLink"
                {$Attributes}
                aria-label="Next page"
            ></a>
        <% else %>
            <span class="discoverer-pagination__arrow discoverer-pagination__arrow--next discoverer-pagination__arrow--disabled"></span>
        <% end_if %>
    </div>
<% end_if %>
