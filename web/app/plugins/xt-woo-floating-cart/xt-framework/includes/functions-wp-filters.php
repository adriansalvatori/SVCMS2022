<?php
/**
* XT Framework WP Filters
*
* @author      XplodedThemss
* @category    Core
* @package     XT_Framework/Admin/Functions
* @version     1.0.0
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter('wp_kses_allowed_html', function($allowed_tags, $context ) {

    if($context !== 'post') {
        return $allowed_tags;
    }

    $allowed_tags['input'] = array(
        'id' => array(),
        'type' => array(),
        'class' => array(),
        'step' => array(),
        'min' => array(),
        'max' => array(),
        'name' => array(),
        'value' => array(),
        'title' => array(),
        'placeholder' => array(),
        'inputmode' => array(),
        'readonly' => array(),
    );

    return $allowed_tags;

}, 10, 2);
