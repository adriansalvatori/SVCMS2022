<?php
/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2022 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Liker;

use Merkulove\Liker\Unity\Plugin;
use Merkulove\Liker\Unity\Settings;
use Merkulove\Liker\Unity\TabGeneral;
use Merkulove\Liker\Unity\UI;
use Merkulove\Liker\Unity\Helper;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Settings class used to modify default plugin settings.
 *
 * @since 1.0.0
 *
 **/
final class Config {

	/**
	 * The one true Settings.
	 *
     * @since 1.0.0
     * @access private
	 * @var Config
	 **/
	private static $instance;

    /**
     * Prepare plugin settings by modifying the default one.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function prepare_settings() {

        /** Get default plugin settings. */
        $tabs = Plugin::get_tabs();

	    /** Set System Requirements. */
	    $tabs['status']['reports']['server']['allow_url_fopen'] = false;
	    $tabs['status']['reports']['server']['dom_installed'] = false;
	    $tabs['status']['reports']['server']['xml_installed'] = false;
	    $tabs['status']['reports']['server']['bcmath_installed'] = false;

		/** Short hand access to plugin settings. */
		$options = Settings::get_instance()->options;

		/** General Tab */

	    $tabs['general']['fields']['cpt_support'] = [
		    'type'              => 'cpt',
		    'label'             => esc_html__( 'Post Types:', 'liker' ),
		    'show_label'        => true,
		    'description'       => esc_html__( 'Select post types for which the plugin will work..', 'liker' ),
		    'show_description'  => true,
		    'default'           => [ 'post', 'page' ],
		    'options'           => Helper::get_instance()->get_cpt( [ 'exclude' => [ 'attachment', 'elementor_library' ] ] ),
		    'attr'              => [
			    'multiple' => 'multiple',
		    ]

	    ];

		$tabs['general']['fields']['position'] = [
			'type'              => 'select',
			'label'             => esc_html__( 'Position:', 'liker' ),
			'show_label'        => true,
			'placeholder'       => esc_html__( 'Position', 'liker' ),
			'description'       => esc_html__( 'Select a place on the page to display Liker.', 'liker' ),
			'show_description'  => true,
			'default'           => 'after-content',
			'options'           => [
				'before-title'      => esc_html__( 'Before Title', 'liker' ),
				'after-title'       => esc_html__( 'After Title', 'liker' ),
				'before-content'    => esc_html__( 'Before Content', 'liker' ),
				'after-content'     => esc_html__( 'After Content', 'liker' ),
				'shortcode'         => esc_html__( 'Shortcode Only [liker]', 'liker' ),
			]
		];

		$tabs['general']['fields']['type'] = [
			'type'              => 'select',
			'label'             => esc_html__( 'Liker type:', 'liker' ),
			'show_label'        => true,
			'placeholder'       => esc_html__( 'Liker type', 'liker' ),
			'description'       => esc_html__( 'Select the button(s) captions and type of rating.', 'liker' ),
			'show_description'  => true,
			'default'           => 'two-buttons',
			'options'           => [
				'three-buttons' => esc_html__( 'Three Buttons', 'liker' ),
				'two-buttons' => esc_html__( 'Two Buttons', 'liker' ),
				'one-button' => esc_html__( 'One Button', 'liker' ),
			]
		];

		$tabs['general']['fields']['buttons_caption'] = [
			'type'              => 'buttons_caption',
			'render'            => [ $this, 'render_caption' ],
			'label'             => esc_html__( 'Button(s) caption:', 'liker' ),
			'show_label'        => true,
			'description'       => '',
			'show_description'  => false,
			'default'           => '',
		];

