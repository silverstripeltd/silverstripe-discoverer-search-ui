<?php

namespace SilverStripe\DiscovererSearchUI\Controller;

use PageController;
use SilverStripe\Core\Convert;
use SilverStripe\Discoverer\Query\Query;
use SilverStripe\Discoverer\Service\Results\Results;
use SilverStripe\Discoverer\Service\SearchService;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use SilverStripe\View\Requirements;

class SearchResultsController extends PageController
{

    private static array $allowed_actions = [
        'SearchForm',
    ];

    private static string $field_keywords = 'q';

    private static string $field_pagination = 'start';

    private static string $field_submit = 'search';

    private static string $index_variant = 'main';

    private static int $per_page = 10;

    protected function init()
    {
        parent::init();

        Requirements::css('silverstripe/silverstripe-discoverer-search-ui:dist/css/main.css');
    }

    public function SearchForm(): Form
    {
        $request = $this->getRequest();
        $fieldKeywords = $this->config()->get('field_keywords');
        $fieldSubmit = $this->config()->get('field_submit');

        // The keyword that we want to search
        $keywords = Convert::raw2xml($this->getRequest()->getVar($fieldKeywords));

        $fields = FieldList::create(
            TextField::create($fieldKeywords, _t(self::class . '.FIELD_KEYWORD_LABEL', 'Search terms'), $keywords)
        );

        $actions = FieldList::create(
            FormAction::create($fieldSubmit, _t(self::class . '.FIELD_SUBMIT_LABEL', 'Search'))
        );

        $this->invokeWithExtensions('updateSearchFieldLists', $fields, $actions, $request);

        $form = Form::create(
            $this,
            __FUNCTION__,
            $fields,
            $actions
        )
            ->setFormAction($this->dataRecord->Link())
            ->setFormMethod('GET');

        $this->invokeWithExtensions('updateSearchForm', $form, $request);

        return $form;
    }

    public function SearchResults(): ?Results
    {
        $request = $this->getRequest();
        // Field names (as configured)
        $fieldKeywords = $this->config()->get('field_keywords');
        $fieldPagination = $this->config()->get('field_pagination');
        // The index variant that we are fetching records from (as defined under `indexes` in search.yml)
        $index = $this->config()->get('index_variant');
        // How many records we want to display per page
        $perPage = $this->config()->get('per_page');
        // The keywords that we want to search
        $keywords = $request->getVar($fieldKeywords);
        // Pagination (if supplied)
        $start = $request->getVar($fieldPagination) ?? 0;

        // No results unless we search for something
        if (!$keywords) {
            return null;
        }

        $service = SearchService::create();
        // Instantiate a new Query, and provide the search terms that we wish to search for
        $query = Query::create($keywords);
        // Set pagination requirements
        $query->setPagination($perPage, $start);

        $this->invokeWithExtensions('updateSearchQuery', $query, $request);

        return $service->search($query, $index);
    }

}
