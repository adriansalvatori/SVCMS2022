<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}




add_action( 'admin_init', 'digits_setup_wizard' );

/**
 * Output the content for Documentation
 */
function digit_documentation() {
	?>
    <h1><?php _e( "Have a look at our documentation", "digits" ); ?></h1>
    <p class="lead"
       style="border-bottom:none;padding-bottom:0;"><?php _e( "Do you feel like you need some help with the setup, go through our detailed documentation it will guide you through.", "digits" ); ?></p>
    <br/><br/>

    <center><a href="https://help.unitedover.com/" class="button"
               target="_blank"><?php _e( "Open Documentation", "digits" ); ?></a></center>
    <br/><br/>

    <p class="lead"><?php _e( "Having the documentation opened in other tab can help you if you get stuck somewhere in the middle.", "digits" ); ?></p>
    <p><?php _e( "Don't worry, we'll not tell anyone that you went through our documentation to setup this simple thing.", "digits" ); ?></p>
    <p class="digits-setup-action step">
        <a href="<?php echo admin_url( 'index.php?page=digits-setup&step=apisettings' ); ?>"
           class="button-primary button button-large button-next"><?php _e( "Continue", "digits" ); ?></a>
        <a href="<?php echo admin_url( 'index.php?page=digits-setup&step=activation' ); ?>"
           class="button"><?php _e( "Back", "digits" ); ?></a>
    </p>
	<?php
}

/**
 * Output the content for Ready
 */
function digit_ready() {
	?>

    <h1><?php _e( "Digits is ready!", "digits" ); ?></h1>
    <p class="lead"><?php _e( "Congratulations! Digits has been activated and your website is ready. Login to your WordPress
        dashboard to make changes and modify any of the content to suit your needs.", "digits" ); ?>

    </p>

    <p class="digits-setup-action step">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=digits_settings&tab=customize' ) ); ?>"
           class="button-primary button button-large button-next"><?php _e( "Continue", "digits" ); ?></a>
        <a href="<?php echo admin_url( 'index.php?page=digits-setup&step=shortcodes' ); ?>"
           class="button"><?php _e( "Back", "digits" ); ?></a>
    </p>

	<?php
}

/**
 * Output the content for Configure
 */

function digit_configure() {
	$color     = get_option( 'digit_color' );
	$bgcolor   = "#4cc2fc";
	$fontcolor = 0;
	if ( $color !== false ) {
		$bgcolor = $color['bgcolor'];
	}
	?>


    <h1><?php _e( "Login Page Configuration", "digits" ); ?></h1>
    <p class="lead"></p>

    <form method="post" enctype="multipart/form-data">
		<?php
		digits_configure_settings();
		?>

        <p class="digits-setup-action step">
            <Button type="submit"
                    class="button-primary button button-large button-next"><?php _e( "Continue", "digits" ); ?></Button>
            <a href="<?php echo admin_url( 'index.php?page=digits-setup&step=apisettings' ); ?>"
               class="button"><?php _e( "Back", "digits" ); ?></a>
        </p>
    </form>


	<?php

	dig_config_scripts();
}


/**
 * Show the setup wizard.
 */
function digits_setup_wizard() {
	if ( empty( $_GET['page'] ) || 'digits-setup' !== $_GET['page'] ) {
		return;
	}


	digits_update_data( 1 );


	wp_enqueue_style( array( 'wp-admin', 'dashicons', 'install' ) );

	//enqueue style for admin notices
	wp_enqueue_style( 'wp-admin' );
	wp_enqueue_media();
	wp_enqueue_script( 'media' );

	ob_start();
	setup_wizard_header();


	exit();
}


/**
 * Setup Wizard Header.
 */
function setup_wizard_header() {
	?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta name="viewport" content="width=device-width"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>DIGITS &rsaquo; <?php _e( "Setup", "digits" ); ?></title>
		<?php do_action( 'admin_print_styles' );
		do_action( 'admin_print_scripts' );


		wp_enqueue_style( 'google-roboto-regular', dig_fonts() );

        digits_select2();

		wp_register_style( 'digits-gs-style', plugins_url( '/assets/css/gs.css', __FILE__ ), array(
			'google-roboto-regular',
			'select2'
		), digits_version(), 'all' );
		wp_print_styles( 'digits-gs-style' );
		?>

        <style>
            body {
                margin: 40px auto 24px;
                box-shadow: none;
                background: #f1f1f1;
                padding: 0;
            }
        </style>
    </head>
    <body class="digits-setup wp-core-ui">
    <h1 id="digits-logo"><a href="https://digits.unitedover.com" target="_blank">
            <img src="<?php echo plugins_url( 'assets/images/Digits_logo.png', __FILE__ ) ?>" alt="Digits"/></a></h1>

	<?php


	$steps = array(
		'page'          => array(
			'name' => __( 'Welcome', 'digits' ),
			'view' => 'digit_introduction'
		),
		'activation'    => array(
			'name' => __( 'Activation', 'digits' ),
			'view' => 'digit_activation'
		),
		'documentation' => array(
			'name' => __( 'Docs', 'digits' ),
			'view' => 'digit_documentation'
		),
		'apisettings'   => array(
			'name' => __( 'API Settings', 'digits' ),
			'view' => 'digit_apisettings'
		),
		'shortcodes'    => array(
			'name' => __( 'Shortcodes', 'digits' ),
			'view' => 'digit_shortcodes'
		),
		'ready'         => array(
			'name' => __( 'Ready', 'digits' ),
			'view' => 'digit_ready'
		)
	);

	$step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $steps ) );


	if ( ! array_key_exists( $step, $steps ) ) {
		$step = current( array_keys( $steps ) );
	}

	setup_wizard_steps( $steps, $step );
	setup_wizard_content( $steps, $step )


	?>

    <div class="dig_load_overlay_gs" ajxsu="0">
        <div class="dig_load_content">

            <div class="circle-loader">
                <div class="checkmark draw"></div>
            </div>

        </div>
    </div>
    </body>
	<?php
	digits_add_admin_settings_scripts( - 1 );
	wp_print_scripts( 'slick' );
	wp_print_scripts( 'digits-script' );
	wp_print_scripts( 'igorescobar-jquery-mask' );

	digits_add_style();

	wp_print_styles( 'digits-login-style' );
	?>
    </html>
	<?php
}


