<?php

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
 * @return void
 */
function fn_vendor_customer_get_users($params, $fields, $sortings, &$condition): void
{
    $company_id = Registry::get('runtime.company_id');
    if (!empty($params['user_type']) && $params['user_type'] == 'N' && !empty($company_id)) {
        $condition['company_id'] = fn_get_company_condition('?:users.company_id', true, $company_id);
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
 * @return void
 */
function fn_vendor_customer_get_user_info_before(&$condition): void
{
    $company_id = Registry::get('runtime.company_id');
    if ($company_id) {
        $condition = "{$condition} OR (user_type = 'N' AND ?:users.company_id = {$company_id})";
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
