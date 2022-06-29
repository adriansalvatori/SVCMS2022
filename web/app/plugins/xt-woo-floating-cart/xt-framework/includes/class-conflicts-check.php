<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Conflicts_Check' ) ) {

	/**
	 * Class that takes care of disabling plugin conflicts and throws notices
	 *
	 * @since      1.0.0
	 * @package    XT_Framework
	 * @subpackage XT_Framework/includes
	 * @author     XplodedThemes
	 */
	class XT_Framework_Conflicts_Check {

		/**
		 * Core class reference.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      XT_Framework $core Core Class
		 */
		private $core;

		protected $conflicts = array();

		/**
		 * Construct.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @var      XT_Framework $core Core Class
		 */
		public function __construct( $core ) {

			$this->core = $core;

			if ( empty( $core->plugin()->conflicts() ) ) {
				return;
			}

			$this->conflicts = $core->plugin()->conflicts();

			add_action( 'admin_init', array( $this, 'disable_conflicted_plugins' ));
		}

		/**
		 * Check if conflict plugins are active, if yes, disabled them and show error notice
		 *
		 * @since    1.0.0
		 */
		public function disable_conflicted_plugins() {

			$failed = array();
			foreach ( $this->conflicts as $key => $item ) {

				if ( is_plugin_active( $item->path ) ) {

					$failed[]                        = $item->path;
					$this->conflicts[ $key ]->failed = true;
				}
			}

			if ( ! empty( $failed ) ) {

				deactivate_plugins( $failed );

				$this->render_conflicts_notices();

				return false;
			}

			return true;
		}

		/**
		 * Render error notice if conflicted plugins are active
		 *
		 * @since    1.0.0
		 */
		public function render_conflicts_notices() {

			foreach ( $this->conflicts as $key => $item ) {
				if ( ! empty( $item->failed ) ) {

					$this->core->plugin_notices()->add_warning_message( sprintf(
						esc_html__( '%1$s has been disabled since it cannot be active while using %2$s!', 'xt-framework' ),
						'<strong>' . $item->name . '</strong>',
						'<strong>' . $this->core->plugin_name() . '</strong>'
					) );

				}
			}
		}
	}
}