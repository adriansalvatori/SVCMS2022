<?php /** @noinspection PhpUndefinedClassInspection */
/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2020 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Liker;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

use Exception;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Merkulove\Liker\Unity\Plugin as UnityPlugin;

/** @noinspection PhpUnused */
/**
 * Liker - Custom Elementor Widget.
 **/
final class liker_elementor extends Widget_Base {

    /**
     * Use this to sort widgets.
     * A smaller value means earlier initialization of the widget.
     * Can take negative values.
     * Default widgets and widgets from 3rd party developers have 0 $mdp_order
     **/
    public $mdp_order = 1;

    /**
     * Widget base constructor.
     * Initializing the widget base class.
     *
     * @access public
     * @throws Exception If arguments are missing when initializing a full widget instance.
     * @param array      $data Widget data. Default is an empty array.
     * @param array|null $args Optional. Widget default arguments. Default is null.
     *
     * @return void
     **/
    public function __construct( $data = [], $args = null ) {

        parent::__construct( $data, $args );

        wp_register_style( 'mdp-liker-elementor-admin', UnityPlugin::get_url() . 'src/Merkulove/Unity/assets/css/elementor-admin' . UnityPlugin::get_suffix() . '.css', [], UnityPlugin::get_version() );
        //wp_register_style( 'mdp-liker', UnityPlugin::get_url() . 'css/liker' . UnityPlugin::get_suffix() . '.css', [], UnityPlugin::get_version() );
	    //wp_register_script( 'mdp-liker', UnityPlugin::get_url() . 'js/liker' . UnityPlugin::get_suffix() . '.js', [ 'jquery', 'elementor-frontend' ], UnityPlugin::get_version(), true );

    }

    /**
     * Return a widget name.
     *
     * @return string
     **/
    public function get_name() {

        return 'mdp-liker-elementor';

    }

    /**
     * Return the widget title that will be displayed as the widget label.
     *
     * @return string
     **/
    public function get_title() {

        return esc_html__( 'Liker', 'liker' );

    }

    /**
     * Set the widget icon.
     *
     * @return string
     */
    public function get_icon() {

        return 'mdp-liker-elementor-widget-icon';

    }

    /**
     * Set the category of the widget.
     *
     * @return array with category names
     **/
    public function get_categories() {

        return [ 'general' ];

    }

    /**
     * Get widget keywords. Retrieve the list of keywords the widget belongs to.
     *
     * @access public
     *
     * @return array Widget keywords.
     **/
    public function get_keywords() {

        return [ 'Merkulove', 'Liker' ];

    }

    /**
     * Get style dependencies.
     * Retrieve the list of style dependencies the widget requires.
     *
     * @access public
     *
     * @return array Widget styles dependencies.
     **/
    public function get_style_depends() {

        return [ 'mdp-liker', 'mdp-liker-elementor-admin' ];

    }

	/**
	 * Get script dependencies.
	 * Retrieve the list of script dependencies the element requires.
	 *
	 * @access public
     *
	 * @return array Element scripts dependencies.
	 **/
	public function get_script_depends() {

		return [ 'mdp-liker' ];

    }

    /**
     * Add the widget controls.
     *
     * @access protected
     * @return void with category names
     **/
    protected function _register_controls() {

        /** Content Tab. */
        $this->tab_content();

        /** Style Tab. */
        $this->tab_style();

    }

    /**
     * Add widget controls on Content tab.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function tab_content() {

        /** Content -> Example Content Section. */
        $this->section_content_example();

    }

    /**
     * Add widget controls on Style tab.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function tab_style() {

        /** Style -> Section Style Example. */
        $this->section_style_example();

    }

    /**
     * Add widget controls: Content -> Example Content Section.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function section_content_example() {

        $this->start_controls_section( 'section_content_example', [
            'label' => esc_html__( 'Example Content Section', 'liker' ),
            'tab'   => Controls_Manager::TAB_CONTENT
        ] );

            # Source field
            $this->add_control(
                'select_field',
                [
                    'label'     => esc_html__( 'Select Filed', 'liker' ),
                    'type'      => Controls_Manager::SELECT,
                    'options'   => [
                        'option-1'    => esc_html__( 'Option 1', 'liker' ),
                        'option-2'    => esc_html__( 'Option 2', 'liker' ),
                        'option-3'    => esc_html__( 'Option 3', 'liker' ),
                    ],
                    'default'   => 'option-1',
                    'separator' => 'none',
                ]
            );

        $this->end_controls_section();

    }

    /**
     * Add widget controls: Style -> Section Style Example.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function section_style_example() {

        $this->start_controls_section( 'section_style_example', [
            'label' => esc_html__( 'Section Style Example', 'liker' ),
            'tab'   => Controls_Manager::TAB_STYLE
        ] );

            # Other Filed
            $this->add_control(
            'other_field_2',
            [
                'label'     => esc_html__( 'Other Filed', 'liker' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'option-1'    => esc_html__( 'Option 1', 'liker' ),
                    'option-2'    => esc_html__( 'Option 2', 'liker' ),
                    'option-3'    => esc_html__( 'Option 3', 'liker' ),
                ],
                'default'   => 'option-1',
                'separator' => 'none',
            ]
        );

        $this->end_controls_section();

    }

    /**
     * Render Frontend Output. Generate the final HTML on the frontend.
     *
     * @access protected
     *
     * @return void
     **/
    protected function render() {

        ?>
        <!-- Start Liker WordPress Plugin -->
        <div class="mdp-liker-box">
            <?php esc_html_e( 'Add your code here.', 'liker' );  ?>
        </div>
        <!-- End Liker WordPress Plugin -->
	    <?php

    }

    /**
     * Return link for documentation
     * Used to add stuff after widget
     *
     * @access public
     *
     * @return string
     **/
    public function get_custom_help_url() {

        return 'https://docs.merkulov.design/tag/liker';

    }

}
