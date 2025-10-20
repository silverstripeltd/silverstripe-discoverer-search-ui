# Implementing Fluent

<!-- TOC -->
* [Implementing Fluent](#implementing-fluent)
  * [Index per language - recommended](#index-per-language---recommended)
    * [If you are using the Forager Fluent module](#if-you-are-using-the-forager-fluent-module)
    * [If you are NOT using the Forager Fluent module](#if-you-are-not-using-the-forager-fluent-module)
  * [Single index options - not recommended](#single-index-options---not-recommended)
    * [Single index using filters](#single-index-using-filters)
    * [Single index with separate fields per language](#single-index-with-separate-fields-per-language)
<!-- TOC -->

[Fluent](https://github.com/tractorcow-farm/silverstripe-fluent/) is a localisation module which helps managing content in multiple languages. When searching in different languages there are a couple of ways to handle localised content:

## Index per language - recommended

The cleanest and safest way to provide multi-language search is to use a different index for each language. This helps to simplify the logic of loading content into indexes, and it also helps to ensure that different language content doesn't "bleed" into search results where you wouldn't expect them. This setup is supported by the [Forager](https://github.com/silverstripeltd/silverstripe-forager/) module and its [companion Fluent module](https://github.com/silverstripeltd/silverstripe-forager-fluent/) that allows you to tag an index with a locale, which in turn, allows the Forager module to index the correct localised content into the correct indexes.

The Fluent module has an assumption that all localised content is presented separately from each other on the front-end (eg by a separate URL path or domain), and using a separate index reflects this assumption. This means that when searching, you can choose the appropriate index to query on based on the current locale.

The advantage to this approach is its simplicity and that it uses a similar mental model to the Fluent module. If however you want to search for content across multiple languages you will need to make multiple queries and combine the results.

This module provides the `updateIndexSuffix()` extension point which allows you to [change the index that will be queried](../README.md#change-the-index-used-for-querying).

### If you are using the Forager Fluent module

If you are using the [Forager Fluent](https://github.com/silverstripeltd/silverstripe-forager-fluent/) module, then you already have a record of which index relates to which locale, and you simply need to look up that information when updating the index suffix. You can do this by implementing the `updateIndexSuffix()` extension point like so:

```php
namespace App\Extensions;

class MySearchExtension extends SearchResultsExtension
{

    public function updateIndexSuffix(string &$suffix): void
    {
        // Grab current locale
        $locale = FluentState::singleton()->getLocale();

        if (!$locale) {
            // No locale detected, you might want to handle this case differently (EG: throw an exception)
            return;
        }

        $indexConfigurations = IndexConfiguration::singleton()
            ->getIndexConfigurations();

        // Now that we have our Locale code, we need to find the corresponding index suffixes
        foreach ($indexConfigurations as $indexSuffix => $indexConfiguration) {
            $indexLocaleCode = $indexConfiguration[IndexDataExtension::INDEX_LOCALE_PROP] ?? '';

            if ($indexLocaleCode !== $locale) {
                continue;
            }

            // First matching index used
            $suffix = $indexSuffix;

            return;
        }
    }

}
```

```yaml
SilverStripe\DiscovererSearchUI\Controller\SearchResultsController:
  extensions:
    - App\Extensions\MySearchExtension
```

### If you are NOT using the Forager Fluent module

The same overall idea applies here (you need to update the index suffix based on what locale your user is browsing), but this documentation can no longer know what information may or may not already be available to your application (because we don't know how you are indexing your content). You will need to implement your own logic to map between locale codes and index suffixes.

One option could be to add a new field to Fluent's `Locale` data object to store the index suffix, and then look up the index suffix from there. An example implementation could look like this:

```php
namespace App\Extensions;

/**
 * @property string $IndexSuffix
 */
class MyLocaleExtension extends DataExtension
{

    private static $db = [
        'IndexSuffix' => 'Varchar(50)',
    ];

}
```

```php
namespace App\Extensions;

class MySearchExtension extends SearchResultsExtension
{

    public function updateIndexSuffix(string &$suffix): void
    {
        // Grab current locale code
        $localeCode = FluentState::singleton()->getLocale();

        if (!$localeCode) {
            // No locale code detected, you might want to handle this case differently (EG: throw an exception)
            return;
        }

        $locale = Locale::getByLocale($localeCode);

        if (!$locale) {
            // No matching Locale found, you might want to handle this case differently (EG: throw an exception)
            return;
        }

        $suffix = $locale->IndexSuffix;
    }

}
```

```yaml
TractorCow\Fluent\Model\Locale:
  extensions:
    - App\Extensions\MyLocaleExtension

SilverStripe\DiscovererSearchUI\Controller\SearchResultsController:
  extensions:
    - App\Extensions\MySearchExtension
```

Another (more hardcoded) option, if your locales are relatively well set in stone, could be to simply use a hardcoded array of values that you can switch between.

```php
namespace App\Extensions;

class MySearchExtension extends SearchResultsExtension
{

    private array $localeToIndexSuffixMap = [
        'en_NZ' => 'en',
        'fr_FR' => 'fr',
        // Add more mappings as needed
    ];

    public function updateIndexSuffix(string &$suffix): void
    {
        // Grab current locale
        $locale = FluentState::singleton()->getLocale();

        if (!$locale) {
            // No locale detected, you might want to handle this case differently (EG: throw an exception)
            return;
        }

        $map = $this->localeToIndexSuffixMap[$locale] ?? null;

        if (!$map) {
            // No matching suffix found, you might want to handle this case differently (EG: throw an exception)
            return;
        }

        $suffix = $map;
    }

}
```

## Single index options - not recommended

Below is a short brainstorm of some other options that are possible, but not recommended. We will not be providing code samples for these options, as we do not support them as a use-case.

### Single index using filters

Search Documents could be given unique IDs per language, and a locale field could be added to each Document. You could then index a separate Document for each language, making sure that they have the correct value set for the `locale` field. At query time, a filter could be applied to limit results to the current locale. This filter could be applied using the same general ideas that were presented above for [Index per language - recommended](#index-per-language---recommended).

The risk with this approach is that there are multiple ways that you could quite easily index or return results in the wrong language. This approach is also not recommended because there is no direct indexing support (EG: The Forager module does not support this use-case).

### Single index with separate fields per language

Perhaps even more difficult and risky than above, could be to have a single Document that contains separate fields for each language (eg: `title_en_nz`, `title_fr_fr`, etc). At query time, the correct fields could be queried based on the current locale.
