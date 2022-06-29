<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Recipe_Post_Type
 * @package Uncanny_Automator_Pro
 */
class Pro_Ui {

	/**
	 * RecipePostType constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_filter( 'automator_get_recipe_data_query', array( $this, 'q_get_recipe_data' ), 20, 3 );
		add_filter( 'add_recipe_child', array( $this, 'add_recipe_child' ), 20, 4 );
		add_filter( 'automator_api_setup', array( $this, 'uap_api_setup' ), 20 );
	}

	/**
	 * Enqueue scripts only on custom post type edit pages
	 *
	 * @param $hook
	 */
	public function scripts( $hook ) {

		// Add scripts ONLY to recipe custom post type
		if ( ( 'post-new.php' === $hook || 'post.php' === $hook ) && get_post_type() === 'uo-recipe' ) {

			global $post;
			// $post return $post->ID as a string, Our JS expects an int... change it
			$post->ID = (int) $post->ID;

			// Recipe UI scripts
			wp_enqueue_script( 'automator-recipe-ui-bundle-pro-js', Utilities::get_recipe_dist( 'automator-recipe-pro-ui.bundle.js' ), array(
				'jquery',
			), AUTOMATOR_PRO_PLUGIN_VERSION, true );

		}
	}

	/**
	 * @param string $q
	 * @param int $recipe_ID
	 * @param string $type
	 *
	 * @return string
	 */
	public function q_get_recipe_data( $q, $recipe_ID, $type ) {

		if ( 'uo-trigger' === $type ) {
			global $wpdb;

			return "Select ID, post_status FROM $wpdb->posts WHERE post_parent = $recipe_ID AND post_type = '{$type}'";
		}

		return $q;

	}

	/**
	 * @param bool $create_post
	 * @param string $post_type
	 * @param string $action
	 * @param object $recipe
	 *
	 * @return bool
	 */
	public function add_recipe_child( $create_post, $post_type, $action, $recipe ) {

		if ( 'uo-trigger' === $post_type && 'create_trigger' === $action ) {
			return true;
		}

		return $create_post;
	}

	/**
	 * @param array $api_setup
	 *
	 * @return array
	 */
	public function uap_api_setup( $api_setup ) {
		if ( ! $api_setup['wp'] ) {
			$api_setup['wp'] = true;
		}

		return $api_setup;
	}

}
