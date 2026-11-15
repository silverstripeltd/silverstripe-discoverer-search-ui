<?php

declare(strict_types=1);

namespace SilverStripe\DiscovererSearchUI\Service;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Discoverer\Service\Blocklist\BlockedKeywordProvider;
use SilverStripe\Discoverer\Service\Blocklist\ConfigBlockedKeywordProvider;
use SilverStripe\DiscovererSearchUI\Model\BlockedSearchKeyword;

/**
 * Supplies blocked search keywords from the CMS, plus any set in YAML config.
 *
 * The list is read on every search, autocomplete and spelling suggestion request, so it is cached for cache_lifetime
 * seconds, and cleared when an editor adds, changes or removes a keyword (see BlockedSearchKeyword). High volumes of
 * searches therefore cost at most one database query per web instance per cache lifetime.
 */
class CmsBlockedKeywordProvider implements BlockedKeywordProvider
{

    use Configurable;
    use Injectable;

    private const string CACHE_KEY = 'keywords';

    /**
     * Seconds the keyword list is cached for. The cache is cleared on the web instance that handles a CMS change, but
     * with the default (local filesystem) cache other instances only pick the change up when their copy expires. A
     * shared cache backend (e.g. Redis or Memcached) applies changes on every instance at once. 0 caches the list until
     * it is cleared.
     *
     * @config
     */
    private static int $cache_lifetime = 300;

    private static array $dependencies = [
        'cache' => '%$' . CacheInterface::class . '.DiscovererBlockedKeywords',
        'configProvider' => '%$' . ConfigBlockedKeywordProvider::class,
    ];

    private ?CacheInterface $cache = null;

    private ?ConfigBlockedKeywordProvider $configProvider = null;

    public function setCache(?CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function setConfigProvider(?ConfigBlockedKeywordProvider $configProvider): void
    {
        $this->configProvider = $configProvider;
    }

    public function getBlockedKeywords(): array
    {
        $configKeywords = $this->configProvider?->getBlockedKeywords() ?? [];

        return array_merge($configKeywords, $this->getCmsKeywords());
    }

    public function clearCache(): void
    {
        $this->cache?->delete(self::CACHE_KEY);
    }

    /**
     * @return int|null Null caches the list until it is cleared
     */
    private function getCacheLifetime(): ?int
    {
        $lifetime = (int) $this->config()->get('cache_lifetime');

        return $lifetime > 0 ? $lifetime : null;
    }

    /**
     * @return string[]
     */
    private function getCmsKeywords(): array
    {
        $cached = $this->cache?->get(self::CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        $keywords = BlockedSearchKeyword::get()->column('Keyword');
        $this->cache?->set(self::CACHE_KEY, $keywords, $this->getCacheLifetime());

        return $keywords;
    }

}
