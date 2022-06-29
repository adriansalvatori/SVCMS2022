<?php
if (!defined("ABSPATH")) {
  exit();
}

class uipress_admin_pages
{
  public function __construct($version, $pluginName, $pluginPath, $textDomain, $pluginURL)
  {
    $this->version = $version;
    $this->pluginName = $pluginName;
    $this->path = $pluginPath;
    $this->pathURL = $pluginURL;
    $this->utils = new uipress_util();
  }

  /**
   * Loads menu editor actions
   * @since 1.0
   */

  public function run()
  {
    ///REGISTER THIS COMPONENT
    add_filter("uipress_register_settings", [$this, "admin_pages_settings_options"], 1, 2);

    $debug = new uipress_debug();

    if (!$debug->check_network_connection()) {
      return;
    }

    $utils = new uipress_util();
    $creatorDisabled = $utils->get_option("admin-pages", "status");

    if ($creatorDisabled == "true") {
      return;
    }

    if (function_exists("is_network_admin")) {
      if (is_network_admin()) {
        return;
      }
    }

    add_action("init", [$this, "uipress_create_admin_page_cpt"], 0);
    add_action("admin_menu", [$this, "add_custom_menu_items"]);
    //  This hooks into the page template and over rides the default template
    add_filter("template_include", [$this, "admin_page_template"], 99);
    ///REDIRECT WHEN NOT LOGGED IN
    add_action("template_redirect", [$this, "uip_redirect_from_admin_pages"]);
    ///SCRIPTS
    add_action("admin_enqueue_scripts", [$this, "add_scripts"]);

    ///ADD POST TYPE META BOXES
    add_action("add_meta_boxes", [$this, "uip_add_admin_page_metaboxes"]);
    add_action("save_post", [$this, "uip_save_admin_page_meta"], 1, 2);
  }

  /**
   * Enqueue admin page scripts
   * @since 2.2.9.2
   */

  public function add_scripts()
  {
    ///ADMIN PAGES META
    wp_enqueue_script("uip-admin-pages", $this->pathURL . "admin/apps/admin-pages/js/admin-pages-meta.min.js", ["jquery"], $this->version, true);
  }

  /**
   * Adds metabox to admin pages
   * @since 2.2.9.2
   */
  public function uip_add_admin_page_metaboxes()
  {
    add_meta_box("uip_admin_page_options", "Admin Page Options", [$this, "uip_admin_page_options"], "uip-admin-page", "side", "default");
  }

  /**
   * Adds metabox options to admin pages metabox
   * @since 2.2.9.2
   */
  public function uip_admin_page_options()
  {
    global $post;

    // Nonce field to validate form request came from current site
    wp_nonce_field(basename(__FILE__), "uip_admin_page_options");

    // Get the location data if it's already been entered
    $Metavalue = get_post_meta($post->ID, "load-subsites", true);
    $menuIcon = get_post_meta($post->ID, "uip-menu-icon", true);
    $menuAuto = get_post_meta($post->ID, "dont-add-to-menu", true);

    $field_id_checked = "";
    if ($Metavalue == "enabled") {
      $field_id_checked = 'checked="true"';
    }

    $addtomenu = "";
    if ($menuAuto == "enabled") {
      $addtomenu = 'checked="true"';
    }

    ob_start();
    if (is_main_site() && is_multisite()) { ?>
    <div class="uip-margin-top-s">
      <div class="uip-margin-bottom-s uip-flex uip-flex-column">
        <span class="uip-text-muted uip-margin-right-s uip-margin-bottom-xs uip-text-bold"><?php _e("Apply to subsites", "uipress"); ?></span>
        <label class="uip-switch">
          <input type="checkbox" name="load-subsites" value="enabled" <?php echo $field_id_checked; ?>>
          <span class="uip-slider"></span>
        </label>
      </div>
    </div>
    
    <?php }
    ?>
    <div class="uip-margin-top-s">
      <span class="uip-text-muted uip-text-bold"><?php _e("Menu Icon", "uipress"); ?>:</span>
    </div>
    <input type="text" name="uip-menu-icon" id="uip-ap-menu-icon" value="<?php echo $menuIcon; ?>" style="display:none;">
    <div class="uip-margin-top-s" id="uip-admin-page-icon-select">
      
    </div>
    
    
    <div class="uip-margin-top-s">
      <div class="uip-margin-bottom-s uip-flex uip-flex-column">
        <span class="uip-text-muted uip-margin-right-s uip-margin-bottom-xs uip-text-bold"><?php _e("Manually add to menu?", "uipress"); ?></span>
        <label class="uip-switch">
          <input type="checkbox" name="dont-add-to-menu" value="enabled" <?php echo $addtomenu; ?>>
          <span class="uip-slider"></span>
        </label>
      </div>
    </div>
    
    <?php echo ob_get_clean();
    // Output the field
    //echo '<input type="text" name="load-subsites" value="' . esc_textarea($Metavalue) . '" class="widefat">';
  }

