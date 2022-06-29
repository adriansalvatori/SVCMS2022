<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

namespace Uncanny_Automator_Pro;

/**
 * Class ACF_POST_FIELD_UPDATED
 *
 * @package Uncanny_Automator_Pro
 */
class ACF_POST_FIELD_UPDATED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'ACF';

	/**
	 * Trigger code.
	 *
	 * @var string
	 */
	private $trigger_code;

	/**
	 * Trigger meta.
	 *
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {

		$this->trigger_code = 'ACF_POST_FIELD_UPDATED';
		$this->trigger_meta = 'ACF_POST_FIELD_UPDATED_META';

		$acf_helper = Acf_Helpers_Pro::get_instance();

		if ( ! $acf_helper ) {
			return;
		}

		// Bailout if helper is not registered.
		if ( empty( $acf_helper ) ) {
			return;
		}

		if ( Automator()->helpers->recipe->is_edit_page() ) {
			add_action( 'wp_loaded', array( $this, 'define_trigger' ), 9999 );
		} else {
			$this->define_trigger();
		}

	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'type'                => 'anonymous',
			'sentence'            => sprintf(
			/* translators: ACF Field trigger */
				__( '{{A field:%1$s}} is updated on {{a post:%2$s}}', 'uncanny-automator-pro' ),
				'ACF_FIELD_LIST:' . $this->trigger_meta,
				'ACF_WP_POSTS_OBJECT_LIST:' . $this->trigger_meta
			),
			/* translators: ACF Field trigger */
			'select_option_name'  => __( '{{A field}} is updated on {{a post}}', 'uncanny-automator-pro' ),
			'action'              => 'updated_post_meta',
			'priority'            => 99,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'post_field_updated' ),
			'supports_tokens'     => false,
			'options_group'       => array(
				$this->trigger_meta => array(
					array(
						'input_type'      => 'select',
						'option_code'     => 'ACF_WP_POSTS_LIST',
						'options'         => $this->acf_get_post_types(),
						'required'        => true,
						'label'           => esc_html__( 'Post type', 'uncanny-automator-pro' ),
						'is_ajax'         => true,
						'endpoint'        => 'uo_automator_acf_get_post_under_post_type',
						'fill_values_in'  => 'ACF_WP_POSTS_OBJECT_LIST',
						'supports_tokens' => false,
						'relevant_tokens' => array(),
					),
					array(
						'input_type'      => 'select',
						'option_code'     => 'ACF_WP_POSTS_OBJECT_LIST',
						'options'         => array(),
						'required'        => true,
						'label'           => esc_html__( 'Post', 'uncanny-automator-pro' ),
						'is_ajax'         => true,
						'endpoint'        => 'uo_automator_acf_get_fields',
						'fill_values_in'  => 'ACF_FIELD_LIST',
						'supports_tokens' => false,
						'relevant_tokens' => array(),
					),
					array(
						'input_type'      => 'select',
						'option_code'     => 'ACF_FIELD_LIST',
						'required'        => true,
						'label'           => esc_html__( 'ACF field', 'uncanny-automator-pro' ),
						'relevant_tokens' => array(
							'ACF_TRIGGER_FIELD_NAME' => esc_html__( 'ACF field name', 'uncanny-automator-pro' ),
							'ACF_TRIGGER_FIELD'      => esc_html__( 'ACF field value', 'uncanny-automator-pro' ),
							'ACF_TRIGGER_POST_ID'    => esc_html__( 'Post ID', 'uncanny-automator-pro' ),
							'ACF_TRIGGER_POST_TITLE' => esc_html__( 'Post title', 'uncanny-automator-pro' ),
							'ACF_TRIGGER_POST_TYPE'  => esc_html__( 'Post type', 'uncanny-automator-pro' ),
							'ACF_TRIGGER_POST_URL'   => esc_html__( 'Post URL', 'uncanny-automator-pro' ),
						),
					),
				),
			),
		);

		$uncanny_automator->register->trigger( $trigger );

	}

	/**
	 * Get ACF post types.
	 *
	 * @return array
	 */
	public function acf_get_post_types() {

		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$output   = 'objects'; // names or objects, note names is the default
		$operator = 'or';

		$post_types = get_post_types( $args, $output, $operator );

		$delivered_post_types = array();

		$disallowed_post_types = $this->get_disallowed_post_types();

		foreach ( $post_types as $key => $post_type ) {
			if ( ! in_array( $key, $disallowed_post_types, true ) ) {
				$delivered_post_types[ $key ] = $post_type->label;
			}
		}

		return $delivered_post_types;

	}

	/**
	 * Get disallowed post types.
	 *
	 * @return mixed|void
	 */
	public function get_disallowed_post_types() {

		$disallowed_post_types = array(
			'attachment',
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'user_request',
			'wp_block',
			'wp_template',
		);

		return apply_filters( 'uo_automator_acf_disallowed_post_types', $disallowed_post_types );

	}

	/**
	 * Validation function when the trigger action is hit
	 */
	public function post_field_updated( $meta_id, $post_id, $meta_key, $meta_value ) {

		$post_acf_fields_keys = array();
		$post_acf_fields      = get_fields( $post_id );

		if ( is_array( $post_acf_fields ) && ! empty( $post_acf_fields ) ) {
			$post_acf_fields_keys = array_keys( $post_acf_fields );
		}

		// Bail out if the user us just editing normal stuff and not acf meta keys.
		if ( ! in_array( $meta_key, $post_acf_fields_keys, true ) ) {
			return;
		}

		$trigger_meta_field = 'ACF_FIELD_LIST';

		$matched_recipe_ids = array();

		$recipes = Automator()->get->recipes_from_trigger_code( $this->trigger_code );

		$trigger_meta = Automator()->get->meta_from_recipes( $recipes, $trigger_meta_field );

		$post_type_meta = Automator()->get->meta_from_recipes( $recipes, 'ACF_WP_POSTS_LIST' );

		foreach ( $recipes as $recipe_id => $recipe ) {

			foreach ( $recipe['triggers'] as $trigger ) {

				$trigger_id = $trigger['ID'];
				if ( ! isset( $trigger_meta[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $trigger_meta[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}
				$field = $trigger_meta[ $recipe_id ][ $trigger_id ];

				// Check to see if trigger matches `Any` trigger.
				if ( intval( '-1' ) === intval( $field ) || $meta_key === $field ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}
		if ( empty( $matched_recipe_ids ) ) {
			return;
		}

		foreach ( $matched_recipe_ids as $matched_recipe_id ) {

			$args = array(
				'code'             => $this->trigger_code,
				'meta'             => $trigger_meta_field,
				'user_id'          => get_current_user_id(),
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
				'ignore_post_id'   => true,
			);

			$args = Automator()->maybe_add_trigger_entry( $args, false );

			// Save trigger meta
			if ( $args ) {

				foreach ( $args as $result ) {

					if ( true === $result['result'] ) {

						// Get ACF field object.
						$field_object = get_field_object( $meta_key );

						// Added support for gallery type.
						if ( 'gallery' === $field_object['type'] ) {
							$images = array();
							if ( ! empty( $meta_value ) && is_array( $meta_value ) ) {
								foreach ( $meta_value as $image_id ) {
									$images[] = wp_get_attachment_url( $image_id );
								}
							}
							// Assign image array as meta value.
							$meta_value = $images;
						}

						// Added support for image type.
						if ( 'image' === $field_object['type'] ) {
							$meta_value = wp_get_attachment_url( $meta_value );
						}

						// Check if meta value is array convert to string separated by comma.
						if ( is_array( $meta_value ) ) {
							$meta_value = apply_filters( 'ua_acf_field_meta_value', implode( ', ', $meta_value ) );
						}

						// Added support for true or false.
						if ( 'true_false' === $field_object['type'] ) {
							$values     = array( 'False', 'True' );
							$meta_value = $values[ $meta_value ];
						}

						// Field value.
						$acf_field_meta = array(
							'user_id'        => get_current_user_id(),
							'trigger_id'     => $result['args']['trigger_id'],
							'run_number'     => $result['args']['run_number'], //get run number
							'trigger_log_id' => $result['args']['trigger_log_id'],
							'meta_key'       => 'ACF_FIELD_META_VALUE',
							'meta_value'     => $meta_value,
						);

						// Meta key.
						$acf_field_meta_key = array(
							'user_id'        => get_current_user_id(),
							'trigger_id'     => $result['args']['trigger_id'],
							'run_number'     => $result['args']['run_number'], //get run number
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'meta_key'       => 'ACF_FIELD_META_KEY',
							'meta_value'     => $meta_key,
						);

						// Post type name.
						$post_type = array(
							'user_id'        => get_current_user_id(),
							'trigger_id'     => $result['args']['trigger_id'],
							'run_number'     => $result['args']['run_number'], //get run number
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'meta_key'       => 'ACF_POST_TYPE_NAME',
							'meta_value'     => $post_type_meta[ $matched_recipe_id['recipe_id'] ][ $matched_recipe_id['trigger_id'] ],
						);

						// Post Id.
						$post_id = array(
							'user_id'        => get_current_user_id(),
							'trigger_id'     => $result['args']['trigger_id'],
							'run_number'     => $result['args']['run_number'], //get run number
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'meta_key'       => 'ACF_POST_ID',
							'meta_value'     => absint( $post_id ),
						);

						// Insert ACF field meta.
						Automator()->insert_trigger_meta( $acf_field_meta );

						// Insert ACF field meta name.
						Automator()->insert_trigger_meta( $acf_field_meta_key );

						// Insert post type.
						Automator()->insert_trigger_meta( $post_type );

						// Insert the post id.
						Automator()->insert_trigger_meta( $post_id );

						// Complete the trigger.
						Automator()->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}
}
