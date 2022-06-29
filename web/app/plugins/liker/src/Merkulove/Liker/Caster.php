<?php
/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2020 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Liker;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

use Merkulove\Liker\Unity\Settings;
use Merkulove\Liker\Unity\TabActivation;
use Merkulove\Liker\Unity\UI;
use Merkulove\Liker\Unity\Plugin;

/**
 * SINGLETON: Caster class contain main plugin logic.
 * @since 1.0.0
 **/
final class Caster {

	/**
	 * The one true Caster.
	 *
     * @since 1.0.0
     * @access private
	 * @var Caster
	 **/
	private static $instance;

    /**
     * Setup the plugin.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function setup() {

        /** Define hooks that runs on both the front-end as well as the dashboard. */
        $this->both_hooks();

        /** Define public hooks. */
        $this->public_hooks();

        /** Define admin hooks. */
        $this->admin_hooks();

	    /** Add action to AJAX check like on load. */
	    add_action( 'wp_ajax_get_like', [ LikerLogics::get_instance(), 'get_like'] );
	    add_action( 'wp_ajax_nopriv_get_like', [ LikerLogics::get_instance(), 'get_like'] );

	    /** Add action to AJAX process like. */
	    add_action( 'wp_ajax_process_like', [ LikerLogics::get_instance(), 'process_like'] );
	    add_action( 'wp_ajax_nopriv_process_like', [ LikerLogics::get_instance(), 'process_like'] );

	    /** Initialize Liker Logics. */
	    LikerLogics::get_instance()->liker_filter( Settings::get_instance()->options['position'] );

    }

	/**
	 * Store our table name in $wpdb with correct prefix.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function register_liker_table() {
		global $wpdb;

		$wpdb->liker = "{$wpdb->prefix}liker";
	}

	/**
	 * Creating a liker table.
	 * id | liker_id (Post ID) | val_1 | val_2 | val_3 | ip | guid | created | modified
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function create_liker_table() {
		global $wpdb;
		global $charset_collate;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/** Call this manually as we may have missed the init hook. */
		$this->register_liker_table();


		/** @noinspection SqlDialectInspection */
		$sql_create_table = "
            CREATE TABLE " . $wpdb->liker . " (				
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                liker_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                val_1 INT(3) UNSIGNED NOT NULL DEFAULT '0',
                val_2 INT(3) UNSIGNED NOT NULL DEFAULT '0',
                val_3 INT(3) UNSIGNED NOT NULL DEFAULT '0',
                ip varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
                guid varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
                created DATETIME DEFAULT NULL,
                modified DATETIME DEFAULT NULL,
                PRIMARY KEY (id)
            ) $charset_collate; ";

		dbDelta( $sql_create_table );
	}

	/**
	 * Registers a WP_Widget widget.
	 *
	 * @return void
	 * @since 1.1.6
	 * @access public
	 **/
	public function register_widget() {

		register_widget( Widget::class );

	}

    /**
     * Define hooks that runs on both the front-end as well as the dashboard.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function both_hooks() {

	    /** Register Liker Widget. */
	    add_action( 'widgets_init', [$this, 'register_widget'] );

	    /** Creating liker table. */
	    add_action( 'init', [ $this, 'register_liker_table' ], 1 );
	    add_action( 'switch_blog', [ $this, 'register_liker_table' ] );

    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function public_hooks() {

        /** Work only on frontend area. */
        if ( is_admin() ) { return; }

	    /** Show Advanced Schema Markup. */
	    add_action( 'wp_head', [ LikerLogics::get_instance(), 'structured_data']);

	    /** Add additional scripts to front-end */
	    FrontScripts::get_instance();

	    /** Add additional styles to front-end */
	    FrontStyles::get_instance();

	    /** Post meta data saving and displaying */
		PostMeta::get_instance();

		/** Liker Shortcode */
		Shortcode::get_instance();

    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function admin_hooks() {

        /** Work only in admin area. */
        if ( ! is_admin() ) { return; }

	    /** Clear likes on remove post/page. */
	    add_action( 'before_delete_post', [ LikerLogics::get_instance(), 'before_delete_post'] );

	    /** Add Liker column to Selected Posts list. */
	    AdminPostTable::get_instance();

	    /** Reset likes.  */
	    if ( defined('DOING_AJAX') ) {
		    add_action( 'wp_ajax_reset_liker', [ LikerLogics::get_instance(), 'reset_liker'] );
	    }

	    /**
	     * The filter posts_results is executed just after the query
	     * was executed. We'll use it as a after_get_posts-action.
	     **/
	    add_filter( 'posts_results', [ LikerLogics::get_instance(), 'after_set_post_query'], 10, 2 );

	    /** Create widget on admin dashboard. */
	    DashboardWidget::get_instance();

	    /** Add additional scripts to admin area. */
	    AdminScripts::get_instance();

	    /** Add additional styles to admin area. */
	    AdminStyles::get_instance();

	    /** Show activation warning */
	    add_action( 'admin_footer', [ $this, 'not_activated_notice' ] );

	    /** Add not-activated class to the admin body */
	    add_filter( 'admin_body_class', [ $this, 'not_activated_class' ] );

	    /** Store all rating data of each post to the post meta */
	    if ( isset( $_GET[ 'liker_meta' ] ) ) {
	        add_action( 'wp_loaded', [ PostMeta::get_instance(), 'db_to_meta' ] );
        }

    }

	/**
	 * Render Activation message.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 **/
	public function not_activated_notice() {

		/** Get current screen. */
		$screen = get_current_screen();
		if ( null === $screen ) { return; }

		/** Readabler Settings Page. */
		if ( in_array( $screen->base ,Plugin::get_menu_bases() ) && ! TabActivation::get_instance()->is_activated() ) {

			/** Render "Before you start" message. */
			UI::get_instance()->render_snackbar(
				esc_html__( 'Activate your copy of the Liker to enable viewing results in the admin area and additional features', 'liker' ),
				'info', // Type
				-1, // Timeout
				true, // Is Closable
				[ [ 'caption' => 'Activate', 'link' => get_admin_url('admin', 'admin.php?page=mdp_liker_settings&tab=activation' ) ] ] // Buttons
			);

		}

	}

	/**
	 * @param $classes
	 *
	 * @return string
	 */
	public function not_activated_class( $classes ) {

		if ( TabActivation::get_instance()->is_activated() ) { return $classes; }

		$my_class = 'mdp-liker-not-activated';

		return $classes ? $classes . ' ' . $my_class : $my_class;

	}

	/**
	 * This method used in register_activation_hook
	 * Everything written here will be done during plugin activation.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function activation_hook() {

		/** Create Liker Table */
		$this->create_liker_table();

	}

	/**
	 * Main Caster Instance.
	 * Insures that only one instance of Caster exists in memory at any one time.
	 *
	 * @static
     * @since 1.0.0
     * @access public
     *
	 * @return Caster
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}
