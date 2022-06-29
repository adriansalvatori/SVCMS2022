<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Privacy_Abstract;
use AutomateWoo\Format;

defined( 'ABSPATH' ) || exit;

/**
 * @class Privacy
 * @since 2.0
 */
class Privacy extends Privacy_Abstract {

	static $limit = 10;


	/**
	 * Init - hook into events.
	 */
	function __construct() {
		$self = __CLASS__; /** @var $self Privacy */

		parent::__construct( __( 'AutomateWoo - Refer A Friend', 'automatewoo-referrals' ) );

		// erasers
		$this->add_eraser( 'automatewoo-referral-codes', __( 'Referral codes', 'automatewoo-referrals' ), [ $self, 'erase_referral_codes' ] );
		$this->add_eraser( 'automatewoo-referrals', __( 'Referrals', 'automatewoo-referrals' ), [ $self, 'anonymize_referrals' ] );
		$this->add_eraser( 'automatewoo-invites', __( 'Referral invites', 'automatewoo-referrals' ), [ $self, 'erase_referral_invites' ] );

		// exporters
		$this->add_exporter( 'automatewoo-referrals', __( 'Referrals', 'automatewoo-referrals' ), [ $self, 'export_referrals' ] );
		$this->add_exporter( 'automatewoo-invites', __( 'Referral invites', 'automatewoo-referrals' ), [ $self, 'export_referral_invites' ] );
		$this->add_exporter( 'automatewoo-referral-codes', __( 'Referral codes', 'automatewoo-referrals' ), [ $self, 'export_referral_codes' ] );

		add_action( 'automatewoo/privacy/erase_user_meta', [ $self, 'erase_user_meta' ] );
		add_filter( 'automatewoo/privacy/exported_customer_data', [ $self, 'filter_exported_customer_data' ], 10, 2 );
		add_filter( 'automatewoo/privacy/exported_data_layer', [ $self, 'filter_exported_data_layer' ], 10, 2 );
	}


	/**
	 * Remove any referral codes linked to the customer.
	 *
	 * @param string $email
	 * @param int $page
	 * @return array
	 */
	static function erase_referral_codes( $email, $page ) {
		$response = [
			'items_removed' => false,
			'items_retained' => false,
			'messages' => [],
			'done' => true,
		];

		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof \WP_User ) {
			return $response;
		}

		// query shouldn't be paged because items are being deleted in each batch
		$query = new Advocate_Key_Query();
		$query->where( 'advocate_id', $user->ID );
		$query->set_limit( self::$limit );
		$results          = $query->get_results();
		$count            = count( $results );
		$response['done'] = $count < self::$limit;

		if ( $response['done'] ) {
			$response['messages'][] = __( "Erased user's referral codes.", 'automatewoo-referrals' );
		}

		if ( $results ) {
			$response['items_removed'] = true;

			foreach ( $results as $result ) {
				$result->delete();
			}
		}