		$tabs[ 'general' ][ 'fields' ][ 'layout' ] = [
			'type' => 'layout',
			'label' => esc_html__( 'Layout:', 'liker' ),
			'show_label'        => true,
			'placeholder'       => esc_html__( 'Layout', 'liker' ),
			'description'       => esc_html__( 'Select a layout for buttons and description', 'liker' ),
			'show_description'  => true,
			'default'           => 'row-right',
			'options'           => [
				'bottom-left' => esc_html__( 'Bottom Left', 'liker'),
				'bottom-center' => esc_html__( 'Bottom Center', 'liker'),
				'bottom-right' => esc_html__( 'Bottom Right', 'liker'),
				'row-left' => esc_html__( 'Row Left', 'liker'),
				'row-right' => esc_html__( 'Row Right', 'liker'),
				'top-center' => esc_html__( 'Top Center', 'liker'),
				'top-left' => esc_html__( 'Top Left', 'liker'),
				'top-right' => esc_html__( 'Top Right', 'liker')
			]
		];

	    $tabs['general']['fields']['divider_layouts'] = [
		    'type'              => 'divider',
		    'label'             => '',
		    'show_label'        => false,
		    'default'           => '',
	    ];

		$tabs[ 'general' ][ 'fields' ][ 'description' ] = [
			'type'              => 'editor',
			'label'             => esc_html__( 'Description', 'liker' ),
			'show_label'        => true,
			'description'       => '',
			'show_description'  => false,
			'default'           => '<h4>' . esc_html__( 'Was this helpful?', 'liker' ) . '</h4>',
			'attr'              => [
				'textarea_rows' => '3',
			]
		];

		/** Design Tab */

	    $offset = 1;
	    $tabs = array_slice( $tabs, 0, $offset, true ) +
            ['design' => [
                'enabled'       => true,
                'class'         => TabGeneral::class, // Handler
                'label'         => esc_html__( 'Design', 'liker' ),
                'title'         => esc_html__( 'Buttons design settings', 'liker' ),
                'show_title'    => true,
                'icon'          => 'palette', // Icon for tab
                'fields'        => []
            ] ] +
            array_slice( $tabs, $offset, NULL, true );

