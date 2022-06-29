<?php

namespace Uncanny_Automator_Pro;

use ReflectionClass;
use ReflectionException;
use Uncanny_Automator\Automator_Exception;
use Uncanny_Automator\Set_Up_Automator;

/**
 * Class Internal_Triggers_Actions
 *
 * @package Uncanny_Automator_Pro
 */
class Internal_Triggers_Actions {
	/**
	 * The directories that are autoloaded and initialized
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array
	 */
	private $auto_loaded_directories = null;

	/**
	 * @var array|string[]
	 */
	public $default_directories = array();

	/**
	 * @var
	 */
	public $active_directories;

	/**
	 * @var
	 */
	public $directories_to_include = array();

	/**
	 * @var array
	 */
	public $all_integrations = array();

	/**
	 * @throws \Uncanny_Automator\Automator_Exception
	 */
	public function __construct() {
		if ( ! method_exists( '\Uncanny_Automator\Set_Up_Automator', 'read_directory' ) ) {
			add_action( 'admin_notices', array( '\Uncanny_Automator_Pro\Boot', 'free_needs_to_be_upgraded' ) );

			return;
		}
		// Path of the Pro integration directory
		$directory = dirname( AUTOMATOR_PRO_FILE ) . '/src/integrations';
		// Check if the cache exists
		$integrations = Automator()->cache->get( 'automator_pro_get_all_integrations' );
		if ( empty( $integrations ) ) {
			// Read directory again to grab all integration folders
			$integrations = Set_Up_Automator::read_directory( $directory );
			Automator()->cache->set( 'automator_pro_get_all_integrations', $integrations, 'automator', Automator()->cache->long_expires );
		}
		$this->all_integrations = apply_filters( 'automator_pro_integrations_setup', $integrations );

		// check if files with in directory exists
		$this->auto_loaded_directories = Automator()->cache->get( 'automator_pro_integration_directories_loaded' );
		if ( empty( $this->auto_loaded_directories ) ) {
			$this->auto_loaded_directories = Set_Up_Automator::extract_integration_folders( $integrations, $directory );
			Automator()->cache->set( 'automator_pro_integration_directories_loaded', $this->auto_loaded_directories, 'automator', Automator()->cache->long_expires );
		}
		// Add Pro integrations
		add_action( 'automator_add_integration', array( $this, 'init' ), 11 );
		// Add Pro integration helpers
		add_action( 'automator_add_integration_helpers', array( $this, 'add_integration_helpers' ), 13 );
		// Add Pro integration triggers/actions/etc
		add_action(
			'automator_add_integration_recipe_parts',
			array(
				$this,
				'boot_triggers_actions_closures',
			),
			15
		);
	}

	/**
	 *
	 * @throws \Exception
	 */
	public function init() {
		$this->initialize_add_integrations();
		$this->auto_loaded_directories = apply_filters_deprecated(
			'uncanny_automator_pro_integration_directory',
			array( $this->auto_loaded_directories ),
			'3.9',
			'automator_pro_integration_directory'
		);
		$this->auto_loaded_directories = apply_filters( 'automator_pro_integration_directory', $this->auto_loaded_directories );

		do_action_deprecated( 'uncanny_automator_pro_loaded', array(), '3.9', 'automator_pro_loaded' );
		do_action( 'automator_pro_loaded' );
	}

	/**
	 *
	 * @throws \Exception
	 */
	public function initialize_add_integrations() {
		if ( empty( $this->auto_loaded_directories ) ) {
			return;
		}
		// Check each directory
		foreach ( $this->auto_loaded_directories as $directory ) {
			$files    = array();
			$dir_name = basename( $directory );
			if ( ! isset( $this->all_integrations[ $dir_name ] ) ) {
				continue;
			}

			if ( ! isset( $this->all_integrations[ $dir_name ]['main'] ) || empty( $this->all_integrations[ $dir_name ]['main'] ) ) {
				continue;
			}

			$files[] = $this->all_integrations[ $dir_name ]['main'];
			if ( empty( $files ) ) {
				continue;
			}
			foreach ( $files as $file ) {
				// bail early if the $file is not a string
				if ( is_array( $file ) ) {
					continue;
				}

				if ( ! file_exists( $file ) ) {
					continue;
				}
				$file_name = basename( $file, '.php' );

				require_once $file;

				$class = apply_filters( 'automator_pro_integrations_class_name', $this->get_class_name( $file ), $file );
				try {
					$is_using_trait = ( new ReflectionClass( $class ) )->getTraits();
				} catch ( ReflectionException $e ) {
					throw new \Exception( $e->getMessage() );
				}
				$i                = new $class();
				$integration_code = ! empty( $is_using_trait ) ? $i->get_integration() : $class::$integration;
				$active           = ! empty( $is_using_trait ) ? $i->plugin_active() : $i->plugin_active( 0, $integration_code );
				$active           = apply_filters( 'automator_pro_maybe_integration_active', $active, $integration_code );
				if ( true !== $active ) {
					unset( $i );
					continue;
				}

				// Include only active integrations
				if ( method_exists( $i, 'add_integration_func' ) ) {
					$i->add_integration_func();
				}

				if ( ! in_array( $integration_code, Set_Up_Automator::$active_integrations_code, true ) ) {
					Set_Up_Automator::$active_integrations_code[] = $integration_code;
				}

				$this->active_directories[ $dir_name ] = $i;
				$this->active_directories              = apply_filters( 'automator_pro_active_integration_directories', $this->active_directories );
				if ( method_exists( $i, 'add_integration_directory_func' ) ) {
					$directories_to_include = $i->add_integration_directory_func( array(), $file );
					if ( $directories_to_include ) {
						foreach ( $directories_to_include as $dir ) {
							$this->directories_to_include[ $dir_name ][] = basename( $dir );
						}
					}
				}

				//Now everything is checked, add integration to the system.
				if ( method_exists( $i, 'add_integration' ) ) {
					$i->add_integration( $i->get_integration(), array( $i->get_name(), $i->get_icon() ) );
				}

				Utilities::add_class_instance( $class, $i );
			}
		}
	}

