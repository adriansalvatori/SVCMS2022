<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Ld_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Ld_Pro_Tokens {

	/**
	 *
	 */
	public function __construct() {
		add_filter(
			'automator_maybe_trigger_ld_ld_usercompletesgroupscourse_tokens',
			array(
				$this,
				'group_course_possible_tokens',
			),
			9999,
			2
		);
		add_filter( 'automator_maybe_parse_token', array( $this, 'ld_tokens' ), 20, 6 );
	}

	/**
	 * @param $tokens
	 * @param $args
	 *
	 * @return array|array[]
	 */
	public function group_course_possible_tokens( $tokens = array(), $args = array() ) {
		$trigger_meta = $args['meta'];

		$fields = array(
			array(
				'tokenId'         => 'GROUP_COURSES',
				'tokenName'       => __( 'Group courses', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
		);

		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return false|int|mixed|string|\WP_Error
	 */
	public function ld_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( empty( $pieces ) ) {
			return $value;
		}
		if ( ! in_array( 'LD_USERCOMPLETESGROUPSCOURSE', $pieces, true ) ) {
			return $value;
		}
		if ( empty( $trigger_data ) ) {
			return $value;
		}
		foreach ( $trigger_data as $trigger ) {
			if ( empty( $trigger ) ) {
				continue;
			}
			$trigger_id     = $trigger['ID'];
			$trigger_log_id = $replace_args['trigger_log_id'];
			$meta_key       = 'COURSEINGROUP';
			$group_id       = Automator()->helpers->recipe->get_form_data_from_trigger_meta( $meta_key, $trigger_id, $trigger_log_id, $user_id );
			if ( ! empty( $group_id ) ) {
				if ( 'LDGROUPCOURSES_ID' === $pieces[2] ) {
					$value = $group_id;
				} elseif ( 'LDCOURSE' === $pieces[2] ) {
					$value = get_the_title( $group_id );
				} elseif ( 'LDGROUPCOURSES_URL' === $pieces[2] ) {
					$value = get_permalink( $group_id );
				} elseif ( 'LDGROUPCOURSES_THUMB_ID' === $pieces[2] ) {
					$value = get_post_thumbnail_id( $group_id );
				} elseif ( 'LDGROUPCOURSES_THUMB_URL' === $pieces[2] ) {
					$value = get_the_post_thumbnail_url( $group_id );
				} elseif ( 'GROUP_COURSES' === $pieces[2] ) {
					$courses                          = array();
					$learndash_group_enrolled_courses = learndash_group_enrolled_courses( $group_id );
					foreach ( $learndash_group_enrolled_courses as $course_id ) {
						$courses[] = get_the_title( $course_id );
					}
					$value = join( ', ', $courses );
				}
			}
		}

		return $value;
	}
}
