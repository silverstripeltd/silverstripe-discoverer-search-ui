<?php

declare(strict_types=1);

namespace SilverStripe\DiscovererSearchUI\Tests\Model;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\DiscovererSearchUI\Model\BlockedSearchKeyword;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class BlockedSearchKeywordTest extends SapphireTest
{

    /**
     * @var bool
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $usesDatabase = true;

    protected function setUp(): void
    {
        parent::setUp();

        Injector::inst()->registerService(
            new Psr16Cache(new ArrayAdapter()),
            CacheInterface::class . '.DiscovererBlockedKeywords'
        );
    }

    public function testKeywordIsTrimmed(): void
    {
        $keyword = BlockedSearchKeyword::create(['Keyword' => '  casino  ']);
        $keyword->write();

        $this->assertSame('casino', $keyword->Keyword);
    }

    public function testEmptyKeywordIsRejected(): void
    {
        $this->expectException(ValidationException::class);

        BlockedSearchKeyword::create(['Keyword' => '   '])->write();
    }

    public function testDuplicateKeywordIsRejected(): void
    {
        BlockedSearchKeyword::create(['Keyword' => 'casino'])->write();

        $this->expectException(ValidationException::class);

        BlockedSearchKeyword::create(['Keyword' => ' casino '])->write();
    }

    public function testKeywordCanBeResavedWithoutDuplicateError(): void
    {
        $keyword = BlockedSearchKeyword::create(['Keyword' => 'casino']);
        $keyword->write();

        $keyword->Keyword = 'casino';
        $keyword->write();

        $this->assertSame(1, BlockedSearchKeyword::get()->count());
    }

    public function testOnlyMembersWithPermissionCanManageKeywords(): void
    {
        $keyword = BlockedSearchKeyword::create(['Keyword' => 'casino']);
        $editor = $this->createMemberWithPermission(BlockedSearchKeyword::PERMISSION_MANAGE);
        $other = $this->createMemberWithPermission('CMS_ACCESS_CMSMain');

        $this->assertTrue($keyword->canView($editor));
        $this->assertTrue($keyword->canEdit($editor));
        $this->assertTrue($keyword->canCreate($editor));
        $this->assertTrue($keyword->canDelete($editor));

        $this->assertFalse($keyword->canView($other));
        $this->assertFalse($keyword->canEdit($other));
        $this->assertFalse($keyword->canCreate($other));
        $this->assertFalse($keyword->canDelete($other));
    }

}
