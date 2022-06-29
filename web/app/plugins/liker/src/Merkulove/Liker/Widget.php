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
use WP_Widget;

/**
 * Class to implement Liker widget.
 *
 * @since 1.0.0
 *
 **/
final class Widget extends WP_Widget {

	/**
	 * Sets up a new Liker Widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function __construct() {

		/**
		 * Widget options. See wp_register_sidebar_widget() for information
		 * on accepted arguments. Default empty array.
		 **/
		$widget_options = [
			'classname' => 'mdp-liker-widget',
			'description' => __( 'Show top rated posts at frontend.', 'liker' ),
		];

		/**
		 * Optional. Widget control options. See wp_register_widget_control() for
		 * information on accepted arguments. Default empty array.
		 **/
		$control_options = [
			'width' => 200,
			'height' => 250,
			'id_base' => 'mdp_liker_widget', // !!! DO NOT USE dash '-' as separator.
		];

		/** Add scripts depending on the current screen. */
		add_action( 'current_screen', [$this, 'detect_current_screen'] );

		/** Call WP_Widget constructor with our settings. */
		parent::__construct( 'mdp_liker_widget', esc_html__( 'Liker Widget', 'liker' ), $widget_options, $control_options );

	}

	/**
	 * Outputs the content for the current Liker Widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current 42Theme Text Widget instance.
	 **/
	public function widget( $args, $instance ) {

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$number = empty( $instance['number'] ) ? 5 : $instance['number'];
		$post_type_name = empty( $instance['post_type'] ) ? '' : $instance['post_type'];

		/** Get a post type object by name. */
		$post_type = get_post_type_object( $post_type_name );
		if ( null === $post_type ) { return; }

		echo wp_kses_post( $args['before_widget'] );

		/** Widget Title. */
		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		/** Render list of 5 top rated posts by post_type. */
        $this->render_top_rated( $post_type, false, $number );

		echo wp_kses_post( $args['after_widget'] );

	}

	/**
	 * Outputs Liker Widget settings form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 **/
	public function form( $instance ) {

		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$instance = wp_parse_args( (array) $instance, [
            'title'     => '',
            'number'    => 5,
            'post_type' => '',
        ] );

		$title = sanitize_text_field( $instance['title'] );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$post_type = sanitize_text_field( $instance['post_type'] );

		/** Get supported post formats. */
		$cpt_support = Settings::get_instance()->options['cpt_support'];
		?>

		<p>
			<label for="<?php esc_attr_e( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'liker' ); ?></label>
			<input class="widefat"
			       id="<?php esc_attr_e( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php esc_attr_e( $this->get_field_name('title') ); ?>"
			       type="text"
			       value="<?php esc_attr_e( $title ); ?>"
            />
		</p>

        <p>
            <label for="<?php esc_attr_e( $this->get_field_id( 'post_type' ) ); ?>"><?php esc_html_e( 'Post Type:', 'liker' ); ?></label>
            <select name="<?php esc_attr_e( $this->get_field_name( 'post_type' ) ); ?>" id="<?php esc_attr_e( $this->get_field_id( 'post_type' ) ); ?>" class="widefat">
                <?php foreach ( $cpt_support as $cpt ): ?>
                    <option value="<?php esc_attr_e( $cpt ); ?>"<?php selected( $post_type, $cpt ); ?>><?php esc_html_e( get_post_type_object( $cpt )->label ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="<?php esc_attr_e( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'liker' ); ?></label>
            <input class="tiny-text"
                   id="<?php esc_attr_e( $this->get_field_id( 'number' ) ); ?>"
                   name="<?php esc_attr_e( $this->get_field_name( 'number' ) ); ?>"
                   type="number" step="1" min="1"
                   value="<?php esc_attr_e( $number ); ?>"
                   size="3"
            />
        </p>

		<?php

	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * @since 1.0.0
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 **/
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title' ]);
		$instance['number'] = strip_tags( $new_instance['number'] );
		$instance['post_type'] = strip_tags( $new_instance['post_type'] );

		return $instance;

	}

	/**
	 * Add JS and CSS.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function enqueue_assets () {

		add_action( 'admin_enqueue_scripts', [$this, 'admin_styles'] );

	}

	/**
	 * Detect current page and add scripts
	 *
	 * @param $current_screen
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function detect_current_screen( $current_screen ) {

		/** Only for specific pages: customizer and widgets. */
		if ( in_array( $current_screen->id, ['customize', 'widgets'] ) ) {

			/** Add scripts and styles. */
			$this->enqueue_assets();
		}

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
	 * Add Widget admin CSS.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function admin_styles() {

		/** Widget admin CSS */
		wp_enqueue_style( 'mdp-liker-widget-admin', Plugin::get_url() . '/css/widget-admin' . Plugin::get_suffix() . '.css', [], Plugin::get_version() );

	}

} // End Class Widget.
