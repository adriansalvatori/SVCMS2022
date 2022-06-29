<?php

namespace Uncanny_Automator_Pro;

use MeprBaseRealGateway;
use MeprEvent;
use MeprOptions;
use MeprProduct;
use MeprTransaction;
use MeprUser;
use MeprUtils;

/**
 * Class MP_ADDUSERMEMBERSHIP
 * @package Uncanny_Automator_Pro
 */
class MP_ADDUSERMEMBERSHIP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'MPADDUSERMEMBERSHIP';
		$this->action_meta = 'MPUSERMEMBERSHIP';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$taxn_status  = [
			/* translators: MemberPress membership status */
			MeprTransaction::$complete_str => __( 'Complete', 'uncanny-automator-pro' ),
			/* translators: MemberPress membership status */
			MeprTransaction::$pending_str  => __( 'Pending', 'uncanny-automator-pro' ),
			/* translators: MemberPress membership status */
			MeprTransaction::$failed_str   => __( 'Failed', 'uncanny-automator-pro' ),
			/* translators: MemberPress membership status */
			MeprTransaction::$refunded_str => __( 'Refunded', 'uncanny-automator-pro' ),
		];
		$mepr_options = MeprOptions::fetch();

		$pms      = array_keys( $mepr_options->integrations );
		$gateways = [ 'manuel' => __( 'Manual', 'uncanny-automator-pro' ) ];
		foreach ( $pms as $pm_id ) {
			$obj = $mepr_options->payment_method( $pm_id );
			if ( $obj instanceof MeprBaseRealGateway ) {
				$gateways[ $obj->id ] = sprintf( '%1$s (%2$s)', $obj->label, $obj->name );
			}
		}

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/memberpress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - MemberPress */
			'sentence'           => sprintf( __( 'Add the user to {{a membership:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - MemberPress */
			'select_option_name' => __( 'Add the user to {{a membership}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'add_membership' ),
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->memberpress->pro->all_memberpress_products( __( 'Membership', 'uncanny-automator-pro' ), $this->action_meta, [ 'uo_include_any' => false ] ),
					$uncanny_automator->helpers->recipe->field->text_field( 'SUBTOTAL', __( 'Subtotal', 'uncanny-automator-pro' ), false, 'text', '0', true ),
					$uncanny_automator->helpers->recipe->field->text_field( 'TAXAMOUNT', __( 'Tax amount', 'uncanny-automator-pro' ), false, 'text', '0', true ),
					$uncanny_automator->helpers->recipe->field->text_field( 'TAXRATE', __( 'Tax rate', 'uncanny-automator-pro' ), false, 'text', '', true ),
					$uncanny_automator->helpers->recipe->field->select_field( 'STATUS', __( 'Status', 'uncanny-automator-pro' ), $taxn_status ),
					$uncanny_automator->helpers->recipe->field->select_field( 'GATEWAY', __( 'Gateway', 'uncanny-automator-pro' ), $gateways ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EXPIRATIONDATE', __( 'Expiration date', 'uncanny-automator-pro' ), false, 'text', '', false, __( 'Leave empty to use expiry settings from the membership, or type a specific date in the format YYYY-MM-DD', 'uncanny-automator-pro' ) ),
				],
			],
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function add_membership( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$product_id      = $uncanny_automator->parse->text( $action_data['meta'][ $this->action_meta ] );
		$sub_total       = $uncanny_automator->parse->text( $action_data['meta']['SUBTOTAL'] );
		$tax_amount      = $uncanny_automator->parse->text( $action_data['meta']['TAXAMOUNT'] );
		$tax_rate        = $uncanny_automator->parse->text( $action_data['meta']['TAXRATE'] );
		$tnx_status      = $uncanny_automator->parse->text( $action_data['meta']['STATUS'] );
		$gateway         = $uncanny_automator->parse->text( $action_data['meta']['GATEWAY'] );
		$expiration_date = $uncanny_automator->parse->text( $action_data['meta']['EXPIRATIONDATE'] );

		$txn  = new MeprTransaction();
		$user = new MeprUser();
		$user->load_user_data_by_id( $user_id );

		$txn->trans_num  = uniqid( 'ua-mp-' );
		$txn->user_id    = $user->ID;
		$txn->product_id = sanitize_key( $product_id );

		$txn->amount     = (float) $sub_total;
		$txn->tax_amount = (float) $tax_amount;
		$txn->total      = ( (float) $sub_total + (float) $tax_amount );
		$txn->tax_rate   = (float) $tax_rate;
		$txn->status     = sanitize_text_field( $tnx_status );
		$txn->gateway    = sanitize_text_field( $gateway );
		$txn->created_at = MeprUtils::ts_to_mysql_date( time() );

		if ( isset( $expiration_date ) && ( $expiration_date === '' || is_null( $expiration_date ) ) ) {
			$obj           = new MeprProduct( sanitize_key( $product_id ) );
			$expires_at_ts = $obj->get_expires_at();
			if ( is_null( $expires_at_ts ) ) {
				$txn->expires_at = MeprUtils::db_lifetime();
			} else {
				$txn->expires_at = MeprUtils::ts_to_mysql_date( $expires_at_ts, 'Y-m-d 23:59:59' );
			}
		} else {
			$txn->expires_at = MeprUtils::ts_to_mysql_date( strtotime( $expiration_date ), 'Y-m-d 23:59:59' );
		}

		$txn->store();

		if ( $txn->status == MeprTransaction::$complete_str ) {
			MeprEvent::record( 'transaction-completed', $txn );

			// This is a recurring payment
			if ( ( $sub = $txn->subscription() ) && $sub->txn_count > 1 ) {
				MeprEvent::record( 'recurring-transaction-completed',
					$txn );
			} elseif ( ! $sub ) {
				MeprEvent::record( 'non-recurring-transaction-completed',
					$txn );
			}
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
