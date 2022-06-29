<?php

if (!defined('ABSPATH')) {
    exit;
}

function digits_presets_custom_fields()
{
    return array(
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('Last Name', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'last_name'
            )
        ),
        array(
            'type' => 'user_role',
            'values' => array(
                'label' => __('User Role', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'user_role'
            )
        ),
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('Display Name', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'display_name'
            )
        ),
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('Company', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'billing_company'
            )
        ),
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('Address Line 1', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'billing_address_1'
            )
        ),
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('Address Line 2', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'billing_address_2'
            )
        ),
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('City', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'billing_city'
            )
        ),
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('State', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'billing_state'
            )
        ),
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('Country', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'billing_country'
            )
        ),
        array(
            'type' => 'text',
            'values' => array(
                'label' => __('Postcode / ZIP', 'digits'),
                'required' => 1,
                'custom_class' => '',
                'meta_key' => 'billing_postcode'
            )
        ),


    );
}

function digits_customfieldsTypeList()
{

    return array(
        'text' => array(
            'name' => __('Text', 'digits'),
            'force_required' => 0,
            'meta_key' => 1,
            'options' => 0,
            'slug' => 'text'
        ),
        'textarea' => array(
            'name' => __('TextArea', 'digits'),
            'force_required' => 0,
            'meta_key' => 1,
            'options' => 0,
            'slug' => 'textarea'
        ),
        'number' => array(
            'name' => __('Number', 'digits'),
            'force_required' => 0,
            'meta_key' => 1,
            'options' => 0,
            'slug' => 'number'
        ),
        'dropdown' => array(
            'name' => __('DropDown', 'digits'),
            'force_required' => 0,
            'meta_key' => 1,
            'options' => 1,
            'slug' => 'dropdown'
        ),
        'checkbox' => array(
            'name' => __('CheckBox', 'digits'),
            'force_required' => 0,
            'meta_key' => 1,
            'options' => 1,
            'slug' => 'checkbox'
        ),
        'radio' => array(
            'name' => __('Radio', 'digits'),
            'force_required' => 0,
            'meta_key' => 1,
            'options' => 1,
            'slug' => 'radio'
        ),
        'tac' => array(
            'name' => __('Terms & Conditions', 'digits'),
            'force_required' => 1,
            'meta_key' => 1,
            'options' => 0,
            'slug' => 'tac',
            'pref_label' => 'I Agree [t]Terms and Conditions[/t] & [p]Privacy Policy[/t]'
        ),
        'captcha' => array(
            'name' => __('Captcha', 'digits'),
            'force_required' => 1,
            'meta_key' => 0,
            'options' => 0,
            'slug' => 'captcha'
        ),
        'user_role' => array(
            'name' => __('User Role', 'digits'),
            'force_required' => 1,
            'meta_key' => 1,
            'options' => 0,
            'slug' => 'user_role',
            'hidden' => 1,
            'user_role' => 1
        ),
        'date' => array(
            'name' => __('Date', 'digits'),
            'force_required' => 0,
            'meta_key' => 1,
            'options' => 0,
            'slug' => 'date'
        ),
    );

}

