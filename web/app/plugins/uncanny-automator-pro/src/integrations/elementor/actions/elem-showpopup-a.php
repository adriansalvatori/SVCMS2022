<?php

namespace Uncanny_Automator_Pro;

use ElementorPro\Modules\Popup\Module;

/**
 * Class ELEM_SHOWPOPUP_A
 * @package Uncanny_Automator_Pro
 */
class ELEM_SHOWPOPUP_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'ELEM';

	private $action_code;
	private $action_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'ELEMSHOWPOPUP';
		$this->action_meta = 'ELEMPOPUP';
		$this->define_action();
		add_action( 'wp_footer', array( $this, 'action_callback' ) );
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/elementor/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'requires_user'      => false,
			/* translators: Action - BuddyPress */
			'sentence'           => sprintf( __( 'Show {{a popup:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyPress */
			'select_option_name' => __( 'Show {{a popup}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'show_elem_popup' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options = array(
			'options' => array(
				Automator()->helpers->recipe->elementor->options->pro->all_elementor_popups( null, $this->action_meta ),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options );
	}

	/**
	 * Show Elementor Popup
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 *
	 * @since 1.1
	 */
	public function show_elem_popup( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		if ( isset( $action_data['meta'][ $this->action_meta ] ) ) {

			$popup_id = $action_data['meta'][ $this->action_meta ];

			$popup_id = absint( $popup_id );

			$existing_popup = get_user_meta( $user_id, 'ua_elementor_popups', true );

			if ( empty( $existing_popup ) || ! is_array( $existing_popup ) ) {
				$existing_popup = array();
			}

			$existing_popup[] = $popup_id;

			update_user_meta( $user_id, 'ua_elementor_popups', $existing_popup );

			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		}

		return;
	}

	public function action_callback() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id        = get_current_user_id();
		$existing_popup = get_user_meta( $user_id, 'ua_elementor_popups', true );
		if ( ! empty( $existing_popup ) ) {
			delete_user_meta( $user_id, 'ua_elementor_popups' );
			foreach ( $existing_popup as $popup_id ) {
				Module::add_popup_to_location( $popup_id ); //insert the popup to the current page
				?>
				<script>
					jQuery(document).ready(function () { //wait for the page to load
						jQuery(window).on('elementor/frontend/init', function () { //wait for elementor to load
							elementorFrontend.on('components:init', function () { //wait for elementor pro to load
								elementorFrontend.documentsManager.documents[<?php echo $popup_id; ?>].showModal(); //show the popup
							});
						});
					});
				</script>
				<?php
			}
		}
	}
}
