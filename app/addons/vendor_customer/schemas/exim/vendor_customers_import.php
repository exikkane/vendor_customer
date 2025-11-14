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
