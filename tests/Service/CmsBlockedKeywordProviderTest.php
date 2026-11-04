<?php

declare(strict_types=1);

namespace SilverStripe\DiscovererSearchUI\Tests\Service;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Discoverer\Service\Blocklist\BlockedKeywordProvider;
use SilverStripe\Discoverer\Service\Blocklist\ConfigBlockedKeywordProvider;
use SilverStripe\Discoverer\Service\Blocklist\QueryBlocklist;
use SilverStripe\DiscovererSearchUI\Model\BlockedSearchKeyword;
use SilverStripe\DiscovererSearchUI\Service\CmsBlockedKeywordProvider;
use SilverStripe\ORM\Queries\SQLInsert;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class CmsBlockedKeywordProviderTest extends SapphireTest
{

    /**
     * @var bool
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $usesDatabase = true;

    protected function setUp(): void
    {
        parent::setUp();

        ConfigBlockedKeywordProvider::config()->set('blocked_keywords', []);
        Injector::inst()->registerService(
            new Psr16Cache(new ArrayAdapter()),
            CacheInterface::class . '.DiscovererBlockedKeywords'
        );
        Injector::inst()->unregisterNamedObject(CmsBlockedKeywordProvider::class);
    }

    public function testProviderIsRegisteredForDiscoverer(): void
    {
        $this->assertInstanceOf(
            CmsBlockedKeywordProvider::class,
            Injector::inst()->get(BlockedKeywordProvider::class)
        );
    }

    public function testReturnsCmsKeywords(): void
    {
        $this->createKeyword('casino');
        $this->createKeyword('adult video');

        $this->assertEqualsCanonicalizing(
            ['casino', 'adult video'],
            CmsBlockedKeywordProvider::singleton()->getBlockedKeywords()
        );
    }

    public function testIncludesConfigKeywords(): void
    {
        ConfigBlockedKeywordProvider::config()->set('blocked_keywords', ['lottery']);
        $this->createKeyword('casino');

        $this->assertEqualsCanonicalizing(
            ['lottery', 'casino'],
            CmsBlockedKeywordProvider::singleton()->getBlockedKeywords()
        );
    }

    public function testRepeatedCallsUseCacheInsteadOfDatabase(): void
    {
        $this->createKeyword('casino');
        $provider = CmsBlockedKeywordProvider::singleton();
        $provider->getBlockedKeywords();

        // Bypasses the ORM so that no write hook clears the cache
        SQLInsert::create('"DiscovererBlockedSearchKeyword"', ['"Keyword"' => 'lottery'])->execute();

        $this->assertSame(['casino'], $provider->getBlockedKeywords());
    }

    public function testAddingKeywordClearsCache(): void
    {
        $this->createKeyword('casino');
        $provider = CmsBlockedKeywordProvider::singleton();
        $provider->getBlockedKeywords();

        $this->createKeyword('lottery');

        $this->assertEqualsCanonicalizing(['casino', 'lottery'], $provider->getBlockedKeywords());
    }

    public function testEditingKeywordClearsCache(): void
    {
        $keyword = $this->createKeyword('casino');
        $provider = CmsBlockedKeywordProvider::singleton();
        $provider->getBlockedKeywords();

        $keyword->Keyword = 'lottery';
        $keyword->write();

        $this->assertSame(['lottery'], $provider->getBlockedKeywords());
    }

    public function testDeletingKeywordClearsCache(): void
    {
        $keyword = $this->createKeyword('casino');
        $provider = CmsBlockedKeywordProvider::singleton();
        $provider->getBlockedKeywords();

        $keyword->delete();

        $this->assertSame([], $provider->getBlockedKeywords());
    }

    public function testKeywordsAreCachedForConfiguredLifetime(): void
    {
        $cache = $this->registerRecordingCache();
        $this->createKeyword('casino');

        CmsBlockedKeywordProvider::singleton()->getBlockedKeywords();

        $this->assertSame([300], $cache->getLifetimes());
    }

    public function testCacheLifetimeIsConfigurable(): void
    {
        CmsBlockedKeywordProvider::config()->set('cache_lifetime', 60);
        $cache = $this->registerRecordingCache();

        CmsBlockedKeywordProvider::singleton()->getBlockedKeywords();

        $this->assertSame([60], $cache->getLifetimes());
    }

    public function testZeroCacheLifetimeCachesUntilCleared(): void
    {
        CmsBlockedKeywordProvider::config()->set('cache_lifetime', 0);
        $cache = $this->registerRecordingCache();

        CmsBlockedKeywordProvider::singleton()->getBlockedKeywords();

        $this->assertSame([null], $cache->getLifetimes());
    }

    public function testOtherInstanceReloadsKeywordsWhenItsCacheExpires(): void
    {
        $this->createKeyword('casino');
        $otherInstanceCache = new Psr16Cache(new ArrayAdapter());
        $otherInstance = new CmsBlockedKeywordProvider();
        $otherInstance->setCache($otherInstanceCache);
        $otherInstance->getBlockedKeywords();

        // A keyword added on this instance only clears this instance's cache
        $this->createKeyword('lottery');

        $this->assertSame(['casino'], $otherInstance->getBlockedKeywords());

        $otherInstanceCache->clear();

        $this->assertEqualsCanonicalizing(['casino', 'lottery'], $otherInstance->getBlockedKeywords());
    }

    public function testCmsKeywordBlocksSearch(): void
    {
        $this->createKeyword('casino');
        $blocklist = QueryBlocklist::create();

        $this->assertTrue($blocklist->isBlocked('best Online Casino'));
        $this->assertFalse($blocklist->isBlocked('library opening hours'));
    }

    private function registerRecordingCache(): RecordingCache
    {
        $cache = new RecordingCache(new Psr16Cache(new ArrayAdapter()));
        Injector::inst()->registerService($cache, CacheInterface::class . '.DiscovererBlockedKeywords');
        Injector::inst()->unregisterNamedObject(CmsBlockedKeywordProvider::class);

        return $cache;
    }

    private function createKeyword(string $keyword): BlockedSearchKeyword
    {
        $record = BlockedSearchKeyword::create(['Keyword' => $keyword]);
        $record->write();

        return $record;
    }

}
