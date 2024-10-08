<?php

namespace SilverStripe\DiscovererSearchUI\Controller;

use PageController;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\Discoverer\Query\Query;
use SilverStripe\Discoverer\Query\Suggestion;
use SilverStripe\Discoverer\Service\Results\Results;
use SilverStripe\Discoverer\Service\Results\Suggestions;
use SilverStripe\Discoverer\Service\SearchService;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use SilverStripe\View\Requirements;

class SearchResultsController extends PageController
{

    public const ENV_SUGGESTIONS_ENABLED = 'SEARCH_SUGGESTIONS_ENABLED';

    private static array $allowed_actions = [
        'SearchForm',
    ];

    private static string $field_keywords = 'q';

    private static string $field_pagination = 'start';

    private static string $field_submit = 'search';

    private static string $index_variant = 'main';

    private static int $per_page = 10;

    private static int $result_count_for_suggestions = 0;

    private static int $suggestions_limit = 4;

    private ?Results $results = null;

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
                ->setInputType('search')
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
        // Local cache to make sure we don't perform this task more than one (@see QuerySuggestions())
        if ($this->results) {
            return $this->results;
        }

        $request = $this->getRequest();
        // Keyword (aka search query string) field (as configured)
        $fieldKeywords = $this->config()->get('field_keywords');
        // The keywords that we want to search
        $keywords = $request->getVar($fieldKeywords);

        // No results unless we search for something
        if (!$keywords) {
            return null;
        }

        // Pagination field (as configured)
        $fieldPagination = $this->config()->get('field_pagination');
        // The index variant that we are fetching records from (as defined under `indexes` in search.yml)
        $index = $this->config()->get('index_variant');
        // How many records we want to display per page
        $perPage = $this->config()->get('per_page');
        // Pagination (if supplied)
        $start = $request->getVar($fieldPagination) ?? 0;

        // Instantiate our service
        $service = SearchService::singleton();
        // Instantiate a new Query, and provide the search terms that we wish to search for
        $query = Query::create($keywords);
        // Set pagination requirements
        $query->setPagination($perPage, $start);

        $this->invokeWithExtensions('updateSearchQuery', $query, $request);

        $this->results = $service->search($query, $index);

        return $this->results;
    }

    public function QuerySuggestions(): ?Suggestions
    {
        if (!Environment::getEnv(self::ENV_SUGGESTIONS_ENABLED)) {
            return null;
        }

        // Get the search results for this request
        $results = $this->SearchResults();

        if (!$results) {
            return null;
        }

        // Our results contain enough records that we don't want to query for suggestions
        if ($results->getRecords()->count() > $this->config()->get('result_count_for_suggestions')) {
            return null;
        }

        $request = $this->getRequest();
        // Keyword (aka search query string) field (as configured)
        $fieldKeywords = $this->config()->get('field_keywords');
        // The keywords that are being searched
        $keywords = $request->getVar($fieldKeywords);

        // The index variant that we are fetching records from (as defined under `indexes` in search.yml)
        $index = $this->config()->get('index_variant');

        $service = SearchService::singleton();
        $suggestion = Suggestion::create($keywords);
        $suggestion->setLimit($this->config()->get('suggestions_limit'));

        $this->invokeWithExtensions('updateSuggestionQuery', $suggestion);

        $suggestions = $service->querySuggestion($suggestion, $index);

        if (!$suggestions->getSuggestions()) {
            return null;
        }

        $suggestions->setTargetQueryUrl($this->dataRecord->Link());
        $suggestions->setTargetQueryStringField($this->config()->get('field_keywords'));

        return $suggestions;
    }

}
