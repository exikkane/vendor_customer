<?php

use Tygh\Enum\UserTypes;

if (
    !empty($_REQUEST['user_type'])
    && (UserTypes::isCustomer($_REQUEST['user_type']) || UserTypes::isAdmin($_REQUEST['user_type']))
) {
    $schema['profiles.manage']['vendor_administrators'] = [
        'href' => 'profiles.manage?user_type=N',
        'text' => __('vendor_customers'),
        'position' => 100
    ];
}

return $schema;