	    $tabs['design']['fields']['style'] = [
		    'type'              => 'select_img',
		    'label'             => esc_html__( 'Button(s) style:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Button(s) style', 'liker' ),
		    'description'       => esc_html__( 'Select visual style for the Liker button(s).', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'style-unset',
		    'options'           => [
			    'style-border'      => esc_html__( 'Border button', 'liker' ),
			    'style-fill'        => esc_html__( 'Fill button', 'liker' ),
			    'style-border-fill' => esc_html__( 'Border to Fill', 'liker' ),
			    'style-fill-border' => esc_html__( 'Fill to Border', 'liker' ),
			    'style-unset'       => esc_html__( 'Theme styled button', 'liker' ),
		    ]
	    ];

	    $tabs['design']['fields']['text_color'] = [
		    'type'              => 'colorpicker',
		    'label'             => esc_html__( 'Caption color:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Text color', 'liker' ),
		    'description'       => esc_html__( 'Select the button(s) text and icon color', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'rgba(255, 33, 40, 1)',
		    'attr'              => [
			    'readonly'      => 'readonly',
		    ]
	    ];

	    $tabs['design']['fields']['bg_color'] = [
		    'type'              => 'colorpicker',
		    'label'             => esc_html__( 'Base color:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Base color', 'liker' ),
		    'description'       => esc_html__( 'Select the button(s) background and border color', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'rgba(255, 255, 255, 1)',
		    'attr'              => [
			    'readonly'      => 'readonly',
		    ]
	    ];

	    $tabs['design']['fields']['divider_active'] = [
		    'type'              => 'divider',
		    'label'             => '',
		    'show_label'        => false,
		    'default'           => '',
	    ];

	    $tabs['design']['fields']['text_color_active'] = [
		    'type'              => 'colorpicker',
		    'label'             => esc_html__( 'Active button caption color:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Text color', 'liker' ),
		    'description'       => esc_html__( 'Select the active button text and icon color', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'rgba(255, 255, 255, 1)',
		    'attr'              => [
			    'readonly'      => 'readonly',
		    ]
	    ];

	    $tabs['design']['fields']['bg_color_active'] = [
		    'type'              => 'colorpicker',
		    'label'             => esc_html__( 'Active button base color:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Active button base color:', 'liker' ),
		    'description'       => esc_html__( 'Select the background and border color of active button(s)', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'rgba(255, 33, 40, 1)',
		    'attr'              => [
			    'readonly'      => 'readonly',
		    ]
	    ];

	    $tabs['design']['fields']['divider_hover'] = [
		    'type'              => 'divider',
		    'label'             => '',
		    'show_label'        => false,
		    'default'           => '',
	    ];

	    $tabs['design']['fields']['text_color_hover'] = [
		    'type'              => 'colorpicker',
		    'label'             => esc_html__( 'Hover button caption color:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Text color', 'liker' ),
		    'description'       => esc_html__( 'Select the hover button text and icon color', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'rgba(255, 255, 255, 1)',
		    'attr'              => [
			    'readonly'      => 'readonly',
		    ]
	    ];

	    $tabs['design']['fields']['bg_color_hover'] = [
		    'type'              => 'colorpicker',
		    'label'             => esc_html__( 'Hover button base color:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Hover button base color:', 'liker' ),
		    'description'       => esc_html__( 'Select the background and border color of hover button(s)', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'rgba(255, 33, 40, 1)',
		    'attr'              => [
			    'readonly'      => 'readonly',
		    ]
	    ];

	    $tabs['design']['fields']['divider_default'] = [
		    'type'              => 'divider',
		    'label'             => '',
		    'show_label'        => false,
		    'default'           => '',
	    ];

	    $backend_options = get_option( 'mdp_liker_design_settings' );

        $default = 8;
        $key = 'radius';
        $tabs[ 'design' ][ 'fields' ][ $key ] = [
            'type'              => 'slider',
            'label'             => esc_html__( 'Button border-radius:', 'liker' ),
            'show_label'        => true,
            'description'       => esc_html__( 'Border-radius:', 'liker' ) .
                                   ' <strong>' .
                                   esc_html( isset( $backend_options[ $key ] ) ? $backend_options[ $key ] : $default ) .
                                   '</strong>' .
                                   esc_html__( ' px', 'liker' ),
            'show_description'  => true,
            'min'               => 0,
            'max'               => 50,
            'step'              => 1,
            'default'           => $default,
            'discrete'          => true,
        ];

        $default = 1;
        $key = 'border';
        $tabs[ 'design' ][ 'fields' ][ $key ] = [
            'type'              => 'slider',
            'label'             => esc_html__( 'Button border-width:', 'liker' ),
            'show_label'        => true,
            'description'       => esc_html__( 'Border-width:', 'liker' ) .
                                   ' <strong>' .
                                   esc_html( isset( $backend_options[ $key ] ) ? $backend_options[ $key ] : $default ) .
                                   '</strong>' .
                                   esc_html__( ' px', 'liker' ),
            'show_description'  => true,
            'min'               => 0,
            'max'               => 10,
            'step'              => 1,
            'default'           => $default,
            'discrete'          => true,
        ];

        $default = 14;
        $key = 'size';
        $tabs[ 'design' ][ 'fields' ][ $key ] = [
            'type'              => 'slider',
            'label'             => esc_html__( 'Button caption size:', 'liker' ),
            'description'       => esc_html__( 'Caption size:', 'liker' ) .
                                   ' <strong>' .
                                   esc_html( isset( $backend_options[ $key ] ) ? $backend_options[ $key ] : $default ) .
                                   '</strong>' .
                                   esc_html__( ' px', 'liker' ),
            'min'               => 0,
            'max'               => 40,
            'step'              => 1,
            'default'           => $default,
            'discrete'          => true,
        ];

	    $default = 22;
	    $key = 'padding-horizontal';
	    $tabs[ 'design' ][ 'fields' ][ $key ] = [
		    'type'              => 'slider',
		    'label'             => esc_html__( 'Horizontal padding:', 'liker' ),
		    'description'       => esc_html__( 'Horizontal button padding:', 'liker' ) .
		                           ' <strong>' .
		                           esc_html( isset( $backend_options[ $key ] ) ? $backend_options[ $key ] : $default ) .
		                           '</strong>' .
		                           esc_html__( ' px', 'liker' ),
		    'min'               => 0,
		    'max'               => 40,
		    'step'              => 1,
		    'default'           => $default,
		    'discrete'          => true,
	    ];

	    $default = 16;
	    $key = 'padding-vertical';
	    $tabs[ 'design' ][ 'fields' ][ $key ] = [
		    'type'              => 'slider',
		    'label'             => esc_html__( 'Vertical padding:', 'liker' ),
		    'description'       => esc_html__( 'Vertical button padding:', 'liker' ) .
		                           ' <strong>' .
		                           esc_html( isset( $backend_options[ $key ] ) ? $backend_options[ $key ] : $default ) .
		                           '</strong>' .
		                           esc_html__( ' px', 'liker' ),
		    'min'               => 0,
		    'max'               => 40,
		    'step'              => 1,
		    'default'           => $default,
		    'discrete'          => true,
	    ];

		/** Results Tab */

		$offset = 2;
		$tabs = array_slice( $tabs, 0, $offset, true ) +
	        ['backend' => [
		        'enabled'       => true,
		        'class'         => TabGeneral::class, // Handler
		        'label'         => esc_html__( 'Results', 'liker' ),
		        'title'         => esc_html__( 'Results Display Settings', 'liker' ),
		        'show_title'    => true,
		        'icon'          => 'favorite', // Icon for tab
		        'fields'        => []
	        ] ] +
	        array_slice( $tabs, $offset, NULL, true );

		$tabs[ 'backend' ][ 'fields' ][ 'results' ] = [ 'type' => 'select',
            'label' => esc_html__( 'Results on front-end:', 'liker' ),
            'show_label'        => true,
            'placeholder'       => esc_html__( 'Results', 'liker' ),
            'description'       => esc_html__( 'Show/Hide ranking results on the front-end.', 'liker' ),
            'show_description'  => true,
            'default'           => 'hide',
            'options'           => [
	            'show' => esc_html__( 'Display results', 'liker' ),
	            'hide' => esc_html__( 'Hide results', 'liker' )
            ]
		];

	    $tabs[ 'backend' ][ 'fields' ][ 'display' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Results before voting:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Show results before voting', 'liker' ),
		    'description'       => esc_html__( 'Show/Hide results before voting', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'off',
	    ];

	    $tabs[ 'backend' ][ 'fields' ][ 'vote' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'User vote:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Show user vote', 'liker' ),
		    'description'       => esc_html__( 'Show/Hide user vote if already voted', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'on',
	    ];

	    $tabs[ 'backend' ][ 'fields' ][ 'revoting' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Re-voting:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Update voting', 'liker' ),
		    'description'       => esc_html__( 'Allow/Deny updating previous voting', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'on',
	    ];

	    $tabs[ 'backend' ][ 'fields' ][ 'meta' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'After Page Title:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Likes counter after title', 'liker' ),
		    'description'       => esc_html__( 'Show/Hide likes counter after the Page title', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'off',
	    ];

	    $tabs['backend']['fields']['divider_limits'] = [
		    'type'              => 'divider',
		    'label'             => '',
		    'show_label'        => false,
		    'default'           => '',
	    ];

	    $tabs[ 'backend' ][ 'fields' ][ 'limit_by_ip' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Voting IP limit:', 'liker' ),
		    'placeholder'       => esc_html__( 'Limit voting by IP', 'liker' ),
		    'description'       => esc_html__( 'Enable/Disable voting limit by IP address', 'liker' ),
		    'default'           => 'on',
	    ];

	    $backend_options = is_array( get_option( 'mdp_liker_backend_settings' ) );
	    $key = 'voting_limit';
	    $default = 1;
	    $tabs[ 'backend' ][ 'fields' ][$key] = [
		    'type'              => 'slider',
		    'label'             => esc_html__( 'Voting limit:', 'liker' ),
		    'show_label'        => true,
		    'description'       => esc_html__( 'The limit of votes from one IP:', 'liker' ) .
		                           ' <strong>' .
		                           esc_html( ( $backend_options ) ? get_option( 'mdp_liker_backend_settings' )[ $key ] : $default ) .
		                           '</strong>',
		    'show_description'  => true,
		    'min'               => 1,
		    'max'               => 10,
		    'step'              => 1,
		    'default'           => $default,
		    'discrete'          => true,
	    ];

	    $tabs[ 'backend' ][ 'fields' ][ 'limit_msg' ] = [
            'type' => 'text',
            'label'             => esc_html__( 'Limit exceeded message:', 'liker' ),
            'show_label'        => true,
            'placeholder'       => esc_html__( 'Message', 'liker' ),
            'description'       => esc_html__( 'This message will be shown when the user exceeds the vote limit.', 'liker' ),
            'show_description'  => true,
            'default'           => esc_html__( 'You have already voted. Sorry.', 'liker' ),
            'attr'              => [
              'maxlength' => '300'
            ]
	    ];

	    $tabs['backend']['fields']['divider_frontend'] = [
		    'type'              => 'divider',
		    'label'             => '',
		    'show_label'        => false,
		    'default'           => '',
	    ];

		$tabs[ 'backend' ][ 'fields' ][ 'results_admin' ] = [ 'type' => 'select',
            'label' => esc_html__( 'Results in the admin area:', 'liker' ),
            'show_label'        => true,
            'placeholder'       => esc_html__( 'Results', 'liker' ),
            'description'       => esc_html__( 'The format for displaying the results in the admin area', 'liker' ),
            'show_description'  => true,
            'default'           => 'amount',
            'options'           => [
	            'amount' => esc_html__( 'Amount', 'liker' ),
	            'total' => esc_html__( 'Amount / Total', 'liker' ),
	            'split' => esc_html__( '+1 | 0 | -1', 'liker' )
            ]
		];

	    $tabs[ 'backend' ][ 'fields' ][ 'progressbar' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Progressbar bar:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Display progressbar bar', 'liker' ),
		    'description'       => esc_html__( 'Show/Hide progress bar in the list of posts', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'on',
	    ];

		$tabs['backend']['fields']['divider_reset'] = [
			'type'              => 'divider',
			'label'             => '',
			'show_label'        => false,
			'default'           => '',
		];

		$tabs[ 'backend' ][ 'fields' ][ 'reset_liker_results' ] = [ 'type'  => 'button',
            'label'             => esc_html__( 'Reset ratings:', 'liker' ),
            'show_label'        => true,
            'placeholder'       => esc_html__( 'Reset', 'liker' ),
            'description'       => esc_html__( 'Press to reset completely', 'liker' ),
            'show_description'  => true,
            'default'           => '',
            'icon'              => 'close',
            'attr'              => [
                'class'     => 'mdp-reset mdc-button--unelevated',
                "id" => "reset",
            ]
		];

		/** Rich Snippet Tab */

		$offset = 3;
		$tabs = array_slice( $tabs, 0, $offset, true ) +
		        [ 'schema' => [
			        'enabled'       => true,
			        'class'         => TabGeneral::class, // Handler
			        'label'         => esc_html__( 'Rich Snippet', 'liker' ),
			        'title'         => esc_html__( 'Rich Snippet Settings', 'liker' ),
			        'show_title'    => true,
			        'icon'          => 'pageview', // Icon for tab
			        'fields'        => []
		        ] ] +
		        array_slice( $tabs, $offset, NULL, true );

		$tabs[ 'schema' ][ 'fields' ][ 'google_search_results' ] = [
			'type'              => 'switcher',
			'label'             => esc_html__( 'Schema Markup:', 'liker' ),
			'show_label'        => true,
			'placeholder'       => '',
			'description'       => esc_html__( 'SEO microdata for search indexes', 'liker' ),
			'show_description'  => true,
			'default'           => 'off',
		];

		$tabs[ 'schema' ][ 'fields' ][ 'advanced_markup' ] = [
			'type'              => 'switcher',
			'label'             => esc_html__( 'Advanced Schema Markup:', 'liker' ),
			'show_label'        => true,
			'placeholder'       => esc_html__( 'Advanced Schema Markup', 'liker' ),
			'description'       => esc_html__( 'Provide the JSON+LD structure', 'liker' ),
			'show_description'  => true,
			'default'           => 'off',
		];

		$tabs[ 'schema' ][ 'fields' ][ 'json_ld' ] = [
			'type'              => 'textarea',
			'label'             => esc_html__( 'JSON+LD Markup:', 'liker' ),
			'show_label'        => true,
			'placeholder'       => esc_html__( 'JSON', 'liker' ),
			'description'       => esc_html__( 'The following variables are available: ', 'liker' ) .
			                       '<br><strong>[title]</strong>' . esc_html__( ' — Post Title.', 'liker' ) .
			                       '<br><strong>[value]</strong>' . esc_html__( ' — A numerical quality rating for the item.', 'liker' ) .
			                       '<br><strong>[best]</strong>' . esc_html__( ' — The highest value allowed in this rating system.', 'liker' ) .
			                       '<br><strong>[count]</strong>' . esc_html__( ' — The total number of ratings for the post on your site. ', 'liker' ),
			'show_description'  => true,
			'default'           => $this->get_json_ld_default_value(),
			'attr'              => [
				'spellcheck'    => 'false',
				'wrapper-class' => 'mdp-liker-backend-settings-json-ld',
			]
		];

	    $offset = 3;
	    $tabs = array_slice( $tabs, 0, $offset, true ) +
	            [ 'shortcode' => [
		            'enabled'       => true,
		            'class'         => TabGeneral::class, // Handler
		            'label'         => esc_html__( 'Shortcode', 'liker' ),
		            'title'         => esc_html__( 'Shortcode settings of Top-rated posts list', 'liker' ),
		            'show_title'    => true,
		            'icon'          => 'stars', // Icon for tab
		            'fields'        => []
	            ] ] +
	            array_slice( $tabs, $offset, NULL, true );
	    $shortcode_options = is_array( get_option( 'mdp_liker_shortcode_settings' ) );

	    # Shortcode Header.
	    $tabs['shortcode']['fields']['shortcode_header'] = [
		    'type'              => 'header',
		    'label'             => '',
		    'show_label'        => false,
		    'description'       => $this->get_shortcode_description(),
		    'show_description'  => true,
		    'default'           => ''
	    ];

	    $key = 'top_gutter';
	    $default = 11;
	    $tabs[ 'shortcode' ][ 'fields' ][ $key ] = [
		    'type'              => 'slider',
		    'label'             => esc_html__( 'Gutter:', 'liker' ),
		    'show_label'        => true,
		    'description'       => esc_html__( 'Space between posts:', 'liker' ) .
		                           ' <strong>' .
		                           esc_html( ( $shortcode_options ) ? get_option( 'mdp_liker_shortcode_settings' )[ $key ] : $default ) .
		                           '</strong>' . esc_html__( 'px', 'liker' ),
		    'show_description'  => true,
		    'min'               => 0,
		    'max'               => 100,
		    'step'              => 1,
		    'default'           => $default,
		    'discrete'          => true,
	    ];

	    $tabs[ 'shortcode' ][ 'fields' ][ 'top_image' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Post image:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Display post image', 'liker' ),
		    'description'       => esc_html__( 'Show/Hide post image', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'on',
	    ];

	    $tabs[ 'shortcode' ][ 'fields' ][ 'top_image_size' ] = [
		    'type' => 'select',
		    'label' => esc_html__( 'Image size :', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Image size', 'liker' ),
		    'description'       => esc_html__( 'Select the image size for post', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'large',
		    'options'           => [
			    'thumbnail' => esc_html__( 'Thumbnail', 'liker' ),
			    'medium' => esc_html__( 'Medium', 'liker' ),
			    'large' => esc_html__( 'Large', 'liker' ),
			    'full' => esc_html__( 'Full', 'liker' ),
		    ]
	    ];

	    $tabs[ 'shortcode' ][ 'fields' ][ 'top_equal' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Equal image:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Equal image height', 'liker' ),
		    'description'       => esc_html__( 'Allow/Deny equal image sizes', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'on',
	    ];

	    $key = 'top_height';
	    $default = 320;
	    $tabs[ 'shortcode' ][ 'fields' ][ $key ] = [
		    'type'              => 'slider',
		    'label'             => esc_html__( 'Image height:', 'liker' ),
		    'show_label'        => true,
		    'description'       => esc_html__( 'Image height:', 'liker' ) .
		                           ' <strong>' .
		                           esc_html( ( $shortcode_options ) ? get_option( 'mdp_liker_shortcode_settings' )[ $key ] : $default ) .
		                           '</strong>' . esc_html__( 'px', 'liker' ),
		    'show_description'  => true,
		    'min'               => 0,
		    'max'               => 640,
		    'step'              => 1,
		    'default'           => $default,
		    'discrete'          => true,
	    ];

	    $tabs[ 'shortcode' ][ 'fields' ][ 'top_title' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Post title:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Display post title', 'liker' ),
		    'description'       => esc_html__( 'Show/Hide post title', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'on',
	    ];

	    $tabs[ 'shortcode' ][ 'fields' ][ 'top_title_tag' ] = [
		    'type' => 'select',
		    'label' => esc_html__( 'Post title tag :', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Title HTML tag', 'liker' ),
		    'description'       => esc_html__( 'Select the tag for post title', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'h3',
		    'options'           => [
			    'h1' => esc_html__( 'H1', 'liker' ),
			    'h2' => esc_html__( 'H2', 'liker' ),
			    'h3' => esc_html__( 'H3', 'liker' ),
			    'h4' => esc_html__( 'H4', 'liker' ),
			    'h5' => esc_html__( 'H5', 'liker' ),
			    'h6' => esc_html__( 'H6', 'liker' ),
		    ]
	    ];

	    $tabs[ 'shortcode' ][ 'fields' ][ 'top_excerpt' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Post excerpt:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Display post excerpt', 'liker' ),
		    'description'       => esc_html__( 'Show/Hide post excerpt', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'off',
	    ];

	    $tabs[ 'shortcode' ][ 'fields' ][ 'top_rating' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Post rating:', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Display post rating', 'liker' ),
		    'description'       => esc_html__( 'Show/Hide post rating', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'on',
	    ];

	    $tabs[ 'shortcode' ][ 'fields' ][ 'dashicons' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Dashicons heart', 'liker' ),
		    'show_label'        => true,
		    'placeholder'       => esc_html__( 'Display dashicons heart', 'liker' ),
		    'description'       => esc_html__( 'Use Dashicons heart icon', 'liker' ),
		    'show_description'  => true,
		    'default'           => 'on',
	    ];

	    $key = 'top_size';
	    $default = 14;
	    $tabs[ 'shortcode' ][ 'fields' ][ $key ] = [
		    'type'              => 'slider',
		    'label'             => esc_html__( 'Rating icon size:', 'liker' ),
		    'show_label'        => true,
		    'description'       => esc_html__( 'Icon size:', 'liker' ) .
		                           ' <strong>' .
		                           esc_html( ( $shortcode_options ) ? get_option( 'mdp_liker_shortcode_settings' )[ $key ] : $default ) .
		                           '</strong>' . esc_html__( 'px', 'liker' ),
		    'show_description'  => true,
		    'min'               => 1,
		    'max'               => 50,
		    'step'              => 1,
		    'default'           => $default,
		    'discrete'          => true,
	    ];

	    # Divider
	    $key = 'assets_divider';
	    $tabs[ 'shortcode' ][ 'fields' ][ $key ] = [ 'type' => 'divider', 'default' => '' ];

	    # CSS and JS everywhere
	    $tabs[ 'shortcode' ][ 'fields' ][ 'assets' ] = [
		    'type'              => 'switcher',
		    'label'             => esc_html__( 'Shortcode assets:', 'liker' ),
		    'placeholder'       => esc_html__( 'Add everywhere', 'liker' ),
		    'description'       => esc_html__( 'Enable if you use shortcodes in widget positions or directly in theme code', 'liker' ),
		    'default'           => 'on',
	    ];

		/** Set updated tabs. */
		Plugin::set_tabs( $tabs );

		/** Refresh settings. */
		Settings::get_instance()->get_options();

	}

	/**
	 * Return HTML description for "shortcode_header" field.
	 *
	 * @since 2.1.1
	 *
	 * @return string
	 **/
	private function get_shortcode_description() {

		return esc_html__( 'Use shortcode ', 'liker' ) .
		       ' <code>[liker]</code> ' .
		       esc_html__( 'to display the rating block for current Post/Page or ', 'liker' ) .
		       ' <code>[liker id="1"]</code> ' .
		       esc_html__( 'to display the rating block for post width id="1". ', 'liker' ) .
               esc_html__( 'Add shortcode', 'liker' ) .
		       ' <code>[liker top="10" for="page" cols="4"]</code> ' .
		       esc_html__( 'to display the list of top-rated posts. Read more about the shortcode parameters in the ', 'liker' ) .
		       '<a href="https://docs.merkulov.design/start-with-the-liker-wordpress-plugin/#shortcode" target="_blank">' .
		       esc_html__( 'documentation', 'liker' ) .
		       '</a>';

	}

	/**
	 * Render Liker buttons captions.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function render_caption() {

	    /** Get general options */
	    $general_options = is_array( get_option( 'mdp_liker_general_settings' ) );

	    /** Set default caption settings */
	    if ( ! $general_options ) {
	        $default_captions = [
		        'caption_1' => '+1',
		        'caption_2' => '0',
		        'caption_3' => '-1',
            ];
	        add_option( 'mdp_liker_general_settings', $default_captions );
        }

		/** Render input group */
		?>
		<div class="mdp-row mdp-input-group">
			<div class="mdp-col">
		<?php

			UI::get_instance()->render_input(
				$general_options ? Settings::get_instance()->options[ 'caption_1' ] : '+1',
				esc_html__( 'Plus button', 'liker' ),
				esc_html__( 'Caption for +1 button', 'liker' ),
				[
					"name" => "mdp_liker_general_settings[caption_1]",
					"id" => "mdp_liker_general_settings_caption_1"
				]
			);

		?>
			</div>
			<div class="mdp-col">
		<?php

		UI::get_instance()->render_input(
			$general_options ? Settings::get_instance()->options[ 'caption_2' ] : '0',
			esc_html__( 'Neutral button', 'liker' ),
			esc_html__( 'Caption for 0 button', 'liker' ),
			[
				"name" => "mdp_liker_general_settings[caption_2]",
				"id" => "mdp_liker_general_settings_caption_2"
			]
		);

		?>
			</div>
			<div class="mdp-col">
		<?php

		UI::get_instance()->render_input(
			$general_options ? Settings::get_instance()->options[ 'caption_3' ] : '-1',
			esc_html__( 'Minus button', 'liker' ),
			esc_html__( 'Caption for -1 button', 'liker' ),
			[
				"name" => "mdp_liker_general_settings[caption_3]",
				"id" => "mdp_liker_general_settings_caption_3"
			]
		);

		?>
			</div>
		</div>
		<?php

	}

	/**
	 * Return json_ld default value.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 **/
	private function get_json_ld_default_value() {

		return '{
    "@context": "https://schema.org/",
    "@type": "CreativeWorkSeries",
    "name": "[title]",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "[value]",
        "bestRating": "[best]",
        "ratingCount": "[count]"
    }
}';

	}

	/**
	 * Main Settings Instance.
	 * Insures that only one instance of Settings exists in memory at any one time.
	 *
	 * @static
     * @since 1.0.0
     * @access public
     *
	 * @return Config
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}
