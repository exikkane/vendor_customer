<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'vendor_customer/schemas/exim/vendor_customers_import.functions.php');
include_once(Registry::get('config.dir.schemas') . 'exim/users.functions.php');

$cp_profile_type_fields = fn_get_cp_profile_fields_data();

$schema = array(
    'section' => 'vendor_customers',
    'name' => __('vendor_customers_import'),
    'pattern_id' => 'vendor_customers_import',
    'key' => ['user_id'],
    'order' => 30,
    'table' => 'users',
    'permissions' => array(
        'import' => 'manage_vendor_customers',
        'export' => 'manage_vendor_customers',
    ),
    'references'    => [
        'user_profiles' => [
            'reference_fields' => ['user_id' => '#key', 'profile_type' => 'P'],
            'join_type'        => 'LEFT',
        ],

    ],


    'export_fields' => [
        'E-mail' => [
            'db_field' => 'email',
            'alt_key'  => true,
            'required' => true,
        ],
        'Status' => [
            'db_field' => 'status',
        ],
        'User type' => [
            'db_field' => 'user_type',
        ],
        'First name' => [
            'db_field' => 'firstname',
        ],
        'Last name' => [
            'db_field' => 'lastname',
        ],
        'Phone' => [
            'db_field' => 'phone',
        ],
        'Company' => [
            'db_field' => 'company',
        ],
        'Billing: first name' => [
            'db_field' => 'b_firstname',
            'table'    => 'user_profiles',
        ],
        'Billing: last name' => [
            'db_field' => 'b_lastname',
            'table'    => 'user_profiles',
        ],
        'Billing: address' => [
            'db_field' => 'b_address',
            'table'    => 'user_profiles',
        ],
        'Billing: address (line 2)' => [
            'db_field' => 'b_address_2',
            'table'    => 'user_profiles',
        ],
        'Billing: city' => [
            'db_field' => 'b_city',
            'table'    => 'user_profiles',
        ],
        'Billing: state' => [
            'db_field' => 'b_state',
            'table'    => 'user_profiles',
        ],
        'Billing: country' => [
            'db_field' => 'b_country',
            'table'    => 'user_profiles',
        ],
        'Billing: zipcode' => [
            'db_field' => 'b_zipcode',
            'table'    => 'user_profiles',
        ],
        'Billing: phone' => [
            'db_field' => 'b_phone',
            'table'    => 'user_profiles',
        ],
        'Shipping: first name' => [
            'db_field' => 's_firstname',
            'table'    => 'user_profiles',
        ],
        'Shipping: last name' => [
            'db_field' => 's_lastname',
            'table'    => 'user_profiles',
        ],
        'Shipping: address' => [
            'db_field' => 's_address',
            'table'    => 'user_profiles',
        ],
        'Shipping: address (line 2)' => [
            'db_field' => 's_address_2',
            'table'    => 'user_profiles',
        ],
        'Shipping: city' => [
            'db_field' => 's_city',
            'table'    => 'user_profiles',
        ],
        'Shipping: state' => [
            'db_field' => 's_state',
            'table'    => 'user_profiles',
        ],
        'Shipping: country' => [
            'db_field' => 's_country',
            'table'    => 'user_profiles',
        ],
        'Shipping: zipcode' => [
            'db_field' => 's_zipcode',
            'table'    => 'user_profiles',
        ],
        'Shipping: phone' => [
            'db_field' => 's_phone',
            'table'    => 'user_profiles',
        ],
    ],
);

$schema['export_fields'] = array_merge($cp_profile_type_fields, $schema['export_fields']);

$schema['import_process_data']['check_company_id'] = [
    'function'    => 'fn_import_check_user_vendors_company_id',
    'args'        => ['$primary_object_id', '$object'],
];
$schema['post_processing']['assign_vendor_customer_mapping_for_new_customers'] = [
    'function'    => 'fn_import_assign_vendor_customer_mapping_for_new_customers',
    'args'        => ['$primary_object_ids'],
    'import_only' => true,
];

$schema['pre_export_process'] = [
    'set_allowed_company_ids' => [
        'function'    => 'fn_set_allowed_vendors_company_ids',
        'args'        => ['$conditions'],
        'export_only' => true,
    ],
];

return $schema;
