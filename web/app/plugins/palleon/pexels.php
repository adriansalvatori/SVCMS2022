<?php
defined( 'ABSPATH' ) || exit;

class PalleonPexels {
    /**
	 * The single instance of the class
	 */
	protected static $_instance = null;

    /**
	 * Main Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
	 * Constructor
	 */
    public function __construct() {
        add_action('wp_ajax_pexelsSearch', array($this, 'search'));
    }

    public function search() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        // Get The Api Key
        $getApiKey =  PalleonSettings::get_option('pexels', '');
        $apiKey = trim($getApiKey);
        $error = '';
        $curlURL = '';
        $imgSize =  PalleonSettings::get_option('pexels_img_size', 'large2x');
        $pagination =  PalleonSettings::get_option('pexels_pagination', 15);
        $lang =  PalleonSettings::get_option('pexels_lang', 'en-US');
        $caching =  PalleonSettings::get_option('pexels_caching', 24);
        $query = $_POST['keyword'];
        $orientation = $_POST['orientation'];
        $color = $_POST['color'];
        $page = $_POST['page'];

        if (empty($query) && empty($orientation) && empty($color)) {
            $curlURL = "https://api.pexels.com/v1/curated?locale=" . $lang . "&page=" . $page . "&per_page=" . $pagination;
        } else {
            $curlURL = "https://api.pexels.com/v1/search?";
            $curlURL .= 'locale=' . $lang . '&';
            if (!empty($query)) {
                $query = str_replace(' ', '%20', $query);
                $curlURL .= 'query=' . $query . '&';
            }
            if (!empty($orientation)) {
                $curlURL .= 'orientation=' . $orientation . '&';
            }
            if (!empty($color)) {
                $curlURL .= 'color=' . $color . '&';
            }
            $curlURL .= 'page=' . $page . 'per_page=' . $pagination;
        }

        $transient_value = get_transient($curlURL);

        if (false !== $transient_value){
            $response =	get_transient($curlURL);
        } else {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curlURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer {$apiKey}"
                )
            ));
        
            $response = @curl_exec($ch);
            if (curl_errno($ch) > 0) { 
                $error = esc_html__( 'Error connecting to API: ', 'palleon' ) . curl_error($ch);
            }
        
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($responseCode === 429) {
                $error = esc_html__( 'Too many requests!', 'palleon' );
            }
            if ($responseCode !== 200) {
                $error = "HTTP {$responseCode}";
            }
            if (empty($error)) {
                set_transient( $curlURL, $response,$caching * HOUR_IN_SECONDS );
            }
        }

        $data = @json_decode($response);
        if ($data === false && json_last_error() !== JSON_ERROR_NONE) {
            $error = esc_html__( 'Error parsing response', 'palleon' );
        }

        if (!empty($error)) {
            echo '<div class="notice notice-danger">' . $error . '</div>';
        } else {
            $photos = $data->photos;

            if ($photos == array()) {
                echo '<div class="notice notice-warning">' . esc_html__('Nothing Found.', 'palleon') . '</div>';
            } else {

                echo '<div class="palleon-grid media-library-grid pexels-grid">';

                foreach ( $photos as $photo ) {
                    $url = $photo->url;
                    $src = $photo->src;
                    $thumb = $src->tiny;
                    $full = $src->$imgSize;
                    $alt = $photo->alt;

                    echo '<div class="palleon-masonry-item">';
                    echo '<a href="' . esc_url($url) . '" class="pexels-url" target="_blank"><span class="material-icons">info</span></a>';
                    echo '<div class="palleon-masonry-item-inner">';
                    echo '<div class="palleon-img-wrap">';
                    echo '<div class="palleon-img-loader"></div>';
                    echo '<img class="lazy" data-src="' . esc_url($thumb) . '" data-full="' . esc_url($full) . '" data-id="' . esc_attr($photo->id) . '" data-filename="' . esc_attr($alt) . '" title="' . esc_attr($alt) . '" />';
                    echo '</div>';
                    echo '<div class="palleon-masonry-item-desc">' . esc_html($alt) . '</div>';
                    echo '</div></div>';
                }

                echo '</div>';

                echo '<button id="pexels-loadmore" type="button" class="palleon-btn palleon-lg-btn primary" autocomplete="off" data-page="' . $page . '">' . esc_html__('Load More', 'palleon') . '</button>';
            }

        }
        wp_die();
    }

    static function curated() {
        // Get The Api Key
        $getApiKey =  PalleonSettings::get_option('pexels', '');
        $apiKey = trim($getApiKey);
        $error = '';
        $imgSize =  PalleonSettings::get_option('pexels_img_size', 'large2x');
        $pagination =  PalleonSettings::get_option('pexels_pagination', 15);
        $caching =  PalleonSettings::get_option('pexels_caching', 24);
        $lang =  PalleonSettings::get_option('pexels_lang', 'en-US');
        $curlURL = "https://api.pexels.com/v1/curated?locale=" . $lang . "&page=1&per_page=" . $pagination;

        $transient_value = get_transient($curlURL); 

        if (false !== $transient_value){
            $response =	get_transient($curlURL);
        } else {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curlURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer {$apiKey}"
                )
            ));
        
            $response = @curl_exec($ch);
            if (curl_errno($ch) > 0) { 
                $error = esc_html__( 'Error connecting to API: ', 'palleon' ) . curl_error($ch);
            }
        
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($responseCode === 429) {
                $error = esc_html__( 'Too many requests!', 'palleon' );
            }
            if ($responseCode !== 200) {
                $error = "HTTP {$responseCode}";
            }
            if (empty($error)) {
                set_transient( $curlURL, $response, $caching * HOUR_IN_SECONDS );
            }
        }

        $data = @json_decode($response);
        if ($data === false && json_last_error() !== JSON_ERROR_NONE) {
            $error = esc_html__( 'Error parsing response', 'palleon' );
        }

        if (!empty($error)) {
            echo '<div class="notice notice-danger">' . $error . '</div>';
        } else {
            $photos = $data->photos;

            echo '<div class="palleon-grid media-library-grid pexels-grid">';

            foreach ( $photos as $photo ) {
                $url = $photo->url;
                $src = $photo->src;
                $thumb = $src->tiny;
                $full = $src->$imgSize;

                echo '<div class="palleon-masonry-item">';
                echo '<a href="' . esc_url($url) . '" class="pexels-url" target="_blank"><span class="material-icons">info</span></a>';
                echo '<div class="palleon-masonry-item-inner">';
                echo '<div class="palleon-img-wrap">';
                echo '<div class="palleon-img-loader"></div>';
                echo '<img class="lazy" data-src="' . esc_url($thumb) . '" data-full="' . esc_url($full) . '" data-id="' . esc_attr($photo->id) . '" data-filename="' . esc_attr($photo->alt) . '" title="' . esc_attr($photo->alt) . '" />';
                echo '</div>';
                echo '<div class="palleon-masonry-item-desc">' . esc_attr($photo->alt) . '</div>';
                echo '</div></div>';
            }

            echo '</div>';

            echo '<button id="pexels-loadmore" type="button" class="palleon-btn palleon-lg-btn primary" autocomplete="off" data-page="1">' . esc_html__('Load More', 'palleon') . '</button>';

        }
    }
}

/**
 * Returns the main instance of the class
 */
function PalleonPexels() {  
	return PalleonPexels::instance();
}
// Global for backwards compatibility
$GLOBALS['PalleonPexels'] = PalleonPexels();    