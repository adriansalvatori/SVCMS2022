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

use Merkulove\Liker as Liker;
use WP_Post;
use WP_Query;
use Merkulove\Liker\Unity\Settings;
use Merkulove\Liker\Unity\TabAssignments;
use Merkulove\Liker\Unity\TabActivation;

/**
 * Class LikerLogics
 * @package Merkulove\Liker
 */
final class LikerLogics {

	/**
	 * The one true LikerLogics.
	 *
	 * @var LikerLogics
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new LikerLogics instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {}

	/**
	 * Remove all Liker data from DataBase.
	 *
	 * @return void
	 * @since 1.1.0
	 * @access public
	 */
	public function reset_liker( ) {
		global $wpdb;

		/** Check nonce for security. */
		check_ajax_referer( 'reset_liker', 'nonce' );

		/** Return false for not-activated */
		if ( ! TabActivation::get_instance()->is_activated() ) {

			echo json_encode( false );
			wp_die();

        }

		/** Do we need to do a full reset? */
		if ( empty( $_POST['doReset'] ) ) {
		    wp_die( 'Wrong parameter value.' );
        }

		/** Remove meta */
		PostMeta::get_instance()->remove_liker_meta();

        /** Remove liker data from table. */
		$sql = "TRUNCATE TABLE " . $wpdb->prefix . "liker";
		$res = $wpdb->query(
			$wpdb->prepare( $sql )
		);

        /** Mark time when we make last reset. */
        update_option( 'mdp_liker_reset_timestamp', date('U' ) );

        /** Return JSON result. */
        if ( $res ) {
	        echo json_encode( true );
        } else {
	        echo json_encode( false );
        }

		/** Exit. */
		wp_die();
	}

	/**
	 * The filter posts_results is executed just after the query
	 * was executed. We'll use it as a after_get_posts-action.
	 *
	 * @param WP_Post[] $posts - Array of post objects.
	 * @param WP_Query  $query - The WP_Query instance (passed by reference).
	 *
	 * @since  1.0.0
	 * @access public
	 * @return mixed
	 **/
	public function after_set_post_query( $posts, $query ) {

		/** Work only in admin area. */
		if ( ! is_admin() ) { return $posts; }

		/** Work only with liker column sort. */
		$order_by = $query->get( 'orderby' );
		if ( 'liker_results' !== $order_by ) { return $posts; }

		/** Get order: ASC or DESC. */
		$order = $query->get( 'order' );

		/** Do sort by float rating field. */
		usort( $posts, function( $a, $b ) use ( $order ) {

			$result = 0;

			$likes = Request::get_instance()->get_likes_data( $a->ID );
			$a_rating = ! empty( $likes ) ? $likes[ 1 ] - $likes[ 3 ] : 0;

			$likes = Request::get_instance()->get_likes_data( $b->ID );
			$b_rating = ! empty( $likes ) ? $likes[ 1 ] - $likes[ 3 ] : 0;

			if ( $a_rating > $b_rating ) {

				if ( 'ASC' === $order ) {
					$result = 1;
				} else {
					$result = -1;
				}

			} elseif ( $a_rating < $b_rating ) {

				if ( 'ASC' === $order ) {
					$result = -1;
				} else {
					$result = 1;
				}

			}

			return $result;

		} );

		return $posts;

	}

