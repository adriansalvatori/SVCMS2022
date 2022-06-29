<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

namespace Uncanny_Automator_Pro;

use Exception;
use WC_Order;
use WC_Order_Item_Product;

/**
 * Class ANON_ORDER_ITEM_CREATED
 * @since v3.3
 * @package Uncanny_Automator_Pro
 * @version v3.4 - Added woo trigger condition options
 */
class ANON_ORDER_ITEM_CREATED {

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
	 * @var string
	 */
	private $trigger_condition;

	/**
	 * Set up Automator trigger constructor.
	 * @throws Exception
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'migrate_existing_triggers_to_new' ), 999 );
		$this->trigger_code      = 'ANONORDERITEMCREATED';
		$this->trigger_meta      = 'WOOPRODUCT';
		$this->trigger_condition = 'TRIGGERCOND';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 * @throws Exception
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/woocommerce/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Anonymous trigger - WooCommerce */
			'sentence'            => sprintf( __( '{{A product:%1$s}} has its associated order {{completed, paid for, thank you page visited:%2$s}}', 'uncanny-automator-pro' ), $this->trigger_meta, $this->trigger_condition ),
			/* translators: Anonymous trigger - WooCommerce */
			'select_option_name'  => __( '{{A product}} has its associated order {{completed, paid for, thank you page visited}}', 'uncanny-automator-pro' ),
			'action'              => array(
				'woocommerce_order_status_completed',
				'woocommerce_thankyou',
				'woocommerce_payment_complete',
			),
			'priority'            => 999,
			'accepted_args'       => 3,
			'type'                => 'anonymous',
			'validation_function' => array( $this, 'woo_order_item_created' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {

		$options            = Automator()->helpers->recipe->woocommerce->options->all_wc_products( __( 'Product', 'uncanny-automator' ) );
		$options['options'] = array( '-1' => __( 'Any product', 'uncanny-automator' ) ) + $options['options'];

		$trigger_condition = Automator()->helpers->recipe->woocommerce->pro->get_woocommerce_order_item_trigger_conditions( $this->trigger_condition );
		$options_array     = array(
			'options' => array(
				$trigger_condition,
				$options,
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Handle the actual trigger run
	 *
	 * @param $order_id
	 */
	public function woo_order_item_created( $order_id ) {
		$order = $this->validate_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		global $uncanny_automator;
		$recipes          = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$user_id          = $order->get_customer_id();
		$items            = $order->get_items();
		/** @var WC_Order_Item_Product $item */
		foreach ( $items as $item ) {
			$product_id = (int) $item->get_product_id();
			// Check if this product ID is allowed to run a trigger
			if ( ! $this->is_product_allowed( $product_id, $item, $order ) ) {
				continue;
			}
			//Add where option is set to Any product
			foreach ( $recipes as $recipe_id => $recipe ) {
				foreach ( $recipe['triggers'] as $trigger ) {
					$trigger_id = absint( $trigger['ID'] );
					$recipe_id  = absint( $recipe_id );
					if ( ! isset( $required_product[ $recipe_id ] ) || ! isset( $required_product[ $recipe_id ][ $trigger_id ] ) ) {
						continue;
					}
					if ( intval( '-1' ) !== intval( $required_product[ $recipe_id ][ $trigger_id ] ) && (int) $required_product[ $recipe_id ][ $trigger_id ] !== $product_id ) {
						continue;
					}
					if ( ! $this->has_trigger_condition_met( $recipes, $recipe_id, $order, $required_product[ $recipe_id ][ $trigger_id ] ) ) {
						continue;
					}

					$args = $this->run_trigger( $user_id, $recipe_id, $trigger_id );
					//Adding an action to save order id in trigger meta
					do_action( 'uap_wc_trigger_save_meta', $order_id, $recipe_id, $args, 'product' );
					do_action( 'uap_wc_order_item_meta', $item->get_id(), $order_id, $recipe_id, $args );

					$this->complete_trigger( $args );
				}
			}
		}
	}

	/**
	 * @param $order_id
	 *
	 * @return WC_Order|object
	 */
	public function validate_order( $order_id ) {
		if ( ! $order_id ) {
			return (object) array();
		}

		return wc_get_order( $order_id );
	}

	/**
	 * Check if trigger condition is met
	 *
	 * @param $recipes
	 * @param $check_recipe_id
	 * @param $order
	 * @param $required_product
	 *
	 * @return bool
	 */
	public function has_trigger_condition_met( $recipes, $check_recipe_id, $order, $required_product ) {
		global $uncanny_automator;
		$required_condition = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_condition );
		if ( empty( $required_product ) || empty( $recipes ) || empty( $required_condition ) ) {
			return false;
		}

		$trigger_cond_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = absint( $trigger['ID'] );
				if ( $check_recipe_id !== $recipe_id ) {
					continue;
				}
				if ( ! array_key_exists( $recipe_id, $required_condition ) ) {
					continue;
				}
				if ( ! array_key_exists( $trigger_id, $required_condition[ $recipe_id ] ) ) {
					continue;
				}
				if ( current_action() === (string) $required_condition[ $recipe_id ][ $trigger_id ] ) {
					$trigger_cond_ids[] = $recipe_id;
				}
			}
		}

		if ( empty( $trigger_cond_ids ) ) {
			return false;
		}

		if ( 'woocommerce_order_status_completed' === current_action() && 'completed' !== $order->get_status() ) {
			return false;

		}

		return true;
	}

	/**
	 * Filter the product
	 *
	 * @param $product_id
	 * @param $item
	 * @param $order
	 *
	 * @return bool
	 */
	public function is_product_allowed( $product_id, $item, $order ) {
		// Allow users to skip specific product ids
		$skip_product_ids = apply_filters( 'automator_woocommerce_item_added_skip_product_ids', array(), $product_id, $item, $order );
		if ( ! empty( $skip_product_ids ) && in_array( absint( $product_id ), $skip_product_ids, true ) ) {
			return false;
		}

		// Allow users to skip specific product types
		$skip_product_types = apply_filters( 'automator_woocommerce_item_added_skip_product_type', array(), $product_id, $item, $order );
		if ( ! empty( $skip_product_types ) && in_array( $item->get_product()->get_type(), $skip_product_types, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Run the trigger when all conditions have met
	 *
	 * @param $user_id
	 * @param $recipe_id
	 * @param $trigger_id
	 *
	 * @return array|bool|int|null
	 */
	public function run_trigger( $user_id, $recipe_id, $trigger_id ) {
		global $uncanny_automator;
		$pass_args = array(
			'code'             => $this->trigger_code,
			'meta'             => $this->trigger_meta,
			'user_id'          => $user_id,
			'recipe_to_match'  => $recipe_id,
			'trigger_to_match' => $trigger_id,
			'ignore_post_id'   => true,
		);

		if ( 0 !== $user_id ) {
			$pass_args['is_signed_in'] = true;
		}

		return $uncanny_automator->process->user->maybe_add_trigger_entry( $pass_args, false );
	}

	/**
	 * Completing the trigger
	 *
	 * @param $args
	 */
	public function complete_trigger( $args ) {
		if ( empty( $args ) ) {
			return;
		}
		global $uncanny_automator;
		foreach ( $args as $result ) {
			if ( true === $result['result'] ) {
				$uncanny_automator->process->user->maybe_trigger_complete( $result['args'] );
			}
		}
	}

	/**
	 * We introduced this trigger in v3.3, but due to some limitations and complains, switched to order complete, payment
	 * made etc hook and recursively complete trigger.
	 *
	 * @since v3.4
	 */
	public function migrate_existing_triggers_to_new() {
		if ( 'yes' === get_option( 'automator_woo_order_item_trigger_migrated', 'no' ) ) {
			return;
		}
		global $wpdb;
		$existing_triggers = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", 'code', 'ANONORDERITEMCREATED' ) );
		if ( empty( $existing_triggers ) ) {
			update_option( 'automator_woo_order_item_trigger_migrated', 'yes', false );

			return;
		}
		foreach ( $existing_triggers as $trigger_id ) {
			$exists = get_post_meta( $trigger_id, 'TRIGGERCOND', true );
			if ( ! empty( $exists ) ) {
				continue;
			}
			update_post_meta( $trigger_id, 'TRIGGERCOND', 'woocommerce_order_status_completed' );
			update_post_meta( $trigger_id, 'TRIGGERCOND_readable', 'completed' );
			$readable = get_post_meta( $trigger_id, 'sentence_human_readable', true );
			$readable = str_replace( 'is purchased in an order', 'has its associated order {{completed}}', $readable );
			update_post_meta( $trigger_id, 'sentence_human_readable', $readable );

			$sentence = get_post_meta( $trigger_id, 'sentence', true );
			$sentence = str_replace( 'is purchased in an order', 'has its associated order {{completed, paid for, thank you page visited:TRIGGERCOND}}', $sentence );
			update_post_meta( $trigger_id, 'sentence', $sentence );

			$sentence_html = get_post_meta( $trigger_id, 'sentence_human_readable_html', true );
			$sentence_html = str_replace( 'is purchased in an order</span>', 'has its associated order</span><span class="item-title__token" data-token-id="TRIGGERCOND" data-options-id="TRIGGERCOND">completed</span>', $sentence_html );
			update_post_meta( $trigger_id, 'sentence_human_readable_html', $sentence_html );
		}
		update_option( 'automator_woo_order_item_trigger_migrated', 'yes', false );
	}
}
