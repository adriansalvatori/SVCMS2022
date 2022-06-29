<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VillaTheme_Support_Pro' ) ) {

	/**
	 * Class VillaTheme_Support_Pro
	 * 1.1.4
	 *
	 */
	class VillaTheme_Support_Pro {
		protected $plugin_base_name;

		public function __construct( $data ) {
			$this->data               = array();
			$this->data['support']    = $data['support'];
			$this->data['docs']       = $data['docs'];
			$this->data['review']     = $data['review'];
			$this->data['css_url']    = $data['css'];
			$this->data['images_url'] = $data['image'];
			$this->data['slug']       = $data['slug'];
			$this->data['menu_slug']  = $data['menu_slug'];
			$this->data['version']    = isset( $data['version'] ) ? $data['version'] : '1.0.0';
			add_action( 'villatheme_support_' . $this->data['slug'], array( $this, 'villatheme_support' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9999 );
			$this->plugin_base_name = "{$this->data['slug']}/{$this->data['slug']}.php";
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			/*Add toolbar*/
			add_action( 'admin_bar_menu', array( $this, 'add_toolbar' ), 100 );
		}

		/**Add link to Documentation, Support and Reviews
		 *
		 * @param $links
		 * @param $file
		 *
		 * @return array
		 */
		public function plugin_row_meta( $links, $file ) {
			if ( $this->plugin_base_name === $file ) {
				$row_meta = array(
					'support' => '<a href="' . esc_attr( esc_url( $this->data['support'] ) ) . '" target="_blank" title="' . esc_attr__( 'VillaTheme Support', $this->data['slug'] ) . '">' . esc_html__( 'Support', $this->data['slug'] ) . '</a>',
					'review'  => '<a href="' . esc_attr( esc_url( $this->data['review'] ) ) . '" target="_blank" title="' . esc_attr__( 'Rate this plugin', $this->data['slug'] ) . '">' . esc_html__( 'Reviews', $this->data['slug'] ) . '</a>',
				);
				if ( ! empty( $this->data['docs'] ) ) {
					$row_meta['docs'] = '<a href="' . esc_attr( esc_url( $this->data['docs'] ) ) . '" target="_blank" title="' . esc_attr__( 'Plugin Documentation', $this->data['slug'] ) . '">' . esc_html__( 'Docs', $this->data['slug'] ) . '</a>';
				}

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		public function admin_init() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( ! isset( $_GET['_villatheme_nonce'] ) ) {
				return;
			}
			if ( wp_verify_nonce( sanitize_text_field( $_GET['_villatheme_nonce'] ), 'villatheme_hide_toolbar' ) ) {
				update_option( 'villatheme_hide_admin_toolbar', time() );
				wp_safe_redirect( esc_url_raw( remove_query_arg( array( '_villatheme_nonce' ) ) ) );
				exit();
			}
			if ( wp_verify_nonce( sanitize_text_field( $_GET['_villatheme_nonce'] ), 'villatheme_show_toolbar' ) ) {
				delete_option( 'villatheme_hide_admin_toolbar' );
				wp_safe_redirect( esc_url_raw( remove_query_arg( array( '_villatheme_nonce' ) ) ) );
				exit();
			}
			if ( wp_verify_nonce( sanitize_text_field( $_GET['_villatheme_nonce'] ), 'villatheme_hide_banner' ) ) {
				update_option( 'villatheme_hide_dashboard_banner', time() );
				wp_safe_redirect( esc_url_raw( remove_query_arg( array( '_villatheme_nonce' ) ) ) );
				exit();
			}
			if ( wp_verify_nonce( sanitize_text_field( $_GET['_villatheme_nonce'] ), 'villatheme_show_banner' ) ) {
				delete_option( 'villatheme_hide_dashboard_banner' );
				wp_safe_redirect( esc_url_raw( remove_query_arg( array( '_villatheme_nonce' ) ) ) );
				exit();
			}
			if ( wp_verify_nonce( $_GET['_villatheme_nonce'], 'hide_maybe' ) ) {
				set_transient( $this->data['slug'] . $this->data['version'] . 'hide_maybe', 1, 2592000 );
			}
		}

		/**
		 * Add Extension page
		 */
		public function admin_menu() {
			add_submenu_page( $this->data['menu_slug'], esc_html__( 'Extensions', $this->data['slug'] ), esc_html__( 'Extensions', $this->data['slug'] ), 'manage_options', $this->data['slug'] . '-extensions', array(
				$this,
				'page_callback'
			) );
		}

		/**
		 * Extensions page
		 */
		public function page_callback() {
			$ads = '';
			?>
            <div class="villatheme-extension-page">
                <div class="villatheme-extension-top">
                    <h2><?php echo esc_html__( 'THE BEST PLUGINS FOR WOOCOMMERCE', $this->data['slug'] ) ?></h2>
                    <p><?php echo esc_html__( 'Our plugins are constantly updated and thanks to your feedback. We add new features on a daily basis. Try our live demo and start increasing the conversions on your ecommerce right away.', $this->data['slug'] ) ?></p>
                </div>
                <div class="villatheme-extension-content">
					<?php
					$feeds = get_transient( 'villatheme_ads' );
					if ( ! $feeds ) {
						$request = wp_remote_get( 'https://villatheme.com/wp-json/info/v1', array(
							'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
							'timeout'    => 10,
						) );
						if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
							$ads = $request['body'];
							set_transient( 'villatheme_ads', $ads, 86400 );
						}

					} else {
						$ads = $feeds;
					}
					if ( $ads ) {
						$ads = json_decode( $ads );
						$ads = array_filter( $ads );
					} else {
						return;
					}
					if ( is_array( $ads ) && count( $ads ) ) {
						foreach ( $ads as $ad ) {
							?>
                            <div class="villatheme-col-3">
								<?php
								if ( $ad->image ) { ?>
                                    <div class="villatheme-item-image">
                                        <img src="<?php echo esc_attr( esc_url( $ad->image ) ) ?>">
                                    </div>
									<?php
								}
								?>
								<?php
								if ( $ad->title ) { ?>
                                    <div class="villatheme-item-title">
										<?php
										if ( $ad->link ) { ?>
                                        <a target="_blank"
                                           href="<?php echo esc_attr( esc_url( $ad->link ) ) ?>">
											<?php } ?>
											<?php echo esc_html( $ad->title ) ?>
											<?php if ( $ad->link ) { ?>
                                        </a>
									<?php
									}
									?>
                                    </div>
									<?php
								}
								?>
                                <div class="villatheme-item-controls">
                                    <div class="villatheme-item-controls-inner">
										<?php
										if ( $ad->link ) {
											?>
                                            <a class="button button-primary" target="_blank"
                                               href="<?php echo esc_attr( esc_url( $ad->link ) ) ?>"><?php echo esc_html__( 'Download', $this->data['slug'] ) ?></a>
											<?php
										}
										if ( $ad->demo_url ) {
											?>
                                            <a class="button" target="_blank"
                                               href="<?php echo esc_attr( esc_url( $ad->demo_url ) ) ?>"><?php echo esc_html__( 'Demo', $this->data['slug'] ) ?></a>
											<?php
										}
										if ( $ad->free_url ) {
											?>
                                            <a class="button" target="_blank"
                                               href="<?php echo esc_attr( esc_url( $ad->free_url ) ) ?>"><?php echo esc_html__( 'Trial', $this->data['slug'] ) ?></a>
											<?php
										}
										?>
                                    </div>
                                </div>
                            </div>
							<?php
						}
					}
					?>
                </div>
            </div>
			<?php
		}

		/**
		 * Init script
		 */
		public function scripts() {
			wp_enqueue_style( 'villatheme-support', $this->data['css_url'] . 'villatheme-support.css' );
		}

		/**
		 *
		 */
		public function villatheme_support() {
			$hide_banner = get_option( 'villatheme_hide_dashboard_banner' );
			?>
            <div id="villatheme-support" class="vi-ui form segment">
                <h3><?php echo esc_html__( 'MAYBE YOU LIKE', $this->data['slug'] ) ?>
                    &nbsp;&nbsp;
                    <a class="vi-ui button labeled icon tiny" target="_blank"
                       href="<?php echo esc_attr( esc_url( $this->data['docs'] ) ) ?>">
                        <i class="book icon"></i>
						<?php esc_html_e( 'Documentation', $this->data['slug'] ) ?>
                    </a>
                    <a class="vi-ui button inverted labeled icon orange tiny" target="_blank"
                       href="<?php echo esc_attr( esc_url( $this->data['review'] ) ) ?>">
                        <i class="star icon"></i>
						<?php esc_html_e( 'Review', $this->data['slug'] ) ?>
                    </a>
                    <a class="vi-ui button labeled icon green tiny" target="_blank"
                       href="<?php echo esc_attr( esc_url( $this->data['support'] ) ) ?>">
                        <i class="users icon"></i>
						<?php esc_html_e( 'Request Support', $this->data['slug'] ) ?>
                    </a>
					<?php
					if ( get_option( 'villatheme_hide_admin_toolbar' ) ) {
						?>
                        <a class="vi-ui button labeled icon blue inverted tiny" target="_self"
                           title="<?php echo esc_attr( 'VillaTheme toolbar helps you access all VillaTheme items quickly' ) ?>"
                           href="<?php echo esc_url( add_query_arg( array( '_villatheme_nonce' => wp_create_nonce( 'villatheme_show_toolbar' ) ) ) ) ?>">
                            <i class="toggle on icon"></i>
							<?php echo esc_html( 'Show Toolbar' ) ?>
                        </a>
						<?php
					}
					if ( $hide_banner ) {
						$nonce = wp_create_nonce( 'villatheme_show_banner' );
						$icon  = 'on';
						$text  = 'Show Banner';
					} else {
						$nonce = wp_create_nonce( 'villatheme_hide_banner' );
						$icon  = 'off';
						$text  = 'Hide Banner';
					}
					?>
                    <a class="vi-ui button labeled icon green inverted tiny" target="_self"
                       title="<?php echo esc_attr( 'Toggle recommended plugins from VillaTheme' ) ?>"
                       href="<?php echo esc_url( add_query_arg( array( '_villatheme_nonce' => $nonce ) ) ) ?>">
                        <i class="toggle <?php echo esc_attr( $icon ) ?> icon"></i>
						<?php echo esc_html( $text ) ?>
                    </a>
                </h3>
				<?php
				if ( ! $hide_banner ) {
					?>
                    <div class="fields">
						<?php
						$items = $this->get_data( $this->data['slug'] );
						if ( is_array( $items ) && count( $items ) ) {
							shuffle( $items );
							$items = array_slice( $items, 0, 12 );
							foreach ( $items as $k => $item ) {
								if ( $k % 4 == 0 && $k > 0 ) {
									echo wp_kses_post( '</div><div class="fields">' );
								}
								?>
                                <div class="four wide field">
                                    <div class="villatheme-item">
                                        <a target="_blank" href="<?php echo esc_attr( esc_url( $item->link ) ) ?>">
                                            <img src="<?php echo esc_attr( esc_url( $item->image ) ) ?>"/>
                                        </a>
                                    </div>
                                </div>
								<?php
							}
						}
						?>
                    </div>
					<?php
				}
				?>
            </div>
			<?php
		}

		/**
		 * Get data from server
		 *
		 * @param bool $slug
		 *
		 * @return array
		 */
		protected function get_data( $slug = false ) {
			$ads   = '';
			$feeds = get_transient( 'villatheme_ads' );
			if ( ! $feeds ) {
				$request = wp_remote_get( 'https://villatheme.com/wp-json/info/v1', array(
					'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
					'timeout'    => 10,
				) );
				if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
					$ads = $request['body'];
					set_transient( 'villatheme_ads', $ads, 86400 );
				}
			} else {
				$ads = $feeds;
			}
			$results = array();
			if ( $ads ) {
				$ads = json_decode( $ads );
				$ads = array_filter( $ads );
				foreach ( $ads as $ad ) {
					if ( $slug ) {
						if ( $ad->slug == $slug ) {
							continue;
						}
					}
					$item        = new stdClass();
					$item->title = $ad->title;
					$item->link  = $ad->link;
					$item->thumb = $ad->thumb;
					$item->image = $ad->image;
					$item->desc  = $ad->description;
					$results[]   = $item;
				}
			}

			return $results;
		}

		/**
		 * Add toolbar in WordPress Dashboard
		 */
		public function add_toolbar() {
			/**
			 * @var $wp_admin_bar WP_Admin_Bar
			 */
			global $wp_admin_bar;
			if ( get_option( 'villatheme_hide_admin_toolbar' ) ) {
				return;
			}
			if ( ! $wp_admin_bar->get_node( 'villatheme' ) ) {
				$wp_admin_bar->add_node( array(
					'id'    => 'villatheme',
					'title' => '<span class="ab-icon dashicons-star-filled villatheme-rotating"></span>' . 'VillaTheme',
					'href'  => '',
					'meta'  => array(
						'class' => 'villatheme-toolbar'
					),
				) );
				add_action( 'admin_bar_menu', array( $this, 'hide_toolbar_button' ), 200 );
			}
			if ( $this->data['menu_slug'] ) {
				$wp_admin_bar->add_node( array(
					'id'     => $this->data['slug'],
					'title'  => $this->get_plugin_name(),
					'parent' => 'villatheme',
					'href'   => strpos( $this->data['menu_slug'], '.php' ) === false ? admin_url( 'admin.php?page=' . $this->data['menu_slug'] ) : admin_url( $this->data['menu_slug'] ),
				) );
			}
		}

		public function hide_toolbar_button() {
			global $wp_admin_bar;
			/**
			 * @var $wp_admin_bar WP_Admin_Bar
			 */
			$warning = 'return confirm("VillaTheme toolbar helps you access all VillaTheme items quickly, do you want to hide it anyway?")';
			$wp_admin_bar->add_node( array(
				'id'     => 'villatheme_hide_toolbar',
				'title'  => '<span class="dashicons dashicons-dismiss"></span><span class="villatheme-hide-toolbar-button-title">Hide VillaTheme toolbar</span>',
				'parent' => 'villatheme',
				'href'   => add_query_arg( array( '_villatheme_nonce' => wp_create_nonce( 'villatheme_hide_toolbar' ) ) ),
				'meta'   => array(
					'onclick' => esc_js( $warning )
				),
			) );
		}

		private function get_plugin_name() {
			$plugins = get_plugins();

			return isset( $plugins[ $this->plugin_base_name ]['Title'] ) ? $plugins[ $this->plugin_base_name ]['Title'] : ucwords( str_replace( '-', ' ', $this->data['slug'] ) );
		}
	}
}