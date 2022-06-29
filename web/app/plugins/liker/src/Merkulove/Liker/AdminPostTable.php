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
use Merkulove\Liker\Unity\TabActivation;

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
final class AdminPostTable {

	/**
	 * The one true AdminPostTable.
	 *
	 * @var AdminPostTable
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * @var array
	 */
	private static $options;

	/**
	 * Sets up a new AdminPostTable instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		self::$options = Settings::get_instance()->options;

		/** Add Liker column to Selected Posts list. */
		foreach ( self::$options['cpt_support'] as $cpt ) {

			add_filter( "manage_{$cpt}_posts_columns", [ $this, 'add_head_column'], 10 );
			add_action( "manage_{$cpt}_posts_custom_column", [ $this, 'add_content_column'], 10, 2 );

			/** Register our custom column liker_results as 'sortable'. */
			add_filter( "manage_edit-{$cpt}_sortable_columns", [ $this, 'sortable_result_column' ] );

		}

	}

	/**
	 * Register our custom column liker_results as 'sortable'.
	 *
	 * @param array $sortable_columns - An array of sortable columns.
	 *
	 * @since  1.1.4
	 * @access public
	 * @return mixed
	 **/
	public function sortable_result_column( $sortable_columns ) {

		$sortable_columns['liker_results'] = 'liker_results';

		return $sortable_columns;

	}

	/**
	 * Add content for liker column with results.
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 **/
	public function add_content_column( $column_name, $post_id ) {

		/** Work only with Liker column. */
		if ( $column_name !== 'liker_results' ) { return; }

		/** Get likes values for current post. */
		$likes = Request::get_instance()->get_likes_data( $post_id );

		/** Exit if likes for current post not found. */
		if ( count( $likes ) === 0 ) { return; }

		/** Likes - Dislikes = Amount. */
		$amount = $likes[1] - $likes[3];

		/** Likes + Neutral + Dislikes = Total votes. */
		$total = $likes[1] + $likes[2] + $likes[3];
		$total_percent = round( abs( ( $amount / $total ) * 100 ), 2 );

		/** Calculate gradient percents */
		if ( $amount < 0 ) {
			$total_css = 'background: #e0e0e0; background: linear-gradient(-90deg, #fd5e6b ' . $total_percent . '%, #e5e5e5 ' . $total_percent . '%);';
			$results_is = '-negative';
		} else {
			$total_css = 'background: #e0e0e0; background: linear-gradient(90deg, #b5d776 ' . $total_percent . '%, #e5e5e5 ' . $total_percent . '%);';
			$results_is = '-positive';
		}

		/** Set background for non-progressbar */
		if ( self::$options[ 'progressbar' ] === 'off' ) {
			$total_css = 'background: #e5e5e5;';
		}

		/** Show results for non-activated */
		if ( ! TabActivation::get_instance()->is_activated() ) {
		    ?>
		    <span class="mdp-liker<?php esc_html_e( $results_is ); ?>" style="background: #e0e0e0">
                <span class="dashicons dashicons-heart"></span>
                <span class="mdp-liker-amount" title="<?php esc_html_e( 'Amount: ' . '???' ) ?>">???</span>
            </span>
		    <?php

        /** Show result as Amount. */
		} else if ( 'amount' === self::$options['results_admin'] ) {

			?>
			<span class="mdp-liker<?php esc_html_e( $results_is ); ?>" style="<?php esc_html_e( $total_css ); ?>" title="<?php esc_html_e( $total_percent . '%' ); ?>">
                <span class="dashicons dashicons-heart"></span>
                <span class="mdp-liker-amount" title="<?php esc_html_e( 'Amount: ' . $amount ) ?>"><?php esc_html_e( $amount ) ?></span>
            </span>
			<?php

        /** Show result as Amount / Total. */
		} elseif ( 'total' === self::$options['results_admin'] ) {

			?>
			<span class="mdp-liker-total" style="<?php esc_html_e( $total_css ); ?>" title="<?php esc_html_e( $total_percent . '%' ); ?>">
                <span class="dashicons dashicons-heart"></span>
                <span class="mdp-liker-amount" title="<?php esc_html_e( 'Amount: ' . $amount ) ?>"><?php esc_html_e( $amount ) ?></span>
                <span class="mdp-liker-divider">/</span>
                <span class="dashicons dashicons-groups"></span>
                <span class="mdp-liker-votes" title="<?php esc_html_e( 'Total: ' . $total, 'liker' ) ?>"><?php esc_html_e( $total ) ?></span>
            </span>
			<?php

        /** Show result as +1 | 0 | -1. */
		} elseif ( 'split' === self::$options['results_admin'] ) {

			?>
			<span class="mdp-liker-split" style="<?php esc_html_e( $total_css ); ?>" title="<?php esc_html_e( $total_percent . '%' ); ?>">
            <?php


            /** Render for One Button. */
            if ( 'one-button' === self::$options['type'] ) {

	            ?>
	            <span class="mdp-liker-amount"><?php esc_html_e( $likes[ 1 ] ); ?></span>
	            <?php
            }

            /** Render for Two Button. */
            if ( 'two-buttons' === self::$options['type'] ) {

	            ?>
	            <span class="mdp-liker-amount"><?php esc_html_e( $likes[ 1 ] ); ?></span>
	            <span class="mdp-liker-divider">|</span>
	            <span class="mdp-liker-amount"><?php esc_html_e( $likes[ 3 ] ); ?></span>
	            <?php

            }

            /** Render for Tree Button. */
            if ( 'three-buttons' === self::$options['type'] ) {

	            ?>
	            <span class="mdp-liker-amount"><?php esc_html_e( $likes[ 1 ] ); ?></span>
	            <span class="mdp-liker-divider">|</span>
	            <span class="mdp-liker-amount"><?php esc_html_e( $likes[ 2 ] ); ?></span>
	            <span class="mdp-liker-divider">|</span>
	            <span class="mdp-liker-amount"><?php esc_html_e( $likes[ 3 ] ); ?></span>
	            <?php

            }

            ?>
            </span>
			<?php

		}

	}

	/**
	 * Add HEAD for liker column with results.
	 *
	 * @param array $columns
	 * @return array
	 * @since 1.0.0
	 * @access public
	 **/
	public function add_head_column( $columns ) {

		/** Add new column to the existing columns. */
		$new = [];

		/** If we have comments column, add after it. */
		$add_after = 'title';
		if ( isset( $columns['comments'] ) ) {
			$add_after = 'comments';
		} elseif ( isset( $columns['author'] ) ) {
			$add_after = 'author';
		} elseif ( isset( $columns['date'] ) ) {
			$add_after = 'date';
		}

		foreach ( $columns as $key => $col ) {

			$new[$key] = $col;

			if ( $key == $add_after ) { // After Comments column.
				$new['liker_results'] = '<span class="dashicons dashicons-heart mdp-dashicons" title="' . esc_attr__('Rating', 'liker' ) . '"><span class="screen-reader-text">' . esc_attr__('Rating', 'liker' ) . '</span></span>';
			}

		}

		/** Return a new column array to WordPress. */
		return $new;

	}

	/**
	 * Main AdminPostTable Instance.
	 *
	 * Insures that only one instance of AdminPostTable exists in memory at any one time.
	 *
	 * @static
	 * @return AdminPostTable
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class AdminPostTable.
