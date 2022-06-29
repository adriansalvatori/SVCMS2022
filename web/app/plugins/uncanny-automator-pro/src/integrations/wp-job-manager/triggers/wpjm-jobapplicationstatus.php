<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPJM_JOBAPPLICATIONSTATUS
 * @package Uncanny_Automator_Pro
 */
class WPJM_JOBAPPLICATIONSTATUS {

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
		$this->trigger_code = 'WPJMJOBAPPLICATIONSTATUS';
		$this->trigger_meta = 'WPJMAPPSTATUS';
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
			'sentence'            => sprintf( esc_attr__( "A user's application to a {{specific type of:%1\$s}} job is set to {{a specific status:%2\$s}}", 'uncanny-automator-pro' ), 'WPJMJOBTYPE', $this->trigger_meta ),
			/* translators: Logged-in trigger - WP Job Manager */
			'select_option_name'  => esc_attr__( "A user's application to a {{specific type of}} job is set to {{a specific status}}", 'uncanny-automator-pro' ),
			'action'              => 'post_updated',
			'priority'            => 29,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'new_job_application_updated' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->wp_job_manager->options->list_wpjm_job_types(),
				$uncanny_automator->helpers->recipe->wp_job_manager->pro->list_wpjm_job_application_statuses(),
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @param $post_id
	 * @param $post_after
	 * @param $post_before
	 */
	public function new_job_application_updated( $post_id, $post_after, $post_before ) {

		global $uncanny_automator;
		$post = get_post( $post_id );
		if ( 'job_application' !== (string) $post->post_type ) {
			return;
		}

		$job_id = $post->post_parent;
		if ( empty( $job_id ) ) {
			return;
		}
		$new_status = $post_after->post_status;
		$old_status = $post_before->post_status;
		$job_terms  = wpjm_get_the_job_types( $job_id );

		$recipes    = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$conditions = $this->match_condition( $job_terms, $recipes, $this->trigger_meta, $this->trigger_code, $new_status );

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

							$trigger_meta['meta_key']   = 'WPJMSUBMITJOB';
							$trigger_meta['meta_value'] = $job_id;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBAPPLICATIONID';
							$trigger_meta['meta_value'] = $post->ID;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$entry_terms = [];
							if ( ! empty( $job_terms ) ) {
								foreach ( $job_terms as $term ) {
									$entry_terms[] = esc_html( $term->name );
								}
							}
							$value                      = implode( ', ', $entry_terms );
							$trigger_meta['meta_key']   = $this->trigger_code . ':WPJMJOBTYPE';
							$trigger_meta['meta_value'] = $value;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );
							// Post Status Token
							$app_status                 = get_job_application_statuses();
							$trigger_meta['meta_key']   = $this->trigger_code . ':WPJMAPPSTATUS';
							$trigger_meta['meta_value'] = maybe_serialize( $app_status[ $new_status ] );
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
	 * @param string $new_status
	 *
	 * @return array|bool
	 */
	public function match_condition( $terms, $recipes = null, $trigger_meta = null, $trigger_code = null, $new_status = '' ) {

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
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && ( in_array( (int) $trigger['meta']['WPJMJOBTYPE'], $entry_to_match, true ) || $trigger['meta']['WPJMJOBTYPE'] === "-1" ) && ( (string) $trigger['meta'][ $trigger_meta ] === $new_status || $trigger['meta'][ $trigger_meta ] === "-1" ) ) {
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
