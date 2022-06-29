<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Addons extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Addons', 'checkout-wc' ), 'manage_options', 'addons' );
	}

	public function init() {
		if ( ! has_filter( 'cfw_admin_addon_tabs' ) ) {
			return;
		}

		parent::init();
	}

	public function output() {
		/**
		 * Filters an array of addon tabs
		 *
		 * 'foo' => array(
		 *      'name'    => 'Foo',
		 *      'function => callable,
		 * )
		 *
		 * @since 3.0.0
		 *
		 * @param array $addon_tabs The addon tabs
		 */
		$addon_tabs        = apply_filters( 'cfw_admin_addon_tabs', array() );
		$current_addon_tab = isset( $_GET['addontab'] ) ? esc_attr( $_GET['addontab'] ) : key( $addon_tabs );
		$callable          = $addon_tabs[ $current_addon_tab ]['function'];

		$array_keys = array_keys( $addon_tabs );

		if ( empty( $addon_tabs ) ) {
			return;
		}
		?>
		<div class="wrap">
			<ul class="subsubsub">
				<?php foreach ( $addon_tabs as $id => $addon_tab ) : ?>
					<li>
						<a href="<?php echo add_query_arg( array( 'addontab' => $id ) ); ?>" class="<?php echo $id === $current_addon_tab ? 'current' : ''; ?>">
							<?php echo $addon_tab['name']; ?>
						</a>

						<?php echo ( end( $array_keys ) ) !== $id ? '|' : ''; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<br class="clear">

			<?php is_callable( $callable ) ? call_user_func( $callable ) : null; ?>
		</div>
		<?php
	}
}
