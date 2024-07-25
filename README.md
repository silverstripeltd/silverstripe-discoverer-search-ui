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
