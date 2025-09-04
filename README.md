# 🧭 Silverstripe Discoverer > 🎨 Search UI

Provides a Silverstripe page type, search UI, and basic theme to get you started with your search implementation.

## Installation

```sh
composer require silverstripe/silverstripe-discoverer-search-ui
```

## Using this module

The templates provided make some guesses as to what fields you might have defined for your search, but it's likely that
some manual intervention will still be required on your part to help this search UI meet your use case.

### Search results page

This module comes out of the box with a `SearchResults` page which will be made available to you in the CMS. Simply
create one of these pages on your website, and the `SearchResultsController` will take care of creating the search form
and displaying results.

### Default fields

This search UI assumes that you have the following fields available in your index:

* `title`
* `link`
* `content` (optional)
* `body` (optional)

## Spelling suggestions (aka "did you mean")

**Not to be confused with Query suggestions (aka autocomplete).**

Spelling suggestions for queries can be enabled with the following environment variable.

```yaml
SEARCH_SPELLING_SUGGESTIONS_ENABLED=1
```

Note: Spelling suggestions is an API query that happens **after** you have received results - so it will impact your
page load times.

The spelling suggestions feature needs to know what fields you would like it to search in. By default, it **only**
provides suggestions based on the `title` field. You can add additional fields by updating the following configuration.

```yaml
SilverStripe\DiscovererSearchUI\Controller\SearchResultsController:
  spelling_suggestion_fields:
    - content
    - body
```

By default, these suggestions will be provided when you have zero (`0`) search results. This default can be updated
through the following configruation.

```yaml
SilverStripe\DiscovererSearchUI\Controller\SearchResultsController:
  result_count_for_spelling_suggestions: 5
```

