<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

/**
 * @class Frontend
 */
class Frontend {


	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'template_redirect', [ $this, 'init_shortcodes' ] );
		add_action( 'template_redirect', [ $this, 'maybe_prevent_caching' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_loaded', [ $this, 'maybe_handle_share_form' ] );
		add_action( 'woocommerce_email_customer_details', [ $this, 'maybe_display_widget_on_order_emails' ], 40, 4 );
		add_action( 'woocommerce_email_footer', [ $this, 'maybe_display_widget_on_new_account_email' ], 5 ); // priority is important here

		add_filter( 'automatewoo/mailer/styles', [ $this, 'inject_email_styles' ] );
		add_filter( 'woocommerce_email_styles', [ $this, 'inject_email_styles' ] );
		add_filter( 'storefront_customizer_css', [ $this, 'storefront_css' ] );

		if ( AW_Referrals()->options()->enabled && AW_Referrals()->options()->widget_on_order_confirmed ) {

			switch ( (string) AW_Referrals()->options()->widget_on_order_confirmed ) {
				case 'top':
					add_action( 'woocommerce_before_template_part', [ $this, 'display_share_widget_before_thankyou' ], 10, 4 );
					break;

				case 'bottom':
					add_action( 'woocommerce_thankyou', [ $this, 'display_share_widget_after_thankyou' ], 20 );
					break;
			}
		}
	}

	/**
	 * Init shortcodes. Only called on frontend.
	 *
	 * @since 2.3.2
	 */
	public function init_shortcodes() {
		add_shortcode( 'automatewoo_referrals_page', [ $this, 'get_share_page_html' ] );
		add_shortcode( 'automatewoo_referrals_share_widget', [ $this, 'shortcode_share_widget' ] );
		add_shortcode( 'automatewoo_advocate_referral_link', [ $this, 'shortcode_advocate_referral_link' ] );
		add_shortcode( 'automatewoo_advocate_referral_coupon', [ $this, 'shortcode_advocate_referral_coupon' ] );
	}


	/**
	 * @return false|Advocate
	 */
	function get_current_advocate() {
		return Advocate_Factory::get( get_current_user_id() );
	}


	/**
	 *
	 */
	function maybe_prevent_caching() {
		if ( $this->is_share_page() ) {
			$this->nocache();
		}
	}


	/**
	 * Register js and css
	 */
	function register_scripts() {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$dir    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min/';

		if ( AW_Referrals()->options()->type === 'link' ) {
			wp_register_script( 'js-cookie', WC()->plugin_url() . "/assets/js/js-cookie/js.cookie{$suffix}.js", [ ], '2.1.4', true );
			$dependencies = [ 'js-cookie' ];
		} else {
			$dependencies = [ 'jquery' ];
		}

		wp_register_script( 'automatewoo-referrals', AW_Referrals()->url( "/assets/js/{$dir}automatewoo-referrals{$suffix}.js" ), $dependencies, AW_Referrals()->version, true );
		wp_register_style( 'automatewoo-referrals', AW_Referrals()->url( '/assets/css/automatewoo-referrals.css' ), [], AW_Referrals()->version );

		wp_localize_script(
			'automatewoo-referrals',
			'automatewooReferralsLocalizeScript',
			[
				'is_link_based'  => AW_Referrals()->options()->type === 'link',
				'link_param'     => AW_Referrals()->options()->share_link_parameter,
				'cookie_expires' => apply_filters( 'automatewoo/referrals/link_cookie_expires', 365 )
			]
		);

		if ( AW_Referrals()->options()->type === 'link' || is_checkout() || is_account_page() || $this->is_share_page() ) {
			$this->enqueue_scripts();
		}
	}


	/**
	 * Enqueue js and css
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'automatewoo-referrals' );
		wp_enqueue_style( 'automatewoo-referrals' );
	}



	/**
	 * @return string
	 */
	function get_share_page_html() {

		if ( AW_Referrals()->options()->enabled ) {
			$this->enqueue_scripts();

			ob_start();

			AW_Referrals()->get_template(
				'share-page.php',
				[
					'advocate'              => $this->get_current_advocate(),
					'enable_facebook_share' => AW_Referrals()->options()->enable_facebook_share,
					'enable_twitter_share'  => AW_Referrals()->options()->enable_twitter_share,
					'enable_email_share'    => AW_Referrals()->options()->enable_email_share,
				]
			);

			return ob_get_clean();
		} else {
			return '<p><strong>' . __( 'Referrals are currently disabled.', 'automatewoo-referrals' ) . '</strong></p>';
		}
	}


	/**
	 * @param $template_name
	 * @param $template_path
	 * @param $located
	 * @param $args
	 */
	function display_share_widget_before_thankyou( $template_name, $template_path, $located, $args ) {
		if ( $template_name !== 'checkout/thankyou.php' )
			return;

		/** @var \WC_Order $order */
		$order = isset( $args['order'] ) ? $args['order'] : false;

		if ( ! $order || $order->has_status( 'failed' ) ) {
			return;
		}

		if ( $widget = $this->get_share_widget( 'thankyou-top', $order->get_id() ) ) {
			// phpcs:disable WordPress.Security.EscapeOutput
			echo $widget;
			// phpcs:enable
		}
	}


	/**
	 * @param $order_id
	 */
	function display_share_widget_after_thankyou( $order_id ) {
		if ( $widget = $this->get_share_widget( 'thankyou-bottom', $order_id ) ) {
			// phpcs:disable WordPress.Security.EscapeOutput
			echo $widget;
			// phpcs:enable
		}
	}


	/**
	 * @return string
	 */
	function shortcode_share_widget() {
		return $this->get_share_widget( 'shortcode' );
	}



	/**
	 * @param string $position : 'shortcode', 'thankyou-bottom', 'thankyou-to'
	 * @param int $order_id
	 * @return string|false
	 */
	function get_share_widget( $position = '', $order_id = 0 ) {
		if ( ! AW_Referrals()->options()->enabled ) {
			return false;
		}

		$advocate = $this->get_current_advocate();

		if ( ! $this->show_share_widget( $advocate, $position, $order_id ) ) {
			return false;
		}

		$this->enqueue_scripts();

		ob_start();

		if ( $advocate && $advocate->can_share() !== true ) {
			$advocate = false; // if advocate can't share treat them as logged out
		}

		AW_Referrals()->get_template(
			'share-widget.php',
			[
				'advocate'              => $advocate,
				'widget_heading'        => AW_Referrals()->options()->widget_heading,
				'widget_text'           => AW_Referrals()->options()->widget_text,
				'enable_facebook_share' => AW_Referrals()->options()->enable_facebook_share,
				'enable_twitter_share'  => AW_Referrals()->options()->enable_twitter_share,
				'enable_email_share'    => AW_Referrals()->options()->enable_email_share,
				'position'              => $position
			]
		);

		return ob_get_clean();
	}


	/**
	 * @param \WC_Order $order
	 * @param $sent_to_admin
	 * @param $plain_text
	 * @param \WC_Email $email
	 */
	function maybe_display_widget_on_order_emails( $order, $sent_to_admin, $plain_text, $email ) {
		if ( ! AW_Referrals()->options()->enabled || ! AW_Referrals()->options()->widget_on_order_emails ) {
			return;
		}

		$valid_emails = apply_filters(
			'automatewoo/referrals/share_widget_valid_emails',
			[
				'customer_processing_order',
				'customer_completed_order',
				'customer_processing_renewal_order',
				'customer_completed_renewal_order',
				'customer_completed_switch_order'
			],
			$order,
			$email
		);

		if ( ! in_array( $email->id, $valid_emails, true ) ) {
			return;
		}

		$advocate = Advocate_Factory::get( $order->get_user_id() );

		if ( ! $this->show_share_widget( $advocate, 'email', $order->get_id() ) ) {
			return;
		}

		if ( $advocate && $advocate->can_share() !== true ) {
			$advocate = false; // if advocate can't share treat them as logged out
		}

		$this->output_email_share_widget( $advocate );
	}


	/**
	 * @param \WC_Email $email
	 */
	function maybe_display_widget_on_new_account_email( $email ) {
		if ( ! AW_Referrals()->options()->enabled || ! AW_Referrals()->options()->widget_on_new_account_email ) {
			return;
		}

		if ( $email->id !== 'customer_new_account' || ! is_a( $email->object, 'WP_User' ) ) {
			return;
		}

		$advocate = Advocate_Factory::get( $email->object->ID );

		if ( ! $this->show_share_widget( $advocate, 'email' ) ) {
			return;
		}

		if ( $advocate && $advocate->can_share() !== true ) {
			$advocate = false; // if advocate can't share treat them as logged out
		}

		$this->output_email_share_widget( $advocate );
	}


	/**
 	 * @param Advocate|bool $advocate
 	 */
	function output_email_share_widget( $advocate = false ) {

		if ( ! AW_Referrals()->options()->enabled )
			return;

		AW_Referrals()->get_template(
			'share-widget-email.php',
			[
				'advocate'       => $advocate,
				'widget_heading' => AW_Referrals()->options()->widget_heading,
				'widget_text'    => AW_Referrals()->options()->widget_text,
			]
		);
	}


	function shortcode_advocate_referral_link() {
		$advocate = $this->get_current_advocate();
		if ( $advocate && $advocate->can_share() === true ) {
			return esc_url( $advocate->get_shareable_link() );
		}
	}


	function shortcode_advocate_referral_coupon() {
		$advocate = $this->get_current_advocate();
		if ( $advocate && $advocate->can_share() === true ) {
			return esc_attr( $advocate->get_shareable_coupon() );
		}
	}


	function maybe_handle_share_form() {

		if ( aw_request( 'action' ) != 'aw-referrals-email-share' ) {
			return;
		}

		$handler = new Invite_Form_Handler();
		$handler->handle();
		$handler->set_response_notices();
	}


	/**
	 * Set nocache constants and headers.
	 */
	function nocache() {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( "DONOTCACHEPAGE", true );
		}
		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( "DONOTCACHEOBJECT", true );
		}
		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( "DONOTCACHEDB", true );
		}
		nocache_headers();
	}


	/**
	 * @param $styles
	 * @return string
	 */
	function inject_email_styles( $styles ) {
		ob_start();
		AW_Referrals()->get_template( 'email-styles.php' );
		$styles .= ' ' . ob_get_clean();
		return $styles;
	}


	/**
	 * Add an account area icon if using storefront
	 */
	function storefront_css( $css ) {
		$css .= '
			.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--referrals a:before {
				content: "\f0a1";
			}
		';

		return $css;
	}


	/**
	 * @return bool
	 */
	function is_share_page() {
		return AW_Referrals()->options()->referrals_page && is_page( AW_Referrals()->options()->referrals_page );
	}


	/**
	 * Applies to widgets on site and in email.
	 *
	 * @param Advocate|false $advocate - if advocate is false the user is logged out
	 * @param string $position
	 * @param int $order_id
	 * @return bool
	 */
	function show_share_widget( $advocate, $position = '', $order_id = 0 ) {
		$show = true; // show widget even if logged out

		if ( $advocate ) {
			if ( $advocate->is_blocked() ) {
				$show = false;
			}
		}

		return apply_filters( 'automatewoo/referrals/show_share_widget', $show, $position, $order_id, $advocate );
	}


}
