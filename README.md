# Discovery Starter Theme

This theme is used as an optional aid for getting started with Silverstripe's search service. It provides some
basic Silverstripe templates along with some styling for common form elements and results.

Some manual intervention will still be required on your part, as Discover (and this theme) can't know what search fields
you have defined for your application, and we also can't know how you have implemented search for your application.

## Assumptions

* You are using set up similar to what has been described in [Discoverer > Simple Usage](https://github.com/silverstripeltd/discoverer/blob/main/docs/simple-usage.md).
* In particular:
  * That you will use a Silverstripe `Form` to present your search form.
  * That you have two methods available for your template, one to supply the search form, and the other for your search
    results object.

## Installation

Add the following to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:silverstripeltd/discoverer.git"
        },
        {
            "type": "vcs",
            "url": "git@github.com:silverstripeltd/discoverer-theme.git"
        }
    ]
}
```

Then run the following:

```sh
composer require silverstripeltd/discoverer-theme
```

## Implementation

These instructions assume that you are following a set up similar to the one described in
[Discoverer > Simple Usage](https://github.com/silverstripeltd/discoverer/blob/main/docs/simple-usage.md).

If you are not, then you will likely need to adjust your approach.

### CSS requirements

Add this into your `SearchResultsController`.

```php
class SearchResultsController extends PageController
{
    protected function init()
    {
        parent::init();

        Requirements::css('vendor/silverstripeltd/discoverer-theme/dist/css/main.css');
    }
}
```

**OR**

Add the following in the `<head>` section of an appropriate Silverstripe template (potentially `SearchResults.ss` if you
have created one, or `Page.ss` could also make sense, though that will usual apply to all pages).

```silverstripe
<% require css("silverstripeltd/discoverer-theme:dist/css/main.css") %>
```

### Search form and results template

This theme does **not** specify an outer container width limit. It is very much assumed that your project will have its
own content containers, and you should continue to use those.

The following snippet is the template for this Discoverer theme:

```silverstripe
<div class="discoverer">
    <div class="discoverer-form">
        $SearchForm
    </div>

    $SearchResults
</div>
```

But you will **probably** want to add the above snippet within your project's content container (in this case, perhaps
your content container is a class called `.container`):

```silverstripe
<div class="container">
    <div class="discoverer">
        <div class="discoverer-form">
            $SearchForm
        </div>

        $SearchResults
    </div>
</div>
```

* A `SearchForm()` method should be available to return a Silverstripe `Form` object.
* A `SearchResults()` method should be available to return a Discoverer `Results` object.

If you don't have this setup, either update the snippet above, or consider implementing the
[Discoverer > Simple Usage](https://github.com/silverstripeltd/discoverer/blob/main/docs/simple-usage.md)
example.

### Records template

As mentioned in the description, Discoverer (and this theme) have no way of knowing what fields you are using in your
search index.

This theme has provided a sample `Records.ss` template, which assumes some basic fields are available:

* `title`
* `link`
* `body` (this is a default field for the Silverstripe search service when files like PDFs are processed)
* `content`

If you do not use these fields, or you have slightly different names for them, then you will need to override the
template found under `templates/SilverStripe/Discoverer/Service/Results/Records.ss`.

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
