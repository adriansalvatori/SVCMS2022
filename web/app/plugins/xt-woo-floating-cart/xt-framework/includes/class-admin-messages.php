<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Admin_Messages' ) ) {

	/**
	 * Class that takes care of rendering common message blocks
	 *
	 * @since      1.0.0
	 * @package    XT_Framework
	 * @subpackage XT_Framework/includes
	 * @author     XplodedThemes
	 */
	class XT_Framework_Admin_Messages {

		/**
		 * Core class reference.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      XT_Framework    core    Core Class
		 */
		private $core;

		public function __construct( $core ) {

			$this->core = $core;
		}

		public function premium_required( $scope = 'this section', $classes = array(), $tag = 'p' ) {

			$classes = array_merge( array( 'xt-framework-premium-required' ), $classes );
			$classes = implode( $classes, " " );
			?>
            <<?php echo esc_attr($tag); ?> class="<?php echo esc_attr( $classes ); ?>">
			<?php
            
            if($this->core->access_manager()->is__premium_only()) {
	            echo sprintf(
		            wp_kses_post( __( 'You can <a href="%s">Upgrade</a> or %s your license to unlock access to %s.', 'xt-framework' ) ),
		            esc_url( $this->core->access_manager()->get_upgrade_url() ),
		            $this->core->get_license_activation_link__premium_only(esc_html__('Activate', 'xt-framework')),
		            $scope
	            );
            }else{
	            echo sprintf(
		            wp_kses_post( __( 'You can <a href="%s">Upgrade</a> your license to unlock access to %s.', 'xt-framework' ) ),
		            esc_url( $this->core->access_manager()->get_upgrade_url() ),
		            $scope
	            );
            }
	
			?>
            </<?php echo esc_attr($tag); ?>>
			<?php
		}

		public function premium_required_table_row( $scope = 'this section', $classes = array(), $colspan = 2 ) {

			$classes = array_merge( array( 'xt-framework-premium-required' ), $classes );
			$classes = implode( $classes, " " );

			?>
            <tr class="<?php echo esc_attr( $classes ); ?>">
                <th colspan="<?php echo esc_attr( $colspan ); ?>">
					<?php
			        if($this->core->access_manager()->is__premium_only()) {

				        echo sprintf(
					        wp_kses_post( __( 'You can <a href="%s">Upgrade</a> or %s your license to unlock access to %s.', 'xt-framework' ) ),
					        esc_url( $this->core->access_manager()->get_upgrade_url() ),
					        $this->core->get_license_activation_link__premium_only(esc_html__('Activate', 'xt-framework')),
					        esc_attr( $this->core->plugin_slug() ),
					        $scope
				        );

			        }else{

			            echo sprintf(
					        wp_kses_post( __( 'You can <a href="%s">Upgrade</a> your license to unlock access to %s.', 'xt-framework' ) ),
					        esc_url( $this->core->access_manager()->get_upgrade_url() ),
					        $scope
				        );
			        }
					?>
                </th>
            </tr>
			<?php
		}

		public function premium_required_big( $scope = 'this section', $classes = array(), $tag = 'div' ) {

			$classes = array_merge( array( 'xt-framework-premium-required-big' ), $classes );
			$classes = implode( $classes, " " );
			?>
            <<?php echo esc_attr($tag); ?> class="<?php echo esc_attr( $classes ); ?>">
                <p>
                    <?php
                    echo sprintf(
                        esc_html__( 'The %s will unlock access to %s.', 'xt-framework' ),
                        wp_kses_post( '<strong>' . esc_html__( 'Premium Version', 'xt-framework' ) . '</strong>' ),
                        $scope
                    );
                    ?>
                </p>
                <p>
                    <a href="<?php echo esc_url( $this->core->access_manager()->get_upgrade_url() ); ?>" class="button button-primary button-hero">
                        <?php echo esc_html__( 'Upgrade License', 'xt-framework' ); ?>
                    </a>
			        <?php
                    if($this->core->access_manager()->is__premium_only()) {
				        echo ($this->core->get_license_activation_link__premium_only(esc_html__('Activate License', 'xt-framework'), array('button', 'button-secondary', 'button-hero')));
			        }
                    ?>
                </p>
            </<?php echo esc_attr($tag); ?>>
			<?php
		}

    }
}
