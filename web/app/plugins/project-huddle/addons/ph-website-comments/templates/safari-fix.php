<?php
/**
 * The for building cross origin website screenshots
 *
 * @package     ProjectHuddle
 * @subpackage  Website Comments
 * @copyright   Copyright (c) 2016, Andre Gagnon
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// simlulate ajax to prevent other plugins from outputting html here
define( 'DOING_AJAX', true );

// dynamic javascript output
header( 'Access-Control-Max-Age:' . 5 * 60 * 1000 );
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Request-Method: *' );
header( 'Access-Control-Allow-Methods: OPTIONS, GET' );
header( 'Access-Control-Allow-Headers: *' );

// Url params
$url  = isset( $_GET['ph_safari_cookie'] ) ? $_GET['ph_safari_cookie'] : ''; ?>

<script>
document.cookie = 'safarifixed=fixed; expires=Tue, 19 Jan 2038 03:14:07 UTC; path=/';
window.location.replace('<?php echo esc_url( $url ); ?>');
</script>

<style>
    .full-container {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f7fafc;
    }
    .notice {
        width: 100%;
        max-width: 600px;
    }
    .text-center {
        text-align: center;
    }
    .m-auto {
        margin: auto;
    }
</style>

<body class="full-container">
    <div class="notice text-center m-auto">
        <h1>
			<?php if ( $logo = apply_filters( 'ph_login_logo_id', get_option( 'ph_login_logo' ) ) ) : ?>
				<?php
				// get logo image
				$logo_image = wp_get_attachment_image_src( $logo, 'full' );

				// check retina option
				if ( apply_filters( 'ph_login_logo_retina', get_option( 'ph_login_logo_retina' ) ) ) :
					$logo_image[1] = $logo_image[1] / 2;
					$logo_image[2] = $logo_image[2] / 2;
				endif;
				?>
                <img class="m-auto mb-8" src="<?php echo esc_url( $logo_image[0] ); ?>" width="<?php echo (float) $logo_image[1]; ?>" height="<?php echo (float) $logo_image[2]; ?>"/>
			<?php endif; ?>
        </h1>

        <div>
            <svg width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" class="m-auto">
                <defs>
                    <linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a">
                        <stop stop-color="#222" stop-opacity="0" offset="0%"/>
                        <stop stop-color="#222" stop-opacity=".631" offset="63.146%"/>
                        <stop stop-color="#222" offset="100%"/>
                    </linearGradient>
                </defs>
                <g fill="none" fill-rule="evenodd">
                    <g transform="translate(1 1)">
                        <path d="M36 18c0-9.94-8.06-18-18-18" id="Oval-2" stroke="url(#a)" stroke-width="2">
                            <animateTransform
                                attributeName="transform"
                                type="rotate"
                                from="0 18 18"
                                to="360 18 18"
                                dur="0.9s"
                                repeatCount="indefinite" />
                        </path>
                        <circle fill="#fff" cx="36" cy="18" r="1">
                            <animateTransform
                                attributeName="transform"
                                type="rotate"
                                from="0 18 18"
                                to="360 18 18"
                                dur="0.9s"
                                repeatCount="indefinite" />
                        </circle>
                    </g>
                </g>
            </svg>
        </div>
    </div>
</body>