function digit_customfields()
{
    $user_can_register = get_option('dig_enable_registration', 1);

    $show_asterisk = get_option('dig_show_asterisk', 0);
    ?>

    <table class="form-table">
        <tr id="enableregistrationrow">
            <th scope="row"><label class="top-10"><?php _e('Enable Registration', 'digits'); ?> </label></th>
            <td>
                <?php digits_input_switch('dig_enable_registration', $user_can_register); ?>
                <!--                <p class="dig_ecr_desc"><?php /*_e('This function only works on Digits Login/Signup Modal and Page', 'digits'); */ ?></p>-->
            </td>
        </tr>

        <tr id="showasteriskrow">
            <th scope="row"><label
                        class="top-10"><?php _e('Show asterisk (*) on required fields', 'digits'); ?> </label></th>
            <td>
                <?php digits_input_switch('dig_show_asterisk', $show_asterisk); ?>
            </td>
        </tr>

    </table>


    <input type="hidden" name="dig_custom_field_data" value="1"/>
    <div class="dig_admin_head"><span><?php _e('Login Fields', 'digits'); ?></span></div>

    <table class="form-table">
        <?php
        $dig_login_field_details = digit_get_login_fields();
        foreach (digit_default_login_fields() as $login_field => $values) {
            $field_value = $dig_login_field_details[$login_field];

            ?>
            <tr>
                <th scope="row"><label class="top-10"><?php _e($values['name'], "digits"); ?> </label></th>
                <td>
                    <?php digits_input_switch($login_field, $field_value); ?>
                </td>
            </tr>
            <?php
        }
        ?>

        <tr>
            <?php
            $remember_me = get_option('dig_login_rememberme', 1);
            ?>
            <th scope="row"><label><?php _e('Remember Me', "digits"); ?> </label></th>
            <td>
                <select name="dig_login_rememberme"
                        class="dig_custom_field_sel">
                    <option value="2" <?php if ($remember_me == 2) {
                        echo 'selected';
                    } ?>><?php _e('Always', 'digits'); ?></option>
                    <option value="1" <?php if ($remember_me == 1) {
                        echo 'selected';
                    } ?>><?php _e('Yes', 'digits'); ?></option>
                    <option value="0" <?php if ($remember_me == 0) {
                        echo 'selected';
                    } ?>><?php _e('No', 'digits'); ?></option>
                </select>
            </td>
        </tr>

    </table>

    <div class="dig_admin_head"><span><?php _e('Registration Fields', 'digits'); ?></span></div>


    <?php
    $reg_custom_fields = stripslashes(base64_decode(get_option("dig_reg_custom_field_data", "e30=")));


    $dig_sortorder = get_option("dig_sortorder");
    ?>

    <input type="hidden" id="dig_sortorder" name="dig_sortorder"
           value='<?php echo esc_attr($dig_sortorder); ?>'/>

    <input type="hidden" id="dig_reg_custom_field_data" name="dig_reg_custom_field_data"
           value='<?php echo esc_attr($reg_custom_fields); ?>'/>
    <table class="form-table dig-reg-fields <?php if (is_rtl()) {
        echo 'dig_rtl';
    } ?>" id="dig_custom_field_table">

        <tbody>
        <?php
        $dig_reg_field_details = digit_get_reg_fields();
        foreach (digit_default_reg_fields() as $reg_field => $values) {

            $field_value = $dig_reg_field_details[$reg_field];
            ?>
            <tr id="dig_cs_<?php echo cust_dig_filter_string($values['id']); ?>">
                <th scope="row"><label><?php _e($values['name'], "digits"); ?> </label></th>
                <td class="dg_cs_td">
                    <div class="icon-drag icon-drag-dims dig_cust_field_drag dig_cust_default_fields_drag"></div>
                    <select name="<?php echo $reg_field; ?>"
                            class="dig_custom_field_sel" <?php if (isset($values['ondis_disable'])) {
                        echo 'data-disable="' . $values['ondis_disable'] . '"';
                    } ?>>
                        <option value="2" <?php if ($field_value == 2) {
                            echo 'selected';
                        } ?>><?php _e('Required', 'digits'); ?></option>
                        <option value="1" <?php if ($field_value == 1) {
                            echo 'selected';
                        } ?>><?php _e('Optional', 'digits'); ?></option>
                        <option value="0" <?php if ($field_value == 0) {
                            echo 'selected';
                        } ?>><?php _e('No', 'digits'); ?></option>
                    </select>
                </td>
            </tr>
            <?php
        }
        ?>


        <?php

        if (!empty($reg_custom_fields)) {

            $reg_custom_fields = json_decode($reg_custom_fields, true);

            $digits_fields = digits_get_all_custom_fields();
            foreach ($reg_custom_fields as $key => $values) {


                $label = $values['label'];
                $field_key = cust_dig_filter_string($values['meta_key']);

                $type = digits_strtolower($values['type']);
                if (!array_key_exists($type, $digits_fields)) {
                    continue;
                }

                ?>
                <tr id="dig_cs_<?php echo esc_attr($field_key); ?>"
                    class="dig_field_type_<?php echo digits_strtolower($values['type']); ?>"
                    dig-lab="<?php echo esc_attr($values['meta_key']); ?>">
                    <th scope="row"><label><?php echo $label; ?> </label></th>
                    <td>
                        <div class="dig_custom_field_list">
                            <span><?php echo dig_requireCustomToString($values['required']); ?></span>
                            <div class="dig_icon_customfield">
                                <div class="icon-shape icon-shape-dims dig_cust_field_delete"></div>
                                <div class="icon-drag icon-drag-dims dig_cust_field_drag"></div>
                                <div class="icon-gear icon-gear-dims dig_cust_field_setting"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>

        <tfoot>
        <th></th>
        <td>
            <div id="dig_add_new_reg_field"><?php _e('ADD FIELD', 'digits'); ?></div>
            <?php do_action("dig_cf_add_new_btn"); ?>
        </td>
        </tfoot>
    </table>


    <div class="dig_side_bar">


        <div class="dig_sb_head"><?php _e('Select a type', 'digits'); ?></div>
        <div class="dig_sb_content">

            <div class="dig_sb_select_field">
                <?php
                $dig_custom_fields = digits_customfieldsTypeList();
                foreach ($dig_custom_fields as $fieldname => $type) {
                    if (isset($type['hidden']) && $type['hidden'] == 1) {
                        continue;
                    }
                    ?>

                    <div class="dig_sb_field_types dig_sb_field_list"
                         id="dig_cust_list_type_<?php echo $fieldname; ?>" data-val='<?php echo $fieldname; ?>'
                         data-configure_fields='<?php echo json_encode($type); ?>'>
                        <?php _e($type['name'], 'digits'); ?>
                    </div>

                    <?php

                }
                do_action('dig_custom_fields_list');

                echo '<div class="dig_dsc_cusfield">' . __('WordPress / WooCommerce Fields', 'digits') . '</div>';
                foreach (digits_presets_custom_fields() as $custom_field) {
                    ?>
                    <div class="dig_sb_field_wp_wc_types dig_sb_field_list"
                         id="dig_cust_list_type_<?php echo $custom_field['type']; ?>"
                         data-val='<?php echo $custom_field['type']; ?>'
                         data-values='<?php echo json_encode($custom_field['values']); ?>'
                         data-configure_fields='<?php echo json_encode($dig_custom_fields[$custom_field['type']]); ?>'>
                        <?php _e($custom_field['values']['label'], 'digits'); ?>
                    </div>
                    <?php
                    do_action('dig_custom_preset_fields_list');
                }
                ?><br/><br/><br/></div>

            <div class="dig_fields_options">
                <div class="dig_fields_options_main">
                    <input type="hidden" data-type="" id="dig_custom_field_data_type"/>
                    <div class="dig_sb_field" data-req="1" id="dig_field_label">
                        <div class="dig_sb_field_label">
                            <label for="custom_field_label"><?php _e('Label', 'digits'); ?><span
                                        class="dig_sb_required">*</span></label>
                        </div>
                        <div class="dig_sb_field_input">
                            <input type="text" id="custom_field_label" name="label"/>
                        </div>

                        <div class="dig_sb_field_tac dig_sb_extr_fields dig_sb_field_tac_desc">
                            <?php _e('Enclose the word(s) between [t] and [/t] for terms and condition and [p] and [/t] for privacy policy.', 'digits'); ?>
                            <br/><br/>
                            <?php _e('For example "I Agree [t]Terms and Conditions[/t] & [p]Privacy Policy[/t]"', 'digits'); ?>
                        </div>
                        <?php do_action('dig_custom_fields_label_desc'); ?>
                    </div>

                    <div class="dig_sb_field" id="dig_field_required" data-req="1">
                        <div class="dig_sb_field_label">
                            <label><?php _e('Required Field', 'digits'); ?><span
                                        class="dig_sb_required">*</span></label>
                        </div>
                        <div class="dig_sb_field_input">
                            <select name="required">
                                <option value="1"><?php _e('Yes', 'digits'); ?></option>
                                <option value="0"><?php _e('No', 'digits'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="dig_sb_field" id="dig_field_meta_key" data-req="1">
                        <div class="dig_sb_field_label">
                            <label for="custom_field_meta_key"><?php _e('Meta Key', 'digits'); ?><span
                                        class="dig_sb_required">*</span></label>
                        </div>
                        <div class="dig_sb_field_input">
                            <input type="text" id="custom_field_meta_key" name="meta_key"/>
                        </div>
                    </div>
                    <div class="dig_sb_field" id="dig_field_custom_class" data-req="0">
                        <div class="dig_sb_field_label">
                            <label for="custom_field_class"><?php _e('Custom Class', 'digits'); ?></label>
                        </div>
                        <div class="dig_sb_field_input">
                            <input type="text" id="custom_field_class" name="custom_class"/>
                        </div>
                    </div>

                    <div class="dig_sb_field" id="dig_field_options" data-req="1" data-list="1">
                        <div class="dig_sb_field_label">
                            <label><?php _e('Options', 'digits'); ?><span class="dig_sb_required">*</span></label>
                        </div>
                        <ul id="dig_field_val_list"></ul>

                        <div class="dig_sb_field_list dig_sb_field_add_opt">
                            <input type="text" class="dig_sb_field_list_input"
                                   placeholder="<?php _e('Add a Option', 'digits'); ?>"/>
                        </div>
                    </div>


                    <div class="dig_sb_field dig_sb_field_tac dig_sb_extr_fields" data-req="1">
                        <div class="dig_sb_field_label">
                            <label for="dig_csf_tac_link"><?php _e('Terms & Conditions Link', 'digits'); ?><span
                                        class="dig_sb_required">*</span></label>
                        </div>
                        <div class="dig_sb_field_input">
                            <input type="text" id="dig_csf_tac_link" name="tac_link"/>
                        </div>
                    </div>

                    <div class="dig_sb_field dig_sb_field_tac dig_sb_extr_fields" data-req="0">
                        <div class="dig_sb_field_label">
                            <label for="dig_csf_tac_privacy_link"><?php _e('Privacy Link', 'digits'); ?></label>
                        </div>
                        <div class="dig_sb_field_input">
                            <input type="text" id="dig_csf_tac_privacy_link" name="tac_privacy_link"/>
                        </div>
                    </div>


                    <div class="dig_sb_field dig_sb_extr_fields dig_sb_field_user_role" id="dig_field_roles"
                         data-req="1" data-list="2">
                        <div class="dig_sb_field_label">
                            <label><?php _e('User Roles', 'digits'); ?><span class="dig_sb_required">*</span></label>
                        </div>
                        <ul>


                            <?php
                            global $wp_roles;
                            foreach ($wp_roles->roles as $key => $value):
                                ?>
                                <label><input class="dig_chckbx_usrle" type="checkbox"
                                              value="<?php echo $key; ?>"/><?php echo $value['name']; ?></label>
                            <?php endforeach; ?>

                        </ul>
                    </div>


                    <?php do_action('dig_custom_fields_options'); ?>


                </div>


                <div id="dig_cus_field_footer">
                    <div class="dig_admin_blue dig_cus_field_done"><?php _e('Add', 'digits'); ?></div>
                    <div class="dig_admin_cancel"><?php _e('Back', 'digits'); ?></div>
                </div>

            </div>


        </div>


    </div>
    <?php

    do_action("after_dig_custom_section", digit_default_reg_fields(), $reg_custom_fields);

}

function digits_strtolower($str)
{
    return function_exists('mb_strtolower') ? mb_strtolower($str) : strtolower($str);
}