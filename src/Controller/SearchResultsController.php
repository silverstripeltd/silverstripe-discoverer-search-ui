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
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\View\Requirements;

class SearchResultsController extends PageController
{

    private const string ENV_SPELLING_SUGGESTIONS_ENABLED = 'SEARCH_SPELLING_SUGGESTIONS_ENABLED';

    private static array $allowed_actions = [
        'SearchForm',
    ];

    private static string $field_keywords = 'q';

    private static string $field_pagination = 'start';

    private static string $field_submit = 'search';

    private static string $index_variant = 'main';

    private static int $per_page = 10;

    private static int $result_count_for_spelling_suggestions = 0;

    private static int $spelling_suggestions_limit = 1;

    private static bool $spelling_suggestions_formatted = false;

    private static array $spelling_suggestion_fields = [
        'title',
    ];

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

        // The keyword that we want to search (templating will handle escaping for the input field)
        $keywords = $this->getRequest()->getVar($fieldKeywords);

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
        // Local cache to make sure we don't perform this task more than one (@see SpellingSuggestions())
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

    public function SpellingSuggestions(): ?Suggestions
    {
        if (!Environment::getEnv(self::ENV_SPELLING_SUGGESTIONS_ENABLED)) {
            return null;
        }

        // Get the search results for this request
        $results = $this->SearchResults();

        // No search has been performed yet
        if (!$results) {
            return null;
        }

        // Our results contain enough records that we don't want to query for suggestions
        if ($results->getRecords()->count() > $this->config()->get('result_count_for_spelling_suggestions')) {
            return null;
        }

        $request = $this->getRequest();
        // Keyword (aka search query string) field (as configured)
        $fieldKeywords = $this->config()->get('field_keywords');
        // The keywords that are being searched
        $keywords = $request->getVar($fieldKeywords);
        // The fields that we want to query on
        $suggestionFields = $this->config()->get('spelling_suggestion_fields');
        // Whether we want to have formatted results (if supported by our search service)
        $suggestionsFormatted = $this->config()->get('spelling_suggestions_formatted');

        // The index variant that we are fetching records from (as defined under `indexes` in search.yml)
        $index = $this->config()->get('index_variant');

        $service = SearchService::singleton();
        $suggestion = Suggestion::create(
            $keywords,
            $this->config()->get('spelling_suggestions_limit'),
            $suggestionFields,
            $suggestionsFormatted
        );

        $this->invokeWithExtensions('updateSuggestionQuery', $suggestion);

        $suggestions = $service->spellingSuggestion($suggestion, $index);

        if (!$suggestions->getSuggestions()) {
            return null;
        }

        $suggestions->setTargetQueryUrl($this->dataRecord->Link());
        $suggestions->setTargetQueryStringField($fieldKeywords);

        return $suggestions;
    }

}
