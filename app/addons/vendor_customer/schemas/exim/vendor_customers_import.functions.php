<?php

use Tygh\Registry;

/**
 * Gets the user fields that are assigned to a Vendor Customers profile type.
 *
 * @return array
 */
function fn_get_cp_profile_fields_data()
{
    fn_vendor_customer_actualize_profile_tables();
    $db = Tygh::$app['db'];
    $user_profiles_columns = $db->getTableFields('user_profiles');

    $fields = db_get_hash_array('
        SELECT pfd.description, pf.field_id, pf.field_name as db_field
        FROM ?:profile_fields pf 
        LEFT JOIN ?:profile_field_descriptions pfd ON pfd.object_id = pf.field_id 
        WHERE pf.field_name IN (?a) AND pf.profile_type = ?s', 'description',
        $user_profiles_columns, Registry::get('addons.vendor_customer.vendor_customers_field_type')
    );
    $company_id = Registry::get('runtime.company_id');

    $fields['Company ID'] = [
        'process_put' => ['fn_exim_set_vendor_customer_info', $company_id, '#key', '#new'],
    ];

   if (!empty($fields)) {
       foreach ($fields as $description => &$field) {
           if ($description === 'Company ID') {
               continue;
           }

           $field = [
               'table'    => 'user_profiles',
               'db_field' => $field['db_field'],
           ];
       }
   }

   return $fields;
}

function fn_exim_set_vendor_customer_info($company_id, $user_id, $is_new)
{
    if ($is_new) {
        db_query('UPDATE ?:users SET company_id = ?i WHERE user_id = ?i', $company_id, $user_id);
    }
}