		return $response;
	}


	/**
	 * @param string $email
	 * @param int $page
	 * @return array
	 */
	static function erase_referral_invites( $email, $page ) {
		$response = [
			'items_removed' => false,
			'items_retained' => false,
			'messages' => [],
			'done' => true,
		];

		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof \WP_User ) {
			return $response;
		}

		// query shouldn't be paged because items are being deleted in each batch
		$query = new Invite_Query();
		$query->where( 'advocate_id', $user->ID );
		$query->set_limit( self::$limit );
		$results          = $query->get_results();
		$count            = count( $results );
		$response['done'] = $count < self::$limit;

		if ( $response['done'] ) {
			$response['messages'][] = __( "Erased user's invite records.", 'automatewoo-referrals' );
		}

		if ( $results ) {
			$response['items_removed'] = true;

			foreach ( $results as $result ) {
				$result->delete();
			}
		}

		return $response;
	}


	/**
	 * @param string $email
	 * @param int $page
	 * @return array
	 */
	static function anonymize_referrals( $email, $page ) {
		$response = [
			'items_removed' => false,
			'items_retained' => false,
			'messages' => [],
			'done' => true,
		];

		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof \WP_User ) {
			return $response;
		}

		// query shouldn't be paged because items are being deleted in each batch
		$query                         = new Referral_Query();
		$query->combine_wheres_with_or = true;
		$query->where( 'advocate_id', $user->ID );
		$query->where( 'user_id', $user->ID );
		$query->set_limit( self::$limit );
		$results          = $query->get_results();
		$count            = count( $results );
		$response['done'] = $count < self::$limit;

		if ( $response['done'] ) {
			$response['messages'][] = __( "Anonymized user's referrals.", 'automatewoo-referrals' );
		}

		if ( $results ) {
			$response['items_retained'] = true;

			foreach ( $results as $referral ) {
				if ( $referral->get_user_id() == $user->ID ) {
					$referral->set_user_id( 0 );
					$referral->set_order_id( 0 );
				}

				if ( $referral->get_advocate_id() == $user->ID ) {
					$referral->set_advocate_id( 0 );
				}

				$referral->save();
			}
		}

		return $response;
	}



	/**
	 * Erase user meta
	 * @param \WP_User $user
	 */
	static function erase_user_meta( $user ) {
		if ( ! $user instanceof \WP_User ) {
			return;
		}

		delete_user_meta( $user->ID, '_aw_referrals_advocate_ip' );
		delete_user_meta( $user->ID, '_automatewoo_referral_ip_address' );
	}


	/**
	 * @param array $data
	 * @param string $email
	 * @return array
	 */
	static function filter_exported_customer_data( $data, $email ) {
		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof \WP_User ) {
			return $data;
		}

		$data[ __( 'Advocate IP address', 'automatewoo-referrals' ) ]        = get_user_meta( $user->ID, '_aw_referrals_advocate_ip', true );
		$data[ __( 'Customer signup IP address', 'automatewoo-referrals' ) ] = get_user_meta( $user->ID, '_automatewoo_referral_ip_address', true );

		return $data;
	}


	/**
	 * @param string $email
	 * @param int $page
	 * @return array
	 */
	static function export_referrals( $email, $page ) {
		$response = [
			'data' => [],
			'done' => true,
		];

		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof \WP_User ) {
			return $response;
		}

		$query                         = new Referral_Query();
		$query->combine_wheres_with_or = true;
		$query->where( 'advocate_id', $user->ID );
		$query->where( 'user_id', $user->ID );
		$query->set_limit( self::$limit );
		$query->set_page( $page );

		$results          = $query->get_results();
		$response['done'] = count( $results ) < self::$limit;

		foreach ( $results as $referral ) {
			$item               = [
				'group_id' => 'automatewoo_referrals',
				'group_label' => __( 'Refer A Friend referrals', 'automatewoo-referrals' ),
				'item_id' => 'referral-' . $referral->get_id(),
				'data' => self::get_referral_data( $referral, $user->ID ),
			];
			$response['data'][] = $item;
		}

		return $response;
	}


	/**
	 * @param Referral $referral
	 * @param int $requesting_user_id
	 * @return array
	 */
	static function get_referral_data( $referral, $requesting_user_id ) {
		$data = [];

		$data[ __( 'ID', 'automatewoo-referrals' ) ]                      = $referral->get_id();
		$data[ __( 'Status', 'automatewoo-referrals' ) ]                  = $referral->get_status_name();
		$data[ __( 'Date created', 'automatewoo-referrals' ) ]            = Format::datetime( $referral->get_date(), 0 );
		$data[ __( 'Reward type', 'automatewoo-referrals' ) ]             = AW_Referrals()->get_reward_types()[ $referral->get_reward_type() ];
		$data[ __( 'Reward amount', 'automatewoo-referrals' ) ]           = Format::decimal( $referral->get_reward_amount() );
		$data[ __( 'Reward amount remaining', 'automatewoo-referrals' ) ] = Format::decimal( $referral->get_reward_amount_remaining() );
		$data[ __( 'Order ID', 'automatewoo-referrals' ) ]                = $referral->get_order_id();

		if ( $offer_type = $referral->get_offer_type() ) {
			$data[ __( 'Offer type', 'automatewoo-referrals' ) ]   = AW_Referrals()->get_offer_types()[ $offer_type ];
			$data[ __( 'Offer amount', 'automatewoo-referrals' ) ] = Format::decimal( $referral->get_offer_amount() );
		}

		// don't include user data that belongs to someone else
		if ( $requesting_user_id == $referral->get_advocate_id() ) {
			$data[ __( 'Advocate ID', 'automatewoo-referrals' ) ] = $referral->get_advocate_id();
		}

		if ( $requesting_user_id == $referral->get_user_id() ) {
			$data[ __( 'Friend ID', 'automatewoo-referrals' ) ] = $referral->get_user_id();
		}

		return Privacy::parse_export_data_array( $data );
	}


	/**
	 * @param string $email
	 * @param int $page
	 * @return array
	 */
	static function export_referral_invites( $email, $page ) {
		$response = [
			'data' => [],
			'done' => true,
		];

		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof \WP_User ) {
			return $response;
		}

		$query = new Invite_Query();
		$query->where( 'advocate_id', $user->ID );
		$query->set_limit( self::$limit );
		$query->set_page( $page );

		$results          = $query->get_results();
		$response['done'] = count( $results ) < self::$limit;

		foreach ( $results as $invite ) {
			$item               = [
				'group_id' => 'automatewoo_referral_invites',
				'group_label' => __( 'Refer A Friend invites', 'automatewoo-referrals' ),
				'item_id' => 'invite-' . $invite->get_id(),
				'data' => self::get_invite_data( $invite ),
			];
			$response['data'][] = $item;
		}

		return $response;
	}


	/**
	 * @param Invite $invite
	 * @return array
	 */
	static function get_invite_data( $invite ) {
		$data = [];

		$data[ __( 'ID', 'automatewoo-referrals' ) ]            = $invite->get_id();
		$data[ __( 'Invited email', 'automatewoo-referrals' ) ] = $invite->get_email();
		$data[ __( 'Date', 'automatewoo-referrals' ) ]          = Format::datetime( $invite->get_date(), 0 );

		return Privacy::parse_export_data_array( $data );
	}


	/**
	 * @param string $email
	 * @param int $page
	 * @return array
	 */
	static function export_referral_codes( $email, $page ) {
		$response = [
			'data' => [],
			'done' => true,
		];

		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof \WP_User ) {
			return $response;
		}

		$query = new Advocate_Key_Query();
		$query->where( 'advocate_id', $user->ID );
		$query->set_limit( self::$limit );
		$query->set_page( $page );

		$results          = $query->get_results();
		$response['done'] = count( $results ) < self::$limit;

		foreach ( $results as $invite ) {
			$item               = [
				'group_id' => 'automatewoo_referral_codes',
				'group_label' => __( 'Refer A Friend referral codes', 'automatewoo-referrals' ),
				'item_id' => 'referral-code-' . $invite->get_id(),
				'data' => self::get_referral_code_data( $invite ),
			];
			$response['data'][] = $item;
		}

		return $response;
	}


	/**
	 * @param Advocate_Key $referral_code
	 * @return array
	 */
	static function get_referral_code_data( $referral_code ) {
		$data = [];

		$data[ __( 'Referral code ID', 'automatewoo-referrals' ) ] = $referral_code->get_id();
		$data[ __( 'Referral code', 'automatewoo-referrals' ) ]    = $referral_code->get_key();
		$data[ __( 'Date created', 'automatewoo-referrals' ) ]     = Format::datetime( $referral_code->get_date_created(), 0 );

		return Privacy::parse_export_data_array( $data );
	}


	/**
	 * @param array $formatted_data
	 * @param array $raw_data_layer
	 * @return array
	 */
	static function filter_exported_data_layer( $formatted_data, $raw_data_layer ) {

		foreach ( $raw_data_layer as $data_type => $data_item ) {

			if ( ! $data_item ) {
				continue;
			}

			switch ( $data_type ) {
				case 'referral':
					/** @var $data_item Referral */
					$formatted_data[] = [
						'title' => __( 'Referral', 'automatewoo-referrals' ),
						'value' => '#' . $data_item->get_id()
					];
					break;

				// Exclude advocate data since it's possible that a different user could be viewing the data layer
				case 'advocate':
					break;

			}
		}

		return $formatted_data;
	}


	/**
	 * Add suggested privacy policy content for the privacy policy page.
	 */
	public function get_privacy_message() {
		return Privacy_Policy_Guide::get_content();
	}


}
