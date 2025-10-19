<?php

namespace SilverStripe\DiscovererSearchUI\Extension;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Discoverer\Query\Query;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;

class SearchResultsExtension extends Extension
{

    public function updateSearchFieldLists(FieldList $fields, FieldList $actions, HTTPRequest $request): void
    {
        // Override this method if you would like to update the field lists before they are added to your search form
    }

    public function updateSearchForm(Form $form, HTTPRequest $request): void
    {
        // Override this method if you would like to update the search form before it is sent to the template
    }

    public function updateSearchQuery(Query $query, HTTPRequest $request): void
    {
        // Override this method if you would like to update the Query made to your search service
    }

    public function updateIndexSuffix(string &$suffix): void
    {
        // Override this method if you would like to update the Index the query will be run on
    }

}
