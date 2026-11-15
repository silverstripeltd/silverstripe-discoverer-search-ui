<?php

declare(strict_types=1);

namespace SilverStripe\DiscovererSearchUI\Model;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\DiscovererSearchUI\Service\CmsBlockedKeywordProvider;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;

/**
 * A word or short phrase that stops a search from being sent to the search service. Searches containing it as whole
 * words return no results (see QueryBlocklist in silverstripe-discoverer).
 *
 * @property string $Keyword
 */
class BlockedSearchKeyword extends DataObject implements PermissionProvider
{

    public const string PERMISSION_MANAGE = 'MANAGE_BLOCKED_SEARCH_KEYWORDS';

    private static string $table_name = 'DiscovererBlockedSearchKeyword';

    private static string $singular_name = 'Blocked search keyword';

    private static string $plural_name = 'Blocked search keywords';

    private static array $db = [
        'Keyword' => 'Varchar(255)',
    ];

    private static array $indexes = [
        'Keyword' => true,
    ];

    private static array $summary_fields = [
        'Keyword',
    ];

    private static string $default_sort = 'Keyword ASC';

    /**
     * @param bool $includerelations
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Keyword'] = _t(self::class . '.KEYWORD', 'Keyword or phrase');

        return $labels;
    }

    /**
     * @return FieldList
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->dataFieldByName('Keyword')?->setDescription(_t(
            self::class . '.KEYWORD_DESCRIPTION',
            'Searches containing this as a whole word (or, for a phrase, these words together) return no results. '
            . 'Matching ignores case and punctuation.'
        ));

        return $fields;
    }

    public function validate(): ValidationResult
    {
        $result = parent::validate();
        $keyword = trim((string) $this->Keyword);

        if ($keyword === '') {
            $result->addFieldError('Keyword', _t(self::class . '.KEYWORD_REQUIRED', 'Enter a keyword or phrase.'));

            return $result;
        }

        $duplicate = static::get()
            ->filter('Keyword', $keyword)
            ->exclude('ID', (int) $this->ID)
            ->exists();

        if ($duplicate) {
            $result->addFieldError(
                'Keyword',
                _t(self::class . '.KEYWORD_DUPLICATE', 'This keyword is already blocked.')
            );
        }

        return $result;
    }

    /**
     * @param Member|null $member
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function canView($member = null): bool
    {
        return $this->canManage($member);
    }

    /**
     * @param Member|null $member
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function canEdit($member = null): bool
    {
        return $this->canManage($member);
    }

    /**
     * @param Member|null $member
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function canDelete($member = null): bool
    {
        return $this->canManage($member);
    }

    /**
     * @param Member|null $member
     * @param array $context
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function canCreate($member = null, $context = []): bool
    {
        return $this->canManage($member);
    }

    public function providePermissions(): array
    {
        return [
            self::PERMISSION_MANAGE => [
                'name' => _t(self::class . '.PERMISSION_MANAGE', 'Manage blocked search keywords'),
                'category' => _t(self::class . '.PERMISSION_CATEGORY', 'Search'),
            ],
        ];
    }

    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        $this->Keyword = trim((string) $this->Keyword);
    }

    /**
     * The keyword list is cached for every search, so it is cleared whenever the list changes.
     */
    protected function onAfterWrite(): void
    {
        parent::onAfterWrite();

        CmsBlockedKeywordProvider::singleton()->clearCache();
    }

    protected function onAfterDelete(): void
    {
        parent::onAfterDelete();

        CmsBlockedKeywordProvider::singleton()->clearCache();
    }

    private function canManage(?Member $member): bool
    {
        return Permission::check(self::PERMISSION_MANAGE, 'any', $member);
    }

}