	/**
	 *
	 */
	public function add_integration_helpers() {
		if ( empty( $this->active_directories ) ) {
			return;
		}
		foreach ( $this->active_directories as $dir_name => $object ) {

			$files = isset( $this->all_integrations[ $dir_name ]['helpers'] ) && in_array( 'helpers', $this->directories_to_include[ $dir_name ], true ) ? $this->all_integrations[ $dir_name ]['helpers'] : array();

			if ( empty( $files ) ) {
				continue;
			}
			// Loop through all files in directory to create class names from file name
			foreach ( $files as $file ) {
				// bail early if the $file is not a string
				if ( is_array( $file ) ) {
					continue;
				}
				if ( ! file_exists( $file ) ) {
					continue;
				}
				require_once $file;
				// Remove file extension my-class-name.php to my-class-name
				$file_name = basename( $file, '.php' );
				$class     = apply_filters( 'automator_pro_helpers_class_name', $this->get_class_name( $file ), $file );
				if ( class_exists( $class ) ) {
					$mod = str_replace( '-', '_', $dir_name );
					try {
						$reflection = new ReflectionClass( $class );
						if ( $reflection->hasMethod( 'setOptions' ) ) {
							// Todo: Do not initiate helpers class.
							Utilities::add_helper_instances( $mod, new $class() );
						}
					} catch ( Automator_Exception $e ) {
						// is not a helper file.. shouldn't be loaded as helper
						Utilities::add_class_instance( $class, new $class() );
					}
				}
			}
		}

		Automator_Pro_Helpers_Recipe::load_pro_recipe_helpers();

	}

	/**
	 *
	 */
	public function boot_triggers_actions_closures() {
		if ( empty( $this->active_directories ) ) {
			return;
		}

		foreach ( $this->active_directories as $dir_name => $object ) {
			$mod = $dir_name;
			if ( ! isset( $this->all_integrations[ $mod ] ) ) {
				continue;
			}

			$tokens     = isset( $this->all_integrations[ $mod ]['tokens'] ) && in_array( 'tokens', $this->directories_to_include[ $mod ], true ) ? $this->all_integrations[ $mod ]['tokens'] : array();
			$triggers   = isset( $this->all_integrations[ $mod ]['triggers'] ) && in_array( 'triggers', $this->directories_to_include[ $mod ], true ) ? $this->all_integrations[ $mod ]['triggers'] : array();
			$actions    = isset( $this->all_integrations[ $mod ]['actions'] ) && in_array( 'actions', $this->directories_to_include[ $mod ], true ) ? $this->all_integrations[ $mod ]['actions'] : array();
			$closures   = isset( $this->all_integrations[ $mod ]['closures'] ) && in_array( 'closures', $this->directories_to_include[ $mod ], true ) ? $this->all_integrations[ $mod ]['closures'] : array();
			$conditions = isset( $this->all_integrations[ $mod ]['conditions'] ) && in_array( 'conditions', $this->directories_to_include[ $mod ], true ) ? $this->all_integrations[ $mod ]['conditions'] : array();
			$vendor     = array();

			$files = array_merge( $tokens, $triggers, $actions, $closures, $conditions, $vendor );
			if ( empty( $files ) ) {
				continue;
			}
			// Loop through all files in directory to create class names from file name
			foreach ( $files as $file ) {
				// bail early if the $file is not a string
				if ( is_array( $file ) ) {
					continue;
				}
				if ( ! file_exists( $file ) ) {
					continue;
				}
				require_once $file;
				// Remove file extension my-class-name.php to my-class-name
				$file_name = basename( $file, '.php' );

				// Implode array into class name - eg. array( 'My', 'Class', 'Name') to MyClassName
				$class_name = Set_Up_Automator::file_name_to_class( $file_name );

				$class = __NAMESPACE__ . '\\' . strtoupper( $class_name );
				if ( class_exists( $class ) ) {
					Utilities::add_class_instance( $class, new $class() );
				}
			}
		}
	}

