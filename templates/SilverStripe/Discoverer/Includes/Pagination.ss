<% if $MoreThanOnePage %>
    <nav aria-label="<%t SilverStripe\Discoverer\Includes\Pagination.PaginationLabel 'Search pagination' %>" class="discoverer-pagination">
        <ul>
            <li>
                <% if $NotFirstPage %>
                    <a
                        class="discoverer-pagination__arrow discoverer-pagination__arrow--prev"
                        href="$PrevLink"
                        {$Attributes}
                        aria-label="<%t SilverStripe\Discoverer\Includes\Pagination.PreviousPage 'Previous page' %>"
                    >&laquo;</a>
                <% else %>
                    <a
                        class="discoverer-pagination__arrow discoverer-pagination__arrow--prev discoverer-pagination__arrow--disabled"
                        href="#"
                        aria-label="<%t SilverStripe\Discoverer\Includes\Pagination.PreviousPageDisabled 'Previous page (not available)' %>"
                        aria-disabled="true"
                    >&laquo;</a>
                <% end_if %>
            </li>

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

            <li>
                <% if $NotLastPage %>
                    <a
                        class="discoverer-pagination__arrow discoverer-pagination__arrow--next"
                        href="$NextLink"
                        {$Attributes}
                        aria-label="<%t SilverStripe\Discoverer\Includes\Pagination.NextPage 'Next page' %>"
                    >&raquo;</a>
                <% else %>
                    <a
                        class="discoverer-pagination__arrow discoverer-pagination__arrow--next discoverer-pagination__arrow--disabled"
                        href="#"
                        aria-label="<%t SilverStripe\Discoverer\Includes\Pagination.NextPageDisabled 'Next page (not available)' %>"
                        aria-disabled="true"
                    >&raquo;</a>
                <% end_if %>
            </li>
        </ul>
    </nav>
<% end_if %>
