<?php
defined( 'ABSPATH' ) || exit;

if (class_exists('Bsf_Custom_Fonts_Render')) {
    class palleonCustomFonts extends Bsf_Custom_Fonts_Render {
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
         * Palleon Constructor
         */
        public function __construct() {
            add_action( 'palleon_head', array( $this, 'add_style' ) );
            add_action( 'palleon_fonts', array( $this, 'add_fonts' ) );
            add_action( 'palleon_body_end', array( $this, 'add_scripts' ) );
        }

        /**
         * Font Select
         */
        public function add_fonts() {
			$all_fonts = Bsf_Custom_Fonts_Taxonomy::get_fonts();
			if ( ! empty( $all_fonts ) ) {
                echo '<optgroup id="custom-fonts" label="' . esc_html__('Custom Fonts', 'palleon') . '">';
				foreach ( $all_fonts as $font_family_name => $fonts_url) {
					echo '<option class="noload" value="' . $font_family_name . '">' . $font_family_name . '</option>';
				}
                echo '</optgroup>';
			}
		}

        public function add_scripts() {
			$all_fonts = Bsf_Custom_Fonts_Taxonomy::get_fonts();
			if ( ! empty( $all_fonts ) ) { 
                $fonts = array();
                foreach ( $all_fonts as $font_family_name => $fonts_url) {
                    array_push($fonts, $font_family_name);
                }
                ?>
                <script>
                /* <![CDATA[ */
                var palleonCustomFonts = {
                    "fonts": <?php echo json_encode($fonts); ?>,
                };
                /* ]]> */
                </script>
			<?php }
		}
    }

    /**
     * Returns the main instance of the class
     */
    function palleonCustomFonts() {  
        return palleonCustomFonts::instance();
    }
    // Global for backwards compatibility
    $GLOBALS['palleonCustomFonts'] = palleonCustomFonts();
}