	/**
	 * AJAX Process Like.
	 * Users can like 10 times from 1 IP in 24 hours.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function process_like() {

		check_ajax_referer( 'process_like', 'nonce' );

	    $options = Settings::get_instance()->options;

		$is_new =  filter_var( $_POST['new_like'], FILTER_VALIDATE_BOOLEAN );
		$liker_id = intval( $_POST['liker_id'] );
		$val_1 = intval( $_POST['val_1'] );
		$val_2 = intval( $_POST['val_2'] );
		$val_3 = intval( $_POST['val_3'] );
		$user_ip = sanitize_text_field( $this->get_ip() );
		$guid = sanitize_text_field( $_POST['guid'] );
		$session = filter_var( $_POST[ 'session' ], FILTER_VALIDATE_BOOLEAN );
		$revoting = $options[ 'revoting' ] === 'on';
		$created = gmdate( 'Y-m-d H:i:s' );
		$modified = gmdate( 'Y-m-d H:i:s' );

		$limit = Request::get_instance()->check_likes_limits( $liker_id, $user_ip ); // True if IP not reach limit

        if ( ! $session ) { // New session

            if ( $limit ) {

                // Add record if votes are less than the IP limit
	            $request_callback = Request::get_instance()->insert_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, true );
	            $liker = Request::get_instance()->get_likes( $liker_id );
	            echo json_encode(
                    [
                        'is_new' => $is_new,
                        'liker' => $liker,
                        'callback' => $request_callback
                    ]
                );

            } else {

                // Update record if IP limit exceed
	            $request_callback = Request::get_instance()->update_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, false, false );
	            $liker = Request::get_instance()->get_likes( $liker_id );
	            echo json_encode(
                    [
                        'is_new' => $is_new,
                        'liker' => $liker,
                        'callback' => $request_callback
                    ]
                );

            }

        } else { // Re-voting

            if ( $revoting ) {

                // Revoting all cases
	            $request_callback = Request::get_instance()->update_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, true, $limit );
	            $liker = Request::get_instance()->get_likes( $liker_id );
	            echo json_encode(
                    [
                        'is_new' => $is_new,
                        'liker' => ! empty( $liker ) ? $liker : Request::$empty_like,
                        'callback' => $request_callback
                    ]
                );

            } else {

                // Reject vote
	            $liker = Request::get_instance()->get_likes( $liker_id );
	            echo json_encode(
                    [
                        'liker' => $liker,
                        'callback' => [ 'status' => false , 'message' => $options[ 'limit_msg' ], 'wpdb' => 0 ]
                    ]
                );

            }

        }

		wp_die(); //Required to terminate immediately and return a proper response

	}


	/**
	 * Get likes by post id for front-end AJAX call
	 */
	public function get_like() {

		check_ajax_referer( 'get_like', 'nonce' );

	    if ( ! isset( $_POST['liker_id'] ) ) { return; }

		$liker_id = intval( $_POST['liker_id'] );
		$request_callback = Request::get_instance()->get_likes( $liker_id );

		if ( ! empty( $request_callback ) ) {

			echo json_encode(

                [
                    'status' => true,
                    'message' => 'OK',
                    'liker' => $request_callback
                ]

            );

		} else {

			echo json_encode(

                [
                    'status' => false,
                    'message' => 'No likes found for this post.',
                    'liker' => $request_callback
                ]

            );

		}

		wp_die(); //Required to terminate immediately and return a proper response

	}


