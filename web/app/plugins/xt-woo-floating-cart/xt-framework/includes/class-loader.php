<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Loader' ) ) {

	/**
	 * Register all actions and filters for the plugin.
	 *
	 * Maintain a list of all hooks that are registered throughout
	 * the plugin, and register them with the WordPress API. Call the
	 * run function to execute the list of actions and filters.
	 *
	 * @package    XT_Framework
	 * @subpackage XT_Framework/includes
	 * @author     XplodedThemes
	 */
	class XT_Framework_Loader {

		/**
		 * The array of actions registered with WordPress.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      array $actions The actions registered with WordPress to fire when the plugin loads.
		 */
		protected $actions;

		/**
		 * The array of filters registered with WordPress.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      array $filters The filters registered with WordPress to fire when the plugin loads.
		 */
		protected $filters;

		/**
		 * Initialize the collections used to maintain the actions and filters.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->actions = array();
			$this->filters = array();
		}

		/**
		 * Add a new action to the collection to be registered with WordPress.
		 *
		 * @param string $hook The name of the WordPress action that is being registered.
		 * @param object $component A reference to the instance of the object on which the action is defined.
		 * @param string $callback The name of the function definition on the $component.
		 * @param int $priority Optional. he priority at which the function should be fired. Default is 10.
		 * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
		 *
		 * @since    1.0.0
		 */
		public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
			$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );

			add_action( $hook, array( $component, $callback ), $priority, $accepted_args );
		}

        /**
         * Remove action from the collection to be registered with WordPress.
         *
         * @param string $hook The name of the WordPress action that is being registered.
         * @param object $component A reference to the instance of the object on which the action is defined.
         * @param string $callback The name of the function definition on the $component.
         * @param int $priority Optional. he priority at which the function should be fired. Default is 10.
         * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
         *
         * @since    1.0.0
         */
        public function remove_action( $hook, $component, $callback ) {
            $this->actions = $this->remove( $this->actions, $hook, $component, $callback );

            remove_action( $hook, array( $component, $callback ));
        }

		/**
		 * Add a new filter to the collection to be registered with WordPress.
		 *
		 * @param string $hook The name of the WordPress filter that is being registered.
		 * @param object $component A reference to the instance of the object on which the filter is defined.
		 * @param string $callback The name of the function definition on the $component.
		 * @param int $priority Optional. he priority at which the function should be fired. Default is 10.
		 * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1
		 *
		 * @since    1.0.0
		 */
		public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
			$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );

			add_filter( $hook, array( $component, $callback ), $priority, $accepted_args );
		}

        /**
         * Remove filter from the collection to be registered with WordPress.
         *
         * @param string $hook The name of the WordPress filter that is being registered.
         * @param object $component A reference to the instance of the object on which the filter is defined.
         * @param string $callback The name of the function definition on the $component.
         * @param int $priority Optional. he priority at which the function should be fired. Default is 10.
         * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1
         *
         * @since    1.0.0
         */
        public function remove_filter( $hook, $component, $callback ) {
            $this->filters = $this->remove( $this->filters, $hook, $component, $callback);

            remove_filter( $hook, array( $component, $callback ));
        }

        /**
         * Check if has action within the collection of registered with WordPress.
         *
         * @param string $hook The name of the WordPress action that is being registered.
         * @param object $component A reference to the instance of the object on which the action is defined.
         * @param string $callback The name of the function definition on the $component.
         *
         * @since    1.0.0
         */
        public function has_action( $hook, $component = null, $callback = null ) {
            foreach ( $this->actions as $action ) {

                if ( $action['hook'] === $hook ) {

                    if ( ! empty( $component ) && ! empty( $callback ) ) {

                        if ( $action['component'] === $component && $action['callback'] === $callback ) {
                            return true;
                        } else {
                            return false;
                        }

                    } else {
                        return true;
                    }
                }
            }

            return false;
        }

		/**
		 * Check if has filter within the collection of registered with WordPress.
		 *
		 * @param string $hook The name of the WordPress filter that is being registered.
		 * @param object $component A reference to the instance of the object on which the filter is defined.
		 * @param string $callback The name of the function definition on the $component.
		 *
		 * @since    1.0.0
		 */
		public function has_filter( $hook, $component = null, $callback = null ) {
			foreach ( $this->filters as $filter ) {

				if ( $filter['hook'] === $hook ) {

					if ( ! empty( $component ) && ! empty( $callback ) ) {

						if ( $filter['component'] === $component && $filter['callback'] === $callback ) {
							return true;
						} else {
							return false;
						}

					} else {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * A utility function that is used to register the actions and hooks into a single
		 * collection.
		 *
		 * @param array $hooks The collection of hooks that is being registered (that is, actions or filters).
		 * @param string $hook The name of the WordPress filter that is being registered.
		 * @param object $component A reference to the instance of the object on which the filter is defined.
		 * @param string $callback The name of the function definition on the $component.
		 * @param int $priority The priority at which the function should be fired.
		 * @param int $accepted_args The number of arguments that should be passed to the $callback.
		 *
		 * @return   array  The collection of actions and filters registered with WordPress.
		 * @since    1.0.0
		 * @access   private
		 */
		private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

			$hooks[] = array(
				'hook'          => $hook,
				'component'     => $component,
				'callback'      => $callback,
				'priority'      => $priority,
				'accepted_args' => $accepted_args
			);

			return $hooks;

		}

        /**
         * A utility function that is used to unregister the actions and hooks from the
         * collection.
         *
         * @param array $hooks The collection of hooks that is being registered (that is, actions or filters).
         * @param string $hook The name of the WordPress filter that is being registered.
         * @param object $component A reference to the instance of the object on which the filter is defined.
         * @param string $callback The name of the function definition on the $component.
         * @param int $priority The priority at which the function should be fired.
         * @param int $accepted_args The number of arguments that should be passed to the $callback.
         *
         * @return   array  The collection of actions and filters registered with WordPress.
         * @since    1.0.0
         * @access   private
         */
        private function remove( $hooks, $hook, $component, $callback ) {

            foreach ( $hooks as $key => $_hook ) {

                if ( $_hook['hook'] === $hook &&  $_hook['component'] === $component && $_hook['callback'] === $callback) {

                    unset($hooks[$key]);
                    return $hooks;
                }
            }

            return $hooks;
        }

		/**
		 * Get all loader filters registered with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function get_filters() {

			return $this->filters;
		}

		/**
		 * Get all loader actions registered with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function get_actions() {

			return $this->actions;
		}
	}
}