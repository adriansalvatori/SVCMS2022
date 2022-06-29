<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MEMBER_OF_GROUP
 *
 * @package Uncanny_Automator_Pro
 */
class LD_USER_IS_GROUP_MEMBER extends Action_Condition {

	/**
	 * Define_condition
	 *
	 * @return void
	 */
	public function define_condition() {

		$this->integration = 'LD';
		/*translators: Token */
		$this->name = __( 'The user is a member of {{a group}}', 'uncanny-automator-pro' );
		$this->code = 'MEMBER_OF_GROUP';
		// translators: A token matches a value
		$this->dynamic_name  = sprintf( esc_html__( 'The user is a member of {{a group:%1$s}}', 'uncanny-automator-pro' ), 'GROUP' );
		$this->is_pro        = true;
		$this->requires_user = true;
	}

	/**
	 * Fields
	 *
	 * @return array
	 */
	public function fields() {

		$groups_field_args = array(
			'option_code'           => 'GROUP',
			'label'                 => esc_html__( 'Select a group', 'uncanny-automator-pro' ),
			'required'              => true,
			'options'               => $this->ld_groups_options(),
			'supports_custom_value' => false,
		);

		return array(
			// Course field
			$this->field->select_field_args( $groups_field_args ),
		);
	}

	/**
	 * Load options
	 *
	 * @return array[]
	 */
	public function ld_groups_options() {
		$args      = array(
			'post_type'      => 'groups',
			'posts_per_page' => 9999, //phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);
		$ld_groups = array();
		$groups    = Automator()->helpers->recipe->options->wp_query( $args, false, false );
		if ( empty( $groups ) ) {
			return array();
		}
		foreach ( $groups as $group_id => $group_title ) {
			$ld_groups[] = array(
				'value' => $group_id,
				'text'  => $group_title,
			);
		}

		return $ld_groups;
	}

	/**
	 * Evaluate_condition
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function evaluate_condition() {

		$user_groups = learndash_get_users_group_ids( $this->user_id, true );
		if ( empty( $user_groups ) ) {
			$message = __( 'User is not a member of any group.', 'uncanny-automator-pro' );
			$this->condition_failed( $message );
		} else {
			$parsed_group  = $this->get_parsed_option( 'GROUP' );
			$user_in_group = array_intersect( $user_groups, array( $parsed_group ) );

			// Check if the user is enrolled in the group here
			if ( empty( $user_in_group ) ) {

				$message = __( 'User is not a member of ', 'uncanny-automator-pro' ) . $this->get_option( 'GROUP_readable' );
				$this->condition_failed( $message );
			}
		}
	}
}
