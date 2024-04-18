<% if $MoreThanOnePage %>
    <% if $UseAjax %><div class="spinner"></div><% end_if %>
    <div class="search-ui-pagination">
        <% if $NotFirstPage %>
            <a class="search-ui-pagination__arrow search-ui-pagination__arrow--prev"
               href="$PrevLink"
                {$Attributes}
               aria-label="Previous page"
               <% if $UseAjax %>data-href-ajax<% end_if %>
            ></a>
        <% else %>
            <span class="search-ui-pagination__arrow search-ui-pagination__arrow--prev search-ui-pagination__arrow--disabled"></span>
        <% end_if %>
        <% loop $PaginationSummary(2) %>
            <% if $CurrentBool %>
                <span class="search-ui-pagination__number search-ui-pagination__number--current">$PageNum</span>
            <% else %>
                <% if $Link %>
                    <a class="search-ui-pagination__number"
                       href="$Link"
                        {$Up.Attributes}
                        <% if $UseAjax %>data-href-ajax<% end_if %>
                    >$PageNum</a>
                <% else %>
                    <span class="search-ui-pagination__dots">...</span>
                <% end_if %>
            <% end_if %>
        <% end_loop %>
        <% if $NotLastPage %>
            <a class="search-ui-pagination__arrow search-ui-pagination__arrow--next"
               href="$NextLink"
                {$Attributes}
                <% if $UseAjax %>data-href-ajax<% end_if %>
               aria-label="Next page"></a>
        <% else %>
            <span class="search-ui-pagination__arrow search-ui-pagination__arrow--next search-ui-pagination__arrow--disabled"></span>
        <% end_if %>
    </div>
<% end_if %>
