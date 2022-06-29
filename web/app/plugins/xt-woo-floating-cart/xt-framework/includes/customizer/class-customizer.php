<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('XT_Framework_Customizer')) {

    /**
     * Class that takes care of rendering common message blocks
     *
     * @since      1.0.0
     * @package    XT_Framework
     * @subpackage XT_Framework/includes
     * @author     XplodedThemes
     */
    class XT_Framework_Customizer {

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   private
         * @var      XT_Framework    $core    Core Class
         */
        protected $core;

	    /**
	     * Core class reference.
	     *
	     * @since    1.0.0
	     * @access   private
	     * @var      XT_Framework_Module|null    $module    Module Class
	     */
        protected $module;
	    protected $is_module;

        protected $config_id;
        protected $breakpoints = array();
        protected $media_queries = array();
        protected $panels = array();
        protected $sections = array();
        protected $fields = array();
        protected $section_fields_counter = array();

        /**
         * Class constructor
         * @param $core
         */
        public function __construct( $core, $module = null ) {

            $this->core = $core;

            $this->module = $module;
            $this->is_module = !empty($module);

            add_filter( 'xirki_telemetry', '__return_false', 1 );

            require_once XTFW_DIR_CUSTOMIZER . '/class-customizer-helpers.php';
            require_once XTFW_DIR_CUSTOMIZER . '/class-customizer-options.php';
            require_once XTFW_DIR_CUSTOMIZER . '/xirki/xirki.php';

            $this->set_config_id();
            $this->hooks();
            $this->init();
        }

        /**
         * Set config ID
         */
        public function set_config_id() {

            $this->config_id = $this->is_module ? $this->module->id() : $this->core->plugin_id();
        }

        /**
         * Get config ID
         */
        public function config_id() {

            return $this->config_id;
        }


        public function hooks() {

            add_action( 'customize_register', array( $this, 'customizer_controls' ) );

            add_action( 'customize_preview_init', array( $this, 'customizer_preview_assets' ) );
            add_action( 'customize_controls_enqueue_scripts', array( $this, 'customizer_controls_assets' ) );

            add_filter( 'upload_mimes', array( $this, 'allow_myme_types'), 1, 1 );
            add_filter( 'wp_check_filetype_and_ext', array( $this, 'check_filetype_and_ext'), 10, 4 );

            add_filter('body_class', array( $this, 'customizer_preview_class'), 1, 1);

            if(!$this->is_module) {
                add_filter($this->core->plugin_prefix('admin_tabs'), array( $this, 'admin_tabs'), 1, 1);
            }
        }

        /**
         * Get prefix
         */
        public function prefix($suffix = null) {

            return $this->is_module ? $this->module->prefix($suffix) : $this->core->plugin_prefix($suffix);
        }

        public function apply_filters() {

            $this->breakpoints = apply_filters($this->prefix('customizer_breakpoints'), $this->breakpoints, $this);
            $this->panels = apply_filters($this->prefix('customizer_panels'), $this->panels, $this);
            $this->sections = apply_filters($this->prefix('customizer_sections'), $this->sections, $this);
            $this->fields = apply_filters($this->prefix('customizer_fields'), $this->fields, $this);
        }

        /**
         * Init customizer
         */
        public function init() {

	        $this->apply_filters();
            $this->add_config();
	        $this->set_breakpoints();
            $this->add_panels();
            $this->add_sections();
            $this->add_fields();
        }

        /**
         * @param $wp_customize
         */
        public function customizer_controls($wp_customize ) {

            require_once XTFW_DIR_CUSTOMIZER . '/class-customizer-controls.php';

            $controls   = glob( dirname( __FILE__ ) . '/controls/*/*.php' );
            $controls = apply_filters('xtfw_customizer_controls', $controls);

            new XT_Framework_Customizer_Controls( $controls, $wp_customize );
        }

	    /**
	     * Get breakpoint
	     * @param $screen
	     * @return int
	     */
	    public function breakpoint($screen) {

		    return !empty($this->breakpoints[$screen]) ? $this->breakpoints[$screen] : null;
	    }

	    /**
	     * Get breakpoints
	     * @return array
	     */
	    public function breakpoints() {

		    if(empty($this->breakpoints)) {
			    $this->set_breakpoints();
		    }

		    return $this->breakpoints;
	    }

	    /**
	     * Get breakpoints layouts json
	     * @return array
	     */
	    public function breakpointsJson($type = 'max') {

		    $breakpoints = array();
		    $map = array(
                'small_mobile'  => 'XS',
		        'mobile'        => 'S',
                'tablet'        => 'M',
                'desktop'       => 'L',
                'large_desktop' => 'XL'
            );

            foreach($this->breakpoints as $screen => $breakpoint) {

                if(!empty($breakpoint[$type]) && !empty($map[$screen])) {
                    $key = $map[$screen];
                    $breakpoints[$key] = $breakpoint[$type];
                }
            }

		    return $breakpoints;
	    }

	    /**
	     * Get media query
	     * @param $screen
	     * @param $type
	     * @return string
	     */
	    public function media_query($screen, $type = 'minmax') {

            if(empty($this->media_queries)) {
                $this->set_breakpoints();
            }

		    return !empty($this->media_queries[$screen][$type]) ? $this->media_queries[$screen][$type] : null;
	    }

	    /**
	     * Get media queries
	     * @return array
	     */
	    public function media_queries() {

		    return $this->media_queries;
	    }

        /**
         * Get panel ID
         * @param null $id
         * @return string
         */
        public function panel_id( $id = null ) {

            if(empty($this->panels)) {
                return '';
            }

            $panel_id = $this->config_id();

            if ( ! empty( $id ) ) {
                $panel_id .= '-' . $id;
            }

            return $panel_id;
        }

        /**
         * Get section ID
         * @param $id
         * @return string
         */
        public function section_id( $id ) {

            return $this->config_id() . '-' . $id;
        }

        /**
         * Xirki Config
         */
        public function add_config() {

            Xirki::add_config( $this->config_id(), array(
                'capability'  => 'edit_theme_options',
                'option_type' => 'option',
                'option_name' => $this->config_id()
            ) );
        }

	    /**
	     * Set Responsive breakpoints.
	     */
	    public function set_breakpoints() {

		    $default_breakpoints = array(
			    'small_mobile' => array(
				    'min' => null,
				    'max' => 300,
			    ),
			    'mobile' => array(
			        'min' => 301,
                    'max' => 480, // Should be minimum 480
                ),
			    'tablet' => array(
				    'min' => 481,
				    'max' => 782, // Should be minimum 768
			    ),
			    'desktop' => array(
				    'min' => 783,
				    'max' => 1024,
			    ),
			    'large_desktop' => array(
				    'min' => 1025,
				    'max' => null,
			    ),
		    );

		    $this->breakpoints = array_merge($default_breakpoints, $this->breakpoints);

		    if(!empty($this->breakpoints['tablet']) && !empty($this->breakpoints['mobile'])) {

			    $this->breakpoints['tablet_mobile'] = array(
				    'min' => $this->breakpoints['mobile']['min'],
				    'max' => $this->breakpoints['tablet']['max'],
			    );

			    $this->breakpoints['desktop_tablet'] = array(
                    'min' => $this->breakpoints['tablet']['min'],
                    'max' => $this->breakpoints['desktop']['max'],
			    );

            }

		    $this->set_media_queries();

	    }

	    /**
	     * Set Responsive media queries.
	     */
	    private function set_media_queries() {

	        $breakpoints = $this->breakpoints();
	        $media_queries = array();

	        foreach($breakpoints as $screen => $breakpoint) {

		        $media_query_prefix = '@media screen and ';

		        $media_query = '';
		        $media_query_min = '';
		        $media_query_max = '';

	            if(!empty($breakpoint['min'])) {
		            $media_query .= '(min-width: '.$breakpoint['min'].'px)';
		            $media_query_min .= '(min-width: '.$breakpoint['min'].'px)';
                }
		        if(!empty($breakpoint['min']) && !empty($breakpoint['max'])) {
			        $media_query .= ' and ';
		        }
		        if(!empty($breakpoint['max'])) {
			        $media_query .= '(max-width: '.$breakpoint['max'].'px)';
			        $media_query_max .= '(max-width: '.$breakpoint['max'].'px)';
		        }

		        if(!empty($media_query)) {
			        $media_queries[ $screen ]['minmax'] = $media_query_prefix . $media_query;
		        }
		        if(!empty($media_query_min)) {
			        $media_queries[ $screen ]['min'] = $media_query_prefix . $media_query_min;
		        }
		        if(!empty($media_query_max)) {
			        $media_queries[ $screen ]['max'] = $media_query_prefix . $media_query_max;
		        }
            }

		    $this->media_queries = $media_queries;

	    }

        /**
         * Add panels to Xirki.
         */
        public function add_panels() {

            $count = 0;
            foreach ($this->panels as $panel) {

                $panel_id = !empty( $panel['id'] ) ? $this->panel_id( $panel['id'] ) : $this->panel_id();

                if($count > 0) {
                    $panel['panel'] = isset( $panel['panel'] ) ? $this->panel_id( $panel['panel'] ) : $this->panel_id();
                }

                $panel['priority']   = isset( $panel['priority'] ) ? $panel['priority'] : 0;

                if ( !empty( $panel['id'] ) ) {
                    unset( $panel['id'] );
                }

                Xirki::add_panel( $panel_id, $panel);

                $count++;
            }
        }

        /**
         * Add sections to Xirki.
         */
        public function add_sections() {

            $count = 0;
            foreach ( $this->sections as $key => $section ) {

                $section_id            = $this->section_id( $section['id'] );

                $section['capability'] = !empty( $section['capability'] ) ? $section['capability'] : 'edit_theme_options';
                $section['priority']   = isset( $section['priority'] ) ? $section['priority'] : 10;
                $section['panel'] = !empty($section['panel']) ? $this->panel_id($section['panel']) : $this->panel_id();

                if ( ! empty( $section['id'] ) ) {
                    unset( $section['id'] );
                }

                Xirki::add_section( $section_id, $section);

                $count++;
            }
        }

        /**
         * Add fields to Xirki.
         */
        public function add_fields() {

            foreach ( $this->fields as $field ) {

                $field['settings'] = ! empty( $field['id'] ) ? $field['id'] : $field['settings'];
                $field['section']  = $this->section_id( $field['section'] );
                $field['priority'] = isset( $field['priority'] ) ? $field['priority'] : 10;

	            // If field has a css output
                if(!empty($field['output'])) {
                    foreach($field['output'] as $key => $item) {

	                    // If it's a responsive field, add correct media queries
                        if(!empty($field['screen']) && empty($item['media_query'])) {

                            $screen = $field['screen'];
                            $type = in_array($screen, array('desktop_tablet', 'desktop', 'large_desktop')) ? 'min' : 'max';
	                        $media_query = $this->media_query($screen, $type);

                            if(!empty($media_query)) {

	                            $field['output'][$key]['media_query'] = $media_query;
                            }
                        }

	                    // Fix Xirki pattern_replace fields ids to include the config id
                        if(!empty($item['pattern_replace'])) {

                            foreach($item['pattern_replace'] as $k => $val) {
                                $field['output'][$key]['pattern_replace'][$k] = $this->config_id().'['.$val.']';
                            }
                        }
                    }
                }

                if ( !empty( $field['id'] ) ) {
                    unset( $field['id'] );
                }

                if(!isset($this->section_fields_counter[$field['section']]['all'])) {
                    $this->section_fields_counter[$field['section']]['all'] = 0;
                }
                $this->section_fields_counter[$field['section']]['all']++;

                if(!empty($field['type']) && $field['type'] === 'xt-premium') {
                    if(!isset($this->section_fields_counter[$field['section']]['premium'])) {
                        $this->section_fields_counter[$field['section']]['premium'] = 0;
                    }
                    $this->section_fields_counter[$field['section']]['premium']++;
                }

                Xirki::add_field( $this->config_id(), $field );
            }

        }

        public function panels() {

            return $this->panels;
        }

        public function sections() {

            return $this->sections;
        }

        public function fields() {

            return $this->fields;
        }

        /**
         * Add customizer admin tab
         * @param $tabs
         * @return array
         */
        public function admin_tabs( $tabs ) {

            $tabs[] = array(
                'id'          => 'customizer',
                'title'       => esc_html__( 'Customize', 'xt-framework' ),
                'show_menu'   => false,
                'action_link' => true,
                'content'    => array(
                    'type' => 'function',
                    'function' => array($this, 'customizer_tab_section')
                ),
                'order' => 0
            );

            return $tabs;
        }

        public function customizer_tab_section() {

            if(!empty($this->panels)) {
                $this->render_customizer_tab_panels();
            }

            if(!$this->is_module && $this->core->has_modules(true)) {

	            foreach($this->core->modules()->all(true) as $module) {

                    if(empty($module->customizer())) {
                        continue;
                    }

                    $customizer = $module->customizer();

                    if(!empty($this->panels)) {
                        echo '<h4 class="xtfw-modules-title">' . esc_html($module->menu_name()) . ' <span class="xtfw-badge xtfw-badge-blue">'.__('Shared Module', 'xt-framework').'</span></h4>';
                    }

                    $this->render_customizer_tab_panels($customizer);
                }
            }
        }

        public function render_customizer_tab_panels(&$customizer = null) {

	        $customizer = !empty($customizer) ? $customizer : $this;

	        $has_one_panel = count($customizer->panels) === 1;
	        $has_no_panels = empty($customizer->panels);

	        if(!$has_one_panel) {
		        $default_panel = array_shift($customizer->panels);
                $this->render_customizer_tab_sections( $customizer, $default_panel );
	        }

	        if($has_no_panels) {

		        $this->render_customizer_tab_sections( $customizer );

	        }else {

                echo '<ul>';

		        foreach ( $customizer->panels as $panel ) {

			        if ( ! $has_one_panel ) {
				        echo '<li>';
				        echo '    <a title="' . esc_html__( 'Open in Customizer', 'xt-framework' ) . '" href="' . esc_url($customizer->customizer_link( $panel['id'] )) . '"><span class="dashicons ' . $panel['icon'] . '"></span> ' . $panel['title'] . '</a>';
			        }

			        $this->render_customizer_tab_sections( $customizer, $panel );

			        if ( ! $has_one_panel ) {
				        echo '</li>';
			        }
		        }

                echo '</ul>';
	        }
        }

	    public function render_customizer_tab_sections(&$customizer = null, $panel = null) {

		    $has_one_panel = count($customizer->panels) === 1;
		    $has_no_panels = empty($customizer->panels);
            $is_default_panel = empty($panel['id']);
            $default_class = $is_default_panel ? 'is-default' : '';

            if(!$has_one_panel) {
	            echo '<ul class="'.esc_attr($default_class).'">';
            }

		    foreach($customizer->sections as $section) {

                if(!isset($panel['id'])) {
                    $panel['id'] = '';
                }
                if(!isset($section['panel'])) {
                    $section['panel'] = '';
                }

                $is_default_section = empty($section['panel']) && $is_default_panel;

                if(
                    !$is_default_section &&
                    !$has_no_panels &&
                    !$has_one_panel &&
                    (
                        empty($section['panel']) ||
                        (!empty($panel) && $section['panel'] !== $panel['id']))
                    ) {
				    continue;
			    }

                $lock_status = $customizer->get_section_lock_status($section['id']);

			    echo '<li class="xtfw-xirki-section-'.esc_attr($lock_status).'">';
                echo '<a title="'.esc_html__('Open in Customizer', 'xt-framework').'" href="'.esc_url($customizer->customizer_link(null, $section['id'])).'">';

                if(!empty($section['icon'])) {
                    echo '<span class="dashicons ' . esc_attr($section['icon']) . '"></span>';
                }

			    echo esc_html($section['title']);

                echo '</a>';
                echo '</li>';
		    }

		    if(!$has_one_panel) {
			    echo '</ul>';
		    }
	    }

        public function get_section_lock_status($section, $customizer  = null) {

            $section = $this->section_id($section);
            $customizer = !empty($customizer) ? $customizer : $this;

            $section_fields = $customizer->section_fields_counter[$section];

            if(empty($section_fields['premium'])) {
                return 'unclocked';
            }

            if($section_fields['all'] === $section_fields['premium']) {
                return 'locked';
            }else{
                return 'semilocked';
            }
        }

        /**
         * Check if option exists
         * @param $id
         * @return bool
         */
        public function option_exists( $id ) {

            $key = array_search($id, array_column($this->fields, 'id'));

            return ($key !== false);
        }

        /**
         * Get customizer link
         */
        public function customizer_link($panel = null, $section = null) {

            $path = 'customize.php';

            if(!empty($section)) {
                $path .= '?autofocus[section]=' . $this->section_id($section);
            }else if(!empty($panel)) {
                $path .= '?autofocus[panel]='.$this->panel_id($panel);
            }else{
                $path .= '?autofocus[panel]='.$this->panel_id();
            }

            return admin_url( $path );
        }

        /**
         * Get all options
         *
         * @return array
         */
        public function get_options() {

            return get_option($this->config_id());
        }

        /**
         * Update all options
         * @param $options
         * @return bool
         */
        public function update_options( $options ) {

            return update_option($this->config_id(), $options, true);
        }

        /**
         * Delete all options
         *
         * @return bool
         */
        public function delete_options() {

            return delete_option($this->config_id());
        }

        /**
         * Get option and allow it's value to be filtered
         * @param $id
         * @param null $default
         * @return mixed
         */
        public function get_option( $id, $default = null ) {

            if ( ! $this->option_exists( $id ) ) {

                $value = $default;

            }else {

	            $config_id = $this->config_id();

	            $value = Xirki::get_option( $config_id, $id );

	            if ( ! empty( $_POST['customized'] ) ) {

		            $options = json_decode( stripslashes( sanitize_text_field( $_POST['customized'] )), true );

                    if ( isset( $options[ $config_id . '[' . $id . ']' ] ) ) {

			            $value = $options[ $config_id . '[' . $id . ']' ];

			            if ( is_string( $options[ $config_id . '[' . $id . ']' ] ) && strpos( $options[ $config_id . '[' . $id . ']' ], '%22' ) !== false ) {
				            $value = json_decode( urldecode( $value ), true );
			            }

		            }

                }
            }

            return apply_filters( $this->config_id(). '_customizer_option', $value, $id, $default, $this->config_id() );
        }

        /**
         * Get option and cast it as boolean
         * @param $id
         * @param null $default
         * @return bool
         */
        function get_option_bool( $id, $default = null ) {

            return (bool) $this->get_option( $id, $default );
        }

        /**
         * Update option
         *
         * @param $id
         * @param $value
         * @return bool
         */
        function update_option( $id, $value ) {

            $options = $this->get_options();

            $options[ $id ] = $value;

            return $this->update_options( $options );
        }

        /**
         * Delete option
         *
         * @param $id
         * @return bool
         */
        function delete_option( $id ) {

            $options = $this->get_options();

            if ( isset( $options[ $id ] ) ) {
                unset( $options[ $id ] );
            }

            return $this->update_options( $options );
        }

        public function customizer_controls_assets() {

            $handle = 'xtfw_customizer-controls';

            wp_enqueue_script(
                $handle,
                xtfw_dir_url(XTFW_DIR_CUSTOMIZER_ASSETS) . '/js/customizer-controls'.XTFW_SCRIPT_SUFFIX.'.js',
                array(),
                $this->core->framework_version()
            );

            wp_localize_script( $handle, 'XTFW_CUSTOMIZER_CTRL', array(
                'responsive_fields' => $this->customizer_all_plugins_responsive_fields(),
                'device_switcher' => $this->customizer_responsive_field_switcher_html()
            ));

            wp_enqueue_style(
                $handle,
                xtfw_dir_url( XTFW_DIR_CUSTOMIZER_ASSETS ) . '/css/customizer-controls.css',
                array(),
                $this->core->framework_version()
            );

            $responsive_controls_styles = $this->customizer_responsive_fields_styles();

            if(!empty($responsive_controls_styles)) {
                wp_add_inline_style(
                    $handle,
                    $responsive_controls_styles
                );
            }

            if(!empty($_GET['premium_css'])) {
                wp_enqueue_style(
                    $handle.'-premium',
                    xtfw_dir_url(XTFW_DIR_CUSTOMIZER_ASSETS) . '/css/customizer-controls-premium.css',
                    array(),
                    $this->core->framework_version()
                );
            }

            do_action( $this->prefix( 'customizer_controls_assets' ), $handle );

        }

        public function customizer_preview_assets() {

            // Override Xirki postmessage.js
            wp_enqueue_script(
                'xirki_auto_postmessage',
                xtfw_dir_url( XTFW_DIR_CUSTOMIZER_ASSETS ) . '/js/postmessage' . XTFW_SCRIPT_SUFFIX . '.js',
                array( 'jquery', 'customize-preview' ),
                $this->core->framework_version(),
                true
            );

            do_action(  'xtfw_customizer_preview_assets' );
        }

        public function customizer_all_plugins_responsive_fields() {

            $repsonsive_fields = array();

            foreach($this->core->instances() as $instance) {

                if($instance->customizer()) {

                    $repsonsive_fields = array_merge($repsonsive_fields, $instance->customizer()->customizer_responsive_fields());
                }
            }

            return $repsonsive_fields;
        }

        public function customizer_responsive_fields() {

            $repsonsive_fields =  array_filter($this->fields, function ($field) {
                return !empty($field['screen']);
            });

            return array_values(array_map(function ($field) {
                return array(
                    'id' => ! empty( $field['id'] ) ? $field['id'] : $field['settings'],
                    'section' => $this->section_id($field['section']),
                    'config_id' => $this->config_id(),
                    'screen' => $field['screen'],
                    'hidden_screens' => !empty($field['hidden_screens']) ? $field['hidden_screens'] : array()
                );
            }, $repsonsive_fields));

        }

        public function customizer_responsive_fields_styles() {

            $repsonsive_fields = $this->customizer_responsive_fields();

            if(empty($repsonsive_fields)) {
                return null;
            }

            $key_prefix = '#customize-control-'.$this->config_id().'-';

            $desktop_selectors = array_map(
                function($field) use($key_prefix) {
                    return $key_prefix.$field['id'];
                },
                array_filter($repsonsive_fields, function($field) {
                    return $field['screen'] == 'desktop';
                })
            );

            $tablet_selectors = array_map(
                function($field) use($key_prefix){
                    return $key_prefix.$field['id'];
                },
                array_filter($repsonsive_fields, function($field) {
                    return $field['screen'] == 'tablet';
                })
            );

            $mobile_selectors = array_map(
                function($field) use($key_prefix){
                    return $key_prefix.$field['id'];
                },
                array_filter($repsonsive_fields, function($field){
                    return $field['screen'] == 'mobile';
                })
            );

            $desktop_tablet_selectors = array_map(
                function($field) use($key_prefix){
                    return $key_prefix.$field['id'];
                },
                array_filter($repsonsive_fields, function($field){
                    return $field['screen'] == 'desktop_tablet';
                })
            );

            $tablet_mobile_selectors = array_map(
                function($field) use($key_prefix){
                    return $key_prefix.$field['id'];
                },
                array_filter($repsonsive_fields, function($field){
                    return $field['screen'] == 'tablet_mobile';
                })
            );

            $desktop_hidden_selectors = array_merge($tablet_selectors, $mobile_selectors, $tablet_mobile_selectors);
            $tablet_hidden_selectors = array_merge($desktop_selectors, $mobile_selectors);
            $mobile_hidden_selectors = array_merge($desktop_selectors, $tablet_selectors, $desktop_tablet_selectors);

            $desktop_hidden_selectors = array_map(function($selector) {

                return '.preview-desktop '.$selector;

            }, $desktop_hidden_selectors);

            $tablet_hidden_selectors = array_map(function($selector) {

                return '.preview-tablet '.$selector;

            }, $tablet_hidden_selectors);

            $mobile_hidden_selectors = array_map(function($selector) {

                return '.preview-mobile '.$selector;

            }, $mobile_hidden_selectors);

            $hidden_selectors = array_merge($desktop_hidden_selectors, $tablet_hidden_selectors, $mobile_hidden_selectors);
            $hide_field_css = '{height:0;opacity:0;padding: 0;margin: 0;border: 0;overflow: hidden;}';

            return implode(",", $hidden_selectors).$hide_field_css;
        }

        public function customizer_responsive_field_switcher_html() {

            ob_start();
            ?>
            <div class="xirki-devices-wrapper">
                <div class="xirki-devices">
                    <button type="button" class="preview-desktop" aria-pressed="true" data-device="desktop">
                        <span class="screen-reader-text"><?php echo esc_html__('Enter desktop preview mode', 'xt-framework');?></span>
                    </button>
                    <button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
                        <span class="screen-reader-text"><?php echo esc_html__('Enter tablet preview mode', 'xt-framework');?></span>
                    </button>
                    <button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
                        <span class="screen-reader-text"><?php echo esc_html__('Enter mobile preview mode', 'xt-framework');?></span>
                    </button>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

	    /**
	     * Filter body class - Add a unique class to body when site is being viewed from the customizer
	     *
	     * @param $classes
	     * @return array $classes
	     */
        public function customizer_preview_class($classes) {

            if(is_customize_preview()) {
	            $classes[] = 'xt-customizer-preview';
            }

            return $classes;
        }

        /**
         * Allow SVG
         *
         * @param $data
         * @param $file
         * @param $filename
         * @param $mimes
         * @return array
         */
        public function check_filetype_and_ext($data, $file, $filename, $mimes ) {

            global $wp_version;
            if ( $wp_version <= '4.7.1' ) {
                return $data;
            }

            $filetype = wp_check_filetype( $filename, $mimes );

            return [
                'ext'             => $filetype['ext'],
                'type'            => $filetype['type'],
                'proper_filename' => $data['proper_filename']
            ];

        }

        /**
         * @param $mime_types
         * @return mixed
         */
        public function allow_myme_types($mime_types ) {

            $mime_types['svg']  = 'image/svg+xml'; //Adding svg extension
            $mime_types['svgz'] = 'image/svg+xml';

            return $mime_types;

        }

    } // End Class

}
	
