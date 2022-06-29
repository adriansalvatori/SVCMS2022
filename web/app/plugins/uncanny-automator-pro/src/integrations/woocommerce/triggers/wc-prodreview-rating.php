<?php

namespace Uncanny_Automator_Pro;

/**
 * @package Uncanny_Automator_Pro
 * Class WC_PRODREVIEW_RATING
 */
class WC_PRODREVIEW_RATING {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WC';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WCPRODREVIEWRATING';
		$this->trigger_meta = 'WOOPRODUCT';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/woocommerce/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WooCommerce */
			'sentence'            => sprintf( __( 'A user reviews {{a product:%1$s}} with a rating {{greater than, less than or equal to:%2$s}} {{an amount:%3$s}}', 'uncanny-automator-pro' ), $this->trigger_meta, 'NUMBERCOND', 'product_rating' ),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => __( 'A user reviews {{a product}} with a rating {{greater than, less than or equal to}} {{an amount}}', 'uncanny-automator-pro' ),
			'action'              => 'comment_post',
			'priority'            => 90,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wc_product_reviewed' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options_array = array(
			'options' => array(
				Automator()->helpers->recipe->woocommerce->options->all_wc_products( __( 'Product', 'uncanny-automator' ), $this->trigger_meta ),
				Automator()->helpers->recipe->field->select(
					array(
						'option_code'     => 'product_rating',
						'label'           => __( 'Product rating', 'uncanny-automator-pro' ),
						'input_type'      => 'select',
						'supports_tokens' => '',
						'required'        => true,
						'default_value'   => null,
						'options'         => array(
							1 => '1',
							2 => '2',
							3 => '3',
							4 => '4',
							5 => '5',
						),
					)
				),
				Automator()->helpers->recipe->less_or_greater_than(),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $comment_id
	 * @param $comment_approved
	 * @param $commentdata
	 */
	public function wc_product_reviewed( $comment_id, $comment_approved, $commentdata ) {
		if ( 'review' !== (string) $commentdata['comment_type'] ) {
			return;
		}

		$comment = get_comment( $comment_id, OBJECT );

		if ( isset( $comment->user_id ) && 0 === absint( $comment->user_id ) ) {
			return;
		}

		global $uncanny_automator;
		$rating             = get_comment_meta( $comment_id, 'rating', true );
		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_post      = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_condition = $uncanny_automator->get->meta_from_recipes( $recipes, 'NUMBERCOND' );
		$required_rating    = $uncanny_automator->get->meta_from_recipes( $recipes, 'product_rating' );
		$matched_recipe_ids = array();

		//Add where option is set to Any post / specific post
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];

				Utilities::log(
					array(
						$required_post[ $recipe_id ][ $trigger_id ] => $comment->comment_post_ID,
						$required_condition[ $recipe_id ][ $trigger_id ] => 'condition',
						$required_rating[ $recipe_id ][ $trigger_id ] => $rating,
					),
					'check post',
					true,
					'wc-log'
				);

				if ( ( intval( '-1' ) === intval( $required_post[ $recipe_id ][ $trigger_id ] ) || absint( $required_post[ $recipe_id ][ $trigger_id ] ) === absint( $comment->comment_post_ID ) ) && $uncanny_automator->utilities->match_condition_vs_number( $required_condition[ $recipe_id ][ $trigger_id ], $required_rating[ $recipe_id ][ $trigger_id ], $rating ) ) {
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

		//	If recipe matches
		foreach ( $matched_recipe_ids as $matched_recipe_id ) {
			$pass_args = array(
				'code'             => $this->trigger_code,
				'meta'             => $this->trigger_meta,
				'user_id'          => $comment->user_id,
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
				'post_id'          => $comment->comment_post_ID,
			);

			$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

			do_action( 'uap_wp_comment_approve', $comment, $matched_recipe_id['recipe_id'], $matched_recipe_id['trigger_id'], $args );
			do_action( 'uap_wc_trigger_save_product_meta', $comment->comment_ID, $matched_recipe_id['recipe_id'], $args, 'product' );

			if ( $args ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {
						$uncanny_automator->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}

}
