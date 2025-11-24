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
        WHERE pf.field_name IN (?a) AND pf.profile_type = ?s AND pfd.lang_code = ?s', 'description',
        $user_profiles_columns, Registry::get('addons.vendor_customer.vendor_customers_field_type'), CART_LANGUAGE
    );

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


function fn_set_allowed_vendors_company_ids(&$conditions)
{
    if (Registry::get('runtime.company_id')) {
        $company_customers_ids = implode(',', db_get_fields("SELECT vendor_customer_id FROM ?:vendor_customers_mapping WHERE vendor_id = ?i", Registry::get('runtime.company_id')));
        $conditions[] = "users.user_id IN ($company_customers_ids)";
    }
}

function fn_import_check_user_vendors_company_id($primary_object_id, &$object)
{
    if (Registry::get('runtime.company_id')) {
        if ($primary_object_id) {
            db_replace_into('vendor_customers_mapping', [
                'vendor_customer_id' => $primary_object_id['user_id'],
                'vendor_id' => Registry::get('runtime.company_id')
            ]);
        }
        $object['user_type'] = 'N';
    }
}

function fn_import_assign_vendor_customer_mapping_for_new_customers($primary_object_ids)
{
    if (Registry::get('runtime.company_id')) {
        if ($primary_object_ids) {
            foreach ($primary_object_ids as $primary_object) {
                db_replace_into('vendor_customers_mapping', [
                    'vendor_customer_id' => $primary_object['user_id'],
                    'vendor_id' => Registry::get('runtime.company_id')
                ]);
            }
        }
    }
}