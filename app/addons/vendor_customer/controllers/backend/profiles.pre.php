<?php

use Tygh\Enum\ProfileDataTypes;
use Tygh\Registry;

if ($_REQUEST['user_type'] === 'N'
    && !fn_check_permissions('vendor_customer', 'manage_vendor_customers', 'admin')
) {
    return [CONTROLLER_STATUS_DENIED];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

/**
 * Update DB tables dynamically.
 */
function fn_update_user_fields($user_id, $user_data)
{
    $db = Tygh::$app['db'];

    $user_profiles_columns = $db->getTableFields('user_profiles');
    $users_columns         = $db->getTableFields('users');

    foreach ($user_data as $field => $value) {
        if (in_array($field, $user_profiles_columns)) {
            db_query(
                "UPDATE ?:user_profiles SET `$field` = ?s WHERE user_id = ?i",
                $value,
                $user_id
            );
        }

        if (in_array($field, $users_columns)) {
            db_query(
                "UPDATE ?:users SET `$field` = ?s WHERE user_id = ?i",
                $value,
                $user_id
            );
        }
    }
}

/**
 * Auto-fill address fields if needed.
 */
function fn_handle_address(&$user_data, $user_type, $ship_to_another)
{
    if (empty($ship_to_another)) {
        $profile_fields = fn_get_profile_fields($user_type);
        fn_fill_address($user_data, $profile_fields, true);
    }
}

$data      = $_REQUEST;
$user_id   = $data['user_id'] ?? null;
$user_data = $data['user_data'] ?? [];
$user_type = $data['user_type'] ?? 'N';

//
// UPDATE MODE
//
if ($mode === 'update') {

    $has_access = fn_vendor_customers_import_check_vendor_customer_permissions(
        $user_id,
        Registry::get('runtime.company_id')
    );

    if (!$has_access || $user_type !== 'N') {
        fn_set_notification('W', __('warning'), __('access_denied'));
        return false;
    }

    fn_update_user_fields($user_id, $user_data);
    fn_handle_address($user_data, $user_type, $data['ship_to_another']);

    $user_data['profile_id'] = fn_update_user_profile(
        $user_id,
        $user_data,
        'update',
        $data['ship_to_another']
    );
}

//
// ADD MODE
//
if ($mode === 'add') {

    $user_data['user_type'] = 'N';

    $user_id = db_query("INSERT INTO ?:users ?e", $user_data);

    db_replace_into('vendor_customers_mapping', [
        'vendor_customer_id' => $user_id,
        'vendor_id'          => Registry::get('runtime.company_id')
    ]);

    fn_handle_address($user_data, $user_type, $data['ship_to_another']);

    $user_data['profile_id'] = fn_update_user_profile(
        $user_id,
        $user_data,
        'add',
        $data['ship_to_another']
    );

    return [
        CONTROLLER_STATUS_REDIRECT,
        'profiles.update?user_id=' . $user_id . '&user_type=N'
    ];
}