<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Wp_Helpers;

/**
 * Class Wp_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Wp_Pro_Helpers extends Wp_Helpers {

	/**
	 * Wp_Pro_Helpers constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_select_custom_post_by_type_post_meta', array( $this, 'select_custom_post_func' ) );
		add_action( 'wp_ajax_select_all_post_from_SELECTEDPOSTTYPE', array( $this, 'select_posts_by_post_type' ) );
		add_action( 'wp_ajax_select_all_terms_of_SELECTEDTAXONOMY', array( $this, 'select_terms_by_taxonomy' ) );
		add_action( 'wp_ajax_select_all_post_of_selected_post_type', array( $this, 'select_all_posts_by_post_type' ) );
		add_action(
			'wp_ajax_select_post_type_taxonomies_SELECTEDTAXONOMY',
			array(
				$this,
				'endpoint_all_taxonomies_by_post_type',
			)
		);

		add_action(
			'wp_ajax_select_all_fields_of_selected_post',
			array(
				$this,
				'select_all_fields_of_selected_post',
			)
		);
		add_action(
			'wp_ajax_select_all_post_of_selected_post_type_no_all',
			array(
				$this,
				'select_all_posts_by_post_type_no_all',
			)
		);
		add_filter( 'uap_option_wp_user_roles', array( $this, 'add_any_option' ), 99, 3 );
	}

	/**
	 * @param $options
	 *
	 * @return array
	 */
	public function add_any_option( $options ) {
		if ( empty( $options ) ) {
			return $options;
		}

		if ( 'USERCREATEDWITHROLE' !== $options['option_code'] ) {
			return $options;
		}

		$options['options'] = array( '-1' => esc_attr__( 'Any role', 'uncanny-automator-pro' ) ) + $options['options'];

		return $options;
	}

	/**
	 * @param Wp_Pro_Helpers $pro
	 */
	public function setPro( Wp_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}


	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function all_wp_post_types( $label = null, $option_code = 'WPPOSTTYPES', $args = array() ) {

		if ( ! $label ) {
			$label = __( 'Post types', 'uncanny-automator-pro' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$comments     = key_exists( 'comments', $args ) ? $args['comments'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$is_any       = key_exists( 'is_any', $args ) ? $args['is_any'] : true;
		$plural_label = key_exists( 'plural_label', $args ) ? $args['plural_label'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';

		$default_tokens = array(
			$option_code                => __( 'Post title', 'uncanny-automator-pro' ),
			$option_code . '_ID'        => __( 'Post ID', 'uncanny-automator-pro' ),
			$option_code . '_URL'       => __( 'Post URL', 'uncanny-automator-pro' ),
			$option_code . '_THUMB_ID'  => __( 'Post featured image ID', 'uncanny-automator-pro' ),
			$option_code . '_THUMB_URL' => __( 'Post featured image URL', 'uncanny-automator-pro' ),
		);

		$relevant_tokens = key_exists( 'relevant_tokens', $args ) ? $args['relevant_tokens'] : $default_tokens;
		$options         = array();

		if ( $is_any == true ) {
			$options['-1'] = __( 'Any post type', 'uncanny-automator-pro' );
		}

		// now get regular post types.
		$args = array(
			'public'   => true,
			'_builtin' => true,
		);

		$output   = 'object';
		$operator = 'and';

		$post_types = get_post_types( $args, $output, $operator );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				if ( 'attachment' != $post_type->name ) {
					$options[ $post_type->name ] = $plural_label === true ? esc_html( $post_type->labels->name ) : esc_html( $post_type->labels->singular_name );
				}
			}
		}

		// now get regular post types.
		$args = array(
			'public'   => false,
			'_builtin' => true,
		);

		$output   = 'object';
		$operator = 'and';

		$post_types = get_post_types( $args, $output, $operator );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				if ( 'attachment' != $post_type->name ) {
					$options[ $post_type->name ] = $plural_label === true ? esc_html( $post_type->labels->name ) : esc_html( $post_type->labels->singular_name );
				}
			}
		}

		// get all custom post types
		$args = array(
			'public'   => false,
			'_builtin' => false,
		);

		$output   = 'object';
		$operator = 'and';

		$custom_post_types = get_post_types( $args, $output, $operator );

		if ( ! empty( $custom_post_types ) ) {
			foreach ( $custom_post_types as $custom_post_type ) {
				if ( 'attachment' != $custom_post_type->name ) {
					$options[ $custom_post_type->name ] = $plural_label === true ? esc_html( $post_type->labels->name ) : esc_html( $post_type->labels->singular_name );
				}
			}
		}
		// get all custom post types
		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$output   = 'object';
		$operator = 'and';

		$custom_post_types = get_post_types( $args, $output, $operator );

		if ( ! empty( $custom_post_types ) ) {
			foreach ( $custom_post_types as $custom_post_type ) {
				if ( 'attachment' != $custom_post_type->name ) {
					$options[ $custom_post_type->name ] = $plural_label === true ? esc_html( $post_type->labels->name ) : esc_html( $post_type->labels->singular_name );
				}
			}
		}

		// post type supports comments
		if ( $comments ) {
			foreach ( $options as $post_type => $opt ) {
				if ( $post_type != '-1' && ! post_type_supports( $post_type, 'comments' ) ) {
					unset( $options[ $post_type ] );
				}
			}
		}

		$type = 'select';

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'relevant_tokens' => $relevant_tokens,
		);

		return apply_filters( 'uap_option_all_wp_post_types', $option );
	}

	/**
	 * Return all the specific fields of post type in ajax call
	 */
	public function select_posts_by_post_type() {
		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );
		$fields = array();
		if ( isset( $_POST ) && key_exists( 'value', $_POST ) && ! empty( $_POST['value'] ) ) {
			$post_type = sanitize_text_field( $_POST['value'] );

			$args       = array(
				'posts_per_page'   => 999,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'post_type'        => $post_type,
				'post_status'      => 'publish',
				'suppress_filters' => true,
				'fields'           => array( 'ids', 'titles' ),
			);
			$posts_list = $uncanny_automator->helpers->recipe->options->wp_query( $args, false, __( 'Any Post', 'uncanny-automator-pro' ) );

			if ( ! empty( $posts_list ) ) {

				$post_type_label = get_post_type_object( $post_type )->labels->singular_name;

				$fields[] = array(
					'value' => '-1',
					'text'  => sprintf( _x( 'Any %s', 'WordPress post type', 'uncanny-automator-pro' ), strtolower( $post_type_label ) ),
				);
				foreach ( $posts_list as $post_id => $post_title ) {
					// Check if the post title is defined
					$post_title = ! empty( $post_title ) ? $post_title : sprintf( __( 'ID: %1$s (no title)', 'uncanny-automator-pro' ), $post_id );

					$fields[] = array(
						'value' => $post_id,
						'text'  => $post_title,
					);
				}
			} else {
				$post_type_label = 'post';

				if ( $post_type != - 1 ) {
					$post_type_label = get_post_type_object( $post_type )->labels->singular_name;
				}

				$fields[] = array(
					'value' => '-1',
					'text'  => sprintf( _x( 'Any %s', 'WordPress post type', 'uncanny-automator-pro' ), strtolower( $post_type_label ) ),
				);
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * Return all the specific fields of post type in ajax call
	 */
	public function select_all_posts_by_post_type() {
		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );
		$fields = array();
		if ( isset( $_POST ) && key_exists( 'value', $_POST ) && ! empty( $_POST['value'] ) ) {
			$post_type = sanitize_text_field( $_POST['value'] );

			$args       = array(
				'posts_per_page'   => 999,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'post_type'        => $post_type,
				'post_status'      => 'publish',
				'suppress_filters' => true,
				'fields'           => array( 'ids', 'titles' ),
			);
			$posts_list = $uncanny_automator->helpers->recipe->options->wp_query( $args, false, __( 'Any Post', 'uncanny-automator-pro' ) );

			if ( ! empty( $posts_list ) ) {
				$post_type_label = get_post_type_object( $post_type )->labels->name;
				$fields[]        = array(
					'value' => '-1',
					'text'  => sprintf( _x( 'All %s', 'WordPress post type', 'uncanny-automator-pro' ), strtolower( $post_type_label ) ),
				);
				foreach ( $posts_list as $post_id => $post_title ) {
					// Check if the post title is defined
					$post_title = ! empty( $post_title ) ? $post_title : sprintf( __( 'ID: %1$s (no title)', 'uncanny-automator-pro' ), $post_id );

					$fields[] = array(
						'value' => $post_id,
						'text'  => $post_title,
					);
				}
			} else {
				$fields[] = array(
					'value' => '-1',
					'text'  => __( 'All posts', 'uncanny-automator' ),
				);
			}
		}

		echo wp_json_encode( $fields );

		die();
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function all_wp_taxonomy( $label = null, $option_code = 'WPTAXONOMIES', $args = array() ) {

		if ( ! $label ) {
			$label = __( 'Taxonomy', 'uncanny-automator-pro' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$is_any       = key_exists( 'is_any', $args ) ? $args['is_any'] : false;
		$is_all       = key_exists( 'is_all', $args ) ? $args['is_all'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$placeholder  = key_exists( 'placeholder', $args ) ? $args['placeholder'] : '';
		$options      = array();

		if ( $is_any && ! $is_all ) {
			$options['-1'] = __( 'Any taxonomy', 'uncanny-automator-pro' );
		} elseif ( $is_all ) {
			$options['-1'] = __( 'All taxonomies', 'uncanny-automator-pro' );
		}

		// now get regular post types.
		$args = array(
			'public'   => true,
			'_builtin' => true,
		);

		$output   = 'object';
		$operator = 'and';

		$taxonomies = get_taxonomies( $args, $output, $operator );

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$options[ $taxonomy->name ] = esc_html( $taxonomy->labels->singular_name );
			}
		}

		// get all custom post types
		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$output   = 'object';
		$operator = 'and';

		$custom_taxonomies = get_taxonomies( $args, $output, $operator );

		if ( ! empty( $custom_taxonomies ) ) {
			foreach ( $custom_taxonomies as $custom_taxonomy ) {
				$options[ $custom_taxonomy->name ] = esc_html( $custom_taxonomy->labels->singular_name );
			}
		}

		$type = 'select';

		$option = array(
			'placeholder'     => $placeholder,
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code => __( 'Taxonomy', 'uncanny-automator-pro' ),
			),
		);

		return apply_filters( 'uap_option_all_wp_taxonomy', $option );
	}

	/**
	 * Return all the specific fields of post type in ajax call
	 */
	public function select_terms_by_taxonomy() {
		global $uncanny_automator;

		Automator()->utilities->ajax_auth_check();
		$fields   = array();
		$group_id = automator_filter_has_var( 'group_id', INPUT_POST ) ? automator_filter_input( 'group_id', INPUT_POST ) : '';
		if ( ! empty( $group_id ) && 'WPREMOVETAXONOMY' === $group_id ) {
			$fields[] = array(
				'value' => - 1,
				'text'  => __( 'All terms', 'uncanny-automator-pro' ),
			);
		} elseif ( 'WPSETTAXONOMY' === $group_id ) {
			//Nothing here
		} else {
			$fields[] = array(
				'value' => - 1,
				'text'  => __( 'Any term', 'uncanny-automator-pro' ),
			);
		}

		if ( isset( $_POST ) && key_exists( 'value', $_POST ) && ! empty( $_POST['value'] ) ) {
			$taxonomy = sanitize_text_field( $_POST['value'] );

			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					// Check if the post title is defined
					$term_name = ! empty( $term->name ) ? $term->name : sprintf( __( 'ID: %1$s (no title)', 'uncanny-automator-pro' ), $term->term_id );

					$fields[] = array(
						'value' => $term->term_id,
						'text'  => $term_name,
					);
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * Endpoint: Fetch all taxonomies by post type.
	 *
	 * @return string @see wp_json_encode();
	 */
	public function endpoint_all_taxonomies_by_post_type() {

		Automator()->utilities->ajax_auth_check();

		$fields = apply_filters( 'automator_endpoint_all_taxonomies_by_post_type_fields_default', array() );

		if ( ! automator_filter_has_var( 'values', INPUT_POST ) ) {

			echo wp_json_encode( $fields );

			die();

		}

		$request_post_type = isset( $_POST['values']['WPSPOSTTYPES'] ) ? sanitize_text_field( $_POST['values']['WPSPOSTTYPES'] ) : 'post';

		$post_type = get_post_type_object( $request_post_type );

		$fields[] = array(
			'value' => - 1,
			'text'  => __( 'Any taxonomy', 'uncanny-automator-pro' ),
		);

		if ( null !== $post_type ) {

			$taxonomies = get_object_taxonomies( $post_type->name, 'object' );

			if ( ! empty( $taxonomies ) ) {

				foreach ( $taxonomies as $taxonomy ) {

					$fields[] = array(
						'value' => $taxonomy->name,
						'text'  => esc_html( $taxonomy->labels->singular_name ),
					);

				}
			}
		}

		echo wp_json_encode( $fields );

		die();

	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function wp_post_statuses( $label = null, $option_code = 'WPPOSTSTATUSES', $args = array() ) {

		if ( ! $label ) {
			$label = __( 'Status', 'uncanny-automator-pro' );
		}

		$is_any          = key_exists( 'is_any', $args ) ? $args['is_any'] : false;
		$relevant_tokens = key_exists( 'relevant_tokens', $args ) ? $args['relevant_tokens'] : '';
		$options         = array();

		if ( $is_any ) {
			$options['-1'] = __( 'Any status', 'uncanny-automator-pro' );
		}

		if ( empty( $relevant_tokens ) ) {
			$relevant_tokens = array(
				$option_code => __( 'Status', 'uncanny-automator-pro' ),
			);
		}

		$post_statuses = get_post_stati( array(), 'objects' );

		if ( ! empty( $post_statuses ) ) {
			foreach ( $post_statuses as $name => $status ) {
				$options[ $name ] = esc_html( $status->label );
			}
		}

		if ( class_exists( 'EF_Custom_Status' ) ) {
			$ef_Custom_Status = $this->register_edit_flow_status();
			if ( ! empty( $ef_Custom_Status ) ) {
				foreach ( $ef_Custom_Status as $ef_status ) {
					$options[ $ef_status->slug ] = esc_html( $ef_status->name );
				}
			}
		}

		$type = 'select';

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => $relevant_tokens,
		);

		return apply_filters( 'uap_option_wp_post_statuses', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function wp_user_profile_fields( $label = null, $option_code = 'WPUSERFIELDS', $args = array() ) {

		if ( ! $label ) {
			$label = __( 'Profile field', 'uncanny-automator-pro' );
		}

		$is_any          = key_exists( 'is_any', $args ) ? $args['is_any'] : false;
		$relevant_tokens = key_exists( 'relevant_tokens', $args ) ? $args['relevant_tokens'] : '';
		$options         = array();

		if ( $is_any ) {
			$options['-1'] = __( 'Any profile field', 'uncanny-automator-pro' );
		}

		$options['display_name'] = __( 'Display name', 'uncanny-automator-pro' );
		$options['user_email']   = __( 'Email', 'uncanny-automator-pro' );
		$options['user_login']   = __( 'Login', 'uncanny-automator-pro' );
		$options['user_pass']    = __( 'Password', 'uncanny-automator-pro' );
		$options['user_url']     = __( 'Website', 'uncanny-automator-pro' );
		//$options['description']  = __( 'Biographical Info', 'uncanny-automator-pro' );
		//$options['first_name']   = __( 'First name', 'uncanny-automator-pro' );
		//$options['last_name']    = __( 'Last name', 'uncanny-automator-pro' );
		//$options['nickname']     = __( 'Nickname', 'uncanny-automator-pro' );
		$type = 'select';

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => $relevant_tokens,
		);

		return apply_filters( 'uap_option_wp_user_profile_fields', $option );
	}

	/**
	 * Getting custom post statuses used in Edit Flow plugin.
	 *
	 * @return array
	 */
	private function register_edit_flow_status() {
		global $wp_post_statuses;
		$taxonomy_key = 'post_status';
		// Register new taxonomy so that we can store all our fancy new custom statuses (or is it stati?)
		if ( ! taxonomy_exists( $taxonomy_key ) ) {
			$args = array(
				'hierarchical'          => false,
				'update_count_callback' => '_update_post_term_count',
				'label'                 => false,
				'query_var'             => false,
				'rewrite'               => false,
				'show_ui'               => false,
			);
			register_taxonomy( $taxonomy_key, 'post', $args );
		}
		// Handle if the requested taxonomy doesn't exist
		$args     = array(
			'hide_empty' => false,
			'taxonomy'   => $taxonomy_key,
		);
		$statuses = get_terms( $args );
		if ( is_wp_error( $statuses ) || empty( $statuses ) ) {
			$statuses = array();
		}

		// Expand and order the statuses
		$ordered_statuses = array();
		$hold_to_end      = array();
		foreach ( $statuses as $key => $status ) {
			// Unencode and set all of our psuedo term meta because we need the position if it exists
			$unencoded_description = maybe_unserialize( base64_decode( $status->description ) );
			if ( is_array( $unencoded_description ) ) {
				foreach ( $unencoded_description as $key => $value ) {
					$status->$key = $value;
				}
			}
			// We require the position key later on (e.g. management table)
			if ( ! isset( $status->position ) ) {
				$status->position = false;
			}
			// Only add the status to the ordered array if it has a set position and doesn't conflict with another key
			// Otherwise, hold it for later
			if ( $status->position && ! array_key_exists( $status->position, $ordered_statuses ) ) {
				$ordered_statuses[ (int) $status->position ] = $status;
			} else {
				$hold_to_end[] = $status;
			}
		}
		// Sort the items numerically by key
		ksort( $ordered_statuses, SORT_NUMERIC );
		// Append all of the statuses that didn't have an existing position
		foreach ( $hold_to_end as $unpositioned_status ) {
			$ordered_statuses[] = $unpositioned_status;
		}

		return $ordered_statuses;
	}

	/**
	 * @return array|void
	 */
	public function select_all_fields_of_selected_post() {

		Automator()->utilities->ajax_auth_check();

		$selected_post_id = automator_filter_input( 'value', INPUT_POST );

		$items = array(
			array(
				'value' => '-1',
				'text'  => esc_html__( 'Any field', 'uncanny-automator' ),
			),
		);

		if ( empty( $selected_post_id ) ) {
			return array();
		}

		$fields = $this->get_post_fields( $selected_post_id );

		foreach ( $fields as $field ) {
			$items[] = array(
				'value' => $field,
				'text'  => $field,
			);
		}

		wp_send_json( $items );

	}

	/**
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_post_fields( $post_id = 0 ) {

		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT DISTINCT meta_key FROM wp_postmeta WHERE post_id = %d',
				$post_id
			),
			OBJECT
		);

		// Disable all the meta starting with underscore.
		$results = array_map(
			function ( $key ) {
				if ( '_' === substr( $key->meta_key, 0, 1 ) ) {
					return false;
				}

				return $key->meta_key;

			},
			$results
		);

		$fields = array();

		foreach ( $results as $result ) {
			if ( false !== $result ) {
				$fields[] = $result;
			}
		}

		return $fields;
	}

	/**
	 * Return all the specific fields of post type in ajax call (without 'All (post-type)' option).
	 */
	public function select_all_posts_by_post_type_no_all() {

		Automator()->utilities->ajax_auth_check();

		$fields = array();

		if ( isset( $_POST ) && key_exists( 'value', $_POST ) && ! empty( $_POST['value'] ) ) {

			$post_type = sanitize_text_field( $_POST['value'] );

			$args = array(
				'posts_per_page'   => 999,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'post_type'        => $post_type,
				'post_status'      => 'publish',
				'suppress_filters' => true,
				'fields'           => array( 'ids', 'titles' ),
			);

			$posts_list = Automator()->helpers->recipe->options->wp_query( $args, false, __( 'Any Post', 'uncanny-automator-pro' ) );

			if ( ! empty( $posts_list ) ) {

				$post_type_label = get_post_type_object( $post_type )->labels->name;

				$fields[] = array();

				foreach ( $posts_list as $post_id => $post_title ) {
					// Check if the post title is defined
					$post_title = ! empty( $post_title ) ? $post_title : sprintf( __( 'ID: %1$s (no title)', 'uncanny-automator-pro' ), $post_id );

					$fields[] = array(
						'value' => $post_id,
						'text'  => $post_title,
					);
				}
			}
		}

		echo wp_json_encode( $fields );

		die();
	}

	/**
	 * All WP Post Types.
	 *
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function all_wp_post_types_set_taxonomy( $label = null, $option_code = 'WPPOSTTYPES', $args = array() ) {
		$args['plural_label'] = true;

		return $this->all_wp_post_types( $label, $option_code, $args );
	}
}
