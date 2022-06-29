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

use Merkulove\Liker\Unity\Plugin;
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
final class PostMeta {

	/**
	 * The one true PostMeta.
	 *
	 * @var PostMeta
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * @var array
	 */
	private static $options;

	/**
	 * Sets up a new PostMeta instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		self::$options = Settings::get_instance()->options;

		// Show liker results after page title
		if ( self::$options[ 'meta' ] === 'on' ) {

			add_filter( 'the_title', [ $this, 'show_likes_meta' ], 99 );

		}

	}

	/**
	 * Update post meta with liker data
	 *
	 * @param $id
	 */
	public function update_liker_meta( $id )
	{

		// Get likes for post
		$likes = $this->likes_meta_details( $id );
		if ( ! is_array( $likes ) ) { return; }

		// Update liker data to the post meta
		update_post_meta( $id, 'mdp_liker', $this->likes_meta( $likes ) );


	}

	/**
	 * Get likes fro current post
	 *
	 * @param $id
	 * @param $new
	 *
	 * @return array|false
	 */
	private function likes_meta_details( $id )
	{

		$lks = Request::get_instance()->get_likes_data( $id );

		if ( ! is_array( $lks ) ) { return false; }

		return [
			'positive' => $lks[ 1 ],
			'neutral' => $lks[ 2 ],
			'negative' => $lks[ 3 ]
		];

	}

	/**
	 * Calculate 'mdp_liker' post meta data
	 *
	 * @param $likes
	 *
	 * @return array
	 */
	public function likes_meta( $likes )
	{

		return [
			'rating' => $likes[ 'positive' ] - $likes[ 'negative' ],
			'votes' => array_sum( $likes ),
			'likes' => $likes
		];

	}

	/**
	 * Add likes counter to post title
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function show_likes_meta( $title ) {

		$likes = get_post_meta( get_the_ID(), 'mdp_liker', true );

		$icon = ( self::$options[ 'dashicons' ] !== 'on' ) ?
            '<span class="mdp-liker-post-icon">' . self::$options[ 'caption_1' ] . '</span>' :
            '<span class="dashicons dashicons-heart"></span>';

		return is_array( $likes ) ?
			$title . '
			<span class="mdp-liker-meta">' .
                wp_kses_post( $icon ) .
				'<span class="mdp-liker-meta--value">' . $likes[ 'rating' ] . '</span>			
			</span>
			' : $title;

	}

	/**
	 * Copy rating from data base to the post meta
	 */
	public function db_to_meta() {

		if ( ! isset( $_GET[ 'liker_meta' ] ) ) { return; };

		$posts = Request::get_instance()->get_all_likes();
		if ( empty( $posts ) ) { return; }

		foreach ( $posts as $post ) {

			// Remove meta_data
			if ( $_GET[ 'liker_meta' ] === '0' ) {

				delete_post_meta( $post->liker_id, 'mdp_liker' );
				continue;

			}

			// Prepare meta data
			$likes_meta = $this->likes_meta( [

				'positive' => $post->positive,
				'neutral' => $post->neutral,
				'negative' => $post->negative

			] );

			// Update meta data
			update_post_meta( $post->liker_id, 'mdp_liker', $likes_meta );

		}

	}

	/**
	 * Remove all liker meta data
	 */
	public function remove_liker_meta(){

		$posts = Request::get_instance()->get_all_likes();
		if ( ! is_array( $posts ) ) { return; }

		foreach ( $posts as $post ) {

			delete_post_meta( $post->liker_id, 'mdp_liker' );

		}

	}

	/**
	 * Main PostMeta Instance.
	 *
	 * Insures that only one instance of PostMeta exists in memory at any one time.
	 *
	 * @static
	 * @return PostMeta
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class PostMeta.
