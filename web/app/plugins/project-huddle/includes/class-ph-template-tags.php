<?php
/**
 * ProjectHuddle class for displaying tags in text
 *
 * Tags are wrapped in { }
 *
 * To replace tags in content, use: ph_do_tags( $content );
 *
 * @package     Projecthuddle
 * @subpackage  Tags
 * @copyright   Copyright (c) 2016, Andre Gagnon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PH_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since 1.2.1
	 */
	private $tags;

	/**
	 * Add a tag
	 *
	 * @since 1.2.1
	 *
	 * @param string   $tag  Tag to be replace in text
	 * @param callable $func Hook to run when tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}

	/**
	 * Remove a tag
	 *
	 * @since 1.2.1
	 *
	 * @param string $tag Tag to remove hook
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	/**
	 * Check if $tag is a registered tag
	 *
	 * @since 1.2.1
	 *
	 * @param string $tag Tag that will be searched
	 *
	 * @return bool
	 */
	public function tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Returns a list of all tags
	 *
	 * @since 1.2.1
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Search content for tags and filter tags through their hooks
	 *
	 * @param string $content Content to search for tags
	 *
	 * @since 1.2.1
	 *
	 * @return string Content with tags filtered out.
	 */
	public function do_tags( $content ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		// run callback on tag
		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		return $new_content;
	}

	/**
	 * Do a specific tag, this function should not be used. Please use ph_do_tags instead.
	 *
	 * @since 1.2.1
	 *
	 * @param $m message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $tag );
	}

}

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param string $content Content to search for email tags
 *
 * @since 1.2.1
 *
 * @return string Content with email tags filtered out.
 */
function ph_do_tags( $content ) {

	// Replace all tags
	$content = PH()->template_tags->do_tags( $content);

	// Return content
	return $content;
}

/**
 * Load email tags
 *
 * @since 1.9
 */
function ph_load_template_tags() {
	do_action( 'ph_add_template_tags' );
}
add_action( 'init', 'ph_load_template_tags', -999 );