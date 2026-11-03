<?php

declare(strict_types=1);

namespace SilverStripe\DiscovererSearchUI\Service;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Discoverer\Service\Blocklist\BlockedKeywordProvider;
use SilverStripe\Discoverer\Service\Blocklist\ConfigBlockedKeywordProvider;
use SilverStripe\DiscovererSearchUI\Model\BlockedSearchKeyword;

/**
 * Supplies blocked search keywords from the CMS, plus any set in YAML config.
 *
 * The list is read on every search, autocomplete and spelling suggestion request, so it is cached with no expiry and
 * only reloaded from the database after an editor adds, changes or removes a keyword (see BlockedSearchKeyword). High
 * volumes of searches therefore never reach the database.
 */
class CmsBlockedKeywordProvider implements BlockedKeywordProvider
{

    use Injectable;

    private const string CACHE_KEY = 'keywords';

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
     * @return string[]
     */
    private function getCmsKeywords(): array
    {
        $cached = $this->cache?->get(self::CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        $keywords = BlockedSearchKeyword::get()->column('Keyword');
        $this->cache?->set(self::CACHE_KEY, $keywords);

        return $keywords;
    }

}
