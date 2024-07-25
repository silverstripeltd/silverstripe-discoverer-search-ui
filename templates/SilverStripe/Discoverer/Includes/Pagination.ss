<% if $MoreThanOnePage %>
    <nav aria-label="Search paginaiton" class="discoverer-pagination">
        <ul>
        <% if $NotFirstPage %>
            <li>
                <a
                    class="discoverer-pagination__arrow discoverer-pagination__arrow--prev"
                    href="$PrevLink"
                    {$Attributes}
                    aria-label="Previous page"
                ></a>
            </li>
        <% else %>
            <li>
                <a
                    class="discoverer-pagination__arrow discoverer-pagination__arrow--prev discoverer-pagination__arrow--disabled"
                    href="#"
                    aria-label="Previous page (not available)"
                    aria-disabled="true"
                ></a>
            </li>
        <% end_if %>

        <% loop $PaginationSummary(2) %>
            <li>
            <% if $CurrentBool %>
                <a class="discoverer-pagination__number discoverer-pagination__number--current"
                    href="$Link"
                    {$Up.Attributes}
                    aria-current="page"
                >$PageNum</a>
            <% else %>
                <% if $Link %>
                    <a class="discoverer-pagination__number"
                       href="$Link"
                       {$Up.Attributes}
                    >$PageNum</a>
                <% else %>
                    <span class="discoverer-pagination__dots">&hellip;</span>
                <% end_if %>
            <% end_if %>
            </li>
        <% end_loop %>

        <% if $NotLastPage %>
            <li>
                <a
                    class="discoverer-pagination__arrow discoverer-pagination__arrow--next"
                    href="$NextLink"
                    {$Attributes}
                    aria-label="Next page"
                ></a>
            </li>
        <% else %>
            <li>
                <a
                    class="discoverer-pagination__arrow discoverer-pagination__arrow--next discoverer-pagination__arrow--disabled"
                    href="#"
                    aria-label="Next page (not available)"
                    aria-disabled="true"
                ></a>
            </li>
        <% end_if %>
        </ul>
    </nav>
<% end_if %>
