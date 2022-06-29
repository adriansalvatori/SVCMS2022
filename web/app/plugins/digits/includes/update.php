<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('digits_activation_hooks', 'digits_migrate_fields_db');

/*
 * migrate fields
 * hide country code
 * @since v7.0.1.5
 *
 * */
function digits_migrate_fields_db()
{

    $whiteListCountryCodes = get_option("whitelistcountrycodes");
    if (empty($whiteListCountryCodes)) {
        digits_disable_hide_countrycode();
    } else {
        if (is_array($whiteListCountryCodes)) {
            $size = sizeof($whiteListCountryCodes);
            if ($size != 1) {
                digits_disable_hide_countrycode();
            }
        }
    }


    if (get_option('digits_db_migrate7010', false) == false) {
        update_option('digits_db_migrate7010', true);


        $encoded = get_option("dig_reg_custom_field_data", "e30=");
        $reg_custom_fields = stripslashes(base64_decode($encoded));

        if (!empty($reg_custom_fields)) {

            $logics = stripslashes(get_option("dig_reg_lb_data"));
            $dig_sortorder = get_option("dig_sortorder");

            update_option('dig_reg_custom_field_data_backup', $encoded);
            update_option('dig_sortorder_backup', $dig_sortorder);
            update_option('dig_reg_lb_data_backup', $logics);


            $reg_custom_fields = json_decode($reg_custom_fields, true);


            foreach ($reg_custom_fields as $field_key => $values) {
                $label = $values['label'];
                $meta_key = $values['meta_key'];

                if (!array_key_exists($meta_key, $reg_custom_fields)) {
                    $reg_custom_fields[$meta_key] = $reg_custom_fields[$field_key];
                    unset($reg_custom_fields[$field_key]);
                }


                $prefix = "dig_cs_";
                $field_id = $prefix . cust_dig_filter_string($label);
                $field_key_meta = $prefix . cust_dig_filter_string($meta_key);
                if (!empty($logics)) {
                    $logics = str_replace($field_id, $field_key_meta, $logics);
                }

                $dig_sortorder = str_replace($field_id, $field_key_meta, $dig_sortorder);

            }


            $field_data = base64_encode(json_encode($reg_custom_fields));

            update_option('dig_reg_custom_field_data', $field_data);
            update_option('dig_sortorder', $dig_sortorder);

            if (!empty($logics)) {
                update_option('dig_reg_lb_data', $logics);
            }
        }
    }
}

function digits_disable_hide_countrycode()
{
    update_option('dig_hide_countrycode', 0);
}