# Implementing Fluent

[Fluent](https://github.com/tractorcow-farm/silverstripe-fluent/) is a localisation module which helps managing content in multiple languages. When searching in different languages there are a number of options as to how this can be approached.

## Index per language - recommended

The cleanest way to provide multi language search is by using a different index for each language. This helps to simplify the logic when loading content into indexes and when making queries to find content. The [Forager](https://github.com/silverstripeltd/silverstripe-forager/) module has a [companion fluent module](https://github.com/silverstripeltd/silverstripe-forager-fluent/) that allows you to tag an index with a locale and update the indexing job to only fetch content from that locale when indexing.

The fluent module has an assumption that localised content is presented separately on the front-end (eg by a separate URL path or domain) and using a separate index reflects this assumption. This means that when searching you can choose the appropriate index to query on based on the current locale. This module provides the `updateIndexSuffix` extension point which allows you to [change the index that will be queried](../README.md#change-the-index-used-for-querying). To do that with fluent you could use an extension like this:

```php
    public function updateIndexSuffix(& $suffix): void
    {
        // grab current locale
        $locale = FluentState::singleton()->getLocale();

        $indexConfigurations = IndexConfiguration::singleton()
            ->getIndexConfigurations();

        // Now that we have our Locale code, we need to find the corresponding index suffixes
        foreach ($indexConfigurations as $indexSuffix => $indexConfiguration) {
            $indexLocaleCode = $indexConfiguration[IndexDataExtension::INDEX_LOCALE_PROP] ?? '';

            if ($indexLocaleCode !== $locale) {
                continue;
            }

            // first matching index used
            $suffix = $indexSuffix;
            return;
        }
    }
```

The advantage to this approach is its simplicity and that it uses a similar mental model to the fluent module. If however you want to search for content across multiple languages you will need to make multiple queries and combine the results.

## Single index with filters

Another option is to use a single index but tag documents with their locale. When searching you can then use [a filter](https://github.com/silverstripeltd/silverstripe-discoverer/blob/2/docs/detailed-querying.md#filters) to limit searches to a single language. The downside to this approach is that the index may become quite large and potentially can hit document field limits and query performance can be slower with more documents. There is also less separation of content so there is more potential to include incorrect results in the search. Finally, you will need to customise any indexing process to create an index with content from multiple locales.

When searching you can use the [updateSearchQuery](../README.md#update-the-search-query) extension to add a filter based on the detected locale such as:

```php
class SearchExtension extends SearchResultsExtension
{

    public function updateSearchQuery(Query $query, HTTPRequest $request): void
    {
        // grab current locale
        $locale = FluentState::singleton()->getLocale();

        // Apply our locale filter
        if ($locale) {
            $query->filter('locale', $locale, Criterion::EQUAL);
        }
    }

}
```
