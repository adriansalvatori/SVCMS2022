<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Dependencies_Check' ) ) {

	/**
	 * Class that takes care plugin dependencies notices
	 *
	 * @since      1.0.0
	 * @package    XT_Framework
	 * @subpackage XT_Framework/includes
	 * @author     XplodedThemes
	 */
	class XT_Framework_Dependencies_Check {

		/**
		 * Core class reference.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      XT_Framework $core Core Class
		 */
		private $core;

		protected $passed = false;
		protected $dependencies = array();

		/**
		 * Construct.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @var      XT_Framework $core Core Class
		 */
		public function __construct( $core ) {

			$this->core = $core;

			if ( empty( $core->plugin()->dependencies() ) ) {
				return;
			}

			$this->dependencies = $core->plugin()->dependencies();
			$this->passed       = $this->check_dependencies();
		}

		/**
		 * Check if required plugin dependencies are loaded, if not, show error notice
		 *
		 * @since    1.0.0
		 */
		private function check_dependencies() {

			$failed = 0;
			foreach ( $this->dependencies as $key => $item ) {

				if (
					( ! empty( $item->function ) && ! function_exists( $item->function ) ) ||
					( ! empty( $item->class ) && ! class_exists( $item->class ) ) ||
					( ! empty( $item->constant ) && ! defined( $item->constant ) )
				) {

					$failed ++;
					$this->dependencies[ $key ]->failed = true;
				}

			}

			if ( ! empty( $failed ) ) {
				$this->render_dependencies_notices();

				return false;
			}

			return true;
		}

		/**
		 * Render error notice if required plugin dependencies are not loaded
		 *
		 * @since    1.0.0
		 */
		public function render_dependencies_notices() {

			foreach ( $this->dependencies as $item ) {

				if ( ! empty( $item->failed ) ) {

					$class = '';

				    if(!empty($item->url)) {

				        $target = "_blank";
                        $url = $item->url;

                    }else{

					    $target = "_self";

				    	if(!empty($item->slug)) {
						    $url = 'plugin-install.php?tab=plugin-information&plugin=' . esc_attr($item->slug) . '&TB_iframe=true&width=640&height=500';
					        $class = 'thickbox open-plugin-details-modal';
				    	}else{
				    		$url = 'plugin-install.php?s=' . $item->name . '&tab=search&type=term';
					    }

					    $url = is_multisite() ? network_admin_url($url) : admin_url($url);

                    }

					$this->core->plugin_notices()->add_warning_message( sprintf(
						esc_html__( '%1$s%2$s%3$s plugin requires %4$s to be installed and active.', 'xt-framework' ),
						'<strong>',
						$this->core->plugin_name(),
						'</strong>',
						'<a class="'.esc_attr($class).'" target="'.esc_attr($target).'" href="' . esc_url($url) . '"><strong>' . $item->name . '</strong></a>'
					) );

				}
			}
		}

		/**
		 * Check if the plugin depends on a specific plugin
		 *
		 * @return    string $plugin_name
		 * @since     1.0.0
		 */
		public function depends_on( $plugin_name ) {

			$key = array_search( $plugin_name, array_column( $this->dependencies, 'name' ) );

			return ( $key !== false );
		}

		/**
		 * Check if the plugin has passed required dependencies and is ready to be initialised.
		 *
		 * @return    bool
		 * @since     1.0.0
		 */
		public function passed() {

			return $this->passed;
		}

	}
}