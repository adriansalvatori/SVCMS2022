<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'XT_Framework_Modules' ) ) {

    /**
     * Class that takes care of rendering common message blocks
     *
     * @since      1.0.0
     * @package    XT_Framework
     * @subpackage XT_Framework/includes
     * @author     XplodedThemes
     */
    class XT_Framework_Modules {

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   private
         * @var      XT_Framework    core    Core Class
         */
        protected $core;

        /**
         * Loaded modules array
         *
         * @since    1.0.0
         * @access   protected
         * @var $modules XT_Framework_Module[]
         */
        protected $modules = array();

        public function __construct( $core ) {

            $this->core = $core;

            $this->modules = apply_filters( $this->core->plugin_prefix( 'modules' ), $this->modules );
            $this->modules = array_unique($this->modules);

            $this->load();
        }

	    /**
	     * Load modules
	     *
	     * @since    1.0.0
	     * @access   public
	     */
        public function load() {

            foreach($this->modules as $key => $module) {

            	$module_id = $module;
                $module_path = XTFW_DIR_MODULES.'/'.$module.'/module-'.$module.'.php';
                $module = xtfw_dash_to_camel_case($module, true, true);
                $module_name = str_replace("_", " ", $module);
                $module_class = 'XT_Module_'.$module;

                if(file_exists($module_path)) {

                    require_once $module_path;

                    $this->modules[$module_id] = $module_class::get_instance($this->core, $module_id, $module_name, $module_path);
                }

                unset($this->modules[$key]);
            }
        }

	    /**
	     * Loaded modules array
	     *
	     * @since    1.0.0
	     * @access   public
	     *
	     * @return   XT_Framework_Module[] $modules
	     */
        public function all($nofilter = false) {

            if($nofilter) {
                return $this->modules;
            }

            return array_filter($this->modules, function($module) {
                return !empty($module->shared_by());
            });
        }

	    /**
	     * Get module by id
	     *
	     * @since    1.0.0
	     * @access   public
	     *
	     * @return   XT_Framework_Module $module|null
	     */
	    public function get($id) {

		    return !empty($this->modules[$id]) ? $this->modules[$id] : null;
	    }

	    /**
	     * Total loaded modules
	     *
	     * @since    1.0.0
	     * @access   public
	     *
	     * @return   int
	     */
	    public function count($nofilter = false) {

		    return count($this->all($nofilter));
	    }

	    /**
	     * Get loaded module ids
	     *
	     * @since    1.0.0
	     * @access   public
	     *
	     * @return   array
	     */
        public function getIds() {

            $ids = array();

            foreach($this->all() as $module) {

                $ids[] = $module->id();
            }

            return $ids;
        }
    }
}
