<?php

namespace SilverStripe\DiscovererSearchUI\Extension;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Discoverer\Query\Query;
use SilverStripe\Forms\FieldList;

class SearchResultsExtension extends Extension
{

    public function updateSearchForm(FieldList $fields, FieldList $actions): void
    {
        // Override this method if you would like to update the form displayed in your template
    }

    public function updateSearchQuery(Query $query, HTTPRequest $request): void
    {
        // Override this method if you would like to update the Query made to your search service
    }

}
