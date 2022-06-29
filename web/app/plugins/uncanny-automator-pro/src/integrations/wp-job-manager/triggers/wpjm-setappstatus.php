<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPJM_SETAPPSTATUS
 * @package Uncanny_Automator_Pro
 */
class WPJM_SETAPPSTATUS {

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
		$this->trigger_code = 'WPJMAPPLICATIONSTATUS';
		$this->trigger_meta = 'WPJMSETSTATUS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$options                    = $uncanny_automator->helpers->recipe->wp_job_manager->pro->list_wpjm_job_application_statuses( null, $this->trigger_meta );
		$options['relevant_tokens'] = array(
			$this->trigger_meta => __( 'New status', 'uncanny-automator-pro' ),
			'WPMJOLDSTATUS'     => __( 'Old status', 'uncanny-automator-pro' ),
		);

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wp-job-manager/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WP Job Manager */
			'sentence'            => sprintf( esc_attr__( "A user's application is set to {{a specific status:%1\$s}}", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WP Job Manager */
			'select_option_name'  => esc_attr__( "A user's application is set to {{a specific status}}", 'uncanny-automator-pro' ),
			'action'              => 'post_updated',
			'priority'            => 29,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'set_application_status' ),
			'options'             => [
				$options,
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @param $post_id
	 * @param $post_after
	 * @param $post_before
	 */
	public function set_application_status( $post_id, $post_after, $post_before ) {
		global $uncanny_automator;

		$application_id = $post_id;
		$job_id         = $post_after->post_parent;
		$job            = get_post( $job_id );
		if ( empty( $post_id ) ) {
			return;
		}

		if ( 'job_application' !== (string) $post_after->post_type || (string) $post_before->post_status === (string) $post_after->post_status ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( empty( $user_id ) || 0 === $user_id ) {
			return;
		}

		$new_status = $post_after->post_status;
		$old_status = $post_before->post_status;

		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_status    = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = [];

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( (string) $required_status[ $recipe_id ][ $trigger_id ] === (string) $post_after->post_status || intval( '-1' ) === intval( $required_status[ $recipe_id ][ $trigger_id ] ) ) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

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

							$categories = $uncanny_automator->helpers->recipe->wp_job_manager->pro->get_job_categories( $post_id );

							$trigger_meta['meta_key']   = 'WPJMSETSTATUS';
							$trigger_meta['meta_value'] = maybe_serialize( $new_status );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPMJOLDSTATUS';
							$trigger_meta['meta_value'] = maybe_serialize( $old_status );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBTITLE';
							$trigger_meta['meta_value'] = maybe_serialize( wpjm_get_the_job_title( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBLOCATION';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_job_location( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBDESCRIPTION';
							$trigger_meta['meta_value'] = maybe_serialize( wpjm_get_the_job_description( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$method = get_the_job_application_method( $job );
							if ( ! empty( $method ) ) {
								if ( 'email' === $method->type ) {
									$method = $method->email;
								} elseif ( 'url' === $method->type ) {
									$method = $method->url;
								}
							}

							$trigger_meta['meta_key']   = 'WPJMJOBAPPURL';
							$trigger_meta['meta_value'] = maybe_serialize( $method );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBCOMPANYNAME';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_company_name( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBWEBSITE';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_company_website( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBTAGLINE';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_company_tagline( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBVIDEO';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_company_video( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBTWITTER';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_company_twitter( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBLOGOURL';
							$trigger_meta['meta_value'] = maybe_serialize( get_the_company_logo( $job ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$job_types = wpjm_get_the_job_types( $job_id );
							$types     = array();
							if ( ! empty( $job_types ) ) {
								foreach ( $job_types as $type ) {
									$types[] = $type->name;
								}
							}

							$trigger_meta['meta_key']   = 'WPJMJOBTYPE';
							$trigger_meta['meta_value'] = maybe_serialize( implode( ',', $types ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$author          = get_post_field( 'post_author', $job );
							$author_username = get_the_author_meta( 'user_login', $author );
							$author_fname    = get_the_author_meta( 'first_name', $author );
							$author_lname    = get_the_author_meta( 'last_name', $author );
							$author_email    = get_the_author_meta( 'user_email', $author );

							$trigger_meta['meta_key']   = 'WPJMJOBOWNERNAME';
							$trigger_meta['meta_value'] = maybe_serialize( $author_username );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBOWNEREMAIL';
							$trigger_meta['meta_value'] = maybe_serialize( $author_email );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBOWNERFIRSTNAME';
							$trigger_meta['meta_value'] = maybe_serialize( $author_fname );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMJOBOWNERLASTNAME';
							$trigger_meta['meta_value'] = maybe_serialize( $author_lname );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMAPPLICATIONNAME';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_title );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPJMAPPLICATIONMESSAGE';
							$trigger_meta['meta_value'] = maybe_serialize( $post_after->post_content );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$attachment = '';
							$attachment = get_post_meta( $post_after->ID, '_attachment', true );

							if ( ! empty( $attachment ) ) {
								$attachment = maybe_unserialize( $attachment );
							}

							$trigger_meta['meta_key']   = 'WPJMAPPLICATIONCV';
							$trigger_meta['meta_value'] = maybe_serialize( $attachment );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$candidate_email = get_post_meta( $post_id, '_candidate_email', true );
							if ( empty( $candidate_email ) ) {
								$author = get_user_by( 'ID', $post_after->post_author );
								if ( $author instanceof \WP_User ) {
									$candidate_email = $author->user_email;
								}
							}

							$trigger_meta['meta_key']   = 'WPJMAPPLICATIONEMAIL';
							$trigger_meta['meta_value'] = maybe_serialize( $candidate_email );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}

}
