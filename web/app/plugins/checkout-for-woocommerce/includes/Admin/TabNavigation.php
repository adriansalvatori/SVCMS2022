<?php
namespace Objectiv\Plugins\Checkout\Admin;

/**
 * WP Tabbed Navigation
 *
 * Automate creating a tabbed navigation and maintaining tabbed states
 *
 *
 * @since      0.2.0
 * @package    Advanced_Content_Templates
 * @subpackage Advanced_Content_Templates/includes
 * @author     Clifton Griffin <clif@objectiv.co>
 */
class TabNavigation {
	/**
	 * Added tabs.
	 *
	 * @since 0.1.0
	 * @var array $_tabs Array of added tabs.
	 */
	private $_tabs = array();

	/**
	 * Tab title.
	 *
	 * @since 0.1.0
	 * @var boolean|string $_title False if page title, string if page title set.
	 */
	private $_title;

	/**
	 * Selected tab query arg.
	 *
	 * @since 0.2.0
	 * @var boolean|string $_selected_tab_query_arg False defaults to subpage, string if set
	 */
	private $_selected_tab_query_arg = false;

	/**
	 * Constructor.
	 *
	 * @param string $title Admin page title.
	 * @param string $selected_tab_query_arg (optional) Selected tab query arg.
	 * @since 0.1.0
	 *
	 */
	public function __construct( string $title, string $selected_tab_query_arg = 'subpage' ) {
		$this->_title                  = $title;
		$this->_selected_tab_query_arg = $selected_tab_query_arg;
	}

	/**
	 * Adds tab to navigation.
	 *
	 * @param string $title Tab title.
	 * @param string $url   Admin page URL.
	 * @param string|boolean $tab_slug (optional) The tab slug used for matching active tab.
	 *@since 0.1.0
	 *
	 */
	public function add_tab( string $title, string $url, $tab_slug = false ) {
		if ( false === $tab_slug ) {
			$tab_slug = sanitize_key( $title );
		}
		$this->_tabs[ $tab_slug ] = array(
			'title' => $title,
			'url'   => $url,
		);
	}

	/**
	 * Removes tab from navigation.
	 *
	 * @param string $title Tab title.
	 * @since 0.1.0
	 *
	 */
	public function remove_tab( string $title ) {
		$key = sanitize_key( $title );

		if ( isset( $this->_tabs[ $key ] ) ) {
			unset( $this->_tabs[ $key ] );
		}
	}

	/**
	 * Returns markup for tab navigation.
	 *
	 * @since 0.1.0
	 *
	 * @return string Tab markup.
	 */
	public function get_tabs(): string {
		$html = '<nav class="relative z-0 rounded-lg shadow flex divide-x divide-gray-200 mb-6" aria-label="Tabs">';

		$first = false;
		foreach ( $this->_tabs as $slug => $tab ) {
			$active_class = 'bg-transparent';
			$class        = 'text-gray-500 first:rounded-l-lg last:rounded-r-lg hover:text-gray-700 group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-sm font-medium text-center hover:bg-gray-50 focus:z-10';

			$match_url = str_replace( get_site_url(), '', $tab['url'] );
			if ( ( ! empty( $_GET[ $this->_selected_tab_query_arg ] ) && $_GET[ $this->_selected_tab_query_arg ] === $slug ) || html_entity_decode( $match_url ) === $_SERVER['REQUEST_URI'] ) {
				$active_class = 'bg-blue-500';
				$class        = 'text-gray-900 first:rounded-l-lg last:rounded-r-lg group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-sm font-medium text-center hover:bg-gray-50 focus:z-10';
			}

			$html .= sprintf( '<a href="%s" class="%s"><span>%s</span><span aria-hidden="true" class="%s absolute inset-x-0 bottom-0 h-0.5"></span></a>', esc_attr( $tab['url'] ), $class, esc_html( $tab['title'] ), $active_class );
		}

		$html .= '</nav>';

		return $html;
	}

	/**
	 * Outputs tab markup.
	 *
	 * @since 0.1.0
	 */
	public function display_tabs() {
		echo $this->get_tabs();
	}
}
