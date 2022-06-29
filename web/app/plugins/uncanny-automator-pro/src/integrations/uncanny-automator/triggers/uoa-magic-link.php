<?php

namespace Uncanny_Automator_Pro;

/**
 * Class UOA_MAGIC_LINK
 * @package Uncanny_Automator_Pro
 */
class UOA_MAGIC_LINK {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'UOA';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WPMAGICLINK';
		$this->trigger_meta = 'MAGICLINK';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;
		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/automator-core/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core. 1. Shortcode */
			'sentence'            => sprintf( __( 'A user clicks %1$s', 'uncanny-automator-pro' ), '[automator_link id="{{id:WPMAGICLINK}}" text="' . __( 'Click here', 'uncanny-automator-pro' ) . '"]' ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( 'A user clicks {{a magic link}}', 'uncanny-automator-pro' ),
			'action'              => 'automator_magic_button_action',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'save_data' ),
			'options'             => [],
			'inline_css'          => $this->inline_css(),
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * A piece of CSS that it's added only when this item
	 * is on the recipe
	 *
	 * @return string The CSS, with the CSS tags
	 */
	public function inline_css() {
		// Start output
		ob_start();

		?>

		<style>

			.item[data-id="{{item_id}}"] .item-title {
				user-select: auto;
			}

			.item[data-id="{{item_id}}"] .item-title__integration {
				user-select: none;
			}

			.item[data-id="{{item_id}}"] .item-title__normal,
			.item[data-id="{{item_id}}"] .item-title__token {
				margin-right: 0;
			}

		</style>

		<?php

		// Get output
		$output = ob_get_clean();

		// Return output
		return $output;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $trigger_id
	 * @param $user_id
	 */
	public function save_data( $trigger_id, $user_id ) {

		global $uncanny_automator;
		$recipe_id = 0;
		if ( method_exists( '\Uncanny_Automator\Automator_Get_Data', 'maybe_get_recipe_id' ) ) {
			$recipe_id = $uncanny_automator->get->maybe_get_recipe_id( $trigger_id );
		} else {
			$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
			if ( $recipes ) {
				$found = false;
				foreach ( $recipes as $recipe ) {
					foreach ( $recipe['triggers'] as $trigger ) {
						if ( absint( $trigger_id ) === absint( $trigger['ID'] ) ) {
							//trigger found;
							$recipe_id = (int) $recipe['ID'];
							$found     = true;
							break;
						}
					}
					if ( $found ) {
						break;
					}
				}
			}
		}

		$args_to_pass = [
			'code'             => $this->trigger_code,
			'meta'             => $this->trigger_meta,
			'recipe_to_match'  => $recipe_id,
			'trigger_to_match' => $trigger_id,
			'ignore_post_id'   => true,
			'user_id'          => $user_id,
		];

		$results = $uncanny_automator->maybe_add_trigger_entry( $args_to_pass, false );

		$user_data = get_userdata( $user_id );
		// Save trigger meta
		if ( $results ) {
			foreach ( $results as $result ) {
				if ( true === $result['result'] && $result['args']['trigger_id'] && $result['args']['get_trigger_id'] ) {

					$run_number = $uncanny_automator->get->trigger_run_number( $result['args']['trigger_id'], $result['args']['get_trigger_id'], $user_id );
					$save_meta  = [
						'user_id'        => $user_id,
						'trigger_id'     => $result['args']['trigger_id'],
						'run_number'     => $run_number, //get run number
						'trigger_log_id' => $result['args']['get_trigger_id'],
						'ignore_user_id' => true,
					];

					$save_meta['meta_key']   = 'first_name';
					$save_meta['meta_value'] = $user_data->first_name;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'last_name';
					$save_meta['meta_value'] = $user_data->last_name;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'email';
					$save_meta['meta_value'] = $user_data->user_email;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'username';
					$save_meta['meta_value'] = $user_data->user_login;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'user_id';
					$save_meta['meta_value'] = $user_data->ID;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					if ( ! empty( $_GET ) && isset( $_GET['automator_button_post_id'] ) ) {
						$save_meta['meta_key']   = 'automator_button_post_id';
						$save_meta['meta_value'] = absint( $_GET['automator_button_post_id'] );
						$uncanny_automator->insert_trigger_meta( $save_meta );
						$post_data = get_post( absint( $_GET['automator_button_post_id'] ) );
						if ( ! empty( $post_data ) && isset( $post_data->ID ) ) {
							$save_meta['meta_key']   = 'automator_button_post_title';
							$save_meta['meta_value'] = $post_data->post_title;
							$uncanny_automator->insert_trigger_meta( $save_meta );
						}
					}

					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}

	}
}
