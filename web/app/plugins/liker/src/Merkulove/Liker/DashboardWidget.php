<?php
/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2022 Merkulove ( https://merkulov.design/ ). All rights reserved.
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

use Merkulove\Liker\Unity\Plugin;
use Merkulove\Liker\Unity\Settings;

/**
 * SINGLETON: Class used to create widgets on admin dashboard.
 *
 * @since 1.1.6
 *
 **/
final class DashboardWidget {

	/**
	 * The one true DashboardWidget.
	 *
	 * @var DashboardWidget
	 * @since 1.1.6
	 **/
	private static $instance;

	/**
	 * Sets up a new DashboardWidget instance.
	 *
	 * @since 1.1.6
	 * @access public
	 **/
	private function __construct() {

		/** Add status widget to dashboard. */
		add_action( 'wp_dashboard_setup', [$this, 'dashboard_setup'] );

		/** Load JS and CSS for Backend Area. */
		$this->enqueue_backend();

	}

	/**
	 * Load JS and CSS for Backend Area.
	 *
	 * @since 1.1.6
	 * @access public
	 **/
	public function enqueue_backend() {

		/** Add admin styles. */
		add_action( 'admin_enqueue_scripts', [$this, 'admin_styles' ] );

	}

	/**
	 * Add CSS for admin area.
	 *
	 * @since 1.1.6
	 * @return void
	 **/
	public function admin_styles() {

	    /** Get current screen. */
		$screen = get_current_screen();
		if ( null === $screen ) { return; }

		/** Add styles only on dashboard page. */
		if ( 'dashboard' === $screen->base ) {

			wp_enqueue_style( 'mdp-liker-dashboard', Plugin::get_url() . 'css/dashboard' . Plugin::get_suffix() . '.css', [], Plugin::get_version() );

		}

	}

	/**
	 * Fires after core widgets for the admin dashboard have been registered.
	 *
	 * @since 1.1.6
	 * @access public
	 *
	 * @return void
	 **/
	public function dashboard_setup() {

		wp_add_dashboard_widget( 'mdp_liker_dashboard_widget', esc_html__( 'Liker', 'liker' ), [$this, 'dashboard_widget_statistics'] );

	}

	/**
	 * Show Liker Statistic widget.
	 *
	 * @since 1.1.6
	 * @access public
	 *
	 * @return void
	 **/
	public function dashboard_widget_statistics() {

		?><div class="mdp-liker-wp-dashboard"><?php
            foreach ( Settings::get_instance()->options['cpt_support'] as $post_type_name ) {

                /** Get a post type object by name. */
	            $post_type = get_post_type_object( $post_type_name );
	            if ( null === $post_type ) { continue; }

	            /** Render list of 5 top rated posts by post_type. */
	            $this->render_top_rated( $post_type );

            }
        ?></div><?php

	}

	/**
	 * Render list of 5 top rated posts by post_type.
	 *
	 * @param object $post_type
	 * @param string $title
	 * @param integer $limit
	 *
	 * @since  1.1.6
	 * @access public
	 *
	 * @return void
	 **/
	public function render_top_rated( $post_type, $title = '', $limit = 5 ) {
		global $wpdb;

		/** Get plugin settings. */
		$options = Settings::get_instance()->options;

		/**
		 * Get top liked 5 posts of selected $post_type.
		 *
		 * @noinspection SqlDialectInspection
		 **/
		$posts = $wpdb->get_results(
			$wpdb->prepare("
                SELECT $wpdb->liker.liker_id, SUM( $wpdb->liker.val_1 ) as sum_val_1, SUM( $wpdb->liker.val_2 ) as sum_val_2, SUM( $wpdb->liker.val_3 ) as sum_val_3, ( SUM( $wpdb->liker.val_1 ) - SUM( $wpdb->liker.val_3 ) ) AS amount, COUNT( $wpdb->liker.val_1 ) as total, $wpdb->posts.post_type
                FROM $wpdb->liker
                INNER JOIN $wpdb->posts ON $wpdb->liker.liker_id=$wpdb->posts.ID
                WHERE $wpdb->posts.post_type = %s
                GROUP BY $wpdb->liker.liker_id
                ORDER BY amount DESC
                LIMIT %d", [$post_type->name, $limit] )
		);

		/** No posts with likes. Nothing to show. */
		if ( empty( $posts ) ) { return; }

		/** Header "Top Rated {POST_TYPE}:" */
		if ( $title ) {

			?><h3 class="mdp-liker-cpt-title"><?php esc_html_e( $title ); ?></h3><?php

		} elseif ( false !== $title ) {
			?>
            <h3 class="mdp-liker-cpt-title">
                <strong>
					<?php
					esc_html_e( 'Top Rated ', 'liker' );
					esc_html_e( $post_type->label );
					esc_html_e( ':', 'liker' );
					?>
                </strong>
            </h3>
			<?php
		}
		?>
        <div class="mdp-liker-list-box">
            <ul class="mdp-liker-list" role="list">
				<?php foreach ( $posts as $post ) : ?>
                    <li class="mdp-liker-list-item">
                        <a class="mdp-liker-post-link" target="_blank" href="<?php echo get_permalink( $post->liker_id ); ?>"><?php echo get_the_title( $post->liker_id ); ?></a>

						<?php
						/** Show result as Amount. */
						if ( 'amount' === $options['results_admin'] ) {

							?><span class="mdp-liker-result"><?php esc_html_e( $post->amount ); ?></span><?php

							/** Show result as Amount / Total. */
						} elseif ( 'total' === $options['results_admin'] ) {

							?><span class="mdp-liker-result"><?php esc_html_e( $post->amount ); ?> / <?php esc_html_e( $post->total ); ?></span><?php

							/** Show result as +1 | 0 | -1. */
						} elseif ( 'split' === $options['results_admin'] ) {


							/** Render for One Button. */
							if ( 'one-button' === $options['type'] ) {

								?><span class="mdp-liker-result"><?php esc_html_e( $post->sum_val_1 ); ?></span><?php

								/** Render for Two Button. */
							} elseif ( 'two-buttons' === $options['type'] ) {

								?><span class="mdp-liker-result"><?php esc_html_e( $post->sum_val_1 ); ?> | <?php esc_html_e( $post->sum_val_3 ); ?></span><?php

								/** Render for Tree Button. */
							} elseif ( 'three-buttons' === $options['type'] ) {

								?><span class="mdp-liker-result"><?php esc_html_e( $post->sum_val_1 ); ?> | <?php esc_html_e( $post->sum_val_2 ); ?> | <?php esc_html_e( $post->sum_val_3 ); ?></span><?php

							}

						}
						?>

                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
		<?php

	}

	/**
	 * Main DashboardWidget Instance.
	 *
	 * Insures that only one instance of DashboardWidget exists in memory at any one time.
	 *
	 * @static
	 * @return DashboardWidget
	 * @since 1.1.6
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class DashboardWidget.
