<?php

namespace Uncanny_Automator_Pro;

use FrmEntryMeta;

/**
 * Class FI_SUBMITFIELD
 * @package Uncanny_Automator_Pro
 */
class ANON_FI_SUBMITFIELD {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'FI';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'ANONFISUBMITFIELD';
		$this->trigger_meta = 'ANONFIFORM';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name(),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/formidable-forms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'sentence'            => sprintf(
			/* translators: Anonymous trigger - Formidable */
				__( '{{A form:%1$s}} is submitted with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SUBVALUE' . ':' . $this->trigger_meta,
				$this->trigger_code . ':' . $this->trigger_meta
			),
			/* translators: Anonymous trigger - Formidable */
			'select_option_name'  => __( '{{A form}} is submitted with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'frm_after_create_entry',
			'type'                => 'anonymous',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'fi_submit_form' ),
			'options'             => [],
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->formidable->options->all_formidable_forms( null, $this->trigger_meta, [
						'token'        => false,
						'is_ajax'      => true,
						'target_field' => $this->trigger_code,
						'endpoint'     => 'select_form_fields_ANONFIFORMS',
					] ),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_code, __( 'Field', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'SUBVALUE', __( 'Value', 'uncanny-automator-pro' ) ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $entry_id
	 * @param $form_id
	 */
	public function fi_submit_form( $entry_id, $form_id ) {
		if ( $entry_id && class_exists( '\FrmEntryMeta' ) ) {
			global $uncanny_automator;
			$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
			$metas   = FrmEntryMeta::get_entry_meta_info( $entry_id );

			if ( empty( $metas ) ) {
				return;
			}

			$conditions = $uncanny_automator->helpers->recipe->formidable->pro->match_condition( $metas, $form_id, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE' );

			if ( ! $conditions ) {
				return;
			}

			$user_id = get_current_user_id();
			if ( ! empty( $conditions ) ) {
				foreach ( $conditions['recipe_ids'] as $trigger_id => $recipe_id ) {
					if ( ! $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {
						$args = [
							'code'             => $this->trigger_code,
							'meta'             => $this->trigger_meta,
							'recipe_to_match'  => $recipe_id,
							'trigger_to_match' => $trigger_id,
							'ignore_post_id'   => true,
							'user_id'          => $user_id,
						];

						$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );

						if ( $result ) {
							foreach ( $result as $r ) {
								if ( true === $r['result'] ) {
									if ( isset( $r['args'] ) && isset( $r['args']['get_trigger_id'] ) ) {
										//Saving form values in trigger log meta for token parsing!
										$fi_args = [
											'trigger_id'     => (int) $r['args']['trigger_id'],
											'meta_key'       => $this->trigger_meta,
											'user_id'        => $user_id,
											'trigger_log_id' => $r['args']['get_trigger_id'],
											'run_number'     => $r['args']['run_number'],
										];

										$uncanny_automator->helpers->recipe->formidable->pro->extract_save_fi_fields( $entry_id, $form_id, $fi_args );
									}
									$uncanny_automator->maybe_trigger_complete( $r['args'] );
								}
							}
						}
					}
				}
			}
		}
	}

}