By default, you will receive (up to) 1 suggestion (there aren't always spelling suggestions for a given query). This
default can be udpated through the following configuration.

```yaml
SilverStripe\DiscovererSearchUI\Controller\SearchResultsController:
  spelling_suggestions_limit: 5
```

Some services support both "raw" and "foramtted" results for spelling suggestions. Our default behaviour is to **not**
request formatted suggestions. You can enable this in your requests through the following configuration.

```yaml
SilverStripe\DiscovererSearchUI\Controller\SearchResultsController:
    spelling_suggestions_formatted: true
```

## Customisations

The out of the box `SearchResultsController` comes with 3 extension points that will allow you to modify the search
form, and allow you to modify the query that is sent to your search service.

Create a new extension (for example):

```php
<?php

namespace App\Extensions;

use SilverStripe\DiscovererSearchUI\Extension\SearchResultsExtension;

class SearchExtension extends SearchResultsExtension
{
}

```

By extending `SearchResultsExtension` you'll get some scaffolding for the 3 extension points that are available.

Apply the extension (for example):

```yaml
SilverStripe\DiscovererSearchUI\Controller\SearchResultsController:
  extensions:
    - App\Extensions\SearchExtension
```

### Update the search query

If you need to add support for any filter fields you've added, if you'd like to specify specific result fields, or if
you'd like to change absolutely anything else about your `Query` before it is sent to your search service, then you can
do so by implementing the `updateSearchQuery()` method.

```php
class SearchExtension extends SearchResultsExtension
{

    public function updateSearchQuery(Query $query, HTTPRequest $request): void
    {
        // A filter called "topic" that we added to our search form
        $topic = $request->getVar('topic') ?: null;

        // Title field to be limited to 200 chars, and formatted (snippets)
        $query->addResultField('title', 200, true);
        // Content field to be limited to 400 chars, and formatted (snippets)
        $query->addResultField('content', 400, true);
        // Body field to be limited to 400 chars, and formatted (snippets)
        $query->addResultField('body', 400, true);
        // The link to the Page or File
        $query->addResultField('link');

        // Apply our topics filter (if any were provided)
        if ($topic) {
            $query->filter('topic_id', $topic, Criterion::EQUAL);
        }
    }

}
```

### Add search form fields and actions

By default there is a "search terms" field and a "Search" (submit) action available on your search form, but if you need
to add (for example) additional filter options, or any other form fields, then you can do that by implementing the
`updateSearchFieldLists()` method.

```php
class SearchExtension extends SearchResultsExtension
{

    public function updateSearchFieldLists(FieldList $fields, FieldList $actions, HTTPRequest $request): void
    {
        // If the form has previously been submitted, see if a topic was specified
        $topic = $request->getVar('topic') ?: null;
        // A filter called "topics" that we want to add to our search form
        $topics = DropdownField::create(
            'topic',
            'Topic',
            [
                1 => 'Transformers',
                2 => 'Star Wars',
                3 => 'Star Trek',
            ]
        )
            ->setEmptyString('select one')
            // Set the previously submitted value to this field
            ->setValue($topic);

        $fields->add($topics);
    }

}

```

### Update the search form

If (for whatever reason) you need to change the search form itself, then you can do that by implementing the
`updateSearchForm()` method.

```php
class SearchExtension extends SearchResultsExtension
{

    public function updateSearchForm(Form $form, HTTPRequest $request): void
    {
        // For example, disabling the CSRF token?
        $form->disableSecurityToken();
    }

}
```

### Search results template

If you would like the change the way that your search form and results are displayed (at a higher level), then you will
want to override the tamplate found under `templates/SilverStripe/DiscovererSearchUI/Page/Layout/SearchResults.ss`.

### Record template

This module has provided a simple `Record.ss` template, which assumes some basic fields are available:

* `title`
* `link`
* `content`
* `body`

If you do not use these fields, have extra fields you'd like to add, or want to change the way the fields are display,
then you will need to override the template found under `templates/SilverStripe/Discoverer/Service/Results/Record.ss`.

### Mitigating against XSS
When handling user input (such as a search term that might be passed in on the querystring of a link) it is important
to consider security and to understand when and where that user input needs to be sanitised.

#### Preparing the query
Generally speaking you shouldn't need to sanitise the user search term that you pass to the `Query` class. The
search service client should handle this in a safe manner and do any escaping it needs to, such as escaping quotes and
prevent the query json being manipulated.

````php
$keywords = $request->getVar($fieldKeywords);

// Instantiate a new Query, and provide the search terms that we wish to search for
$query = Query::create($keywords);
````

Also, any sanitisation that you do at this point might mean a valid search term is escaped, leading to an incorrect
set of search results. For example, if searching for `O'Leary` you don't want to escape html entities, since
this will convert and send `O&#039;Leary` to the search service.

#### Showing the search term in the search page input field

````php
$keywords = $this->getRequest()->getVar($fieldKeywords);

$fields = FieldList::create(
    TextField::create($fieldKeywords, _t(self::class . '.FIELD_KEYWORD_LABEL', 'Search terms'), $keywords)
        ->setInputType('search')
);
````
When configuring your search form and passing the search term to a `TextField` this won't need sanitising,
since the templating system will handle this for you. 

#### Including the query on the page
The potential for cross site-scripting (where malicious code can be inserted into the page) can occur when outputting user
input back to the page - for example, if you wish to modify the results template to include `Showing 1 of 10 results for "my search term"`.

If your implementation requires you to include the query within the results template, one way you could do this is to
create an extension for the `Results` class and add a custom function that can be called from the Results template.

```` php
public function sanitisedQuery(Query $query): DBText
{
    return DBText::create()->setValue($query->getQueryString());
}
````

For further information about XSS and how the Silverstripe templating system helps keep you safe against attacks,
see https://docs.silverstripe.org/en/5/developer_guides/security/secure_coding/#xss-cross-site-scripting

#### Handling raw values from the search service
Search services such as Elastic have the ability to return `raw` values on result fields. If outputting these to the
template you will need to consider whether they are safe or whether you need to sanitise/escape the raw content.
For further information, see https://www.elastic.co/guide/en/app-search/current/sanitization-guide.html

## Contributing

* [Node and NPM](https://docs.npmjs.com/getting-started/installing-node)
* [Laravel-Mix](https://github.com/JeffreyWay/laravel-mix) and [Webpack](https://webpack.github.io)

```bash
nvm use
```

```bash
yarn install
```

Available commands:

* `yarn dev`: Development build with un-minified files
* `yarn watch`: Development build and watch for ongoing changes
* `yarn build`: Product build

Production dist files should be contributed along with your Pull Request.
