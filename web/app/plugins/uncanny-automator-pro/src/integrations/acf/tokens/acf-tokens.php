<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
namespace Uncanny_Automator_Pro;

/**
 * Acf_Tokens
 *
 * @package Uncanny_Automator
 */
class Acf_Tokens {

	/**
	 * Load options.
	 *
	 * @var bool
	 */
	public $load_options;

	public function __construct() {

		$this->load_options = Automator()->helpers->recipe->maybe_load_trigger_options( __CLASS__ );

		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_tokens' ), 36, 6 );

		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_tokens_user_form' ), 20, 6 );

		add_action( 'automator_before_trigger_completed', array( $this, 'save_token_data_user_form' ), 20, 2 );

		add_filter(
			'automator_maybe_trigger_acf_acf_user_field_updated_tokens',
			array(
				$this,
				'register_trigger_user_form_tokens',
			),
			20,
			2
		);

	}

	/**
	 * Register user form tokens.
	 *
	 * @param $tokens
	 * @param $args
	 *
	 * @return mixed
	 */
	public function register_trigger_user_form_tokens( $tokens, $args ) {

		if ( ! automator_do_identify_tokens() ) {
			return $tokens;
		}

		$user_form_tokens = array(
			esc_html__( 'ACF field name', 'uncanny-automator-pro' ),
			esc_html__( 'ACF field value', 'uncanny-automator-pro' ),
			esc_html__( "Updated user's biographical info", 'uncanny-automator-pro' ),
			esc_html__( "Updated user's display name", 'uncanny-automator-pro' ),
			esc_html__( "Updated user's email", 'uncanny-automator-pro' ),
			esc_html__( "Updated user's first name", 'uncanny-automator-pro' ),
			esc_html__( "Updated user's ID", 'uncanny-automator-pro' ),
			esc_html__( "Updated user's last name", 'uncanny-automator-pro' ),
			esc_html__( "Updated user's nickname", 'uncanny-automator-pro' ),
			esc_html__( "Updated user's username", 'uncanny-automator-pro' ),
		);

		foreach ( $user_form_tokens as $user_form_token ) {

			$id = strtolower( str_replace( ' ', '_', $user_form_token ) );

			$tokens[] = array(
				'tokenId'         => str_replace( array( '&#039;', "'", '"', '&quot;' ), '', $id ),
				'tokenName'       => $user_form_token,
				'tokenType'       => 'user_email' === $id ? 'email' : 'text',
				'tokenIdentifier' => str_replace( array( '&#039;', "'", '"', '&quot;' ), '', 'ACF_USER_FIELD_' . $id ),
			);
		}

		return $tokens;

	}

	/**
	 * Save the user form token data.
	 *
	 * @param $args
	 * @param $trigger
	 *
	 * @return void
	 */
	public function save_token_data_user_form( $args, $trigger ) {

		if ( ! isset( $args['trigger_args'] ) || ! isset( $args['entry_args']['code'] ) ) {

			return;

		}

		$triggers = array( 'ACF_USER_FIELD_UPDATED' );

		if ( in_array( $args['entry_args']['code'], $triggers, true ) ) {

			$user_id = absint( $args['trigger_args'][1] );

			$meta = array(
				'acf'  => array(
					'field_name'  => $args['trigger_args'][2],
					'field_value' => $args['trigger_args'][3],
				),
				'user' => get_userdata( $user_id ),
			);

			if ( false !== $meta['user'] ) {
				$meta['user']->data->acf_field_name  = $args['trigger_args'][2];
				$meta['user']->data->acf_field_value = $args['trigger_args'][3];
				$meta['user']->data->description     = get_user_meta( $user_id, 'description', true );
				$meta['user']->data->first_name      = get_user_meta( $user_id, 'first_name', true );
				$meta['user']->data->last_name       = get_user_meta( $user_id, 'last_name', true );
			}

			Automator()->db->token->save( 'ACF_USER_FIELD_UPDATED', wp_json_encode( $meta ), $args['trigger_entry'] );

		}

	}

	/**
	 * Parse tokens user form.
	 *
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed
	 */
	public function parse_tokens_user_form( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		$trigger_code = '';

		$triggers = array( 'ACF_USER_FIELD_UPDATED' );

		if ( isset( $trigger_data[0]['meta']['code'] ) ) {
			$trigger_code = $trigger_data[0]['meta']['code'];
		}

		if ( empty( $trigger_code ) || ! in_array( $trigger_code, $triggers, true ) ) {
			return $value;
		}

		if ( ! is_array( $pieces ) || ! isset( $pieces[1] ) || ! isset( $pieces[2] ) ) {
			return $value;
		}

		$user_data = json_decode( Automator()->db->token->get( 'ACF_USER_FIELD_UPDATED', $replace_args ), true );

		$adapter = array(
			'acf_field_name'                  => 'acf_field_name',
			'acf_field_value'                 => 'acf_field_value',
			'updated_users_biographical_info' => 'description',
			'updated_users_display_name'      => 'display_name',
			'updated_users_email'             => 'user_email',
			'updated_users_first_name'        => 'first_name',
			'updated_users_id'                => 'ID',
			'updated_users_last_name'         => 'last_name',
			'updated_users_nickname'          => 'user_nicename',
			'updated_users_username'          => 'user_login',
		);

		if ( isset( $user_data['user']['data'][ $adapter[ $pieces[2] ] ] ) ) {

			$value = $user_data['user']['data'][ $adapter[ $pieces[2] ] ];

		}

		return $value;

	}

