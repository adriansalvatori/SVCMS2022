<?php
if (!defined("ABSPATH")) {
  exit();
}

class uipress_styles extends uipress_app
{
  public function __construct($version, $pluginName, $pluginPath, $textDomain, $pluginURL)
  {
    $this->version = $version;
    $this->pluginName = $pluginName;
    $this->path = $pluginPath;
    $this->pathURL = $pluginURL;
    $this->menu = [];
    $this->submenu = [];
  }

  /**
   * Loads UiPress settings page
   * @since 2.2
   */

  public function run()
  {
    $current_page = false;
    if (array_key_exists("page", $_GET)) {
      $current_page = $_GET["page"];
    }

    add_action("plugins_loaded", [$this, "whitelabel_uip"]);

    //AJAX
    add_action("wp_ajax_uip_get_styles", [$this, "uip_get_styles"]);
    add_action("wp_ajax_uip_save_styles", [$this, "uip_save_styles"]);
    add_action("wp_ajax_uip_get_themes", [$this, "uip_get_themes"]);
    add_action("wp_ajax_uip_love_theme", [$this, "uip_love_theme"]);

    //MENU ITEM
    add_action("admin_menu", [$this, "add_menu_item"]);
    add_action("network_admin_menu", [$this, "add_menu_item_network"]);
    add_action("admin_head", [$this, "add_user_styles"]);
    add_action("login_head", [$this, "add_user_styles"]);
    add_action("init", [$this, "add_front_actions"]);

    //PAGE SPECIFIC
    if ($current_page == "uip-styles") {
      add_action("admin_enqueue_scripts", [$this, "add_scripts_and_styles"]);
    }

    //WHITELIST UIPRESS
    add_filter("mailpoet_conflict_resolver_whitelist_style", [$this, "mailPoet_styles"]);
    add_filter("mailpoet_conflict_resolver_whitelist_script", [$this, "mailPoet_styles"]);
    add_filter("fluentform_skip_no_conflict", [$this, "fluentform_styles"]);
    add_filter("fluentcrm_skip_no_conflict", [$this, "fluentform_styles"]);
    add_filter("ninja_table_skip_no_confict", [$this, "fluentform_styles"]);
    add_filter("schedulepress_skip_no_conflict", [$this, "mailPoet_styles"]);
    add_filter("BetterLinks/Admin/skip_no_conflict", "__return_true", 99);
    add_filter("wpsr_skip_no_conflict", "__return_true", 99);
    add_filter("gravityview_noconflict_scripts", [$this, "gravityview_styles_scripts"], 99);
    add_filter("gravityview_noconflict_styles", [$this, "gravityview_styles_scripts"], 99);
  }

  /**
   * Changes name of UiPress
   * @since 2.3
   */

  public function whitelabel_uip()
  {
    $utils = new uipress_util();
    $newname = $utils->get_option("general", "rename-plugin");

    if ($newname != false && $newname != "") {
      $this->pluginName = $newname;
    }
  }

  public function add_front_actions()
  {
    if (is_user_logged_in()) {
      add_action("wp_head", [$this, "add_user_styles"]);
    }
  }

  /**
   * White lists styles for mailpoet
   * @since 1.4
   */

  public function gravityview_styles_scripts($styles)
  {
    $styles[] = "uip-font";
    $styles[] = "uip-icons";
    $styles[] = "uip-app";
    $styles[] = "uip-vue";
    $styles[] = "uip-toolbar-app";

    return $styles;
  }

  /**
   * White lists styles for mailpoet
   * @since 1.4
   */

  public function fluentform_styles($isSkip)
  {
    $styles = ["uip-font", "uip-icons", "uip-app", "uip-vue", "uip-toolbar-app", "admin-menu"];

    return $styles;
  }

  /**
   * White lists styles for mailpoet
   * @since 1.4
   */

  public function mailPoet_styles($styles)
  {
    $styles[] = "uipress";

    return $styles;
  }

  /**
   * Renders Admin Pages
   * @since 1.4
   */

  public function add_menu_item()
  {
    $utils = new uipress_util();
    $override = $utils->get_option("general", "network_override");
    if ($override && is_multisite()) {
      return;
    }

    add_options_page($this->pluginName, $this->pluginName . " " . __("Styles", "uipress"), "manage_options", "uip-styles", [$this, "build_settings_page"]);
  }

