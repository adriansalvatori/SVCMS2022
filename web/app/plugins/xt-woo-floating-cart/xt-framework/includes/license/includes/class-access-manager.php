<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('XT_Framework_Access_Manager')) {

	class XT_Framework_Access_Manager {

		/* @var XT_Framework $core */
		protected $core;

		public $license;
		public $updater;
		public $plugin;

		public $is_last_version;

		public function __construct( $core ) {

			$this->core   = $core;
			$this->plugin = $this->core->plugin();

            $this->is_last_version = !empty($this->core->plugin()->markets->envato->last_version) && version_compare($this->core->plugin()->version, $this->core->plugin()->markets->envato->last_version, '>=');

            $this->license = new XT_Framework_License( $this, $this->core );

            if($this->is_last_version) {
                add_action('admin_notices', array($this, 'last_codecanyon_version'));
            }

			if ( ! empty( $_REQUEST['xt-license-revoke'] ) && ! empty( $_REQUEST['id'] ) ) {

				$id     = intval( $_REQUEST['id'] );
				$market = ! empty( $_REQUEST['market'] ) ? $_REQUEST['market'] : 'envato';

				if ( $this->core->market_product_is( $market, $id ) ) {

					$license = new XT_Framework_License( $this, $this->core );

					$purchase_code = $license->getLocalLicenseInfo( 'purchase_code' );
					$domain        = $license->getLocalLicenseInfo( 'domain' );

					if ( ! empty( $purchase_code ) ) {
						$license->revoke( $purchase_code, $domain );
					}

					$license->deleteLocalLicense();
					die();
				}
			}

			if ( is_admin() ) {

				$this->updater = new XT_Framework_Plugin_Updater( $this->license, $this->plugin, $this->is_last_version );
			}

			return $this;
		}

		function last_codecanyon_version() {
			return;

            $message = __('{title} plugin has retired from CodeCanyon and will now be sold exclusively on XplodedThemes.com.<br><span style="color:green;"><strong>To continue receiving new updates and security patches, please migrate your CodeCanyon license.</strong></span>', 'xt-framework');
            $button = sprintf(__('<a target="_blank" class="button button-secondary" href="https://xplodedthemes.com/codecanyon-license-migration/?id=%d"><strong>Migrate License</strong></a>', 'xt-framework'), $this->core->plugin()->markets->freemius->id);
            $is_xt_admin_page = (!empty($_GET['page']) && strpos($_GET['page'], 'xt-') !== -1);
            $is_xt_status_page = (!empty($_GET['page']) && $_GET['page'] === 'xt-plugins-system-status');
            $is_plugin_admin_page = $this->core->plugin_tabs()->is_admin_tabs_page() || $this->core->framework_tabs()->is_admin_tabs_page();
            $is_dismissible = !$is_plugin_admin_page && !$is_xt_status_page;

            if(($is_plugin_admin_page && $is_xt_admin_page) || !$is_xt_admin_page || $is_xt_status_page) {
                echo $this->core->plugin_notices()->get_backend_message_output('warning', array($message, $button), array(), $is_dismissible);
            }
        }

		/**
		 * Check if user has connected his account (opted-in).
		 *
		 * @return bool
		 * @since  1.0.1
		 */
		function is_registered() {
			return true;
		}

		/**
		 * Returns TRUE if the user opted-in and didn't disconnect (opt-out).
		 *
		 * @return bool
		 * @since  1.2.1.5
		 */
		function is_tracking_allowed() {
			return true;
		}

		/**
		 * Check if running premium params code.
		 * @return bool
		 * @since  1.0.5
		 *
		 */
		function is_premium() {
			return true;
		}

		/**
		 * Check if the user has an activated and valid paid license on current params's install.
		 *
		 * @return bool
		 */
		function is_paying_active() {
			return $this->is_paying() && $this->license->isActivated();
		}

		/**
		 * Check if the user has a valid paid license on current params's install.
		 *
		 * @return bool
		 */
		function is_paying() {
			return $this->license->isFound();
		}

		/**
		 * Check if user in trial or in free plan (not paying).
		 *
		 * @return bool
		 */
		function is_not_paying() {
			return ( $this->is_trial() || $this->is_free_plan() );
		}

		/**
		 * Check if the user is paying or in trial.
		 *
		 * @return bool
		 */
		function is_paying_or_trial() {
			return ( $this->is_paying() || $this->is_trial() );
		}

		/**
		 * Check if user in a trial or have feature enabled license.
		 * @return bool
		 */
		function can_use_premium_code() {
			return $this->is_paying();
		}

		#----------------------------------------------------------------------------------
		#region Premium Only
		#----------------------------------------------------------------------------------

		/**
		 * Returns true when running premium params code.
		 *
		 * @return bool
		 */
		function is__premium_only() {
			return $this->is_premium();
		}

		/**
		 * Check if the user has an activated and valid paid license on current params's install.
		 *
		 * @return bool
		 *
		 */
		function is_paying__premium_only() {
			return ( $this->is__premium_only() && $this->is_paying() );
		}

		/**
		 * Check if the user is paying or in trial.
		 *
		 * All code wrapped in this statement will be only included in the premium code.
		 *
		 *
		 * @return bool
		 */
		function is_paying_or_trial__premium_only() {
			return $this->is_premium() && $this->is_paying_or_trial();
		}


		/**
		 * Check if user in a trial or have feature enabled license.
		 *
		 * All code wrapped in this statement will be only included in the premium code.
		 *
		 * @return bool
		 */
		function can_use_premium_code__premium_only() {
			return $this->is_premium() && $this->can_use_premium_code();
		}

		#endregion

		#----------------------------------------------------------------------------------
		#region Trial
		#----------------------------------------------------------------------------------

		/**
		 * Check if the user in a trial.
		 *
		 * @return bool
		 */
		function is_trial() {
			return false;
		}

		/**
		 * Check if trial already utilized.
		 *
		 *
		 * @return bool
		 */
		function is_trial_utilized() {
			return false;
		}

		#endregion

		#----------------------------------------------------------------------------------
		#region Plans
		#----------------------------------------------------------------------------------

		/**
		 * Check if the user is on the free plan of the product.
		 *
		 * @return bool
		 */
		function is_free_plan() {
			return false;
		}

		/**
		 * Check if params has any release on Freemius,
		 * or all params's code is on WordPress.org (Serviceware).
		 *
		 * @return bool
		 */
		function has_release_on_freemius() {
			return false;
		}

		/**
		 * Checks if it's a freemium params.
		 *
		 * @return bool
		 */
		function is_freemium() {
			return false;
		}

		/**
		 * Get params's upgrade URL.
		 *
		 * @return bool
		 */
		function get_upgrade_url() {

			if ( ! empty( $this->core->market_product()->url ) ) {
				return $this->core->market_product()->url;
			}

			return '';
		}

		/**
		 * Get params's license URL.
		 *
		 * @return string
		 */
		function get_account_url( $action = false, $params = array(), $add_action_nonce = true ) {

			return $this->core->plugin_admin_url( '', array( 'tab' => 'license' ) );
		}

		#----------------------------------------------------------------------------------
		#dummy freemius wrapper functions to avoid errors
		#----------------------------------------------------------------------------------

		function set_basename( $is_premium, $caller ) {

		}

		function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {

			add_action( $tag, $function_to_add, $priority, $accepted_args );
		}

		function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {

			add_filter( $tag, $function_to_add, $priority, $accepted_args );
		}

		function apply_filters( $tag, $value ) {

			return apply_filters( $tag, $value );
		}
	}
}
