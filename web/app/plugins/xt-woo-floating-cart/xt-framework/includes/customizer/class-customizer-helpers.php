<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('XT_Framework_Customizer_Helpers')) {

	/**
	 * Class that contains static helper methods.
	 *
	 * @since      1.0.0
	 * @package    XT_Framework
	 * @subpackage XT_Framework/includes
	 * @author     XplodedThemes
	 */
	class XT_Framework_Customizer_Helpers {

		public static function repeater_fields_string_to_array($data) {

			if(!empty($data) && !is_array($data) && $data !== 'string') {
				$tmp = explode(',', $data);
				$data = array();
				if(!empty($tmp)) {
					foreach ( $tmp as $attr ) {
						$attr   = explode( ":", $attr );
						if(!empty($attr[1])) {
							$data[] = array(
								$attr[0] => $attr[1]
							);
						}
					}
				}
			}

			if(!is_array($data)) {
				$data = array();
			}

			return $data;
		}

	} // End Class
}