  /**
   * Renders Admin Pages Network
   * @since 1.4
   */

  public function add_menu_item_network()
  {
    add_submenu_page(
      "settings.php", // Parent element
      "UiPress", // Text in browser title bar
      $this->pluginName . " " . __("Styles", "uipress"), // Text to be displayed in the menu.
      "manage_options", // Capability
      "uip-styles", // Page slug, will be displayed in URL
      [$this, "build_settings_page"] // Callback function which displays the page
    );
  }

  /**
   * Loads all required styles and scripts for UiPress settings
   * @since 2.2
   */

  public function add_scripts_and_styles()
  {
    ///MENU APP
    wp_enqueue_script("uip-color-picker", $this->pathURL . "assets/js/vue-color/iro.js", ["uip-vue"], $this->version, false);
    wp_enqueue_script("uip-styles", $this->pathURL . "assets/js/uip-styles.min.js", ["uip-app"], $this->version, true);
  }

  /**
   * Gets uip settings object
   * @since 2.2
   */
  public function uip_get_styles()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-security-nonce", "security") > 0) {
      $modules = [];
      $returndata["styles"] = $this->get_styles_object();
      echo json_encode($returndata);
    }
    die();
  }

  /**
   * Gets uip themes
   * @since 2.2
   */
  public function uip_get_themes()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-security-nonce", "security") > 0) {
      $debug = new uipress_debug();
      $string = $debug->get_string("key");
      $instanceid = $debug->get_string_local("instance");
      $domain = get_home_url();

      $uip_query = "?key=" . $string;
      $uip_query = $uip_query . "&instance=" . $instanceid;
      $uip_query = $uip_query . "&d=" . $domain;

      //error_log("https://analytics.uipress.co/view.php?" . $uip_query);
      $remote = wp_remote_get("https://api.uipress.co/themes/" . $uip_query, [
        "timeout" => 10,
        "headers" => [
          "Accept" => "application/json",
        ],
      ]);

      if (!is_wp_error($remote) && isset($remote["response"]["code"]) && $remote["response"]["code"] == 200 && !empty($remote["body"])) {
        $data = [];
        echo $remote["body"];
      } else {
        $returndata["error"] = true;
        $returndata["message"] = __("Unable to fetch themes at this moment. Please try again later", "uipress");
        echo $returndata;
      }
    }
    die();
  }

  /**
   * Gets uip themes
   * @since 2.2
   */
  public function uip_love_theme()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $themeid = $utils->clean_ajax_input($_POST["id"]);
      $domain = get_home_url();

      $uip_query = "?&d=" . $domain;
      $uip_query = $uip_query . "&id=" . $themeid;

      //error_log("https://analytics.uipress.co/view.php?" . $uip_query);
      $remote = wp_remote_get("https://api.uipress.co/themes/love/" . $uip_query, [
        "timeout" => 10,
        "headers" => [
          "Accept" => "application/json",
        ],
      ]);

      if (!is_wp_error($remote) && isset($remote["response"]["code"]) && $remote["response"]["code"] == 200 && !empty($remote["body"])) {
        $data = [];
        echo $remote["body"];
      } else {
        $returndata["error"] = true;
        $returndata["message"] = __("Unable to like theme at this moment. Please try again later", "uipress");
        echo json_encode($returndata);
      }
    }
    die();
  }

  /**
   * Gets uip settings object
   * @since 2.2
   */
  public function uip_save_styles()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $options = $utils->clean_ajax_input($_POST["options"]);

      if (!is_array($options) || !$options) {
        $returndata["error"] = true;
        $returndata["message"] = __("Unable to save styles", "uipress");
        echo json_encode($returndata);
        die();
      }

      $formattedStyles = [];

      foreach ($options as $category) {
        foreach ($category["options"] as $style) {
          $stylename = $style["optionName"];

          if (isset($style["value"])) {
            $standard = $style["value"];
            $formattedStyles[$stylename]["light"] = $standard;
          }

          if (array_key_exists("darkValue", $style)) {
            $dark = $style["darkValue"];
            if ($dark != "") {
              $formattedStyles[$stylename]["dark"] = $dark;
            }
          }
        }
      }

      update_option("uip-styles", $formattedStyles);
      $returndata["message"] = __("Styles saved", "uipress");
      $returndata["test"] = $formattedStyles;
      echo json_encode($returndata);
    }
    die();
  }

  /**
   * Outputs user styles
   * @since 2.2
   */
  public function add_user_styles()
  {
    $current_page = false;
    if (array_key_exists("page", $_GET)) {
      $current_page = $_GET["page"];
    }

    if ($current_page == "uip-styles") {
      return;
    }

    $styles = $this->get_styles_object();
    if (!is_array($styles) || !$styles) {
      return;
    }

    $importurl = false;
    $defaultStyles = "";
    $generalStyles = "";
    $darkStyles = "";

    foreach ($styles as $category) {
      foreach ($category["options"] as $style) {
        $stylename = $style["optionName"];
        $standard = $style["value"];
        $global = false;
        if ($style["global"] == true) {
          $global = true;
        }

        if ($stylename == "--uip-body-font-family" && $standard) {
          $font = $standard;

          if (!$font[0]) {
            continue;
          }

          $formattedfont = "'" . $font[0] . "', " . $font[1];
          $fontURL = str_replace(" ", "%20", "https://fonts.googleapis.com/css2?family=" . $font[0] . ":wght@300;400;700&display=swap");
          $importurl = "@import url('" . $fontURL . "');";
          $generalStyles = $generalStyles . $stylename . ":" . $formattedfont . "!important;";
          continue;
        }

        if ($standard != "" && !is_array($standard)) {
          $defaultStyles = $defaultStyles . $stylename . ":" . $standard . ";";
        }

        if ($global && !is_array($standard) && $standard != "") {
          $generalStyles = $generalStyles . $stylename . ":" . $standard . "!important;";
        }
      }
    }

    foreach ($styles as $category) {
      foreach ($category["options"] as $style) {
        if (!isset($style["darkValue"])) {
          continue;
        }

        $dark = $style["darkValue"];
        $stylename = $style["optionName"];

        if ($dark) {
          $darkStyles = $darkStyles . $stylename . ":" . $dark . ";";
        }
      }
    }

    echo '<style id="uip-user-styles" type="text/css">';

    //ECHO FONT IMPORT
    if ($importurl) {
      echo $importurl;
    }
    //PRINT LIGHT STYLES
    echo 'html:not([data-theme="dark"]){' . $defaultStyles . "}";
    //PRINT DARK STYLES
    echo 'html[data-theme="dark"]{' . $darkStyles . "}";
    //PRINT GENERIC STYLES
    echo "html{" . $generalStyles . "}";

    echo "</style>";
  }

  /**
   * Outputs settings page
   * @since 1.4
   */

  public function build_settings_page()
  {
    ///LOAD UP WP IMAGE MODALS
    $utils = new uipress_util();
    $appicon = $utils->get_option("general", "app-icon");
    ?>
		<style>
		  #wpcontent{
			  padding-left: 0;
		  }
		</style>
		
		
		<div id="uip-styles" class="uip-body-font uip-text-normal">
      
      <div  v-if="isSmallScreen()">
        <div class="uip-padding-m">
          <div class="notice">
            <p class="uip-text-bold"><?php _e('Uipress styles isn\'t optimised for mobile devices. Switch to a larger screen to modify UiPress Styles', "uipress"); ?></p>
          </div>
        </div>
      </div>
      
      <template v-if="!isSmallScreen()">
      
  			<div class="uip-padding-s uip-border-box uip-border-bottom uip-border-top uip-background-default">
  				<div class="uip-flex uip-flex-center">
  					<div class="uip-margin-right-s">
  						<?php if ($appicon != "" && $appicon != false) {
          echo '<img src="' . $appicon . '" alt="" class="uip-light-logo" style="max-height: 33px;">';
          echo '<img src="' . $appicon . '" alt="" class="uip-dark-logo" style="max-height: 33px;">';
        } else {
          echo '<img :src="defaults.logo" alt="" class="uip-light-logo" style="max-height: 33px;">';
          echo '<img :src="defaults.darkLogo" alt="" class="uip-dark-logo" style="max-height: 33px;">';
        } ?>
  					</div>
  					<div class="uip-flex-grow">
  						<div class="uip-text-bold uip-text-l uip-text-emphasis"><?php echo $this->pluginName . " " . __("Styles", "uipress"); ?></div>
  						<div class="uip-text-muted">
  							<?php echo __("Version", "uipress") . " " . $this->version; ?>
  						</div>
  					</div>
  					<div class="uip-flex uip-flex-row uip-gap-xs">
  						<button class="uip-button-primary"
  						type="button" @click="saveSettings()"><?php _e("Save", "uipress"); ?></button>
              
              <template v-if="masterPrefs.dataConnect == true">
              
                <label class="uip-button-default uip-flex uip-flex-center">
                  <span><?php _e("Import", "uipress"); ?></span>
                  <input hidden accept=".json" type="file" single="" id="uip-import-settings" @change="importSettings()">
                </label>
                
                <button class="uip-button-default "
                type="button" @click="exportSettings()"><?php _e("Export", "uipress"); ?></button>
                <a href="#" class="uip-hidden" id="uip-export-styles"></a>
              
              </template>
              
              <template v-else >
                
                <a href="https://uipress.co/pricing/" target="_BLANK" class="uip-no-underline uip-border-round uip-background-primary-wash uip-text-bold uip-text-emphasis " style="padding: var(--uip-padding-button)">
                  <div class="uip-flex">
                    <span class="material-icons-outlined ">redeem</span> 
                    <span><?php _e("Unlock Export and Import features with pro", "uipress"); ?></span>
                  </div> 
                </a>
                
              </template>
              
              
              <uip-offcanvas :translations="translations" :title="translations.themeLibrary" :buttontext="translations.library" icon="book"  buttonsize="medium" :tooltip="true" :tooltiptext="translations.themeLibrary">
                <theme-selector  @import-theme="themImport($event)" :translations="translations" ></theme-selector>
              </uip-offcanvas>
              
              <button class="uip-button-danger "
              type="button" @click="clearSettings()"><?php _e("Clear All", "uipress"); ?></button>
  					</div>
  				</div>
  			</div>
  			<!-- SETTINGS AREA -->
  			<div class="uip-flex uip-margin-bottom-m">
  				<div class="uip-flex-grow uip-flex">
  					
  					<div class="uip-max-w-900 uip-flex-grow uip-margin-auto uip-padding-m">
  						<output-options :uipdata="masterPrefs.dataConnect" :translations="translations" :alloptions="formattedSettings"></output-options>
  					</div>
  					
  				</div>
  			</div>
      
      </template>
      
		</div>
    <style type="text/css" id="uip-variable-preview"></style>
	  <?php
  }

  public function get_style_value_from_object($uipOptions, $option_name, $returnArray = false)
  {
    $data["light"] = "";
    $data["dark"] = "";

    if ($returnArray) {
      $data["light"] = [];
      $data["dark"] = [];
    }

    if ($option_name == false) {
      return $data;
    }

    if (isset($uipOptions[$option_name]["light"])) {
      $value = $uipOptions[$option_name]["light"];
      if ($value != "") {
        $data["light"] = $value;
      }
    }

    if (isset($uipOptions[$option_name]["dark"])) {
      $value = $uipOptions[$option_name]["dark"];
      if ($value != "") {
        $data["dark"] = $value;
      }
    }
    return $data;
  }

  /**
   * Outputs settings page
   * @since 1.4
   */

  public function get_styles_object()
  {
    $utils = new uipress_util();
    $styles = [];
    $stylesObject = $utils->get_style_values_object();

    $optionname = "global";
    $label = __("Global", "uipress");

    $temp = [];
    $temp["name"] = __("Body Background", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-body-background";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Default Background", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-background-default";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Muted Background", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-background-muted";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Muted Background Accent", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-background-grey";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Primary Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-color-primary";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Primary Color Wash", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-color-primary-wash";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Primary Color Hover", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-color-primary-dark";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Font", "uipress");
    $temp["type"] = "font";
    $temp["cssVariable"] = "--uip-body-font-family";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"], true)["light"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Border Width", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-border-width";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"], true)["light"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Border Radius", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-border-radius";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"], true)["light"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Border Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-border-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"], true)["light"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Notification Background", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-notification-background";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    ////TEXT TIME

    $optionname = "text";
    $label = __("Text", "uipress");

    $temp = [];
    $temp["name"] = __("Text Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-text-color-normal";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Text Emphasis Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-text-color-emphasis";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Text Muted Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-text-color-muted";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;
    $styles[$optionname]["preview"] = '<div class="uip-text-emphasis uip-text-xl uip-margin-bottom-s">I am text with emphasis</div>
    <div class="uip-text-l uip-margin-bottom-s">I am normal body text</div>
    <div class="uip-text-muted uip-margin-bottom-s">I am muted text</div>';

    ////BUTTON TIME

    $optionname = "Button";
    $label = __("Buttons", "uipress");

    $temp = [];
    $temp["name"] = __("Button Border Radius", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-button-border-radius";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Button Border Width", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-button-border-width";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Default Button Border Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-border-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Default Button Background Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-default-bg";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Default Button Hover Background Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-default-hover-bg";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Default Button Text Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-text-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    ///PRIMARY BUTTON
    $temp = [];
    $temp["name"] = __("Primary Button Border Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-primary-border-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Primary Button Background Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-primary-bg";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Primary Button Hover Background Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-primary-hover-bg";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Primary Button Text Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-primary-text-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    ///DANGER BUTTON
    $temp = [];
    $temp["name"] = __("Danger Button Border Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-danger-border-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Danger Button Background Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-danger-bg";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Danger Button Hover Background Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-danger-hover-bg";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Danger Button Text Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-danger-text-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    ////PAGE ACTION
    $temp = [];
    $temp["name"] = __("Page Action Button Border Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-page-action-border-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Page Action Button Background Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-page-action-bg";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Page Action Button Hover Background Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-page-action-hover-bg";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Page Action Button Text Colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-button-page-action-text-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;
    $styles[$optionname]["preview"] = '<button class="uip-button-default uip-margin-right-xs uip-margin-bottom-xs" type="button">Default Button</button>
    <button class="uip-button-primary uip-margin-right-xs uip-margin-bottom-xs" type="button">Primary Button</button>
    <button class="uip-button-danger uip-margin-right-xs uip-margin-bottom-xs" type="button">Danger Button</button>
    <button class="page-title-action uip-margin-right-xs uip-margin-bottom-xs" type="button" style="float:none;margin-left:0px!important">Page Action Button</button>';

    ////MENU TIME

    $optionname = "menu";
    $label = __("Menu", "uipress");
    $temp = [];
    $temp["name"] = __("Background Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-background";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Secondary background Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-secondary-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Button Hover background Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-background-grey";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Text Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-text-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Active Text Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-text-emphasis";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Active Top Level Item background colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-active-background";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Search Input background colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-search-background";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Search Input text colour", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-search-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Submenu Active Text Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-menu-sub-text-emphasis";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Width", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-menu-width";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Padding", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-menu-padding";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Logo Height", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-menu-logo-height";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    ////TOOLBAR TIME

    $optionname = "toolbar";
    $label = __("Toolbar", "uipress");

    $temp = [];
    $temp["name"] = __("Toolbar Background Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-toolbar-background";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Text Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-toolbar-text-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Link Hover Text Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-toolbar-text-color-hover";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Secondary background Color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-toolbar-background-secondary";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    ////CARDS TIME

    $optionname = "cards";
    $label = __("Cards", "uipress");

    $temp = [];
    $temp["name"] = __("Background color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-card-background";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Border color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-card-border-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Border radius", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-card-border-radius";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Shadow spread", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-card-shadow-spread";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Shadow blur", "uipress");
    $temp["type"] = "text";
    $temp["cssVariable"] = "--uip-card-shadow-blur";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = true;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    $temp = [];
    $temp["name"] = __("Shadow color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-card-shadow-color";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $temp["premium"] = true;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;
    $styles[$optionname]["preview"] = '
    <div class="uip-card">
      <div class="uip-padding-s">
        <div class="uip-text-l">I am a card</div>
        <p>Cards are used throughout UiPress but are primarily seen on the overview page, dashboard and meta boxes</p>  
      </div>
    </div>';

    ////LOGIN TIME

    $optionname = "login";
    $label = __("Login", "uipress");

    $temp = [];
    $temp["name"] = __("Background color", "uipress");
    $temp["type"] = "color";
    $temp["cssVariable"] = "--uip-login-background";
    $temp["optionName"] = $temp["cssVariable"];
    $temp["value"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["light"];
    $temp["darkValue"] = $this->get_style_value_from_object($stylesObject, $temp["optionName"])["dark"];
    $temp["global"] = false;
    $styles[$optionname]["options"][] = $temp;
    $styles[$optionname]["label"] = $label;

    return $styles;
  }
}