	/**
	 * Process the tokens.
	 *
	 * @param mixed $value The value accepted from `automator_maybe_parse_token`.
	 * @param mixed $pieces The pieces accepted from `automator_maybe_parse_token`.
	 * @param mixed $recipe_id The recipe id accepted from `automator_maybe_parse_token`.
	 * @param mixed $trigger_data The trigger data accepted from `automator_maybe_parse_token`.
	 * @param mixed $user_id The user id accepted from `automator_maybe_parse_token`.
	 * @param mixed $replace_args The arguments accepted from `automator_maybe_parse_token`.
	 *
	 * @return mixed The token value to display.
	 */
	public function parse_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		$to_match = array(
			'ACF_TRIGGER_FIELD',
			'ACF_TRIGGER_POST_TYPE',
			'ACF_TRIGGER_POST_ID',
			'ACF_TRIGGER_POST_URL',
			'ACF_TRIGGER_POST_TITLE',
			'ACF_TRIGGER_FIELD_NAME',
		);

		if ( $pieces ) {

			if ( array_intersect( $to_match, $pieces ) ) {

				$value = $this->replace_values( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args );

			}
		}

		return $value;

	}

	/**
	 * Replaces the token values.
	 *
	 * @return mixed The value.
	 */
	public function replace_values( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		$trigger_meta = $pieces[1];
		$parse        = $pieces[2];

		$recipe_log_id = isset( $replace_args['recipe_log_id'] ) ? (int) $replace_args['recipe_log_id'] : Automator()->maybe_create_recipe_log_entry( $recipe_id, $user_id )['recipe_log_id'];

		if ( ! $trigger_data || ! $recipe_log_id ) {
			return $value;
		}

		$acf_field_value = $this->get_meta_value_from_trigger_log_meta(
			$user_id,
			'ACF_FIELD_META_VALUE',
			$replace_args['trigger_id'],
			$replace_args['trigger_log_id']
		);

		$acf_field_key = $this->get_meta_value_from_trigger_log_meta(
			$user_id,
			'ACF_FIELD_META_KEY',
			$replace_args['trigger_id'],
			$replace_args['trigger_log_id']
		);

		$post_type_value = $this->get_meta_value_from_trigger_log_meta(
			$user_id,
			'ACF_POST_TYPE_NAME',
			$replace_args['trigger_id'],
			$replace_args['trigger_log_id']
		);

		$post_id = $this->get_meta_value_from_trigger_log_meta(
			$user_id,
			'ACF_POST_ID',
			$replace_args['trigger_id'],
			$replace_args['trigger_log_id']
		);

		foreach ( $trigger_data as $trigger ) {

			if ( ! isset( $trigger['meta'] ) ) {
				continue;
			}

			if ( ! key_exists( $trigger_meta, $trigger['meta'] ) && ( ! isset( $trigger['meta']['code'] ) && $trigger_meta !== $trigger['meta']['code'] ) ) {
				continue;
			}

			$value = '';

			switch ( $parse ) {
				case 'ACF_TRIGGER_FIELD':
					$value = $acf_field_value;
					break;
				case 'ACF_TRIGGER_POST_TYPE':
					$value = $post_type_value;
					break;
				case 'ACF_TRIGGER_POST_ID':
					$value = absint( $post_id );
					break;
				case 'ACF_TRIGGER_POST_URL':
					$value = esc_url( get_permalink( $post_id ) );
					break;
				case 'ACF_TRIGGER_POST_TITLE':
					$value = esc_html( get_the_title( $post_id ) );
					break;
				case 'ACF_TRIGGER_FIELD_NAME':
					$value = $acf_field_key;
					break;
			}
		}

		return $value;

	}

	/**
	 * Get the meta value from the trigger log table.
	 *
	 * @param mixed $user_id The user id.
	 * @param mixed $meta_key The meta key.
	 * @param mixed $trigger_id The trigger id.
	 * @param mixed $trigger_log_id The trigger log id.
	 *
	 * @return mixed The meta value.
	 */
	public function get_meta_value_from_trigger_log_meta( $user_id, $meta_key, $trigger_id, $trigger_log_id ) {

		global $wpdb;

		if ( empty( $meta_key ) || empty( $trigger_id ) || empty( $trigger_log_id ) ) {
			return '';
		}

		$meta_value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value
				FROM {$wpdb->prefix}uap_trigger_log_meta
				WHERE user_id = %d
				AND meta_key = %s
				AND automator_trigger_id = %d
				AND automator_trigger_log_id = %d
				ORDER BY ID DESC LIMIT 0,1",
				$user_id,
				$meta_key,
				$trigger_id,
				$trigger_log_id
			)
		);

		if ( ! empty( $meta_value ) ) {
			return maybe_unserialize( $meta_value );
		}

		return '';
	}

}
