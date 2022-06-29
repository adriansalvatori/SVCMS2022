<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GIVE_FORMFIELD
 *
 * @package Uncanny_Automator_Pro
 */
class GIVE_FORMFIELD {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GIVEWP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'SPECIFIEDFORM';
		$this->trigger_meta = 'GIVEWPSPECIFIEDFORM';
		$this->define_trigger();
	}

	/**
	 * Define trigger settings
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/givewp/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - GiveWP */
			'sentence'            => sprintf(
				esc_attr__( 'A user makes a donation via {{a form:%1$s}} with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SPECIFIEDVALUE' . ':' . $this->trigger_meta,
				'SPECIFIEDFIELD' . ':' . $this->trigger_meta
			),
			/* translators: Logged-in trigger - GiveWP */
			'select_option_name'  => esc_attr__( 'A user makes a donation via {{a form}} with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'give_insert_payment',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'givewp_make_donation' ),
			'options'             => array(),
			'options_group'       => array(
				$this->trigger_meta => array(
					/* translators: Noun */
					Automator()->helpers->recipe->give->options->list_all_give_forms(
						esc_attr__( 'Form', 'uncanny-automator-pro' ),
						$this->trigger_meta,
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => 'SPECIFIEDFIELD',
							'endpoint'     => 'select_form_fields_DONATIONFORMS',
						)
					),
					Automator()->helpers->recipe->field->select_field( 'SPECIFIEDFIELD', esc_attr__( 'Field', 'uncanny-automator-pro' ) ),
					Automator()->helpers->recipe->field->text_field( 'SPECIFIEDVALUE', esc_attr__( 'Value', 'uncanny-automator-pro' ) ),
				),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @param $payment_id
	 * @param $payment_data
	 *
	 * @return void
	 */
	public function givewp_make_donation( $payment_id, $payment_data ) {
		$give_form_id = $payment_data['give_form_id'];
		$user_id      = $payment_data['user_info']['id'];
		$amount       = $payment_data['price'];

		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes           = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_form     = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_field    = Automator()->get->meta_from_recipes( $recipes, 'SPECIFIEDFIELD' );
		$required_value    = Automator()->get->meta_from_recipes( $recipes, 'SPECIFIEDVALUE' );
		$form_fields       = Automator()->helpers->recipe->give->get_form_fields_and_ffm( $give_form_id );
		$custom_field_data = give_get_meta( $payment_id, '_give_payment_meta', true );

		foreach ( $form_fields as $i => $field ) {
			if ( $field['custom'] == true ) {
				$payment_data[ $field['key'] ] = $custom_field_data[ $field['key'] ];
			}
		}
		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( isset( $required_form[ $recipe_id ] ) && isset( $required_form[ $recipe_id ][ $trigger_id ] ) ) {
					//Add where option is set to Any Form
					if ( intval( '-1' ) === intval( $required_form[ $recipe_id ][ $trigger_id ] ) &&
						 ( ( $payment_data[ $form_fields[ $required_field[ $recipe_id ][ $trigger_id ] ]['key'] ] == strtolower( $required_value[ $recipe_id ][ $trigger_id ] )
							 || $payment_data[ $form_fields[ $required_field[ $recipe_id ][ $trigger_id ] ]['key'] ] == $required_value[ $recipe_id ][ $trigger_id ] ) ||
						   ( ( $payment_data['user_info'][ $form_fields[ $required_field[ $recipe_id ][ $trigger_id ] ]['key'] ] == strtolower( $required_value[ $recipe_id ][ $trigger_id ] )
							   || $payment_data['user_info'][ $form_fields[ $required_field[ $recipe_id ][ $trigger_id ] ]['key'] ] == $required_value[ $recipe_id ][ $trigger_id ] ) ) ) ) {
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					} //Add where option is set to Specific Form
					elseif ( absint( $required_form[ $recipe_id ][ $trigger_id ] ) === absint( $give_form_id ) &&
							 ( ( $payment_data[ $form_fields[ $required_field[ $recipe_id ][ $trigger_id ] ]['key'] ] == strtolower( $required_value[ $recipe_id ][ $trigger_id ] )
								 || $payment_data[ $form_fields[ $required_field[ $recipe_id ][ $trigger_id ] ]['key'] ] == $required_value[ $recipe_id ][ $trigger_id ] ) ||
							   ( $payment_data['user_info'][ $form_fields[ $required_field[ $recipe_id ][ $trigger_id ] ]['key'] ] == strtolower( $required_value[ $recipe_id ][ $trigger_id ] )
								 || $payment_data['user_info'][ $form_fields[ $required_field[ $recipe_id ][ $trigger_id ] ]['key'] ] == $required_value[ $recipe_id ][ $trigger_id ] ) ) ) {
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					}
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				);

				$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							$trigger_meta ['meta_key']  = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SPECIFIEDFIELD';
							$trigger_meta['meta_value'] = maybe_serialize( $required_field );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta ['meta_key']  = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_ID';
							$trigger_meta['meta_value'] = maybe_serialize( $give_form_id );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta ['meta_key']  = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $payment_data['give_form_title'] );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SPECIFIEDVALUE';
							$trigger_meta['meta_value'] = maybe_serialize( $required_value );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':ACTUALDONATEDAMOUNT';
							$trigger_meta['meta_value'] = maybe_serialize( $amount );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'payment_data';
							$trigger_meta['meta_value'] = maybe_serialize( $payment_data );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'payment_id';
							$trigger_meta['meta_value'] = maybe_serialize( $payment_id );
							Automator()->insert_trigger_meta( $trigger_meta );

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
