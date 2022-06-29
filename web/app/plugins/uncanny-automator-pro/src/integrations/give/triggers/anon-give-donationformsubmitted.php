<?php

namespace Uncanny_Automator_Pro;

/**
 * Class ANON_GIVE_DONATIONFORMSUBMITTED
 *
 * @package Uncanny_Automator_Pro
 */
class ANON_GIVE_DONATIONFORMSUBMITTED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GIVEWP';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'ANONDONATION';
		$this->trigger_meta = 'DONATIONFORM';
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
			'sentence'            => sprintf( esc_attr__( '{{A donation form:%1$s}} is submitted', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - GiveWP */
			'select_option_name'  => esc_attr__( '{{A donation form}} is submitted', 'uncanny-automator-pro' ),
			'action'              => 'give_insert_payment',
			'type'                => 'anonymous',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'givewp_make_donation' ),
			'options'             => array(
				Automator()->helpers->recipe->give->options->list_all_give_forms( esc_attr__( 'Form', 'uncanny-automator-pro' ), $this->trigger_meta ),
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
		$give_form_id   = $payment_data['give_form_id'];
		$amount         = $payment_data['price'];
		$form_submitted = $payment_data['give_form_title'];
		$user_id        = get_current_user_id();

		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_form      = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( isset( $required_form[ $recipe_id ] ) && isset( $required_form[ $recipe_id ][ $trigger_id ] ) ) {
					//Add where option is set to Any Form
					if ( intval( '-1' ) === intval( $required_form[ $recipe_id ][ $trigger_id ] ) ) {
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					} elseif ( $required_form[ $recipe_id ][ $trigger_id ] == $give_form_id ) {
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

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_ID';
							$trigger_meta['meta_value'] = maybe_serialize( $give_form_id );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $form_submitted );
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
