<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('XT_Framework_Settings')) {

    /**
     * Plugin Settings, extend admin tabs to support setting panels
     *
     * @package    XT_Framework_Settings
     * @author     XplodedThemes
     */

    class XT_Framework_Settings {

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   private
         * @var      XT_Framework    core    Core Class
         */
        private $core;

        protected $override_options = null;

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @var      XT_Framework    core    Core Class
         */
        public function __construct( $core ) {

            $this->core = $core;

            // Add setting tabs to the rest of the plugin tabs
            add_filter($this->core->plugin_prefix('admin_tabs'), array( $this, 'add_admin_tabs'), 1, 1);

            // Add Ajax Events
            add_filter($core->plugin_prefix('ajax_add_events'), array($this, 'ajax_add_events'), 1);

            // Show settings tab content type
            add_action($this->core->plugin_prefix('admin_tabs_show_tab'), array( $this, 'show_settings_tab_panel'), 10, 1);

            // Handle settings save
            add_action('admin_post_'.$this->core->ajax()->get_ajax_action('save_settings'), array( $this, 'save_settings') );
        }

        /**
         * Add ajax events
         *
         * @param array $ajax_events
         * @return mixed
         */
        public function ajax_add_events($ajax_events) {

            $prefix = $this->core->plugin_short_prefix().'_';

            $ajax_events[] = array(
                'function' => $prefix.'refresh_preview',
                'callback' => array($this, 'ajax_refresh_preview')
            );

            $ajax_events[] = array(
                'function' => $prefix.'save_settings',
                'callback' => array($this, 'ajax_save_settings')
            );

            $ajax_events[] = array(
                'function' => $prefix.'process_action',
                'callback' => array($this, 'ajax_process_action')
            );

            return $ajax_events;
        }

        /**
         * Get setting tabs
         * @since    1.0.0
         * @param bool $withValues
         *
         * @return array
         */
        public function get_setting_tabs($withValues = false) {

             return $this->core->cache()->result($this->core->plugin_prefix('setting_tabs_'.(string)($withValues)), function() use($withValues) {

                 return $this->sanitize_setting_tabs(apply_filters($this->core->plugin_prefix('setting_tabs'), array() ), $withValues);
             });

        }

        /**
         * Sanitize setting tabs
         *
         * @since    1.0.0
         * @param array $tabs
         * @param bool $withValues
         *
         * @return array
         */
        public function sanitize_setting_tabs($tabs, $withValues = false) {

            return array_map(function($tab) use($withValues) {

                if(!isset($tab['callbacks'])) {
                    $tab['callbacks'] = array();
                }

                $tab['callbacks'][] = array($this, 'setting_tab_callback');

                $tab['settings'] = is_callable($tab['settings']) ? $tab['settings']() : $tab['settings'];

                $tab['settings'] = apply_filters( $this->core->plugin_prefix('settings_'.$tab['id']),
                    $this->sanitize_fields($tab['settings'], $withValues),
                    $tab['settings']
                );

                return $tab;

            }, $tabs);
        }

        /**
         * Sanitize fields
         *
         * @since    1.0.0
         * @param array $fields
         * @param bool $withValues
         *
         * @return array
         */
        public function sanitize_fields($fields, $withValues = false) {

            return array_map(function($field) use($withValues) {

                return $this->sanitize_field($field, $withValues);

            }, $fields);
        }

        /**
         * Sanitize field
         *
         * @since    1.0.0
         * @param array $field
         * @param bool $withValues
         * @param array|null $parentField
         *
         * @return array
         */
        public function sanitize_field($field, $withValues = false, $parentField = null) {

            if(!empty($field['id'])) {

                if(!$parentField) {

                    $field['id'] = $this->core->plugin_short_prefix($field['id']);

                    if ( ! isset( $field['name'] ) ) {
                        $field['name'] = $field['id'];
                    }

                }else{

                    $field['group'] = $parentField['id'];
                    if ( ! isset( $field['name'] ) ) {
                        $field['name'] = $parentField['id'].'['.$field['id'].']';
                    }
                    $field['id'] = $this->field_flat_id($field['name']);
                }

            }else{

                $field['id'] = '';
                $field['name'] = '';
            }

            if ( ! isset( $field['class'] ) ) {
                $field['class'] = '';
            }
            if ( ! isset( $field['css'] ) ) {
                $field['css'] = '';
            }
            if ( ! isset( $field['default'] ) ) {
                $field['default'] = '';
            }
            if ( ! isset( $field['desc'] ) ) {
                $field['desc'] = '';
            }
            if ( ! isset( $field['desc_tip'] ) ) {
                $field['desc_tip'] = false;
            }
            if ( ! isset( $field['placeholder'] ) ) {
                $field['placeholder'] = '';
            }
            if ( ! isset( $field['prefix'] ) ) {
                $field['prefix'] = '';
            }
            if ( ! isset( $field['suffix'] ) ) {
                $field['suffix'] = '';
            }

            if(!empty($field['output']) || ($parentField && !empty($parentField['output']))) {
                $field['class'] .= 'xtfw-has-output';
            }

            if(!empty($field['id'])) {

                if($withValues) {
                    $default = isset($field['default']) ? $field['default'] : null;
                    $field['value'] = $this->get_option($field['name'], $default);
                }
            }

            if(isset($field['has_preview'])) {

                $field['has_preview']['id'] = $this->core->plugin_short_prefix($field['has_preview']['id']);

                if(isset($field['has_preview']['conditions'])) {
                    foreach($field['has_preview']['conditions'] as $ck => $condition) {
                        $field['has_preview']['conditions'][$ck]['id'] = $this->core->plugin_short_prefix($condition['id']);
                    }
                }
            }

            if(isset($field['preview'])) {
                $field['preview'] = $this->core->plugin_short_prefix($field['preview']);
            }

            if(isset($field['conditions'])) {
                foreach($field['conditions'] as $ck => $condition) {
                    $field['conditions'][$ck]['id'] = $this->core->plugin_short_prefix($condition['id']);
                }
            }

            if(isset($field['fields'])) {

                $field['fields'] = array_map(function($sfield) use($withValues, $field) {

                    return $this->sanitize_field($sfield, $withValues, $field);

                }, $field['fields']);

            }

            return $field;
        }

        public function get_current_setting_tab_id() {

            return !empty(filter_input(INPUT_POST, 'tab')) ? filter_input(INPUT_POST, 'tab') : filter_input(INPUT_GET, 'tab');
        }

        public function get_current_setting_tab_subid() {

            return filter_input(INPUT_GET, 'sub_id');
        }

        public function field_prefixed_id($id) {

            $unprefixed = $this->field_unprefixed_id($id);

            return $this->core->plugin_short_prefix($unprefixed);
        }

        public function field_unprefixed_id($id) {

            $prefix = $this->core->plugin_short_prefix();

            return str_replace($prefix.'_', "", $id);
        }

        public function field_flat_id($id) {

            return str_replace(array('[', ']'), array('_', ''), $id);
        }

        public function subfield_unprefixed_key($sfield) {

            //Get subfield original id, without prefix
            preg_match('/\[(.+?)\]/', $sfield['name'], $match);
            return $match[1];
        }

        public function get_field($id) {

            $fields = $this->get_setting_tab_fields(null, false, true);
            $fields = array_filter($fields, function($field) use($id) {

                return (!empty($field['id']) && $field['id'] === $id);
            });

            if(!empty($fields)) {
                return current($fields);
            }

            return null;
        }

        public function get_preview_section($preview_id) {

             $fields = $this->get_setting_tab_fields();

             $fields = array_filter($fields, function($field) use($preview_id) {
                return !empty($field['type']) && $field['type'] === 'title' && !empty($field['has_preview']) && $field['has_preview']['id'] === $preview_id;
             });

             $preview_section = current(array_map(function($field) {
                return $field['has_preview'];
             }, $fields));

             return !empty($preview_section) ? $preview_section : null;
        }

        /**
         * Get setting tab fields by tab id
         *
         * @param string    $tab_id
         * @param bool $withValues
         *
         * @return array
         *@since    1.0.0
         */
        public function get_setting_tab_fields($tab_id = null, $withValues = false, $flatten = false) {

            $tab_id = !empty($tab_id) ? $tab_id : $this->get_current_setting_tab_id();
            $setting_tabs = $this->get_setting_tabs($withValues);

            $tab_key = array_search($tab_id, array_column($setting_tabs, 'id'));
            $tab = $setting_tabs[$tab_key];

            $fields = ((!empty($tab['settings']))) ? $tab['settings'] : array();
			$fields = is_callable($fields) ? $fields() : $fields;

            if($flatten) {

               return $this->flatten_fields_with_subfields($fields);
            }

            return $fields;
        }

        /**
        * Get all setting tab fields
        *
        * @since 1.0.0
        * @param bool $withValues
        * @return array
        */
        public function get_all_setting_tab_fields($withValues = false, $flatten = false) {

            $fields = array();
            $setting_tabs = $this->get_setting_tabs($withValues);

            foreach($setting_tabs as $tab) {

                $fields = array_merge($fields, $tab['settings']);
            }

            if($flatten) {

               return $this->flatten_fields_with_subfields($fields);
            }

            return $fields;
        }

        public function flatten_fields_with_subfields($fields) {

             // Get all group fields
            $sfields = array_filter($fields, function($field) {
                return !empty($field['type']) && $field['type'] === 'group' && !empty($field['fields']);
            });

            // Flatted group subfields into 1 array
            $sfields = xtfw_array_flatmap(function($field) {
                return $field['fields'];
            }, $sfields);

            // Merge all output fields with group subfields into 1 array
            return array_merge($fields, $sfields);
        }

        /**
         * Get all setting tab admin actions
         *
         * @since    1.0.0
         */
        public function get_all_setting_tab_admin_actions() {

            $fields = $this->get_all_setting_tab_fields();

            $fields = array_filter($fields, function($field) {
                return !empty($field['id']) && !empty($field['type']) && empty($field['ajax']) && $field['type'] === 'admin_action';
            });

            return array_map(function($field) {
                return array(
                    'id' =>  $field['id'],
                    'callback' => $field['callback']
                );
            }, $fields);
        }

        /**
         * Get preview fields that need refreshing
         *
         * @since    1.0.0
         */
        public function get_preview_fields($preview_id) {

            $fields = $this->get_setting_tab_fields(null, false, true);

            return array_filter($fields, function($field) use($preview_id) {
                return !empty($field['preview']) && $field['preview'] === $preview_id;
            });
        }

        /**
         * Get preview field ids that need refreshing
         *
         * @since    1.0.0
         */
        public function get_preview_field_option_names($preview_id) {

            $preview_fields = $this->get_preview_fields($preview_id);

            return array_map(function($field) {
                return $field['name'];
            }, $preview_fields);
        }

        /**
         * Check if field is hidden by conditions
         *
         * @since    1.0.0
         */
        public function is_field_hidden($field) {

            $hidden = false;

            if(!empty($field['conditions'])) {

                $conditions = $field['conditions'];
                $total = count($conditions);
                $passed = 0;

                foreach($conditions as $condition) {

                    $targetField = $this->get_field($condition['id']);

                    if(!empty($targetField)) {

                        $targetFieldValue = $this->get_option($targetField['name']);
                        $conditionValue = $condition['value'];

                        $conditionOperator = !empty($condition['operator']) ? $condition['operator'] : '===';

                        // If value is an object and we have a condition on a specific item
                        if(!empty($condition['item'])) {

                            // find the item
                            $valueItem = array_filter(function($item) use($condition) {

                                return $item[$condition['item']['key']] === $condition['item']['value'];

                            }, $targetFieldValue);

                            $valueItem = !empty($valueItem) ? current($valueItem) : null;

                            // Override target value with the item value based on the condition key
                            $targetFieldValue = $valueItem[ $condition['item']['conditionKey'] ];
                        }

                        if (
                            ($conditionOperator === '===' && $targetFieldValue === $conditionValue) ||
                            ($conditionOperator === '!==' && $targetFieldValue !== $conditionValue) ||
                            ($conditionOperator === '<' && $targetFieldValue < $conditionValue) ||
                            ($conditionOperator === '>' && $targetFieldValue > $conditionValue) ||
                            ($conditionOperator === '<=' && $targetFieldValue <= $conditionValue) ||
                            ($conditionOperator === '>=' && $targetFieldValue >= $conditionValue) ||
                            ($conditionOperator === 'in' && in_array($targetFieldValue, $conditionValue)) ||
                            ($conditionOperator === 'not in' && !in_array($targetFieldValue, $conditionValue))
                        ) {
                            $passed++;
                        }
                    }
                }

                if($total !== $passed) {
                    $hidden = true;
                }
            }

            return $hidden;
        }

        /**
         * Get css output for all settings with an output property
         *
         * @since    1.0.0
         */
        public function get_settings_css_output($adminPreview = false) {

            $css = '';

            $fields = $this->get_all_setting_tab_fields(true, true);

            // If requesting css for admin settings view
            if($adminPreview) {

                // Check If has admin preview fields
                $previews = array_filter($fields, function($field) {
                    return !empty($field['type']) && $field['type'] === 'title' && !empty($field['has_preview']);
                });

                // If not, do not return any css;
                if(empty($previews)) {
                    return $css;
                }
            }

            // Filter fields that has outputs
            $fields = array_filter($fields, function($sfield) {
                return !empty($sfield['output']);
            });

            $css_array = array();
            foreach ($fields as $field) {

                if($this->is_field_hidden($field)) {
                    continue;
                }

                $is_group = $field['type'] === 'group';

                foreach($field['output'] as $item) {

                    $element = $item['element'];
                    $property = $item['property'];
                    $value_pattern = isset($item['value_pattern']) ? $item['value_pattern'] : '';
                    $value = isset($field['value']) ? $field['value'] : '';

                    if($is_group && is_array($value)) {

                        // Reorder array values based on original field group subfields
                        // Needed to correctly apply value_pattern, for padding / margin etc...
                        // First get original keys in order
                        $array_keys = array_map(function($sfield) {

                            return $this->subfield_unprefixed_key($sfield);

                        }, $field['fields']);

                        // Then sort based on that order
                        $value = array_replace(array_flip($array_keys), $value);

                        $values = array_values($value);

                        if(empty($value_pattern)) {

                            $value_pattern = '';
                            $total = count($values);
                            for ($i = 1 ; $i < count($values) ; $i++) {
                                $value_pattern .= '$'.$i;
                                if($i < ($total - 1)) {
                                    $value_pattern .= ' ';
                                }
                                $i++;
                            }
                        }

                        $value = $value_pattern;

                        foreach ($values as $key => $val) {
                            $value = str_replace('$'.($key+1), $val, $value);
                        }

                    }else if(!empty($value_pattern) && !is_array($value)) {

                        $value = str_replace('$', $value, $value_pattern);
                    }

                    $css_array[$element][$property] = $value;
                }
            }

            foreach($css_array as $element => $properties) {

                $css .= $element.'{';
                foreach($properties as $property => $value) {
                     if(!empty($value)) {
                        $css .= $property.':'.$value.';';
                     }
                }
                $css .= '}';
            }

            return $css;
        }


        /**
         * Generate css output for all settings with an output property
         *
         * @param null $handle
         * @since    1.0.0
         */
        public function generate_frontend_settings_css_output($handle) {

            $css = $this->core->transient()->result('settings_css_output', function() {
                return $this->get_settings_css_output();
            });

            if(!empty($css)) {

                wp_add_inline_style($handle, $css);
            }
        }

        public function ajax_refresh_preview() {

            $preview_id = filter_input(INPUT_POST, 'preview');

            $preview_fields = $this->get_preview_fields($preview_id);

            $data = $this->prepare_before_save_fields($preview_fields);

            $this->set_override_options($data['update_options']);

            $preview_section = $this->get_preview_section($preview_id);

            if(!empty($preview_section)) {

                $callback = $preview_section['callback'];
                $args = !empty($preview_section['args']) ? $preview_section['args'] : array();

                $preview = xtfw_ob_get_clean(function () use(&$callback, &$args) {
                    call_user_func_array($callback, $args);
                    $this->render_spinner();
                });

                $response = array(
                    'success' => true,
                    'preview' => $preview
                );

            }else{

                $response = array(
                    'success' => false,
                    'preview' => null
                );
            }

            wp_send_json( $response );
        }

        public function ajax_save_settings() {

            $settings_saved = $this->save_settings(true);

            $notices = xtfw_ob_get_clean(function() {
                $this->core->plugin_notices()->render_backend_messages();
            });

            wp_send_json(array(
                'success' => $settings_saved,
                'notices' => $notices
            ));
        }

        public function ajax_process_action() {

            // Handle admin actions
            
            $current_action = !empty(filter_input(INPUT_POST, 'action_id')) ? filter_input(INPUT_POST, 'action_id') : filter_input(INPUT_GET, 'action_id');

            $success = false;

            if(!empty($current_action)) {
                $callback = $this->get_admin_action_callback($current_action);
                if(!empty($callback)) {
                    call_user_func($callback);
                    $success = true;
                }
            }

            $notices = xtfw_ob_get_clean(function() {
                $this->core->plugin_notices()->render_backend_messages();
            });

            wp_send_json(array(
                'success' => $success,
                'notices' => $notices
            ));
        }

        /**
        * Get admin action field callback
        *
        * @param $action_id
        * @return null
        * @since    1.0.0
        */
        public function get_admin_action_callback($action_id) {

            $action = $this->get_field($action_id);

            return !empty($action) && !empty($action['callback']) ? $action['callback'] : null;
        }

        /**
        * Add admin tabs
        *
        * @since    1.0.0
        * @var      array    $tabs          tabs
        * @return array
        */
        public function add_admin_tabs($tabs) {

            $setting_tabs = $this->get_setting_tabs();

            return array_merge($setting_tabs, $tabs);
        }


        /**
         * Show settings tab content
         *
         * @since    1.0.0
         * @var      array    $tab          tab
         * @var      string   $tab_type     tab type
         */
        public function show_settings_tab_panel($tab) {

            if (!empty($tab['settings'])) {

                $this->render_settings_form($tab['id'], $tab['settings']);
            }
        }

         /**
         * Output settings form.
         *
         * Loops though the setting fields array and outputs each field.
         *
         * @param string $tab_id Tab ID
         * @param array[] $settings Opens array to output.
         */
        public function render_settings_form($tab_id, $settings) {

            $has_sections = $this->has_sections($settings);

            $form_classes = array('xtfw-settings-form');

            if($has_sections) {
                $form_classes[] = 'xtfw-settings-sectioned';
            }

            $form_classes = implode(" ", $form_classes);

            do_action($this->core->plugin_prefix('settings_rendering'), $tab_id);
            do_action($this->core->plugin_prefix('settings_'.$tab_id.'_rendering'));

            $form_action = 'admin-post.php';
            $sub_id = $this->get_current_setting_tab_subid();

            if(!empty($sub_id)) {
                $form_action .= '?sub_id='.$sub_id;
            }
            ?>
            <form class="<?php echo esc_attr($form_classes);?>" method="post" action="<?php echo esc_attr($form_action); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo esc_attr($this->core->ajax()->get_ajax_action('save_settings'));?>" />
                <input type="hidden" name="tab" value="<?php echo esc_attr($tab_id);?>" />
                <?php
                    wp_nonce_field( $this->core->plugin_prefix('settings_save_verify') );
                    $this->render_settings($settings, $has_sections);
                ?>
                <div class="xtfw-settings-form-footer">
                    <button id="xtfw-save-settings" type="submit" class="button-primary">
                        <?php esc_attr_e( 'Save Changes', 'xt-framework' ) ?>
                        <?php $this->render_spinner(); ?>
                    </button>
                    <button id="xtfw-reset-all-settings" data-confirm="<?php echo esc_attr(esc_html__('Attention!!! This will reset all settings of all sections to their default values! Are you sure you want to proceed?', 'xt-framework')); ?>" type="button" class="button-secondary">
                        <?php esc_attr_e( 'Reset All', 'xt-framework' ) ?>
                        <?php $this->render_spinner(); ?>
                    </button>
                </div>
            </form>
            <?php

            do_action($this->core->plugin_prefix('settings_rendered'), $tab_id);
            do_action($this->core->plugin_prefix('settings_'.$tab_id.'_rendered'));
        }

        public function render_field($field, &$section_has_preview = false) {

            if ( ! isset( $field['type'] ) ) {
               return;
            }

            $field_filename = $field['type'];

             // Switch based on type.
            switch ( $field['type'] ) {

                // Standard text inputs and subtypes like 'number'.
                case 'text':
                case 'password':
                case 'datetime':
                case 'datetime-local':
                case 'date':
                case 'month':
                case 'time':
                case 'week':
                case 'number':
                case 'range':
                case 'email':
                case 'url':
                case 'tel':

                    $field_filename = 'standard';
                    break;

                // Select boxes.
                case 'select':
                case 'multiselect':

                    $field_filename = 'select';
                    break;

                case 'radio':
				case 'radio-buttons':

                    $field_filename = 'radio';
                    break;
            }

            if(file_exists(XTFW_DIR_SETTINGS."/fields/".$field_filename.".php")) {
	            include XTFW_DIR_SETTINGS."/fields/".$field_filename.".php";
            }else {
	            do_action( $this->core->plugin_prefix( 'settings_field_' ) . $field['type'], $field );
            }
        }

        public function render_field_label($field) {
            ?>
            <label for="<?php echo esc_attr( $field['id'] ); ?>">
                <?php echo esc_html( $field['title'] ); ?>
                <?php $this->render_field_tooltip($field); ?>
                <?php $this->reset_value_button($field); ?>
            </label>
            <?php
        }

        public function reset_value_button($field) {
            $id = $field['name'];
            ?>
            <span class="xtfw-settings-reset" title="<?php echo esc_attr(esc_html__('Reset this setting to it\'s default value!', 'xt-framework')); ?>" data-id="<?php echo esc_attr($id);?>">
                <span class="dashicons dashicons-undo"></span>
            </span>
            <?php
        }

        public function reset_section_button() {
            ?>
            <button type="button" class="xtfw-reset-section-settings button-secondary" data-confirm="<?php echo esc_attr(esc_html__('Attention!!! This will reset all settings within this section to their default values! Are you sure you want to proceed?', 'xt-framework')); ?>">
                <?php esc_attr_e( 'Reset Values', 'xt-framework' ) ?>
                <?php $this->render_spinner(); ?>
            </button>
            <?php
        }

         /**
         * Output settings fields.
         *
         * Loops though the setting fields array and outputs each field.
         *
         * @param array[] $settings Opens array to output.
         * @param bool $has_sections
         */
        public function render_settings($settings, $has_sections) {

            if(!$has_sections) {
                echo '<div class="xtfw-settings-section">' . "\n\n";
                echo '<table class="form-table">' . "\n\n";
            }

            $section = 0;
            $section_has_preview = false;

            foreach ( $settings as $field ) {

                // Render section reset button
                $is_new_section = !empty($field['type']) && $field['type'] === 'title';
	            $is_end_section = !empty($field['type']) && $field['type'] === 'sectionend';

                if(!$has_sections && ($is_new_section || $is_end_section)) {
                    continue;
                }

	            if ( $is_new_section && !empty( $field['title'] ) ) {
		            echo '<div class="xtfw-settings-title" data-sub_id="'.esc_attr($section).'">' . esc_html( $field['title'] );
		            $this->reset_section_button();
		            echo '</div>';
	            }

	            $this->render_field($field, $section_has_preview);

                if($is_new_section) {
	                $section++;
                }
            }

            if(!$has_sections) {
                echo '</table>';
                echo '</div>';
            }

        }

        public function has_sections($sections) {

            $titles = array_filter($sections, function($section) {

                return !empty($section['type']) && $section['type'] === 'title';
            });

            return !empty($titles) && count($titles) > 1;
        }

        /**
         * Helper function to render the formatted description HTML for a
         * given form field. Plugins can call this when implementing their own custom
         * settings types.
         *
         * @param  array $field The form field value array.
         *
         * @return array The description html
         */
        public function render_field_description( $field ) {

            $description  = '';

            if ( true === $field['desc_tip'] ) {
            } elseif ( ! empty( $field['desc_tip'] ) ) {
                $description  = $field['desc'];
            } elseif ( ! empty( $field['desc'] ) ) {
                $description = $field['desc'];
            }

            if ( $description && in_array( $field['type'], array( 'textarea', 'radio', 'checkbox', 'admin_action' ), true ) ) {
                if(!empty($field['desc_inline'])) {
                    ?>
	                <span class="description inline"><?php echo wp_kses_post( $description ); ?></span>
                    <?php
                }else {
                    ?>
	                <p class="description"><?php echo wp_kses_post( $description );?></p>
                    <?php
                }
            } elseif ( $description && in_array( $field['type'], array( 'checkbox' ), true ) ) {

                echo wp_kses_post( $description );

            } elseif ( $description ) {
                ?>
                <span class="description"><?php echo wp_kses_post( $description ); ?></span>
                <?php
            }
        }

        public function render_field_before( $field ) {

            if(!empty($field['before'])) {

                call_user_func($field['before']);
            }
        }

        public function render_field_after( $field ) {

            if(!empty($field['after'])) {

                call_user_func($field['after']);
            }
        }

        /**
         * Helper function to render the formatted tip HTML for a
         * given form field. Plugins can call this when implementing their own custom
         * settings types.
         *
         * @param  array $field The form field value array.
         */
        public function render_field_tooltip( $field ) {

            $tooltip_html = '';

            if ( true === $field['desc_tip'] ) {
                $tooltip_html = $field['desc'];
            } elseif ( ! empty( $field['desc_tip'] ) ) {
                $tooltip_html = $field['desc_tip'];
            }

            xtfw_help_tip( $tooltip_html );
        }

        /**
         * Helper function to render input attributes
         *
         * @param  array $field The form field value array.
         */
        public function render_input_attributes( $field ) {

            // if type range, add oninput attribute to output the amount
            if($field['type'] == 'range') {
                $field['custom_attributes']['oninput'] =  $field['id'].'_amount.value="'.$field['prefix'].'" + '.$field['id'].'.value + "'.$field['suffix'].'"';
            }

            // If preview needs refresh
            if(!empty($field['preview'])) {
                $field['custom_attributes']['data-preview'] = $field['preview'];
            }

            if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

                foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
                    if(is_string($attribute_value)) {
	                    echo ' ' . esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                    }
                }
            }
        }

        /**
         * Save the settings page
         *
         * @since 1.0
         */
        public function save_settings($ajax = false) {

            // Check the nonce.
            check_admin_referer( $this->core->plugin_prefix('settings_save_verify') );

            // Get the setting fields
            $tab_id = $this->get_current_setting_tab_id();
            $fields = $this->get_setting_tab_fields($tab_id, false, true);

            do_action($this->core->plugin_prefix('settings_saving'), $tab_id);
            do_action($this->core->plugin_prefix('settings_'.$tab_id.'_saving'));

            // Save the settings
            $settings_saved = $this->save_fields( $fields );

            if($settings_saved) {

                $this->core->plugin_notices()->add_success_message( esc_html__('Settings saved successfully!', 'xt-framework') );

                do_action($this->core->plugin_prefix('settings_saved'), $tab_id);
                do_action($this->core->plugin_prefix('settings_'.$tab_id.'_saved'));

                $this->core->transient()->delete('settings_css_output');

                // flush rewrite to make sure new endpoint works if any
                flush_rewrite_rules();

            }else{

                $this->core->plugin_notices()->add_error_message( esc_html__('Failed saving settings!', 'xt-framework') );
            }

            if($ajax) {

                return $settings_saved;
            }

            // Set redirect args
            $redirect_args = array();
            if(!empty($_GET['sub_id'])) {
                $redirect_args['sub_id'] = $this->get_current_setting_tab_subid();
            }

            // Go back to the settings page.
            wp_redirect( $this->core->plugin_admin_url( $tab_id, $redirect_args) );
            exit;

        }

        /**
         * Save admin fields.
         *
         * Loops though the plugin options array and outputs each field.
         *
         * @param array $fields Options array to save.
         *
         * @return bool
         */
        public function save_fields( $fields) {

            $data = $this->prepare_before_save_fields($fields);

            return $this->save_fields_options($data['update_options'], $data['autoload_options']);
        }

        /**
         * Prepare fields before Save  .
         *
         * Loops though the plugin options array and prepare options for update
         *
         * @param array $fields Options array to save.
         *
         * @return array containing update_options and autoload_options
         */
        public function prepare_before_save_fields( $fields) {

            $data = filter_input_array(INPUT_POST);

            if ( empty( $data ) ) {
                return false;
            }

             // Options to update will be stored here and saved later.
            $update_options   = array();
            $autoload_options = array();

            // Loop options and get values to save.
            foreach ( $fields as $field ) {

                if ( ! isset( $field['name'] ) || ! isset( $field['type'] ) ) {
                    continue;
                }

                if($field['type'] === 'group') {
                    continue;
                }

                // Get posted value.

                if ( strstr( $field['name'], '[' ) ) {
                    parse_str( $field['name'], $option_name_array );
                    $option_name  = current( array_keys( $option_name_array ) );
                    $setting_name = key( $option_name_array[ $option_name ] );
                    $raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
                } else {
                    $option_name  = $field['name'];
                    $setting_name = '';
                    $raw_value    = isset( $data[ $field['name'] ] ) ? wp_unslash( $data[ $field['name'] ] ) : null;
                }

                $option_type = $field['type'];

                // Format the value based on option type.
                switch ( $option_type ) {
                    case 'checkbox':
                        $value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
                        break;
                    case 'textarea':
                        $value = wp_kses_post( trim( $raw_value ) );
                        break;
                    case 'multiselect':
                    case 'multi_select_countries':
                        $value = array_filter( array_map( 'xtfw_clean', (array) $raw_value ) );
                        break;
                    case 'image_width':
                        $value = array();
                        if ( isset( $raw_value['width'] ) ) {
                            $value['width']  = xtfw_clean( $raw_value['width'] );
                            $value['height'] = xtfw_clean( $raw_value['height'] );
                            $value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
                        } else {
                            $value['width']  = $field['default']['width'];
                            $value['height'] = $field['default']['height'];
                            $value['crop']   = $field['default']['crop'];
                        }
                        break;
                    case 'select':
                        $allowed_values = empty( $field['options'] ) ? array() : array_map( 'strval', array_keys( $field['options'] ) );
                        if ( empty( $field['default'] ) && empty( $allowed_values ) ) {
                            $value = null;
                            break;
                        }
                        $default = ( empty( $field['default'] ) ? $allowed_values[0] : $field['default'] );
                        $value   = in_array( $raw_value, $allowed_values, true ) ? $raw_value : $default;
                        break;
                    case 'relative_date_selector':
                        $value = xtfw_parse_relative_date_option( $raw_value );
                        break;
                    default:
                        $value = xtfw_clean( $raw_value );
                        break;
                }

                /**
                 * Sanitize the value of an option.
                 *
                 * @since 1.0.0
                 */
                $value = apply_filters( $this->core->plugin_prefix('settings_sanitize_option'), $value, $field, $raw_value );

                /**
                 * Sanitize the value of an option by option name.
                 *
                 * @since 1.0.0
                 */
                $value = apply_filters( $this->core->plugin_prefix("settings_sanitize_option_$option_name"), $value, $field, $raw_value );

                /**
                 * Sanitize the value of an option by option type.
                 *
                 * @since 1.0.0
                 */
                $value = apply_filters( $this->core->plugin_prefix("settings_sanitize_option_type_$option_type"), $value, $field, $raw_value );


                if ( is_null( $value ) ) {
                    continue;
                }

                // Check if option is an array and handle that differently to single values.
                if ( $option_name && $setting_name ) {

                    if ( ! isset( $update_options[ $option_name ] ) ) {
                        $update_options[ $option_name ] = get_option( $option_name, array() );
                    }

                    if ( ! is_array( $update_options[ $option_name ] ) ) {
                        $update_options[ $option_name ] = array();
                    }

                    // If has a parent
                    if(!empty($field['group'])) {

                        $parent_field = $this->get_field($field['group']);

                        // Get parent sub fields keys
                        $parentSubFieldsKeys = array_map(function($sfield) {

                            return $this->subfield_unprefixed_key($sfield);

                        }, $parent_field['fields']);

                        // Go through current array value and remove keys that should not exists
                        foreach($update_options[ $option_name ] as $key => $val) {

                            if(!in_array($key, $parentSubFieldsKeys)) {
                                unset($update_options[ $option_name ][ $key ]);
                            }
                        }
                    }

                    $update_options[ $option_name ][ $setting_name ] = $value;

                } else {

                    $update_options[ $option_name ] = $value;
                }

                $autoload_options[ $option_name ] = isset( $field['autoload'] ) ? (bool) $field['autoload'] : true;
            }

            return array(
                'update_options' => $update_options,
                'autoload_options' => $autoload_options
            );
        }


        /**
         * Save admin fields options.
         *
         * Loops though the plugin options array and save them
         *
         * @param array $options Options array.
         * @param array $autoload_options
         *
         * @return bool
         */
        public function save_fields_options( $options, $autoload_options) {

            // Save all options in our array.
            foreach ( $options as $name => $value ) {

                update_option( $name, $value, $autoload_options[ $name ] ? 'yes' : 'no' );
            }

            return true;
        }


        /**
         * Get a setting from the settings API.
         *
         * @param string $option_name Option name.
         * @param mixed  $default     Default value.
         * @param array|null $options Provide options data
         *
         * @return mixed
         */
        public function get_option( $option_name, $default = '') {

            $options = $this->get_override_options();

            $cache_key = md5($option_name.(!empty($options) ? '_options' : ''));

            return $this->core->cache()->result($cache_key, function() use(&$option_name, &$default, &$options) {

                $option_name = $this->field_prefixed_id($option_name);

                $field = $this->get_field($option_name);

                if(!$this->core->plugin_is_admin_url() && $this->is_field_hidden($field)) {
                    return null;
                }

                // Array value.
                if ( strstr( $option_name, '[' ) ) {

                    parse_str( $option_name, $option_array );

                    // Option name is first key.
                    $_option_name = current( array_keys( $option_array ) );

                    // Get value.
                    if($options) {
                       $option_values = isset($options[$_option_name]) ? $options[$_option_name] : get_option( $_option_name, '' );
                    }else{
                       $option_values =  get_option( $_option_name, '' );
                    }

                    $key = key( $option_array[ $_option_name ] );

                    if ( isset( $option_values[ $key ] ) ) {
                        $option_value = $option_values[ $key ];
                    } else {
                        $option_value = null;
                    }

                } else {

                    // Single value.
                    if($options) {
                       $option_value = isset($options[$option_name]) ? $options[$option_name] : get_option( $option_name, null );
                    }else{
                       $option_value =  get_option( $option_name, null );
                    }
                }

                if ( is_array( $option_value ) ) {
                    $option_value = array_map( 'stripslashes', $option_value );
                } elseif ( ! is_null( $option_value ) ) {
                    $option_value = stripslashes( $option_value );
                }

                // If null, return default value from field if available
                if( $option_value === null ) {

                    $field = $this->get_field($option_name);
                    $option_value = !empty($field['default']) ? $field['default'] : $default;
                }

                return apply_filters( $option_name, $option_value, $option_name );
            });

        }

        /**
         * Get a bool setting from the settings API.
         *
         * @param string $option_name Option name.
         * @param mixed  $default     Default value.
         * @param array|null $options Provide options data
         *
         * @return bool
         */
        public function get_option_bool( $option_name, $default = false) {

            $value = $this->get_option($option_name, $default);

            return ($value === 'yes' || $value === '1' || $value === true);
        }

        public function get_override_options() {

            return $this->override_options;
        }

        public function set_override_options($options) {

            $this->override_options = $options;
        }

        /**
         * Get settings page sub section id
         *
         * @since 1.0
         */
        public function get_settings_section_id() {

            return $this->core->plugin_tabs()->get_tab_id() !== '' && isset($_GET['sub_id']) ? $this->get_current_setting_tab_subid() : null;
        }

        /**
         * Setting tab callback function
         *
         * @since    1.0.0
         */
        public function setting_tab_callback() {

            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_assets'));
            add_action( 'admin_body_class', array($this, 'settings_body_class'), 1 );
        }


        /**
         * Register the JavaScript & stylesheets for the settings tabs area.
         *
         * @since    1.0.0
         */
        public function enqueue_assets() {

            // Enqueue settings assets

            $handle = 'xtfw_settings';

            wp_enqueue_style('wp-color-picker');

            wp_enqueue_style(
                $handle,
                xtfw_dir_url( XTFW_DIR_SETTINGS_ASSETS ) . '/css/settings.css',
                array(
                    'wp-color-picker',
                    'xt-jquery-ui',
                    'xt-jquery-tiptip',
                    'xt-jquery-select2'
                ),
                XTFW_VERSION
           );

            if(!empty($_GET['premium_css'])) {
                wp_enqueue_style( $handle.'-premium', xtfw_dir_url( XTFW_DIR_SETTINGS_ASSETS ) . '/css/settings-premium.css', array(), XTFW_VERSION );
            }

            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-accordion');

            wp_enqueue_script(
                $handle,
                xtfw_dir_url( XTFW_DIR_SETTINGS_ASSETS ) . '/js/settings'.XTFW_SCRIPT_SUFFIX.'.js',
                array(
                    'jquery',
                    'jquery-ui-datepicker',
                    'jquery-ui-accordion',
                    'wp-color-picker',
                    'xt-color-picker',
                    'xt-sticky-sidebar',
                    'xt-jquery-tiptip',
                    'xt-jquery-select2',
                    'xt-jquery-inline-confirm'
                ),
                XTFW_VERSION
            );

            wp_localize_script(
                $handle,
                'xtfw_settings',
                array(
                    'prefix' => $this->core->plugin_short_prefix(),
                    'ajax_action' => $this->core->ajax()->get_ajax_action('%%action%%'),
                    'assets_url' => xtfw_dir_url(XTFW_DIR_SETTINGS_ASSETS),
                    'sub_id' => $this->get_settings_section_id(),
                    'fields' => $this->get_setting_tab_fields()
                )
            );

            $css = $this->get_settings_css_output(true);

            if(!empty($css)) {
                wp_add_inline_style($handle, $css);
            }

        }

        public function settings_body_class( $classes ) {

            $classes .= ' xtfw-settings-page';

			return $classes;
		}

		public function render_spinner() {
            ?>
            <span class="xtfw-spinner"><span class="xtfw-spinner-inner"><span></span><span></span><span></span><span></span></span></span>
            <?php
		}
    }
}