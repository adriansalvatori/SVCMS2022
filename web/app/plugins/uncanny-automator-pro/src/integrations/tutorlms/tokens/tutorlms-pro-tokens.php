<?php

namespace Uncanny_Automator_Pro;

/**
 * Class TutorLMS_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class TutorLMS_Pro_Tokens {

	/**
	 *
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', array( $this, 'tutorlms_tokens' ), 21, 6 );
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param array $replace_args
	 *
	 * @return string|null
	 */
	public function tutorlms_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args = array() ) {

		if ( ! is_array( $trigger_data ) ) {
			return $value;
		}

		if ( $pieces ) {
			if ( in_array( 'QUIZPERCENT', $pieces, true )
				 || in_array( 'NUMBERCOND', $pieces, true )
			) {

				if ( ! absint( $user_id ) ) {
					return $value;
				}

				if ( ! absint( $recipe_id ) ) {
					return $value;
				}

				// QUIZPERCENT can be found from trigger meta
				if ( in_array( 'QUIZPERCENT', $pieces, true ) ) {
					$t_data = array_shift( $trigger_data );
					if ( isset( $t_data['meta']['QUIZPERCENT'] ) ) {
						return $t_data['meta']['QUIZPERCENT'];
					}

					return $value;
				}

				// NUMBERCOND can be found from trigger meta
				if ( in_array( 'NUMBERCOND', $pieces, true ) ) {
					$t_data = array_shift( $trigger_data );
					if ( isset( $t_data['meta']['NUMBERCOND_readable'] ) ) {
						return $t_data['meta']['NUMBERCOND_readable'];
					}

					return $value;
				}
			} elseif ( in_array( 'TUTORLMSQUESTIONPOSTED', $pieces, true ) ) {
				if ( $trigger_data ) {
					foreach ( $trigger_data as $trigger ) {
						$trigger_id     = $trigger['ID'];
						$trigger_log_id = $replace_args['trigger_log_id'];
						$meta_key       = $pieces[2];
						$meta_value     = Automator()->helpers->recipe->get_form_data_from_trigger_meta( $meta_key, $trigger_id, $trigger_log_id, $user_id );
						if ( $meta_key === 'TUTORLMSCOURSES' ) {
							$meta_value = get_the_title( $meta_value );
						}
						if ( ! empty( $meta_value ) ) {
							$value = maybe_unserialize( $meta_value );
						}
					}
				}
			}
		}

		return $value;
	}
}
