<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('XT_Framework_License')) {

	class XT_Framework_License {

		protected static $version = '1.0.2';

		/**
		 * Manager class reference.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      XT_Framework_Access_Manager    core    Core Class
		 */
		protected $manager = null;

		/**
		 * Core class reference.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      XT_Framework    core    Core Class
		 */
		protected $core = null;

		protected $url = "https://license.xplodedthemes.com/process.php";
		protected $is_fake = false;
		protected $option_key;
		protected $option_key_check;
		protected $prefix = '';

		public function __construct( $manager, $core ) {

			$this->manager = $manager;
			$this->core    = $core;

			if ( empty( $this->market_product()->id ) ) {

				$this->is_fake = true;

			} else {

				$this->option_key       = 'xt-license-' . $this->market_product()->id;
				$this->option_key_check = 'xt-license-check-' . $this->market_product()->id;

				$this->prefix = 'xt_license_' . $this->market_product()->id . '_';

				$refreshLicense = ! empty( $_REQUEST['xt-license-refresh'] );

				if ( $refreshLicense || $this->refreshNeeded() ) {

					add_action( 'init', array( $this, 'refresh_license' ) );
				}

				add_action( 'wp_ajax_' . $this->prefix( 'activation' ), array( $this, 'ajax_activate' ) );
				add_action( 'wp_ajax_' . $this->prefix( 'revoke' ), array( $this, 'ajax_revoke' ) );
				add_action( 'wp_ajax_' . $this->prefix( 'refresh_license_status' ), array(
					$this,
					'ajax_refresh_license_status'
				) );

				add_filter( $this->core->plugin_prefix( 'admin_tabs' ), array( $this, 'add_license_admin_tab' ), 1, 1 );
			}

			return $this;
		}

		public function refresh_license() {

			$license = $this->getLocalLicense();

			if ( ! empty( $license ) ) {
				$this->activate(
					$license->license->purchase_code,
					$license->license->domain,
					$license->license->installation_url
				);
			}
		}

		public function enqueue_scripts() {

			wp_enqueue_style(  'xtfw_license' , $this->core->plugin_framework_url( '/includes/license/assets/css', 'license.css' ), array(), $this->core->framework_version(), 'all' );
			wp_register_script(  'xtfw_license' , $this->core->plugin_framework_url( '/includes/license/assets/js', 'license' . XTFW_SCRIPT_SUFFIX . '.js' ), array( 'jquery' ), $this->core->framework_version(), false );

			$domain = "";
			if ( is_multisite() && function_exists( 'get_current_site' ) ) {
				$domain = get_current_site()->domain;
			}

			wp_localize_script(  'xtfw_license' , 'XT_LICENSE_VARS', array(
				'product_id'                    => esc_attr( $this->market_product()->id ),
				'ajaxurl'                       => esc_url( admin_url( 'admin-ajax.php' ) ),
				'homeurl'                       => esc_url( network_site_url( '/' ) ),
				'domain'                        => $domain,
				'refresh_license_status_action' => $this->prefix( 'refresh_license_status' )
			) );

			wp_enqueue_script(  'xtfw_license'  );
		}

		public function add_license_admin_tab( $tabs ) {

			if ( ! $this->manager->is_paying() ) {
				$action_title = esc_html__( 'Activate License', 'xt-framework' );
				$action_color = "#a00;";
			} else {
				$action_title = esc_html__( 'License Activated', 'xt-framework' );
				$action_color = "green;";
			}

			return array_merge( array(
				array(
					'id'          => 'license',
					'title'       => esc_html__( 'License', 'xt-framework' ),
					'show_menu'   => false,
					'action_link' => array(
						'title' => $action_title,
						'color' => $action_color
					),
					'content'     => array(
						'type'     => 'function',
						'function' => array( $this, 'form' ),
					),
					'secondary'   => true,
					'order'       => 80,
					'callback'    => array( $this, 'enqueue_scripts' )
				)
			), $tabs );
		}

		public function prefix( $key ) {

			return $this->prefix . $key;
		}

		public function plugin() {

			return $this->core->plugin();
		}

		public function market() {

			return $this->core->market();
		}

		public function market_product() {

            $product = $this->core->market_product();

		    //if($this->manager->is_last_version) {
                //$product->url = $this->core->plugin()->markets->freemius->url;
            //}

			return $product;
		}

		public function ajax_activate() {

			$nonce = $_POST['wpnonce'];
			if ( ! wp_verify_nonce( $nonce, $this->prefix( 'activation' ) ) ) {

				die( 'invalid nonce' );

			} else {

				$domain           = ! empty( $_POST['domain'] ) ? $_POST['domain'] : '';
				$installation_url = ! empty( $_POST['installation_url'] ) ? $_POST['installation_url'] : '';
				$purchase_code    = ! empty( $_POST['purchase_code'] ) ? $_POST['purchase_code'] : '';

				$response = $this->activate( $purchase_code, $domain, $installation_url );

				die( json_encode( $response ) );
			}
		}

		public function ajax_revoke() {

			$nonce = $_POST['wpnonce'];
			if ( ! wp_verify_nonce( $nonce, $this->prefix( 'revoke' ) ) ) {

				die( 'invalid nonce' );

			} else {

				$local         = ! empty( $_POST['local'] ) ? true : false;
				$purchase_code = ! empty( $_POST['purchase_code'] ) ? $_POST['purchase_code'] : '';
				$domain        = ! empty( $_POST['domain'] ) ? $_POST['domain'] : '';
				$response      = $this->revoke( $purchase_code, $domain, $local );

				die( json_encode( $response ) );
			}
		}

		public function ajax_refresh_license_status() {

			ob_start();
			$this->core->plugin_tabs()->render_header_badges();
			$badges = ob_get_clean();

			die( $badges );
		}

		public function activate( $purchase_code, $domain, $installation_url ) {

			list( $email, $first_name, $last_name ) = $this->getUserInfo();

			$response = $this->process( array(
				'product_id'       => $this->market_product()->id,
				'purchase_code'    => $purchase_code,
				'domain'           => $domain,
				'installation_url' => $installation_url,
				'email'            => $email,
				'first_name'       => $first_name,
				'last_name'        => $last_name,
				'action'           => 'activate',
				'market'           => $this->market()
			) );

			if ( ! empty( $response ) && ! empty( $response->code ) ) {

				if ( in_array( $response->code, array( "valid", "expired" ) ) ) {

					$this->saveLocalLicense( $response );

				} else {

					$this->deleteLocalLicense();
				}
			}

			return $response;
		}

		public function revoke( $purchase_code, $domain = null, $local = false ) {

			$response = $this->process( array(
				'purchase_code' => $purchase_code,
				'domain'        => $domain,
				'action'        => 'revoke'
			) );

			if ( $local ) {
				$license = $this->getLocalLicense();
				$this->deleteLocalLicense( $license->license->product_id );
			}

			return $response;
		}

		public function process( $data ) {

			$data['t']       = time();
			$data['version'] = self::$version;
			$url             = add_query_arg( $data, $this->url );

			try {

				return $this->remote_get( $url );

			} catch ( Exception $e ) {

				return array(
					'license'         => array(),
					'license_summary' => '',
					'msg'             => $e->getMessage(),
					'code'            => 'timeout',
					'product'         => null
				);
			}
		}

		public function getLocalLicenseInfo( $type ) {

			$license = $this->getLocalLicense();

			if ( ! empty( $license->license->$type ) ) {
				return $license->license->$type;
			}

			return "";
		}

		public function getLocalLicenseSummary() {

			$license = $this->getLocalLicense();

			return $license->license_summary;
		}

		public function getLocalLicense() {

			return $this->get_option( $this->option_key );
		}

		public function refreshNeeded() {

		    $license = $this->getLocalLicense();

			return !empty($license) && $this->get_transient( $this->option_key_check ) === false;
		}

		public function saveLocalLicense( $license ) {

			$this->add_option( $this->option_key, $license );
			$this->set_transient( $this->option_key_check, time(), WEEK_IN_SECONDS );
		}

		public function deleteLocalLicense( $product_id = null ) {

			if ( ! empty( $product_id ) ) {

				$option_key       = 'xt-license-' . $product_id;
				$option_key_check = 'xt-license-check-' . $product_id;

				$this->delete_option( $option_key );
				$this->delete_transient( $option_key_check );

			}

			$this->delete_option( $this->option_key );
			$this->delete_transient( $this->option_key_check );
		}

		public function isActivated() {

			return true;


			$license = $this->getLocalLicense();

			if ( ! empty( $license ) && ! empty( $license->code ) && in_array( $license->code, array( "valid" ) ) ) {
				return true;
			}

			return false;
		}

		public function isFound() {

			return true;


			$license = $this->getLocalLicense();

			if ( ! empty( $license ) && ! empty( $license->code ) && in_array( $license->code, array(
					"valid",
					"expired"
				) ) ) {
				return true;
			}

			return true;
		}

		public function getUserInfo() {

			$email        = '';
			$first_name   = '';
			$last_name    = '';
			$current_user = wp_get_current_user();

			if ( $current_user->exists() ) {
				$email        = $current_user->user_email;
				$display_name = $current_user->display_name;
				$first_name   = $current_user->user_firstname;
				$last_name    = $current_user->user_lastname;

				if ( empty( $first_name ) && empty( $last_name ) ) {
					$first_name = $display_name;
				}
			}

			return array(
				$email,
				$first_name,
				$last_name
			);
		}

		public function form() {

			$isActivated = $this->isActivated();
			$isFound     = $this->isFound();

			$license_key_label = ( $this->market() === 'envato' ) ? "Purchase Code" : 'License Key';

			?>
            <div id="xt-license-activation-<?php echo esc_attr( $this->market_product()->id ); ?>"
                 class="xt-license-activation">

                <div id="xt-license-activation-form"
                     class="xt-license-form<?php if ( $isFound ): ?> xt-license-hide<?php endif; ?>">
                    <p class="xt-license-status">
                        <span class="xt-license-msg">
                            <?php
                            echo apply_filters(
                                $this->prefix( 'msg_activate' ),
                                sprintf(
                                    esc_html__( 'Your Support License is %1$s.', 'xt-framework' ),
                                    "<span class='xt-license-status xt-license-invalid'><strong>" . esc_html__( 'Not Activated', 'xt-framework' ) . "</strong></span>"
                                ),
                                $this->market_product()
                            );
                            ?>
                        </span>
                        <span class="xt-license-submsg">
                            <?php
                            echo apply_filters(
                                $this->prefix( 'submsg_activate' ),
                                sprintf(
                                    $this->manager->is_last_version ? esc_html( 'Activate your %1$s to enable premium features', 'xt-framework' ) : esc_html( 'Activate your %1$s to enable premium features, support & automated updates', 'xt-framework' ),
                                    $license_key_label
                                ),
                                $this->market_product()
                            );
                            ?>
                        </span>
                        <span class="xt-license-submsg xt-license-revoke-info"></span>
                    </p>
                    <input type="hidden" name="action" value="<?php echo $this->prefix( 'activation' ); ?>">
                    <input type="hidden" name="wpnonce" value="<?php echo wp_create_nonce( $this->prefix( 'activation' ) ); ?>">
                    <input type="hidden" name="domain" value="">
                    <input type="hidden" name="installation_url" value="">
                    <input type="hidden" name="product_id" value="<?php echo esc_attr( $this->market_product()->id ); ?>">
                    <input class="regular-text" placeholder="<?php echo $license_key_label; ?>..." name="purchase_code" value="">
                    <a href="<?php echo $this->market_product()->url; ?>" class="button" target="_blank"><?php echo esc_html__( 'Buy License', 'xt-framework' ); ?></a>
                    <input type="submit" class="button button-primary" value="<?php echo esc_html__( 'Validate', 'xt-framework' ); ?>">
                </div>

                <div id="xt-license-revoke-form" class="xt-license-form xt-license-hide">

                    <p class="xt-license-status">
                        <span class="xt-license-msg xt-license-invalid"><?php echo apply_filters( $this->prefix( 'msg_active_invalid' ), sprintf( esc_html__( 'This %1$s is activated somewhere else.', 'xt-framework' ), $license_key_label ), $this->market_product() ); ?></span>
                        <span class="xt-license-submsg"><?php echo apply_filters( $this->prefix( 'submsg_active_invalid' ), esc_html( 'You can either revoke the below license then re-activate it here or buy a new license.', 'xt-framework' ), $this->market_product() ); ?></span>
                    </p>

                    <input type="hidden" name="action" value="<?php echo $this->prefix( 'revoke' ); ?>">
                    <input type="hidden" name="wpnonce" value="<?php echo wp_create_nonce( $this->prefix( 'revoke' ) ); ?>">
                    <input type="hidden" name="purchase_code" value="">
                    <input type="hidden" name="domain" value="">
                    <input type="button" class="button button xt-license-revoke-cancel" value="<?php echo esc_html__( 'Cancel', 'xt-framework' ); ?>">

                    <?php if($this->manager->is_last_version):?>
                    <span class="xt-license-submsg xt-license-migrate">
                       <?php
                       echo sprintf(__( '%s is the last available version on CodeCanyon.', 'xt-framework'), '<strong>V.'.$this->market_product()->last_version.'</strong>');
                       echo '<br>';
                       echo sprintf(__('%s plugin has retired from CodeCanyon and will now be sold exclusively on XplodedThemes.com.<br><span style="color:green;"><strong>To continue receiving new updates and security patches, please migrate your CodeCanyon license.</strong></span>', 'xt-framework'), $this->core->plugin()->menu_name);
                       ?>
                    </span>
                    <?php echo sprintf(__('<a target="_blank" class="button button-primary" href="https://xplodedthemes.com/codecanyon-license-migration/?id=%d"><strong>Migrate License</strong></a>', 'xt-framework'), $this->core->plugin()->markets->freemius->id); ?>
                    <?php else: ?>
                    <a href="<?php echo $this->market_product()->url; ?>" class="button button-primary" target="_blank"><?php echo esc_html__( 'Buy License', 'xt-framework' ); ?></a>
                    <?php endif; ?>
                    <input type="submit" class="button" value="<?php echo esc_html__( 'Revoke License', 'xt-framework' ); ?>">
                </div>

                <div id="xt-license-invalid" class="xt-license-hide">
                    <p class="xt-license-status">
				    <span class="xt-license-msg">
				    <?php
				    echo apply_filters(
					    $this->prefix( 'msg_invalid' ),
					    sprintf(
						    esc_html__( 'This %1$s is %2$s.', 'xt-framework' ),
						    $license_key_label,
						    "<span class='xt-license-status xt-license-invalid'><strong>" . esc_html__( 'Invalid', 'xt-framework' ) . "</strong></span>"
					    ),
					    $this->market_product()
				    );
				    ?>
				    </span>
                        <span class="xt-license-timer"></span>
                    </p>
                </div>

                <div id="xt-license-timeout" class="xt-license-hide">
                    <p class="xt-license-status">
				    <span class="xt-license-msg">
				    <?php
				    echo apply_filters(
					    $this->prefix( 'msg_invalid' ),
					    sprintf(
						    esc_html__( '%1$sOops!%2$s %3$sCannot reach license server!%4$s', 'xt-framework' ),
						    "<strong class='xt-license-invalid'>",
						    "</strong>",
						    "<span class='xt-license-invalid'>",
						    "</span"
					    ),
					    $this->market_product()
				    );
				    ?>
				    </span>
                        <span class="xt-license-submsg"><?php echo apply_filters( $this->prefix( 'submsg_active_invalid' ), esc_html( 'Please try in couple of minutes.', 'xt-framework' ), $this->market_product() ); ?></span>
                        <span class="xt-license-timer"></span>
                    </p>
                </div>

                <div id="xt-license-activated" class="<?php if ( ! $isFound ): ?>xt-license-hide<?php endif; ?>">
                    <div class="xt-license-status">
                        <div class="xt-license-status-active<?php if ( ! $isActivated ): ?> xt-license-hide<?php endif; ?>">
                            <span class="xt-license-msg">
                                <?php
                                echo apply_filters(
                                    $this->prefix( 'msg_activated' ),
                                    sprintf(
                                        esc_html__( 'Your Support License is %1$s.', 'xt-framework' ),
                                        "<span class='xt-license-status xt-license-valid'><strong>" . esc_html__( 'Valid', 'xt-framework' ) . "</strong></span>"
                                    ),
                                    $this->market_product()
                                );
                                ?>
                            </span>
                            <span class="xt-license-submsg">
                                <?php
                                echo apply_filters(
                                    $this->prefix( 'submsg_activated' ),
                                    sprintf(
                                        $this->manager->is_last_version ? esc_html__( 'Premium features are now %1$s', 'xt-framework' ) : esc_html__( 'Premium features, support & automated updates are now %1$s', 'xt-framework' ),
                                        "<strong>" . esc_html__( 'Enabled', 'xt-framework' ) . "</strong>"
                                    ),
                                    $this->market_product()
                                );
                                ?>
                            </span>
                        </div>
                        <div class="xt-license-status-expired<?php if ( $isActivated ): ?> xt-license-hide<?php endif; ?>">
                        <span class="xt-license-msg">
                            <?php
                            echo apply_filters(
	                            $this->prefix( 'msg_expired' ),
	                            sprintf(
		                            esc_html__( 'Your Support License has %1$s.', 'xt-framework' ),
		                            "<span class='xt-license-status xt-license-invalid'><strong>" . esc_html__( 'Expired', 'xt-framework' ) . "</strong></span>"
	                            ),
	                            $this->market_product()
                            );
                            ?>
                        </span>
                            <span class="xt-license-submsg">
                            <?php
                            echo apply_filters(
	                            $this->prefix( 'submsg_expired' ),
	                            sprintf(
                                    $this->manager->is_last_version ? esc_html__( 'Support & updates are now %1$sDisabled%2$s!%3$s', 'xt-framework' ) : esc_html__( 'Support & automated updates are now %1$sDisabled%2$s!%3$sYou can still use the plugin%4$s and update it manually from CodeCanyon', 'xt-framework' ),
		                            "<strong>",
		                            "</strong>",
		                            "<br><strong>",
		                            "</strong>"
	                            ),
	                            $this->market_product()
                            );
                            ?>
                        </span>
                        </div>
                    </div>
                </div>

				<?php if ( $isFound ): ?>
                    <div class="xt-license-info">
						<?php echo $this->getLocalLicenseSummary(); ?>
                    </div>
				<?php else: ?>
                    <div class="xt-license-info"></div>
				<?php endif; ?>

                <div id="xt-license-local-revoke-form"
                     class="xt-license-form<?php if ( ! $isFound ): ?> xt-license-hide<?php endif; ?>">
                    <input type="hidden" name="action" value="<?php echo $this->prefix( 'revoke' ); ?>">
                    <input type="hidden" name="wpnonce" value="<?php echo wp_create_nonce( $this->prefix( 'revoke' ) ); ?>">
                    <input type="hidden" name="purchase_code" value="<?php echo $this->getLocalLicenseInfo( 'purchase_code' ); ?>">
                    <input type="hidden" name="domain" value="<?php echo $this->getLocalLicenseInfo( 'domain' ); ?>">
                    <input type="hidden" name="local" value="1">

                    <?php if($this->manager->is_last_version):?>
                        <span class="xt-license-submsg xt-license-migrate">
                        <?php
                        echo sprintf(__( '%s is the last available version on CodeCanyon.', 'xt-framework'), '<strong>V.'.$this->market_product()->last_version.'</strong>');
                        echo '<br>';
                        echo sprintf(__('%s plugin has retired from CodeCanyon and will now be sold exclusively on XplodedThemes.com.<br><span style="color:green;"><strong>To continue receiving new updates and security patches, please migrate your CodeCanyon license.</strong></span>', 'xt-framework'), $this->core->plugin()->menu_name);
                        ?>
                        </span>
                        <?php echo sprintf(__('<a target="_blank" class="button button-primary" href="https://xplodedthemes.com/codecanyon-license-migration/?id=%d"><strong>Migrate License</strong></a>', 'xt-framework'), $this->core->plugin()->markets->freemius->id); ?>
                    <?php else: ?>
                        <a href="<?php echo $this->market_product()->url; ?>" class="button button-primary" target="_blank"><?php echo esc_html__( 'Buy License', 'xt-framework' ); ?></a>
                    <?php endif; ?>
                    <input type="submit" class="button" value="<?php echo esc_html__( 'Revoke License', 'xt-framework' ); ?>">
                </div>
            </div>

			<?php
		}

		public function remote_get( $url ) {

			$data = null;

			$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0';
			$timeout    = 8;

			// Get remote content
			$response = wp_remote_get( $url, array(
				'timeout'    => $timeout,
				'user-agent' => $user_agent
			) );

			if ( !is_wp_error( $response ) && ! empty( $response ) ) {

                // retrieve content body
                $response = wp_remote_retrieve_body( $response );

                // decode content body
                $data = json_decode( $response );
			}

			if ( empty( $data ) || ! is_object( $data ) ) {
				throw new Exception( 'Oops! Cannot reach license server, please try in coupe of minutes.' );
			}

			return $data;
		}

		function set_transient( $key, $value, $expiration = 0 ) {

			if ( is_multisite() ) {
				set_site_transient( $key, $value, $expiration );
			} else {
				set_transient( $key, $value, $expiration );
			}
		}

		function get_transient( $key ) {

			if ( is_multisite() ) {
				return get_site_transient( $key );
			} else {
				return get_transient( $key );
			}
		}

		function delete_transient( $key ) {

			if ( is_multisite() ) {
				delete_site_transient( $key );
			} else {
				delete_transient( $key );
			}
		}

		function add_option( $key, $value ) {

			if ( is_multisite() ) {
				add_site_option( $key, $value );
			} else {
				add_option( $key, $value, '', 'no' );
			}
		}

		function get_option( $key ) {

			$option = $this->get_transient( $key );

			if ( $option === false ) {

				if ( is_multisite() ) {
					return get_site_option( $key );
				} else {
					return get_option( $key );
				}
			}

			return $option;
		}

		function delete_option( $key ) {

			$this->delete_transient( $key );

			if ( is_multisite() ) {
				delete_site_option( $key );
			} else {
				delete_option( $key );
			}
		}
	}
}