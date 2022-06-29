<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Ajax' )) {

	class XT_Framework_Ajax {

		/**
		 * Core class reference.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      XT_Framework   $core    Core Class
		 */
		protected $core;

        /**
         * Ajax events to be added
         *
         * @since    1.0.0
         * @access   private
         * @var      array    $ajax_add_events
         */
		protected $ajax_add_events = array();

        /**
         * Ajax events to be removed
         *
         * @since    1.0.0
         * @access   private
         * @var      array    $ajax_remove_events
         */
		protected $ajax_remove_events = array();

		/**
		 * Construct.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @var      XT_Framework $core Core Class
		 */
		public function __construct( $core ) {

			$this->core = $core;

            add_action( 'init', array( $this, 'define_ajax' ), 0 );
            add_action( 'init', array( $this, 'init' ), 9999 );
            add_action( 'template_redirect', array( $this, 'do_ajax' ), 0 );
		}


        /**
         * Get Prefixed Admin Ajax Action.
         *
         * @param string $action
         *
         * @return string
         */
        public function get_ajax_action( $action ) {

            return 'xtfw_'.$this->core->plugin_short_prefix().'_'.$action;
        }

        /**
         * Get Frontend Ajax Endpoint.
         *
         * @param string $request Optional.
         *
         * @return string
         */
        public function get_endpoint( $request = '' ) {
            return esc_url_raw( apply_filters( 'xtfw_ajax_get_endpoint', add_query_arg( 'xtfw-ajax', $request, home_url('/') ), $request ) );
        }

        /**
         * Set AJAX constant and headers.
         */
        public function define_ajax() {
            // phpcs:disable
            if ( ! empty( $_GET['xtfw-ajax'] ) ) {
                xtfw_maybe_define_constant( 'DOING_AJAX', true );
                xtfw_maybe_define_constant( 'XTFW_DOING_AJAX', true );
                if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
                    @ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON.
                }
                $GLOBALS['wpdb']->hide_errors();
            }
            // phpcs:enable
        }

		/**
		 * Init custom ajax events
		 */
		public function init() {

			$this->ajax_add_events = apply_filters($this->core->plugin_prefix('ajax_add_events'), $this->ajax_add_events, $this);
			$this->ajax_remove_events = apply_filters($this->core->plugin_prefix('ajax_remove_events'), $this->ajax_remove_events, $this);

			// Add events
			foreach ( $this->ajax_add_events as $event) {

				add_action( 'wp_ajax_xtfw_' . $event['function'], $event['callback'] );

				if ( !empty($event['nopriv']) ) {
					add_action( 'wp_ajax_nopriv_xtfw_' . $event['function'], $event['callback'] );
					// WC AJAX can be used for frontend ajax requests
					add_action( 'xtfw_ajax_' . $event['function'], $event['callback'] );
				}
			}

			// Remove events
			foreach ( $this->ajax_remove_events as $event) {

				remove_action( 'wp_ajax_xtfw_' . $event['function'], $event['callback'] );

				if ( !empty($event['nopriv']) ) {
					remove_action( 'wp_ajax_nopriv_xtfw_' . $event['function'], $event['callback'] );
					// WC AJAX can be used for frontend ajax requests
					remove_action( 'xtfw_ajax_' . $event['function'], $event['callback'] );
				}
			}
		}

		/**
		 * Get ajax url
		 *
		 * @var   string $endpoint
		 * @return string $url
		 */
		public function get_ajax_url($endpoint = null) {

			$url = urldecode(add_query_arg( 'xtfw-ajax', '%%endpoint%%', home_url( '/' ) ));

			if(!empty($endpoint)) {
				$url = str_replace('%%endpoint%%', $endpoint, $url);
			}

			return $url;
		}

        /**
         * Send headers for XTFW Ajax Requests.
         *
         * @since 2.5.0
         */
        private function ajax_headers() {
            if ( ! headers_sent() ) {
                send_origin_headers();
                send_nosniff_header();
                wc_nocache_headers();
                header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
                header( 'X-Robots-Tag: noindex' );
                status_header( 200 );
            } elseif ( xtfw_debug_mode() ) {
                headers_sent( $file, $line );
                trigger_error( "xtfw_ajax_headers cannot set headers - headers already sent by {$file} on line {$line}" ); // @codingStandardsIgnoreLine
            }
        }

        /**
         * Check for XTFW Ajax request and fire action.
         */
        public function do_ajax() {

            global $wp_query;

            // phpcs:disable WordPress.Security.NonceVerification.Recommended
            if ( ! empty( $_GET['xtfw-ajax'] ) ) {
                $wp_query->set( 'xtfw-ajax', sanitize_text_field( wp_unslash( $_GET['xtfw-ajax'] ) ) );
            }

            $action = $wp_query->get( 'xtfw-ajax' );

            if ( $action ) {
                $this->ajax_headers();
                $action = sanitize_text_field( $action );
                do_action( 'xtfw_ajax_' . $action );

                wp_die();
            }
            // phpcs:enable
        }

        /**
         * Get a refreshed ajax fragment
         */
        public function get_refreshed_fragments() {
            ob_start();

            $data = apply_filters('xtfw_ajax_fragments', array());

            wp_send_json( $data );
        }
    }

}