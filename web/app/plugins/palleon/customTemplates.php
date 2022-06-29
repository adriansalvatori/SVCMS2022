<?php
defined( 'ABSPATH' ) || exit;

class PalleonTemplates {
    /**
	 * The single instance of the class
	 */
	protected static $_instance = null;

    /**
	 * Main Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
	 * Palleon Constructor
	 */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action( 'init', array($this, 'register_taxonomy'), 0 );
        add_filter( 'cmb2_meta_boxes', array($this, 'add_template') );
        add_filter('manage_edit-palleontemplates_columns', array($this, 'admin_column'), 5);
        add_action('manage_posts_custom_column', array($this, 'admin_row'), 5, 2);
        add_filter( 'palleonTemplates', array($this, 'custom_templates'), 10, 2 );
        add_filter( 'palleonTemplateTags', array($this, 'custom_tags'), 10, 2 );
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Palleon Templates', 'palleon' ),
            'singular_name'     => esc_html__( 'Template', 'palleon' ),
            'add_new'           => esc_html__( 'Add new template', 'palleon' ),
            'add_new_item'      => esc_html__( 'Add new template', 'palleon' ),
            'edit_item'         => esc_html__( 'Edit template', 'palleon' ),
            'new_item'          => esc_html__( 'New template', 'palleon' ),
            'view_item'         => esc_html__( 'View template', 'palleon' ),
            'search_items'      => esc_html__( 'Search templates', 'palleon' ),
            'not_found'         => esc_html__( 'No template found', 'palleon' ),
            'not_found_in_trash'=> esc_html__( 'No template found in trash', 'palleon' ),
            'parent_item_colon' => esc_html__( 'Parent template:', 'palleon' ),
            'menu_name'         => esc_html__( 'PE Templates', 'palleon' )
        );
    
        $taxonomies = array();
     
        $supports = array('title', 'thumbnail');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('Template', 'palleon'),
            'public'            => false,
            'exclude_from_search' => true,
            'show_ui'           => true,
            'show_in_nav_menus' => false,
            'publicly_queryable'=> true,
            'query_var'         => true,
            'capability_type'   => 'post',
            'capabilities' => array(
                'edit_post'          => 'update_core',
                'read_post'          => 'update_core',
                'delete_post'        => 'update_core',
                'edit_posts'         => 'update_core',
                'edit_others_posts'  => 'update_core',
                'delete_posts'       => 'update_core',
                'publish_posts'      => 'update_core',
                'read_private_posts' => 'update_core'
            ),
            'has_archive'       => false,
            'hierarchical'      => false,
            'supports'          => $supports,
            'menu_position'     => 10,
            'menu_icon'         => 'dashicons-art',
            'taxonomies'        => $taxonomies
        );
        register_post_type('palleontemplates',$post_type_args);
    }

    /**
	 * Register Taxonomy
	 */
    public function register_taxonomy() {
        register_taxonomy(
            'palleontags',
            'palleontemplates',
            array(
                'labels' => array(
                    'name' => esc_html__( 'Custom Tags', 'palleon' ),
                    'add_new_item' => esc_html__( 'Add new tag', 'palleon' ),
                    'new_item_name' => esc_html__( 'New tag', 'palleon' )
                ),
                'show_ui' => true,
                'show_tagcloud' => false,
                'show_admin_column' => true,
                'show_in_nav_menus' => false,
                'hierarchical' => false,
                'query_var' => true
            )
        );
    }

    /**
	 * Add Template
	 */
    public function add_template( $meta_boxes ) {
        $prefix = 'palleon_cmb2';
        $meta_boxes['palleon_template'] = array(
            'id' => 'palleon_template',
            'title' => esc_attr__( 'Template File', 'palleon'),
            'object_types' => array('palleontemplates'),
            'context' => 'normal',
            'priority' => 'default',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Template File', 'palleon' ),
                    'id'      => 'palleon_cmb2_template',
                    'type'    => 'file',
                    'options' => array(
                        'url' => false
                    ),
                    'query_args' => array(
                        'type' => 'application/json'
                    )
                ),
            ),
        );
    
        return $meta_boxes;
    }

    /**
	 * Add custom admin table column
	 */
    public function admin_column($defaults){
        $defaults['palleon_template_preview'] = esc_html__( 'Preview', 'palleon' );
        return $defaults;
    }

    /**
	 * Add custom admin table row
	 */
    public function admin_row($column_name, $post_id){
        global $post;
        if($column_name === 'palleon_template_preview'){
            echo the_post_thumbnail( 'thumbnail', array('style' => 'width:100%;height:auto;max-width:80px;') );
        }    
    }

    // Custom Templates
    public function custom_templates($templates){
        $defaultTemplates = PalleonSettings::get_option('default_temp','enable');

        if ($defaultTemplates == 'disable') {
            $templates = array();
        }

        $args = array(
            'post_type' => 'palleontemplates',
            'posts_per_page'  => 9999
        );

        $the_query = new WP_Query( $args );

        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) : $the_query->the_post();
            $templateUrl = get_post_meta( get_the_ID(), 'palleon_cmb2_template', true );
            $imageurl = get_the_post_thumbnail_url(get_the_ID(),'thumbnail');
            $terms = get_the_terms( get_the_ID(), 'palleontags' );
            $customTags = array();
            foreach( $terms as $term ) {
                $customTags[] = $term->slug;
            }
            $templates[] = array("custom-template-" . get_the_ID(), get_the_title(), esc_url($imageurl), esc_url($templateUrl), $customTags);
            endwhile;
        }

        return $templates;
    }

    // Custom Tags
    public function custom_tags($tags){
        $defaultTemplates = PalleonSettings::get_option('default_temp','enable');

        if ($defaultTemplates == 'disable') {
            $tags = array();
        }

        $terms = get_terms( 'palleontags', array(
            'hide_empty' => false,
        ) );

        foreach( $terms as $term ) {
            $tags[$term->slug] = $term->name . ' (' . palleon_get_tag_count($term->slug) . ')';
        }

        return $tags;
    }

}

/**
 * Returns the main instance of the class
 */
function PalleonTemplates() {  
	return PalleonTemplates::instance();
}
// Global for backwards compatibility
$GLOBALS['PalleonTemplates'] = PalleonTemplates();
