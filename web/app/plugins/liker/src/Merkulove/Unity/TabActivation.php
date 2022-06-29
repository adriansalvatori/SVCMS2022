<?php
/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2022 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Liker\Unity;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/**
 * SINGLETON: Class used to implement Activation tab on plugin settings page.
 *
 * @since 1.0.0
 *
 **/
final class TabActivation extends Tab {

    /**
     * Slug of current tab.
     *
     * @since 1.0.0
     * @const TAB_SLUG
     **/
    const TAB_SLUG = 'activation';

	/**
	 * The one true PluginActivation.
	 *
	 * @var TabActivation
	 **/
	private static $instance;

    /**
     * Generate Activation Tab.
     *
     * @access public
     **/
    public function add_settings() {

        /** Not show if plugin don't have Envato ID. */
        if ( ! EnvatoItem::get_instance()->get_id() ) { return; }

        /** Activation Tab. */
        register_setting( 'LikerActivationOptionsGroup', 'envato_purchase_code_' . EnvatoItem::get_instance()->get_id() );
        add_settings_section( 'mdp_liker_settings_page_activation_section', '', null, 'LikerActivationOptionsGroup' );

    }

    /**
     * Render form with all settings fields.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function do_settings() {

        /** No status tab, nothing to do. */
        if ( ! $this->is_enabled( self::TAB_SLUG ) ) { return; }

        /** Render title. */
        $this->render_title( self::TAB_SLUG );

        /** Render fields. */
        settings_fields( 'LikerActivationOptionsGroup' );
        do_settings_sections( 'LikerActivationOptionsGroup' );

