<?php

use Tygh\Enum\ProfileDataTypes;
use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        $data = $_REQUEST;
        $user_id = $data['user_id'];

        $has_access = fn_vendor_customers_import_check_vendor_customer_permissions($user_id, Registry::get('runtime.company_id'));

        if (!$has_access || $data['user_type'] != 'N') {
            fn_set_notification('W', __('warning'), __('access_denied'));

            return false;
        }
        $db = Tygh::$app['db'];

        // Get existing columns
        $user_profiles_columns = $db->getTableFields('user_profiles');
        $users_columns = $db->getTableFields('users');

        foreach ($data['user_data'] as $field => $field_value) {
            if (in_array($field, $user_profiles_columns)) {
                db_query("UPDATE ?:user_profiles SET `$field` = ?s WHERE user_id = ?i", $field_value, $user_id);
            }

            if (in_array($field, $users_columns)) {
                db_query("UPDATE ?:users SET `$field` = ?s WHERE user_id = ?i", $field_value, $user_id);
            }
        }

        fn_store_profile_fields(
            $data['user_data'],
            [
                ProfileDataTypes::USER    => $user_id,
                ProfileDataTypes::PROFILE => $data['profile_id']
            ],
            ProfileDataTypes::USER_PROFILE,
        );
    }
}