	/**
	 * Get user IP.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function get_ip() {

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; //to check ip passed from proxy
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		/** Add some validation. */
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$ip = "UNKNOWN";
		}

		return $ip;

	}

	/**
	 * Clear likes after remove post/page.
	 *
	 * @param $post_id
	 *
	 * @return false|int|void
	 * @since 1.0.0
	 * @access public
	 **/
	public function before_delete_post( $post_id ) {

		global $wpdb;

		/** Work only with Selected Post Types. */
		if ( ! in_array( get_post_type( $post_id ), Settings::get_instance()->options['cpt_support'], false ) ) { return; }

		return $wpdb->delete( $wpdb->liker, ['liker_id' => $post_id] );

	}

	/**
     * Add filter
	 *
     * @param $position - position liker
	 * @since 1.0.0
	 * @access public
	 */
	public function liker_filter( $position ) {

		/** Add liker before/after Title. */
        if(  $position === 'after-title' OR $position === 'before-title' ){
	        add_filter( 'the_title', [$this, 'add_to_title'] );
        }

		/** Add liker before/after Content. */
		if ( $position === 'before-content' OR $position === 'after-content' ) {
			add_filter( 'the_content', [$this, 'add_to_content'] );
		}

	}

	/**
	 * Return Liker code.
     *
     * @param array $attr - Attributes from shortcode
	 *
	 * @return string - HTML output for shortcode.
	 * @since 1.0.0
	 * @access public
     */
	public function get_liker( $attr = [] ) {

		/** Retrieve the ID of the current item. */
		$id = isset( $attr['id'] ) ? intval( $attr[ 'id' ] ) : get_the_ID();

		/** Get plugin settings. */
		$options = Settings::get_instance()->options;

		/** Workaround default settings for captions */
		if ( ! isset( $options[ 'caption_1' ] ) ) {

			$options[ 'caption_1' ] = "+1";
			$options[ 'caption_2' ] = "0";
			$options[ 'caption_3' ] = "-1";

        }

		/** Set liker type from settings and shortcode attributes */
        if ( isset( $attr[ 'btn' ] ) && $attr[ 'btn' ] === '3' ) { $type = 'three-buttons';
        } elseif ( isset( $attr[ 'btn' ] ) && $attr[ 'btn' ] === '2' ) { $type = 'two-buttons';
        } elseif ( isset( $attr[ 'btn' ] ) && $attr[ 'btn' ] === '1' ) { $type = 'one-button';
        } else { $type = $options['type']; }

		/** Prepare classes. */
		$classes = [];
		$classes[] = $options['position']; // Position.
		$classes[] = $type; // Type.
		$classes[] = $options['style']; // Style.
		$classes[] = $options['layout']; // Layout.

        /** Prepare values for calculation. */
        $val_1 = $this->get_single_result_value( $id, 1 );
		$val_2 = $this->get_single_result_value( $id, 2 );
		$val_3 = $this->get_single_result_value( $id, 3 );

		/** Total number of votes. */
		$ratingCount = $val_1 + $val_2 + $val_3;

		if ( $ratingCount === 0 ) {
			$ratingValue = 0;
        } else {
			/** Summary average rating. */
			$ratingValue = ( $val_1 * 5 + $val_3 ) / $ratingCount;
			$ratingValue = round( $ratingValue, 2 );
        }

		/** If true then show Schema markup. */
		$isSchema = 'on' === $options[ 'google_search_results' ] && $ratingValue > 0;

		/**
		 * Set false If we use Advanced Schema Markup.
		 * @see Liker->structured_data() for details.
		 **/
		if ( 'on' === $options['advanced_markup'] AND 'on' === $options['google_search_results'] ) { $isSchema = false; }

        ob_start();

		?>

        <div id="mdp-liker-<?php echo esc_attr( $id ); ?>"
             class="mdp-liker-box <?php echo esc_attr( implode( ' ', $classes ) ); ?>"
             data-memory="<?php esc_attr_e( $options[ 'vote' ] ); ?>"
             <?php echo ( $isSchema ) ? ' itemtype="http://schema.org/Article"' : ''; ?>
        >
            <div>

                <div class="mdp-liker-description">
					<?php echo wp_kses_post( $options['description'] ); ?>
                </div>
                <div class="mdp-liker-buttons mdp-liker-results-<?php esc_attr_e( $options[ 'results' ] ); ?>"
	                 <?php echo ( $isSchema ) ? 'itemprop="aggregateRating" itemscope="" itemtype="http://schema.org/AggregateRating"' : ''; ?>
                >
		            <?php if( $isSchema ): ?>
                        <div  itemprop="itemReviewed" itemscope="" itemtype="https://schema.org/Organization"  class="mdp-hidden">
                            <span itemprop="name" class="mdp-hidden"><?php echo get_the_title(  $id ); ?></span>
                            <span itemprop="description" class="mdp-hidden"><?php echo get_the_excerpt( $id ); ?></span>
                        </div>
                        <span itemprop="ratingValue" class="mdp-hidden"><?php esc_html_e( $ratingValue ); ?></span>
                        <span itemprop="worstRating" class="mdp-hidden">1</span>
                        <span itemprop="bestRating" class="mdp-hidden">5</span>
                        <span itemprop="ratingCount" class="mdp-hidden"><?php esc_html_e( $ratingCount ) ; ?></span>
                    <?php endif; ?>

					<?php if ( 'one-button' === $type ) : ?>
                        <button id="mdp-liker-btn-1" data-val="1">
							<?php echo wp_kses_post( $options['caption_1'] ); ?>
							<?php if ( 'show' === $options['results'] ) { echo wp_kses_post( $this->get_single_result( $id, 1 ) ); }  ?>
                        </button>

					<?php elseif ( 'two-buttons' == $type ) : ?>
                        <button id="mdp-liker-btn-1" data-val="1">
							<?php echo wp_kses_post( $options['caption_1'] ); ?>
							<?php if ( $options['results'] === 'show' ) { echo wp_kses_post( $this->get_single_result( $id, 1 ) ); } ?>
                        </button>
                        <button id="mdp-liker-btn-3" data-val="-1">
							<?php echo wp_kses_post( $options['caption_3'] ); ?>
							<?php if ( $options['results'] === 'show' ) { echo wp_kses_post( $this->get_single_result( $id, 3 ) ); } ?>
                        </button>

					<?php elseif ( 'three-buttons' == $type ) : ?>
                        <button id="mdp-liker-btn-1" data-val="-1">
							<?php echo wp_kses_post( $options['caption_1'] ); ?>
							<?php if ( $options['results'] === 'show' ) { echo wp_kses_post( $this->get_single_result( $id, 1 ) ); } ?>
                        </button>
                        <button id="mdp-liker-btn-2" data-val="0">
							<?php echo wp_kses_post( $options['caption_2'] ); ?>
							<?php if ( $options['results'] === 'show' ) { echo wp_kses_post( $this->get_single_result( $id, 2 ) ); } ?>
                        </button>
                        <button id="mdp-liker-btn-3" data-val="1">
							<?php echo wp_kses_post( $options['caption_3'] ); ?>
							<?php if ( $options['results'] === 'show' ) { echo wp_kses_post( $this->get_single_result( $id, 3 ) ); } ?>
                        </button>
					<?php endif; ?>
                </div>
            </div>
        </div>
		<?php
		return ob_get_clean();

	}

	/**
	 * Add liker before/after Title.
	 *
	 * @param $title
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 */
	public function add_to_title( $title ) {

		/** Checks if plugin should work on this page. */
		if ( ! TabAssignments::get_instance()->display() ) { return $title; }

		/** Check if we are in the loop and work only with selected post types. */
		if ( ! ( is_singular( Settings::get_instance()->options['cpt_support'] ) && in_the_loop() ) ) { return $title; }

		/** Run only once. */
		static $already_run = false;
		if ( $already_run == true ) { return $title; }
		$already_run = true;

		$liker = $this->get_liker();
		if ( Settings::get_instance()->options['position'] == 'before-title' ) {

			return $liker . $title;

		} elseif ( Settings::get_instance()->options['position'] == 'after-title' ) {

			return $title . $liker;

		}

		return $title;

	}

	/**
	 * Add liker before/after Content.
	 *
	 * @param $content
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 */
	public function add_to_content( $content ) {

		/** Checks if plugin should work on this page. */
		if ( ! TabAssignments::get_instance()->display() ) { return $content . ''; }

		/** Check if we are in the loop and work only with Pages and Posts. */
		if ( ! ( is_singular( Settings::get_instance()->options['cpt_support'] ) && in_the_loop() ) ) { return $content; }

		/** Run only Once. */
		static $already_run = false;
		if ( $already_run == true ) { return $content; }
		$already_run = true;

		$liker = $this->get_liker();
		if ( Settings::get_instance()->options['position'] == 'before-content' ) {
			return $liker . $content;
		} elseif ( in_array( Settings::get_instance()->options['position'], ['after-content'] ) ) {
			return $content . $liker;
		} else {
			return $content;
		}

	}


	/**
	 * Return single voting result value.
	 *
	 * @param $id
	 * @param $num
	 *
	 * @return false|string
	 * @since 1.0.0
	 * @access public
	 **/
	public function get_single_result_value( $id, $num ) {

	    /** Get likes data from DB.*/
		$res = Request::get_instance()->get_likes_data( $id );

		/** Check for empty value, set default as zero. */
		return ! empty( $res ) ? $res[ $num ] : 0;

    }

	/**
	 * Return HTML of single voting result.
	 *
	 * @param $id
	 * @param $num
	 *
	 * @return false|string
	 * @since 1.0.0
	 * @access public
	 **/
	public function get_single_result( $id, $num ) {

        $display = Settings::get_instance()->options[ 'display' ] === 'on';
		$value = $this->get_single_result_value( $id, $num );

        // Prepare class for results displaying
		$display_class = $display ? " mdp-liker-result" : "";
		$css_class = 'val-' . $num . $display_class;

		ob_start();

		?>
        <span class="<?php echo esc_attr( $css_class ); ?>"><?php if ( $display ) : esc_html_e( $value ); endif; ?></span>
        <?php

		return ob_get_clean();
	}

	/**
	 * Output ld+json Markup.
	 *
	 * @since 1.1.2
	 * @access private
	 * @return void
	 **/
	public function structured_data() {

		global $post;

		/** Checks if plugin should work on this page. */
		if ( ! TabAssignments::get_instance()->display() ) { return; }

		/** Work only with Selected Post Types. */
		if ( ! in_array( get_post_type( $post ), Settings::get_instance()->options['cpt_support'], false ) ) { return; }

		/** Get Plugin Settings. */
		$options = Settings::get_instance()->options;

		/** Show ld+json Markup if it is enabled in settings. */
		if ( ! ( 'on' === $options['advanced_markup'] && 'on' === $options['google_search_results'] ) ) { return; }

		$id = get_post_field( 'ID' );
		$title = htmlentities( get_post_field( 'post_title' ) );
		$best = '5';

		/** Prepare values for calculation. */
		$val_1 = LikerLogics::get_instance()->get_single_result_value( $id, 1 );
		$val_2 = LikerLogics::get_instance()->get_single_result_value( $id, 2 );
		$val_3 = LikerLogics::get_instance()->get_single_result_value( $id, 3 );
		$count = $val_1 + $val_2 + $val_3;
		if ( $count === 0 ) { return; }

		/** Summary average rating. */
		$value = ( $val_1 * 5 + $val_3 ) / $count;
		$value = round( $value, 2 );

		/** We need at least one rating to show structured data. */
		if ( ! $value ) { return; }

		/** Get JSON+LD Markup from settings. */
		$json_ld = Settings::get_instance()->options['json_ld'];

		/** Apply variables replacements. */
		$json_ld = str_replace( ['[title]', '[best]', '[count]', '[value]'], [$title, $best, $count, $value], $json_ld );

		/** Output ld+json Markup. */
		printf( '<script type="application/ld+json">%s</script>', $json_ld );

	}

	/**
	 * Main LikerLogics Instance.
	 *
	 * Insures that only one instance of LikerLogics exists in memory at any one time.
	 *
	 * @static
	 * @return LikerLogics
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof LikerLogics ) ) {
			self::$instance = new LikerLogics();
		}

		return self::$instance;

	}

}