  /**
   * Saves custom admin pages meta
   * @since 2.2.9.2
   */
  public function uip_save_admin_page_meta($post_id, $post)
  {
    // Return if the user doesn't have edit permissions.
    if (!current_user_can("edit_post", $post_id)) {
      return $post_id;
    }

    if (!isset($_POST["uip_admin_page_options"])) {
      return $post_id;
    }

    // Verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times.
    if (!wp_verify_nonce($_POST["uip_admin_page_options"], basename(__FILE__))) {
      return $post_id;
    }

    if (!isset($_POST["dont-add-to-menu"])) {
      delete_post_meta($post->ID, "dont-add-to-menu");
    } else {
      $events_meta["dont-add-to-menu"] = esc_textarea($_POST["dont-add-to-menu"]);
    }

    if (!isset($_POST["load-subsites"])) {
      delete_post_meta($post->ID, "load-subsites");
    } else {
      $events_meta["load-subsites"] = esc_textarea($_POST["load-subsites"]);
    }

    if (isset($_POST["uip-menu-icon"])) {
      $events_meta["uip-menu-icon"] = esc_textarea($_POST["uip-menu-icon"]);
    }

    // Cycle through the $events_meta array.
    // Note, in this example we just have one item, but this is helpful if you have multiple.
    foreach ($events_meta as $key => $value):
      // Don't store custom data twice
      if ("revision" === $post->post_type) {
        return;
      }

      if (get_post_meta($post_id, $key, false)) {
        // If the custom field already has a value, update it.
        update_post_meta($post_id, $key, $value);
      } else {
        // If the custom field doesn't have a value, add it.
        add_post_meta($post_id, $key, $value);
      }

      if (!$value) {
        // Delete the meta key if there's no value
        delete_post_meta($post_id, $key);
      }
    endforeach;
  }

  /**
   * Redirects from admin pages when not logged in
   * @since 2.2
   */
  function uip_redirect_from_admin_pages()
  {
    if (is_singular("uip-admin-page")) {
      if (!is_user_logged_in()) {
        wp_redirect(home_url());
        exit();
      }

      $utils = new uipress_util();
      $shownForOptions = $utils->get_option("admin-pages", "show-pages-for", true);

      if (!empty($shownForOptions)) {
        if (isset($_GET["sub_site_id"]) && $_GET["sub_site_id"] != "" && is_numeric($_GET["sub_site_id"])) {
          switch_to_blog($_GET["sub_site_id"]);
          $adminPagesShownFor = $utils->valid_for_user($shownForOptions);
          restore_current_blog();
        } else {
          $adminPagesShownFor = $utils->valid_for_user($shownForOptions);
        }

        if ($adminPagesShownFor !== true) {
          wp_redirect(admin_url());
          exit();
        }
      }
    }
  }

