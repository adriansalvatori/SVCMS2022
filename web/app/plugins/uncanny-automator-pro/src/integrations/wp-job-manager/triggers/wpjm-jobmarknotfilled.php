<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPJM_JOBMARKFILLED
 * @package Uncanny_Automator_Pro
 */
class WPJM_JOBMARKNOTFILLED {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPJM';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WPJMJOBMARKNOTFILLED';
		$this->trigger_meta = 'WPJMJOBTYPE';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wp-job-manager/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WP Job Manager */
			'sentence'            => sprintf( esc_attr__( 'A user marks a {{specific type of:%1$s}} job as not filled', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WP Job Manager */
			'select_option_name'  => esc_attr__( 'A user marks a {{specific type of}} job as not filled', 'uncanny-automator-pro' ),
			'action'              => 'job_manager_my_job_do_action',
			'priority'            => 20,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'job_manager_my_job_do_action' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->wp_job_manager->options->list_wpjm_job_types(),
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @param $action
	 * @param $job_id
	 */
	public function job_manager_my_job_do_action( $action, $job_id ) {

		global $uncanny_automator;

		if ( 'mark_not_filled' !== $action ) {
			return;
		}

		if ( empty( $job_id ) ) {
			return;
		}

		$job_terms = wpjm_get_the_job_types( $job_id );

		$recipes    = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$conditions = $this->match_condition( $job_terms, $recipes, $this->trigger_meta, $this->trigger_code );

		if ( empty( $conditions ) ) {
			return;
		}
		$user_id = get_current_user_id();

		foreach ( $conditions['recipe_ids'] as $recipe_id ) {
			if ( ! $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {
				$trigger_args = array(
					'code'            => $this->trigger_code,
					'meta'            => $this->trigger_meta,
					'recipe_to_match' => $recipe_id,
					'ignore_post_id'  => true,
					'user_id'         => $user_id,
				);

				$args = $uncanny_automator->maybe_add_trigger_entry( $trigger_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = [
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];
							
							// Get the job categories.
							$categories = $uncanny_automator->helpers->recipe->wp_job_manager->pro->get_job_categories( $job_id );
							
							// Insert categories as meta.
							if ( ! empty( $categories ) ) {
								$trigger_meta['meta_key']   = 'WPJMJOBCATEGORIES';
								$trigger_meta['meta_value'] = implode(', ',$categories );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );
							}

							$trigger_meta['meta_key']   = $this->trigger_code;
							$trigger_meta['meta_value'] = $job_id;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMSUBMITJOB';
							$trigger_meta['meta_value'] = $job_id;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$entry_terms = [];
							if ( ! empty( $job_terms ) ) {
								foreach ( $job_terms as $term ) {
									$entry_terms[] = esc_html( $term->name );
								}
							}
							$value                      = implode( ', ', $entry_terms );
							$trigger_meta['meta_key']   = $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = $value;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );
							$uncanny_automator->maybe_trigger_complete( $result['args'] );
							break;
						}
					}
				}
			}
		}
	}

	/**
	 * @param      $terms
	 * @param null $recipes
	 * @param null $trigger_meta
	 * @param null $trigger_code
	 *
	 * @return array|bool
	 */
	public function match_condition( $terms, $recipes = null, $trigger_meta = null, $trigger_code = null ) {

		if ( null === $recipes ) {
			return false;
		}

		$recipe_ids     = array();
		$entry_to_match = [];
		if ( empty( $terms ) ) {
			return false;
		}
		foreach ( $terms as $term ) {
			$entry_to_match[] = $term->term_id;
		}

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && ( in_array( (int) $trigger['meta'][ $trigger_meta ], $entry_to_match, true ) || $trigger['meta'][ $trigger_meta ] === "-1" ) ) {
					$recipe_ids[ $recipe['ID'] ] = $recipe['ID'];
					break;
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return array(
				'recipe_ids' => $recipe_ids,
				'result'     => true,
			);
		}

		return false;
	}
}
