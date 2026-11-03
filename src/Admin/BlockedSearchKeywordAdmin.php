<?php

declare(strict_types=1);

namespace SilverStripe\DiscovererSearchUI\Admin;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\DiscovererSearchUI\Model\BlockedSearchKeyword;

/**
 * CMS section where editors manage the keywords that stop searches from returning results
 */
class BlockedSearchKeywordAdmin extends ModelAdmin
{

    private static array $managed_models = [
        BlockedSearchKeyword::class,
    ];

    private static string $url_segment = 'blocked-search-keywords';

    private static string $menu_title = 'Blocked search keywords';

    private static string $menu_icon_class = 'font-icon-block';

    private static array $required_permission_codes = [
        BlockedSearchKeyword::PERMISSION_MANAGE,
    ];

}