        /** Render Activation fields. */
        $this->render_activation();

    }

	/**
	 * Display Activation Status.
	 *
	 * @access public
	 **/
	public function display_status() {

        /** Disable this method for custom type plugins. */
        if ( 'custom' === Plugin::get_type() ) { return; }

		$activation_tab = admin_url( 'admin.php?page=mdp_liker_settings&tab=activation' );
		?>

        <hr class="mdc-list-divider">
        <h6 class="mdc-list-group__subheader"><?php esc_html_e( 'CodeCanyon License', 'liker' ); ?></h6>

		<?php if ( $this->is_activated() ) : ?>
            <a class="mdc-list-item mdp-activation-status activated" href="<?php echo esc_url( $activation_tab ); ?>">
                <i class='material-icons mdc-list-item__graphic' aria-hidden='true'>check_circle</i>
                <span class="mdc-list-item__text"><?php esc_html_e( 'Activated', 'liker' ); ?></span>
            </a>
		<?php else : ?>
            <a class="mdc-list-item mdp-activation-status activated" href="<?php echo esc_url( $activation_tab ); ?>">
                <i class='material-icons mdc-list-item__graphic' aria-hidden='true'>check_circle</i>
                <span class="mdc-list-item__text"><?php esc_html_e( 'Activated', 'liker' ); ?></span>
            </a>
		<?php endif;

	}

	/**
	 * Return Activation Status.
	 *
	 * @return boolean True if activated.
	 * @access public
	 */
	public function is_activated() {
		return true;
		/** Not activated if plugin don't have Envato ID. */
		if ( ! EnvatoItem::get_instance()->get_id() ) { return false; }

		$purchase_code = $this->get_purchase_code();

		/** Not activated if we don't have Purchase Code. */
		if ( false === $purchase_code ) { return false; }

		/** Do we have activation in cache? */
		$cache = new Cache();
		$key = 'activation_' . $purchase_code;
		$cached_activation = $cache->get( $key, false );

		/** Use activation from cache. */
		if ( ! empty( $cached_activation ) ) {

			$cached_activation = json_decode( $cached_activation, true );
			return (bool)$cached_activation[$key];

		}

		/** If no cached activation, go to remote check. */
		$activated = $this->remote_validation( $purchase_code );

		/** Store remote validation result */
		$cache->set( $key, [$key => $activated], false );

		return filter_var( $activated, FILTER_VALIDATE_BOOLEAN );

	}

	/**
	 * Validate PID on our server.
	 *
	 * @param $purchase_code - Envato Purchase Code.
	 * @return bool
	 * @access public
	 */
	public function remote_validation( $purchase_code ) {

		/** Prepare URL. */
		$url = $this->prepare_url( $purchase_code );

		/** Download JSON file with purchase code validation status true/false. */
		$json = wp_remote_get( $url, [
			'timeout' => 15,
			'headers' => [
				'Accept' => 'application/json'
			],
            'sslverify'  => Settings::get_instance()->options[ 'check_ssl' ] === 'on'
		] );

		/** We’ll check whether the answer is correct. */
		if ( is_wp_error( $json ) ) { return false; }

		/** Have answer with wrong code. */
		if ( wp_remote_retrieve_response_code( $json ) !== 200 ) { return false; }

		return true === json_decode( $json['body'], true );

	}

	/**
	 * Return Item Purchase Code.
	 *
	 * @access public
	 * @return false|string
	 **/
	private function get_purchase_code() {

		/** CodeCanyon Item ID. */
		$plugin_id = EnvatoItem::get_instance()->get_id();

		/** In this option we store purchase code. */
		$opt_purchase_code = 'envato_purchase_code_' . $plugin_id;

		/** Get fresh PID from settings form. */
		if ( isset( $_POST[$opt_purchase_code] ) ) {

			$purchase_code = filter_input( INPUT_POST, $opt_purchase_code );

		} else {

			/** Or get PID from option. */
			$purchase_code = get_option( $opt_purchase_code );

		}

		/** If we do not have $purchase_code then nothing to check. */
		if ( ! $purchase_code ) { return false; }

		/** Clean purchase code: remove extra spaces. */
		$purchase_code = trim( $purchase_code );

		/** Make sure the code is valid before sending it to Envato. */
		if ( ! preg_match( "/^(\w{8})-((\w{4})-){3}(\w{12})$/", $purchase_code ) ) {

			/** Wrong key format. Not activated. */
			return false;

		}

		return $purchase_code;

	}

	/**
	 * Prepare URL.
	 *
	 * @param $purchase_code - Envato Purchase Code.
	 * @return string
	 * @access private
	 **/
	private function prepare_url( $purchase_code ) {

		/** Prepare URL. */
		$url = 'https://merkulove.host/wp-content/plugins/mdp-purchase-validator/src/Merkulove/PurchaseValidator/Validate.php?';
		$url .= 'action=validate&'; // Action.
		$url .= 'plugin=' . Plugin::get_slug() . '&'; // Plugin Name.
		$url .= 'domain=' . parse_url( site_url(), PHP_URL_HOST ) . '&'; // Domain Name.
		$url .= 'version=' . Plugin::get_version() . '&'; // Plugin version.
		$url .= 'pid=' . $purchase_code . '&'; // Purchase Code.
		$url .= 'admin_e=' . base64_encode( get_option( 'admin_email' ) );

		return $url;

	}

	/**
	 * Render Purchase Code field.
	 *
	 * @access public
	 **/
	public function render_activation() {

        /** Not show if plugin don't have Envato ID. */
        if ( ! EnvatoItem::get_instance()->get_id() ) { return; }

        ?>
        <div class="mdp-activation">
            <?php

            $this->render_form();
            $this->render_FAQ();
            $this->render_subscribe();

            ?>
        </div>
        <?php

	}

	/**
	 * Render e-sputnik Subscription Form block.
	 *
	 * @access public
	 **/
	public function render_subscribe() {

        ?>
        <div class="mdp-subscribe-form">

            <h3><?php esc_html_e( 'Subscribe to newsletter', 'liker' ); ?></h3>
            <p><?php esc_html_e( 'Sign up for the newsletter to be the first to know about news and discounts.', 'liker' ); ?></p>
            <p class="mdp-subscribe-form-message"
               data-success="<?php esc_html_e( 'Hurray! We received your Subscription request. Check your inbox for an email from us.', 'liker' ); ?>"
               data-warn="<?php esc_html_e( 'Oh! Sorry, but we cannot send messages to this email.', 'liker' ); ?>"
               data-error="<?php esc_html_e( 'Oops! Something went wrong. Please try later.', 'liker' ); ?>"
            ></p>

            <?php
            /** Render Name. */
            UI::get_instance()->render_input(
                '',
                esc_html__( 'Your Name', 'liker' ),
                '',
                [
                    'name' => 'mdp-subscribe-name',
                    'id' => 'mdp-subscribe-name'
                ]
            );

            /** Render e-Mail. */
            UI::get_instance()->render_input(
                '',
                esc_html__( 'Your E-Mail', 'liker' ),
                '',
                [
                    'name'  => 'mdp-subscribe-mail',
                    'id'    => 'mdp-subscribe-mail',
                    'type'  => 'email',
                ]
            );

            /** Render button. */
            UI::get_instance()->render_button(
                esc_html__( 'Subscribe', 'liker' ),
                '',
                false,
                [
                    "name"  => "mdp-subscribe",
                    "id"    => "mdp-subscribe"
                ]
            );
            ?>

        </div>
        <?php

	}

	/**
	 * Render CodeCanyon Activation Form
	 */
	public function render_form() {

        /** In this option we store Envato purchase code. */
        $opt_envato_purchase_code = 'envato_purchase_code_' . EnvatoItem::get_instance()->get_id();

        /** Get activation settings. */
        $purchase_code = get_option( $opt_envato_purchase_code );

        ?>
        <div class="mdp-activation-form">
            <h3><?php esc_html_e( 'Plugin Activation', 'liker' ); ?></h3>
            <?php

            /** Render input. */
            UI::get_instance()->render_input(
                $purchase_code,
                esc_html__( 'Purchase code', 'liker'),
                esc_html__( 'Enter your CodeCanyon purchase code. Allowed only one Purchase Code per website.', 'liker' ),
                [
                    'name' => $opt_envato_purchase_code,
                    'id' => 'mdp_envato_purchase_code'
                ]
            );
            ?>
        </div>
        <?php

    }

    /**
     * Render FAQ block.
     *
     * @access public
     **/
    public function render_FAQ() {
        ?>
        <div class="mdp-activation-faq">
            <div class="mdc-accordion" data-mdp-accordion="showfirst: true">

                <h3><?php esc_html_e( 'Activation FAQ\'S', 'liker' ); ?></h3>

                <div class="mdc-accordion-title">
                    <i class="material-icons">help</i>
                    <span class="mdc-list-item__text"><?php esc_html_e( 'Where is my Purchase Code?', 'liker' ); ?></span>
                </div>
                <div class="mdc-accordion-content">
                    <p><?php esc_html_e( 'The purchase code is a unique combination of characters that confirms that you bought the plugin. You can find your purchase code in ', 'liker' ); ?>
                        <a href="https://1.envato.market/cc-downloads" target="_blank"><?php esc_html_e( 'your account', 'liker' );?></a>
                        <?php esc_html_e( ' on the CodeCanyon. Learn more about ', 'liker' ); ?>
                        <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank"><?php esc_html_e( 'How to find your purchase code', 'liker' );?></a>
                        <?php esc_html_e( ' .', 'liker');?>
                    </p>
                </div>

                <div class="mdc-accordion-title">
                    <i class="material-icons">help</i>
                    <span class="mdc-list-item__text"><?php esc_html_e( 'Can I use one Purchase Code on multiple sites?', 'liker' ); ?></span>
                </div>
                <div class="mdc-accordion-content">
                    <p>
                        <?php esc_html_e( 'No, this is prohibited by license terms. You can use the purchase code on only one website at a time. Learn more about ', 'liker' ); ?>
                        <a href="https://1.envato.market/KYbje" target="_blank"><?php esc_html_e( 'Envato License', 'liker' );?></a>
                        <?php esc_html_e( ' terms. ', 'liker' ); ?>
                    </p>
                </div>

                <div class="mdc-accordion-title">
                    <i class="material-icons">help</i>
                    <span class="mdc-list-item__text"><?php esc_html_e( 'What are the benefits of plugin activation?', 'liker' ); ?></span>
                </div>
                <div class="mdc-accordion-content">
                    <p>
                        <?php esc_html_e( 'Activation of the plugin allows you to use all the functionality of the plugin on your site. In addition, in some cases, activating the plugin allows you to access additional features and capabilities of the plugin. Also, using an authored version of the plugin, you can be sure that you will not violate the license.', 'liker' ); ?>
                    </p>
                </div>

                <div class="mdc-accordion-title">
                    <i class="material-icons">help</i>
                    <span class="mdc-list-item__text"><?php esc_html_e( 'What should I do if my Purchase Code does not work?', 'liker' ); ?></span>
                </div>
                <div class="mdc-accordion-content">
                    <p>
                        <?php esc_html_e( 'There are several reasons why the purchase code may not work on your site. Learn more why your ', 'liker' ); ?>
                        <a href="https://merkulove.zendesk.com/hc/en-us/articles/360006100998-Troubleshooting-of-the-plugin-activation" target="_blank"><?php esc_html_e( 'Purchase Code is Not Working', 'liker' );?></a>
                        <?php esc_html_e( ' .', 'liker');?>
                    </p>
                </div>

            </div>
        </div>
        <?php
    }

	/**
	 * Main PluginActivation Instance.
	 * Insures that only one instance of PluginActivation exists in memory at any one time.
	 *
	 * @static
     * @since 1.0.0
     * @access public
     *
     * @return TabActivation
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}
