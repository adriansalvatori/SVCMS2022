<?php

namespace Uncanny_Automator_Pro;

use LP_Order;

/**
 * Class LP_ENRLCOURSE_A
 * @package Uncanny_Automator_Pro
 */
class LP_ENRLCOURSE_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'LPENRLCOURSE-A';
		$this->action_meta = 'LPCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/learnpress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnPress */
			'sentence'           => sprintf( __( 'Enroll the user in {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnPress */
			'select_option_name' => __( 'Enroll the user in {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'lp_enroll_in_course' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->learnpress->options->all_lp_courses( null, 'LPCOURSE', false ),
			],
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function lp_enroll_in_course( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( ! function_exists( 'learn_press_get_user' ) ) {
			$error_message = __( 'The function learn_press_get_user does not exist', 'uncanny-automator-pro' );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		if ( ! class_exists( '\LP_User_Item_Course' ) ) {
			$error_message = __( 'The class LP_User_Item_Course does not exist. Please upgrade your LearnPress.', 'uncanny-automator-pro' );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		//loading LP user by user_id
		$user = learn_press_get_user( $user_id );

		$course_id = $action_data['meta'][ $this->action_meta ];
		// loading LP course by ID
		$course = learn_press_get_course( $course_id );
		if ( $user->has_enrolled_course( $course_id ) ) {
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'User already enrolled in course.', 'uncanny-automator-pro' ) );

			return;
		}
		if ( $course && $course->exists() ) {
			$order = new LP_Order();
			$order->set_customer_note( __( 'Order created by Uncanny Automator', 'uncanny-automator-pro' ) );
			$order->set_status( learn_press_default_order_status( 'lp-' ) );
			$order->set_total( 0 );
			$order->set_subtotal( 0 );
			$order->set_user_ip_address( learn_press_get_ip() );
			$order->set_user_agent( learn_press_get_user_agent() );
			$order->set_created_via( 'Uncanny Automator' );
			$order->set_user_id( $user_id );
			$order_id                      = $order->save();
			$order_item                    = array();
			$order_item['order_item_name'] = $course->get_title();
			$order_item['item_id']         = $course_id;
			$order_item['quantity']        = 1;
			$order_item['subtotal']        = 0;
			$order_item['total']           = 0;
			$item_id                       = $order->add_item( $order_item, 1 );
			$order->update_status( 'completed' );
			//}
			//Enroll to New Course
			// Data user_item for save database
			$user_item_data           = array(
				'user_id' => $user->get_id(),
				'item_id' => $course->get_id(),
				'ref_id'  => $order_id,
			);
			$user_item_data['status'] = LP_COURSE_ENROLLED;

			$user_item_new = new \LP_User_Item_Course( $user_item_data );
			$result        = $user_item_new->update();
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

			return;
		} else {
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'Course not found.', 'uncanny-automator-pro' ) );

			return;
		}
	}
}
