<?php

namespace AmeliaBooking\Infrastructure\WP\WPMenu;

/**
 * Class Submenu
 *
 * @package AmeliaBooking\Infrastructure\WPMenu
 */
class Submenu
{

    /** @var SubmenuPageHandler $submenuHandler */
    private $submenuHandler;

    /** @var  array $menu */
    private $menu;

    /**
     * Submenu constructor.
     *
     * @param SubmenuPageHandler $submenuHandler
     * @param array              $menu
     */
    public function __construct($submenuHandler, $menu)
    {
        $this->submenuHandler = $submenuHandler;
        $this->menu = $menu;
    }

    /**
     * Initialize admin menu in WP
     */
    public function init()
    {
        add_action('admin_menu', [$this, 'addOptionsPages']);
    }

    /**
     * Add options in WP menu
     */
    public function addOptionsPages()
    {
        add_menu_page(
            'Amelia Booking',
            'Amelia',
            'amelia_read_menu',
            'amelia',
            '',
            AMELIA_URL . 'public/img/amelia-logo-admin-icon.svg'
        );

        foreach ($this->menu as $menu) {
            $this->handleMenuItem($menu);
        }

        remove_submenu_page('amelia', 'amelia');

    }

    /**
     * @param array $menu
     */
    public function handleMenuItem($menu)
    {
        $this->addSubmenuPage(
            $menu['parentSlug'],
            $menu['pageTitle'],
            $menu['menuTitle'],
            $menu['capability'],
            $menu['menuSlug'],
            function () use ($menu) {
                $this->submenuHandler->render($menu['menuSlug']);
            }
        );
    }

    /**
     * @noinspection MoreThanThreeArgumentsInspection
     *
     * @param        $parentSlug
     * @param        $pageTitle
     * @param        $menuTitle
     * @param        $capability
     * @param        $menuSlug
     * @param string $function
     */
    private function addSubmenuPage($parentSlug, $pageTitle, $menuTitle, $capability, $menuSlug, $function = '')
    {
        add_submenu_page(
            $parentSlug,
            $pageTitle,
            $menuTitle,
            $capability,
            $menuSlug,
            $function
        );
    }
}
