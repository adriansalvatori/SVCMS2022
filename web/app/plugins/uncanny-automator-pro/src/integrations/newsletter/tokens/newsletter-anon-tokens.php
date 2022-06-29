<?php

namespace Uncanny_Automator_pro;

/**
 * Class Gf_Tokens
 *
 * @package Uncanny_Automator
 */
class Newsletter_Anon_Tokens {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'NEWSLETTER';

	public function __construct() {
		//*************************************************************//
		// See this filter generator AT automator-get-data.php
		// in function recipe_trigger_tokens()
		//*************************************************************//
		add_filter(
			'automator_maybe_trigger_newsletter_newsletterlist_tokens',
			array(
				$this,
				'newsletter_possible_tokens',
			),
			20,
			2
		);
		add_filter( 'automator_maybe_parse_token', array( $this, 'newletter_token' ), 20, 6 );
	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $plugin
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $plugin ) {

		if ( self::$integration === $plugin ) {
			if ( defined( 'NEWSLETTER_VERSION' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	function newsletter_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}

		$trigger_meta = $args['meta'];

		$new_tokens = array(
			array(
				'tokenId'         => 'USEREMAIL',
				'tokenName'       => __( 'User email', 'uncanny-automator' ),
				'tokenType'       => 'email',
				'tokenIdentifier' => $trigger_meta,
			),
		);

		$tokens = array_merge( $tokens, $new_tokens );

		return $tokens;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 *
	 * @return string|null
	 */
	public function newletter_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		if ( $pieces ) {

			global $uncanny_automator;

			if ( 'SUBSCRIBESLIST' === $pieces[1] ) {
				if ( 'NEWSLETTERLIST' === $pieces[2] ) {

					if ( isset( $trigger_data[0]['meta']['NEWSLETTERLIST'] ) && '-1' === $trigger_data[0]['meta']['NEWSLETTERLIST'] ) {

						$replace_pieces = $replace_args['pieces'];
						$trigger_log_id = $replace_args['trigger_log_id'];
						$run_number     = $replace_args['run_number'];
						$trigger_id     = absint( $replace_pieces[0] );

						global $wpdb;

						$q = "SELECT meta_value
													FROM {$wpdb->prefix}uap_trigger_log_meta
													WHERE meta_key = 'LISTSDATA'
													AND automator_trigger_log_id = $trigger_log_id
													AND automator_trigger_id = $trigger_id
													LIMIT 0, 1";

						$lists_data = $wpdb->get_var( $q );

						$lists_data = maybe_unserialize( $lists_data );

						$lists_added = array();
						if ( is_object( $lists_data ) ) {
							$lists = get_option( 'newsletter_subscription_lists', array() );

							for ( $i = 1; $i <= NEWSLETTER_LIST_MAX; $i ++ ) {
								// not a valid list item
								if ( empty( $lists[ 'list_' . $i ] ) ) {
									continue;
								}
								// Don't show private lists. They are admin only
								if ( '1' !== $lists[ 'list_' . $i . '_status' ] ) {
									continue;
								}

								$options[ 'list_' . $i ] = $lists[ 'list_' . $i ];
							}

							foreach ( $lists_data as $list_slug => $data ) {
								if ( '1' === $data ) {
									$lists_added[] = $options[ $list_slug ];
								}
							}
						}

						$value = implode( ', ', $lists_added );
					} else {
						$value = isset( $trigger_data[0]['meta']['NEWSLETTERLIST_readable'] ) ? $trigger_data[0]['meta']['NEWSLETTERLIST_readable'] : '';
					}
				}
			}

			if ( 'NEWSLETTERLIST' === $pieces[1] ) {
				if ( 'USEREMAIL' === $pieces[2] ) {

					$value = '';

					$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;
					$trigger_id     = $pieces[0];

					global $wpdb;
					$q = "SELECT meta_value
													FROM {$wpdb->prefix}uap_trigger_log_meta
													WHERE meta_key = '$pieces[2]'
													AND automator_trigger_log_id = $trigger_log_id
													AND automator_trigger_id = $trigger_id
													LIMIT 0, 1";

					$_entry = $wpdb->get_var( $q );

					if ( ! empty( $_entry ) ) {
						$value = $_entry;
					}
				}
			}
		}

		return $value;
	}
}