  /**
   * Returns settings options for settings page
   * @since 2.2
   */
  public function admin_pages_settings_options($settings, $network)
  {
    $utils = new uipress_util();
    $allOptions = $utils->get_options_object();
    ///////FOLDER OPTIONS
    $moduleName = "admin-pages";
    $category = [];
    $options = [];
    //
    $category["module_name"] = $moduleName;
    $category["label"] = __("Admin Pages - Beta", "uipress");
    $category["description"] = __("Creates custom admin pages.", "uipress");
    $category["icon"] = "segment";

    $temp = [];
    $temp["name"] = __("Disable custom admin pages?", "uipress");
    $temp["description"] = __("If disabled, the admin pages will not be available to any users.", "uipress");
    $temp["type"] = "switch";
    $temp["optionName"] = "status";
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Admin Page Slug", "uipress");
    $temp["description"] = __("This defaults to admin-pages. Changes to this will need you to resave permalink structure.", "uipress");
    $temp["type"] = "text";
    $temp["optionName"] = "admin-slug";
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Who can edit and create admin pages?", "uipress");
    $temp["description"] = __("Editing and creation of admin pages will be disabled for the users or roles you select", "uipress");
    $temp["type"] = "user-role-select";
    $temp["optionName"] = "disabled-for";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], true);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Who can see admin pages?", "uipress");
    $temp["description"] = __("UiPress will only show admin pages for the users or roles you select", "uipress");
    $temp["type"] = "user-role-select";
    $temp["optionName"] = "show-pages-for";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], true);
    $options[$temp["optionName"]] = $temp;

    $category["options"] = $options;
    $settings[$moduleName] = $category;

    return $settings;
  }

  /**
   * Creates custom folder post type
   * @since 1.4
   */
  public function uipress_create_admin_page_cpt()
  {
    $showinMenu = true;
    $utils = new uipress_util();
    $disabledFor = $utils->get_option("admin-pages", "disabled-for", true);

    if (!empty($disabledFor)) {
      $adminPagesShownFor = $utils->valid_for_user($disabledFor);

      if ($adminPagesShownFor !== true) {
        $showinMenu = false;
      }
    }

    $slug = $utils->get_option("admin-pages", "admin-slug", true);
    $apslug = "admin-page";

    if ($slug != false && $slug != "") {
      $apslug = $slug;
    }

    $labels = [
      "name" => _x("Admin Page", "post type general name", "uipress"),
      "singular_name" => _x("Admin Page", "post type singular name", "uipress"),
      "menu_name" => _x("Admin Pages", "admin menu", "uipress"),
      "name_admin_bar" => _x("Admin Page", "add new on admin bar", "uipress"),
      "add_new" => _x("Add New", "Admin Page", "uipress"),
      "add_new_item" => __("Add New Admin Page", "uipress"),
      "new_item" => __("New Admin Page", "uipress"),
      "edit_item" => __("Edit Admin Page", "uipress"),
      "view_item" => __("View Admin Page", "uipress"),
      "all_items" => __("All Admin Pages", "uipress"),
      "search_items" => __("Search Admin Pages", "uipress"),
      "not_found" => __("No Admin Pages found.", "uipress"),
      "not_found_in_trash" => __("No Admin Pages found in Trash.", "uipress"),
    ];
    $args = [
      "labels" => $labels,
      "description" => __("Description.", "Add New Admin Page"),
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_menu" => $showinMenu,
      "query_var" => false,
      "has_archive" => false,
      "hierarchical" => false,
      "supports" => ["editor", "title"],
      "show_in_rest" => true,
      "rewrite" => ["slug" => $apslug],
    ];
    register_post_type("uip-admin-page", $args);
  }

  /**
   * Adds custom admin pages to the menu
   * @since 2.2.9.2
   */

  public function add_custom_menu_items()
  {
    $utils = new uipress_util();
    $disabledFor = $utils->get_option("admin-pages", "show-pages-for", true);

    if (!empty($disabledFor)) {
      $adminPagesShownFor = $utils->valid_for_user($disabledFor);

      if ($adminPagesShownFor !== true) {
        return;
      }
    }

    $multisitePages = false;

    if (!is_main_site() && is_multisite()) {
      $multisitePages = $this->get_multisite_pages();
    }

    $args = [
      "numberposts" => -1,
      "post_status" => "publish",
      "post_type" => "uip-admin-page",
    ];

    $adminppages = get_posts($args);

    if (!$adminppages || count($adminppages) < 1) {
      return;
    }

    foreach ($adminppages as $page) {
      $addToMenu = get_post_meta($page->ID, "dont-add-to-menu", true);
      ///CHECK IF USER DOESN'T WANT PAGE AUTOMATCIALLY ADDED TO MENU
      if ($addToMenu == "enabled") {
        continue;
      }

      $title = get_the_title($page);
      $lc_title = strtolower($title);
      $theid = $page->ID;
      $slug = get_permalink($page->ID);

      $menuIcon = get_post_meta($page->ID, "uip-menu-icon", true);

      $optionIcon = "uip-admin-page-icon article";
      if ($menuIcon) {
        $optionIcon = "uip-admin-page-icon " . $menuIcon;
      }

      $passData["slug"] = $slug;
      $passData["ID"] = $theid;
      $passData["parent_site"] = false;
      $adminPageURL = "uip-admin-page-id-" . $theid;

      add_menu_page(
        $title,
        $title,
        "read",
        $adminPageURL,
        function () use ($passData) {
          $this->handle_custom_page_content($passData);
        },
        $optionIcon
      );
    }
    return;
  }

  /**
   * Gets multsite admin pages and adds to menu
   * @since 2.2.9.2
   */
  public function get_multisite_pages()
  {
    $currentSiteID = get_current_blog_id();
    $mainSiteId = get_main_site_id();
    switch_to_blog($mainSiteId);

    $args = [
      "numberposts" => -1,
      "post_status" => "publish",
      "post_type" => "uip-admin-page",
    ];

    $adminppages = get_posts($args);

    if (!$adminppages || count($adminppages) < 1) {
      restore_current_blog();
      return;
    }

    $allPages = [];

    foreach ($adminppages as $page) {
      $addToMenu = get_post_meta($page->ID, "dont-add-to-menu", true);
      ///CHECK IF USER DOESN'T WANT PAGE AUTOMATCIALLY ADDED TO MENU
      if ($addToMenu == "enabled") {
        continue;
      }

      $status = get_post_meta($page->ID, "load-subsites", true);

      if ($status != "enabled") {
        continue;
      }

      $title = get_the_title($page);
      $lc_title = strtolower($title);
      $theid = $page->ID;
      $slug = get_permalink($page->ID);

      $menuIcon = get_post_meta($page->ID, "uip-menu-icon", true);

      $optionIcon = "uip-admin-page-icon article";
      if ($menuIcon) {
        $optionIcon = "uip-admin-page-icon " . $menuIcon;
      }

      $passData["slug"] = $slug;
      $passData["ID"] = $theid;
      $passData["parent_site"] = true;
      $passData["sub_site_id"] = $currentSiteID;
      $adminPageURL = urldecode("uip-admin-page-id-" . $theid);

      $temp = [];
      $temp["passdata"] = $passData;
      $temp["url"] = $adminPageURL;
      $temp["title"] = $title;
      $temp["icon"] = $optionIcon;
      $allPages[] = $temp;
    }

    restore_current_blog();

    foreach ($allPages as $page) {
      $data = $page["passdata"];
      add_menu_page(
        $page["title"],
        $page["title"],
        "read",
        $page["url"],
        function () use ($data) {
          $this->handle_custom_page_content($data);
        },
        $page["icon"]
      );
    }

    return true;
  }

  /**
   * redirects to front sided admin pages
   * @since 2.2.9.2
   */
  public function handle_custom_page_content($passData)
  {
    if ($passData["parent_site"] == false) { ?><script>window.location = "<?php echo $passData["slug"]; ?>";</script><?php die();}

    if ($passData["parent_site"] == true) { ?>
    
    <style>
      #wpcontent{
        padding-left: 0;
      }
  </style>
  
        <iframe id="uip-admin-page-frame"
        title=""
        src="<?php echo $passData["slug"] . "?uip_no_menu=true&sub_site_id=" . $passData["sub_site_id"]; ?>"
        style="width:100%;height:100vh">
    </iframe>
      <?php }
  }

  /**
   * forces custom admin page template
   * @since 2.2.9.2
   */
  public function admin_page_template($template)
  {
    //KILL ON OXYGEN
    if (isset($_GET["ct_builder"])) {
      if ($_GET["ct_builder"] == "true") {
        return $template;
      }
    }

    $oxycode = get_post_meta(get_the_ID(), "ct_builder_shortcodes", true);
    $brickscode = get_post_meta(get_the_ID(), "_bricks_editor_mode", true);

    if ($oxycode != "" && $oxycode != false) {
      return $template;
    }
    if ($brickscode == "bricks" && $brickscode != false) {
      return $template;
    }
    // Change page to post if not a page your working on or custom post type name
    if (is_singular("uip-admin-page")) {
      ///FIX FOR YOOTHEME
      $theme = wp_get_theme(); // gets the current theme
      if ("YOOtheme" == $theme->name || "YOOtheme" == $theme->parent_theme) {
        $default_template = $this->path . "admin/apps/admin-pages/template/yootheme-admin-page-template.php";
        return $default_template;
      }
      // change the default-page-template.php to your template name
      $default_template = $this->path . "admin/apps/admin-pages/template/admin-page-template.php";
      return $default_template;
    }

    return $template;
  }
}