/**
 * Output the content for introduction
 */
function digit_introduction() {
	?>

    <h1><?php _e( "Welcome to the configuration wizard for DIGITS!", "digits" ); ?></h1>
    <p class="lead">
		<?php _e( "Thank you for choosing Digits. This quick setup wizard will help you to configure this plugin in a few simple steps.", "digits" ); ?>
        <br/><br/>
		<?php _e( "It should only take 4-5 minutes.", "digits" ); ?>
    </p>
    <p><?php _e( "Busy right now! If you don't want to go through the wizard, you can skip and return to the WordPress dashboard and come back anytime.", "digits" ); ?></p>


    <p class="digits-setup-action step">
        <a href="<?php echo admin_url( 'index.php?page=digits-setup&step=activation' ) ?>"
           class="button-primary button button-large button-next"><?php _e( "Continue", "digits" ); ?></a>
    </p>


	<?php
}

/**
 * Output the content for the current step.
 */
function setup_wizard_content( $steps, $step ) {
	echo '<div class="digits-setup-content">';
	call_user_func( $steps[ $step ]['view'] );
	echo '<a class="return-to-dashboard" href="' . esc_url( admin_url() ) . '">' . __( "Return to the WordPress Dashboard", "digits" ) . '</a>';

	echo '</div>';
}


/**
 * Output the steps.
 */
function setup_wizard_steps( $steps, $currentStep ) {
	$ouput_steps = $steps;


	?>
    <ol class="digits-setup-steps">
		<?php foreach ( $ouput_steps as $step_key => $step ): ?>
            <li class="<?php
			if ( $step_key === $currentStep ) {
				echo 'active';
			} elseif ( array_search( $currentStep, array_keys( $steps ) ) > array_search( $step_key, array_keys( $steps ) ) ) {
				echo 'done';
			}
			?>"><?php echo esc_html( $step['name'] ); ?></li>
		<?php endforeach; ?>
    </ol>
	<?php
}

