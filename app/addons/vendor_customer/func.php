<?php

use Tygh\Enum\ProfileDataTypes;
use Tygh\Registry;

function fn_vendor_customer_install(): void
{
    fn_vendor_customer_actualize_profile_tables();
}

/**
 * Adds new columns in user_profiles database table to make it possible importing them properly and store all the information separately.
 *
 * @return void
 */
function fn_vendor_customer_actualize_profile_tables(): void
{
    $profile_type = Registry::get('addons.vendor_customer.vendor_customers_field_type');

    // Fetch fields that should be active (show = Y) and not active (show = N)
    $fields = db_get_hash_array(
        'SELECT field_name, profile_show 
         FROM ?:profile_fields 
         WHERE profile_type = ?s AND field_name != "" AND field_type NOT IN (?a)',
        'field_name',
        $profile_type, ['F']
    );

    if (empty($fields)) {
        return;
    }

    $db = Tygh::$app['db'];

    // Get existing columns
    $user_profiles_columns = $db->getTableFields('user_profiles');
    $users_columns = $db->getTableFields('users');

    foreach ($fields as $field_name => $field_data) {
        $column = '`' . $field_name . '`';

        $is_active = ($field_data['profile_show'] === 'Y');

        // Add column if active
        if ($is_active) {

            if (!in_array($field_name, $user_profiles_columns)
                && !in_array($field_name, $users_columns)) {

                $db->query("ALTER TABLE ?:user_profiles ADD $column VARCHAR(128) NOT NULL DEFAULT ''");
            }
        }
    }
}

/**
 * Hook 'get_users'
 *
 * Adds a condition to check the company ID when users with type = 'N' are requested.
 *
 * @param $params
 * @param $fields
 * @param $sortings
 * @param $condition
 * @param $join
 * @return void
 */
function fn_vendor_customer_get_users($params, $fields, $sortings, &$condition, &$join): void
{
    $company_id = Registry::get('runtime.company_id');
    if (!empty($company_id)) {
        $join .= db_quote(' LEFT JOIN ?:vendor_customers_mapping ON ?:vendor_customers_mapping.vendor_customer_id = ?:users.user_id');
        $condition['company_id'] = fn_get_company_condition('?:vendor_customers_mapping.vendor_id', true, $company_id);
    }
}

/**
 * Hook 'get_user_types'
 *
 * Adds a new user type - Vendor customer
 *
 * @param $types
 * @return void
 */
function fn_vendor_customer_get_user_types(&$types): void
{
    $types['N'] = 'vendor_customer';
}

/**
 * Hook 'get_user_info_before'
 *
 * Adds a condition of retrieving a user info with user type = 'N'. Only 'V' user type is retrieved by default.
 *
 * @param $condition
 * @param $user_id
 * @param $user_fields
 * @param $join
 * @return void
 */
function fn_vendor_customer_get_user_info_before(&$condition, $user_id, $user_fields, &$join): void
{
    $company_id = Registry::get('runtime.company_id');
    if ($company_id) {
        $requested_user_type = db_get_field('SELECT user_type FROM ?:users WHERE user_id = ?i', $user_id);
        if ((isset($_REQUEST['user_type']) && $_REQUEST['user_type'] == 'N') || $requested_user_type == 'N') {
            $join .= db_quote(' LEFT JOIN ?:vendor_customers_mapping ON ?:vendor_customers_mapping.vendor_customer_id = ?:users.user_id');
            $condition = "AND user_type = 'N' AND ?:vendor_customers_mapping.vendor_id = {$company_id}";
        }
    }
}

/**
 * Hook 'is_user_exists_post'
 *
 * Consider that user is not exist if it has user type of 'N' - Vendor customer
 *
 * @param $user_id
 * @param $user_data
 * @param $is_exist
 * @return void
 */
function fn_vendor_customer_is_user_exists_post($user_id, $user_data, &$is_exist): void
{
    if ($is_exist) {
        $current_user_type = db_get_field('SELECT user_type FROM ?:users WHERE email = ?s', $user_data['email']);

        if ($current_user_type == 'N') {
            $is_exist = false;
        }
    }
}

function fn_vendor_customer_get_profile_fields($location, $select, &$condition): void
{
    if ($location == 'N') {
        if (strpos($condition, "profile_type = 'U'") !== false) {
            $condition = str_replace("profile_type = 'U'", "profile_type = 'K'", $condition);
        }
    }
}

function fn_vendor_customers_import_fill_user_data($fields, $profile_data)
{
    $current_fields_values = fn_get_profile_fields_data(ProfileDataTypes::USER, $profile_data['user_id']);
    foreach ($fields as $id => &$field_data) {
        if ($field_data['profile_type'] === 'K') {
            if (isset($current_fields_values[$id])) {
                $field_data['value'] = $current_fields_values[$id];
            } else {
                $field_data['value'] = $profile_data[$field_data['field_name']];
            }
            $field_data['field_type'] = 'I';
        }
    }

    return $fields;
}

function fn_vendor_customers_import_check_vendor_customer_permissions($customer_id, $company_id): bool
{
    return (bool) db_get_field('SELECT COUNT(*) FROM ?:vendor_customers_mapping WHERE vendor_id = ?i AND vendor_customer_id = ?i', $company_id, $customer_id);
}

function fn_vendor_customer_dispatch_before_display()
{
    if (isset(Tygh::$app['session']['notifications_to_delete']) && is_array(Tygh::$app['session']['notifications_to_delete']))
    {
        foreach (Tygh::$app['session']['notifications_to_delete'] as $k => $key) {
            unset(Tygh::$app['session']['notifications'][$key]);
            unset(Tygh::$app['session']['notifications_to_delete'][$k]);
        }
    }
}
function fn_vendor_customer_set_notification_pre($type, $title, $message, $message_state, $extra, $init_message) {

    if  (
        $message ==  __('access_denied')
        && ($_REQUEST['dispatch'] == 'profiles.update' || $_REQUEST['dispatch'] == 'profiles.add')
        && $_REQUEST['user_type'] == 'N') {
        $key = md5($type . $title . $message . $extra);
        Tygh::$app['session']['notifications_to_delete'][] = $key;
    }
}

function fn_vendor_customer_get_users_pre(&$params)
{
    $company_id = Registry::get('runtime.company_id');
    if (!empty($company_id) && empty($params['user_type'])) {
        $params['user_type'] = 'N';
    }
}

function fn_vendor_customer_get_user_type_description(&$type_descr) {
    $type_descr['S']['N'] = 'vendor_customers';
    $type_descr['P']['N'] = 'vendor_customers';
}
