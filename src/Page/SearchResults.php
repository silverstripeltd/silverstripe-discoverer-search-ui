<?php

namespace SilverStripe\DiscovererSearchUI\Page;

use Page;
use SilverStripe\DiscovererSearchUI\Controller\SearchResultsController;

class SearchResults extends Page
{

    private static string $table_name = 'SearchResults';

    private static string $icon_class = 'font-icon-p-search';

    private static string $singular_name = 'Search results page';

    private static string $plural_name = 'Search results pages';

    private static string $description = 'Display search results';

    private static string $controller_name = SearchResultsController::class;

    public function getControllerName()
    {
        return $this->config()->get('controller_name');
    }

}