	/**
	 * Automatically add Pro badge to Pro triggers
	 *
	 * @param $trigger
	 * @param $integration_code
	 * @param $integration
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function uap_register_trigger_func( $trigger, $integration_code, $integration ) {

		if ( ! isset( $trigger['validation_function'] ) ) {
			return $trigger;
		}
		foreach ( $trigger['validation_function'] as $function ) {
			if ( ! is_object( $function ) ) {
				continue;
			}
			$new_reflection = new ReflectionClass( $function );
			if ( $new_reflection ) {
				$namespace = $new_reflection->getNamespaceName();
				if ( 'Uncanny_Automator_Pro' === (string) $namespace ) {
					$trigger['is_pro'] = true;
				}
			}
		}

		return $trigger;
	}

	/**
	 * Automatically add Pro badge to Pro actions
	 *
	 * @param $action
	 * @param $integration_code
	 * @param $integration
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function uap_register_action_func( $action, $integration_code, $integration ) {

		if ( ! isset( $action['execution_function'] ) ) {
			return $action;
		}
		foreach ( $action['execution_function'] as $function ) {
			if ( ! is_object( $function ) ) {
				continue;
			}
			$new_reflection = new ReflectionClass( $function );
			if ( $new_reflection ) {
				$namespace = $new_reflection->getNamespaceName();
				if ( 'Uncanny_Automator_Pro' === (string) $namespace ) {
					$action['is_pro'] = true;
				}
			}
		}

		return $action;
	}

	/**
	 * Automatically add Pro badge to Pro closures
	 *
	 * @param $closure
	 * @param $integration_code
	 * @param $integration
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function uap_register_closure_func( $closure, $integration_code, $integration ) {

		if ( ! isset( $closure['execution_function'] ) ) {
			return $closure;
		}
		foreach ( $closure['execution_function'] as $function ) {
			if ( ! is_object( $function ) ) {
				continue;
			}
			$new_reflection = new ReflectionClass( $function );
			if ( $new_reflection ) {
				$namespace = $new_reflection->getNamespaceName();
				if ( 'Uncanny_Automator_Pro' === (string) $namespace ) {
					$closure['is_pro'] = true;
				}
			}
		}

		return $closure;
	}

	/**
	 * @param $file
	 *
	 * @return mixed|void
	 */
	public function get_class_name( $file ) {
		// Remove file extension my-class-name.php to my-class-name
		$file_name = basename( $file, '.php' );
		// Implode array into class name - eg. array( 'My', 'Class', 'Name') to MyClassName
		$class_name = self::file_name_to_class( $file_name );
		$class      = self::validate_namespace( $class_name, $file_name, $file );

		return apply_filters( 'automator_pro_recipes_class_name', $class, $file, $file_name );
	}

	/**
	 * @param $class_name
	 * @param $file_name
	 * @param $file
	 *
	 * @return mixed|string
	 */
	public static function validate_namespace( $class_name, $file_name, $file ) {
		$class_name = strtoupper( $class_name );
		try {
			$is_free = new ReflectionClass( 'Uncanny_Automator\\' . $class_name );
			if ( $is_free->inNamespace() ) {
				return 'Uncanny_Automator\\' . $class_name;
			}
		} catch ( ReflectionException $e ) { //phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}

		try {
			$is_pro = new ReflectionClass( 'Uncanny_Automator_Pro\\' . $class_name );
			if ( $is_pro->inNamespace() ) {
				return 'Uncanny_Automator_Pro\\' . $class_name;
			}
		} catch ( ReflectionException $e ) { //phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}

		try {
			$custom_namespace = apply_filters( 'automator_class_namespace', __NAMESPACE__, $class_name, $file_name, $file );
			$is_custom        = new ReflectionClass( $custom_namespace . '\\' . $class_name );
			if ( $is_custom->inNamespace() ) {
				return $custom_namespace . '\\' . $class_name;
			}
		} catch ( ReflectionException $e ) { //phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}

		return $class_name;
	}

	/**
	 * @param $file
	 *
	 * @return string
	 */
	public static function file_name_to_class( $file ) {
		$name = array_map(
			'ucfirst',
			explode(
				'-',
				str_replace(
					array(
						'class-',
						'.php',
					),
					'',
					basename( $file )
				)
			)
		);

		return join( '_', $name );
	}
}
