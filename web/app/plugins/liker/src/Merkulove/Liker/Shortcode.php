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

use Merkulove\Liker\Unity\Settings;
use Merkulove\Liker\Unity\TabAssignments;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class adds admin styles.
 * @since 1.0.0
 **/
final class Shortcode {

	/**
	 * The one true Shortcode.
	 *
	 * @var Shortcode
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * @var array
	 */
	private static $options;

	/**
	 * Sets up a new Shortcode instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** @var array options */
		self::$options = Settings::get_instance()->options;

		/**
		 * Add liker by shortcode [liker]
		 * https://docs.merkulov.design/start-with-the-liker-wordpress-plugin/#general
		 */
		add_shortcode( 'liker', [ $this, 'liker_shortcode' ] );

	}

	/**
	 * Add liker by shortcode [liker].
	 *
	 * @param array $atts - Shortcode attributes
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string - Result of shortcode.
	 **/
	public function liker_shortcode( $atts )
	{

		/** Liker TOP shortcode */
		if ( isset( $atts[ 'top' ] ) || isset( $atts[ 'for' ] ) ) {

			return $this->shortcode_liker_top( $atts );

		}

        /** Liker by ID shortcode */
        if ( isset( $atts[ 'id' ] ) ) {

	        return LikerLogics::get_instance()->get_liker( $atts );

        }

		/** Checks if plugin should work on this page. */
		if( ! TabAssignments::get_instance()->display() ) { return ''; }

		$atts = is_array( $atts ) ? $atts : [];
		return LikerLogics::get_instance()->get_liker( $atts );

	}

	/**
	 * Add liker top posts by short code [liker top="10" for="page" cols="4"].
	 *
	 * @param $atts - shortcode attributes
	 *
	 * @return string
	 **/
	public function shortcode_liker_top( $atts = array() )
	{

		global $wpdb;

		// Get shortcode attributes
		$top = isset( $atts[ 'top' ] ) ? (int) $atts['top'] : 4;
		$for = isset( $atts[ 'for' ] ) ? $atts[ 'for' ] : 'post';
		$cols = isset( $atts[ 'cols' ] ) ? (int) $atts['cols'] : 1;

		// Get options
		$image = self::$options[ 'top_image' ] === 'on';
		$title = self::$options[ 'top_title' ] === 'on';
		$excerpt = self::$options[ 'top_excerpt' ] === 'on';
		$rating = self::$options[ 'top_rating' ] === 'on';
		$equal = self::$options[ 'top_equal' ] === 'on';
		$height = self::$options[ 'top_height' ] . 'px';
		$image_size = self::$options[ 'top_image_size' ];
		$tag = self::$options[ 'top_title_tag' ];

		// Get top liked posts of selected $post_type
		$posts = $wpdb->get_results(

			$wpdb->prepare("
                SELECT $wpdb->liker.liker_id, SUM( $wpdb->liker.val_1 ) as sum_val_1, SUM( $wpdb->liker.val_2 ) as sum_val_2, SUM( $wpdb->liker.val_3 ) as sum_val_3, ( SUM( $wpdb->liker.val_1 ) - SUM( $wpdb->liker.val_3 ) ) AS amount, COUNT( $wpdb->liker.val_1 ) as total, $wpdb->posts.post_type
                FROM $wpdb->liker
                INNER JOIN $wpdb->posts ON $wpdb->liker.liker_id=$wpdb->posts.ID
                WHERE $wpdb->posts.post_type = %s
                GROUP BY $wpdb->liker.liker_id
                ORDER BY amount DESC
                LIMIT %d", [ $for, $top ] )

		);

		// No records with Liker. Nothing to show
		if ( empty( $posts ) ) { return ''; }

		ob_start();

		?>
        <!-- Liker Top Rated: Start !-->
        <div class="mdp-liker-top mdp-liker-cols-<?php esc_attr_e( $cols ); ?>">
		<?php foreach ( $posts as $post ) : ?>

            <div class="mdp-liker-post">

	            <?php if ( $image && has_post_thumbnail( $post->liker_id ) ) :

		            if ( $equal ) { ?>
                    <a class="mdp-liker-post-image"
                       style="height: <?php esc_attr_e( $height ); ?>; background-image: url( <?php echo get_the_post_thumbnail_url( $post->liker_id, $image_size ); ?> )"
                       href="<?php echo get_permalink( $post->liker_id ); ?>">
                    </a>
		            <?php } else { ?>
                    <div class="mdp-liker-post-image">
                        <a href="<?php echo get_permalink( $post->liker_id ); ?>">
                            <?php echo get_the_post_thumbnail( $post->liker_id, $image_size ); ?>
                        </a>
                    </div>
		            <?php } ?>

	            <?php endif; ?>
                <div class="mdp-liker-post-content">

	                <?php if ( $title ) : ?>

                    <<?php esc_attr_e( $tag ); ?> class="mdp-liker-post-title">
                        <a href="<?php echo get_permalink( $post->liker_id ); ?>">
                            <?php echo get_the_title( $post->liker_id ); ?>
                        </a>
                    </<?php esc_attr_e( $tag ); ?>>

                    <?php endif; ?>

                    <?php if ( $excerpt ) :?>
                    <p class="mdp-liker-post-excerpt">
                        <?php echo get_the_excerpt( $post->liker_id ) ?>
                    </p>
                    <?php endif; ?>

                    <?php if ( $rating ) : ?>
                    <div class="mdp-liker-post-rating">
                        <?php
                        if ( self::$options[ 'dashicons' ] !== 'on' )
                        {
                            ?><span class="mdp-liker-post-icon"><?php echo wp_kses_post( self::$options[ 'caption_1' ] ) ?></span><?php
                        } else
                        {
                            ?><span class="dashicons dashicons-heart"></span><?php
                        }
                        ?>
                        <span class="mdp-liker-post-value"><?php esc_html_e( $post->amount ); ?></span>
                    </div>
                    <?php endif; ?>

                </div>

            </div>

		<?php endforeach; ?>
        </div>
        <!-- Liker Top Rated: End !-->
		<?php

		return ob_get_clean();

	}

	/**
	 * Main Shortcode Instance.
	 *
	 * Insures that only one instance of Shortcode exists in memory at any one time.
	 *
	 * @static
	 * @return Shortcode
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class Shortcode.
