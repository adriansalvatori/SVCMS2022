<?php
if (!defined("ABSPATH")) {
  exit();
}

class uipress_users
{
  public function __construct($version, $pluginName, $pluginPath, $textDomain, $pluginURL)
  {
    $this->version = $version;
    $this->pluginName = $pluginName;
    $this->path = $pluginPath;
    $this->pathURL = $pluginURL;
    $this->utils = new uipress_util();
    $this->media_date = "";
    $this->attachment_size = "";
    $this->legacyitems = [];
  }

  /**
   * Loads menu actions
   * @since 2.3.5
   */

  public function run()
  {
    ///REGISTER THIS COMPONENT
    add_filter("uipress_register_settings", [$this, "users_settings_options"], 1, 2);

    ///ADD ACTIOS FOR USERS PAGE
    add_action("plugins_loaded", [$this, "add_user_functions"]);
    add_action("plugins_loaded", [$this, "start_history_logger"]);
    ///AVATAR FILTER
    add_filter("get_avatar", [$this, "uip_allow_custom_avatars"], 1, 5);
    add_filter("get_avatar_url", [$this, "uip_allow_custom_avatars_url"], 10, 3);
  }

  /**
   * Returns settings options for users page
   * @since 2.3.5
   */
  public function users_settings_options($settings, $network)
  {
    $utils = new uipress_util();
    $allOptions = $utils->get_options_object();

    ///////FOLDER OPTIONS
    $moduleName = "uipusers";
    $category = [];
    $options = [];
    //
    $category["module_name"] = $moduleName;
    $category["label"] = __("Users", "uipress");
    $category["description"] = __("Creates user management page", "uipress");
    $category["icon"] = "group";

    $temp = [];
    $temp["name"] = __("Disable Users Page?", "uipress");
    $temp["description"] = __("If disabled, the user management page will not be available to any users.", "uipress");
    $temp["type"] = "switch";
    $temp["optionName"] = "status";
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("User management page Disabled for", "uipress");
    $temp["description"] = __("When the user management page module is disabled, the user management page will not be accesible for the users / roles", "uipress");
    $temp["type"] = "user-role-select";
    $temp["optionName"] = "disabled-for";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], true);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Enable user activity log?", "uipress");
    $temp["description"] = __("If enabled, uipress will log actions taken by all users, including page views, comments, post creation etc.", "uipress");
    $temp["type"] = "switch";
    $temp["optionName"] = "history-enabled";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Disable page view logging", "uipress");
    $temp["description"] = sprintf(
      __(
        "By Default, %s will track page views for logged in users as part of the activity log. While sometimes useful, this will have the biggest impact on the log size in the database. Disabling this feature will keep the log smaller.",
        "uipress"
      ),
      "uipress"
    );
    $temp["type"] = "switch";
    $temp["optionName"] = "history-page-views-disabled";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Enable recent page view logging", "uipress");
    $temp["description"] = sprintf(__("Seperate to the main history log, %s will keep 5 recent page views in the user meta. This can have a small impact on the page load speed."), "uipress");
    $temp["type"] = "switch";
    $temp["optionName"] = "recent-page-views-enabled";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("How long to keep history?", "uipress");
    $temp["description"] = __("By default, activity items will be deleted after 60 days, enter the amount of days to keep items to change this", "uipress");
    $temp["type"] = "number";
    $temp["minimum"] = "1";
    $temp["optionName"] = "history-expiry";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Delay history entries?", "uipress");
    $temp["description"] = __(
      "Use scheduled tasks to insert the history actions to avoid any extra page load speed. By default entries are added as they happen, with this enabled there will be delay of about 10 seconds before actions appear.",
      "uipress"
    );
    $temp["type"] = "switch";
    $temp["minimum"] = "1";
    $temp["optionName"] = "delay-history";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Anonymize user IP addresses?", "uipress");
    $temp["description"] = __("Each history item logs the users IP address, enable this to anonymize the address.");
    $temp["type"] = "switch";
    $temp["minimum"] = "1";
    $temp["optionName"] = "anonymize-ip";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $category["options"] = $options;
    $settings[$moduleName] = $category;

    return $settings;
  }

  /**
   * Adds actions for USERS page
   * @since 2.3.5
   */

  public function start_history_logger()
  {
    $debug = new uipress_debug();

    if ($debug->check_network_connection() === true) {
      $utils = new uipress_util();
      $historyEnabled = $utils->get_option("uipusers", "history-enabled");
      ///HISTORY ITEMS
      if ($historyEnabled) {
        $history = new uip_history();
        $history->start();
      }
    }
  }
  /**
   * Adds actions for USERS page
   * @since 2.3.5
   */

  public function add_user_functions()
  {
    $utils = new uipress_util();

    if (!is_admin()) {
      return;
    }

    $utils = new uipress_util();
    $contentDisabled = $utils->get_option("uipusers", "status");
    $usersDisabledForUser = $utils->valid_for_user($utils->get_option("uipusers", "disabled-for", true));

    $contentDisabledForUser = $utils->valid_for_user($utils->get_option("uipusers", "disabled-for", true));

    if ($contentDisabled == "true" || $contentDisabledForUser) {
      return;
    }

    require_once $this->path . "admin/apps/users/uip-users-ajax.php";
    require_once $this->path . "admin/apps/users/uip-user-groups.php";
    $ajaxFunctions = new uipress_users_ajax($this->version, $this->pluginName, $this->path, "", $this->pathURL);
    $ajaxFunctions->ajax_actions();

    $userGroups = new uipress_user_groups($this->version, $this->pluginName, $this->path, "", $this->pathURL);
    $userGroups->start();

    if (!$usersDisabledForUser) {
      add_action("admin_menu", [$this, "add_menu_item"]);
    }

    if (isset($_GET["page"])) {
      if ($_GET["page"] == "uip-user-management") {
        add_action("admin_enqueue_scripts", [$this, "add_scripts"], 0);
        add_filter("script_loader_tag", [$this, "add_type_attribute"], 10, 3);
      }
    }
  }

  /**
   * Adds USERS menu item
   * @since 2.3.5
   */

  public function add_menu_item()
  {
    add_submenu_page(
      "users.php", // Parent element
      __("User Management", "uipress"), // Text in browser title bar
      __("User Management", "uipress"), // Text to be displayed in the menu.
      "edit_users", // Capability
      "uip-user-management", // Page slug, will be displayed in URL
      [$this, "build_user_page"] // Callback function which displays the page
    );
    return;
  }

  /**
   * Enqueue scripts for user management page
   * @since 2.3.5
   */

  public function add_scripts()
  {
    wp_register_style("uip-quill-style", $this->pathURL . "admin/apps/users/js/libs/quill.snow.css", [], $this->version);
    wp_enqueue_style("uip-quill-style");

    wp_enqueue_script("uip-quill", $this->pathURL . "admin/apps/users/js/libs/quill.min.js", [], $this->version);
    ///LOAD CONTENT APP IN FOOTER
    wp_enqueue_script("uip-user-app", $this->pathURL . "admin/apps/users/js/uip-user-app.min.js", ["uip-app"], $this->version, true);
    wp_localize_script("uip-user-app", "uip_user_app_ajax", [
      "ajax_url" => admin_url("admin-ajax.php"),
      "security" => wp_create_nonce("uip-user-app-security-nonce"),
      "appData" => $this->buildAppData(),
    ]);
  }

  /**
   * Builds Data for app
   * @since 2.3.5
   */

  public function buildAppData()
  {
    $previewImage = $this->pathURL . "assets/img/user_management.png";

    $debug = new uipress_debug();
    $data = [];
    $data["app"] = [
      "currentPage" => "users",
      "translations" => $this->build_translations(),
      "capabilities" => $this->uip_get_role_capabilities(),
      "dataConnect" => $debug->check_network_connection(),
      "previewImage" => $previewImage,
      "pages" => [
        [
          "name" => "users",
          "label" => __("Users", "uipress"),
        ],
        [
          "name" => "roles",
          "label" => __("Roles", "uipress"),
        ],
        [
          "name" => "activity",
          "label" => __("Activity", "uipress"),
        ],
      ],
    ];

    return json_encode($data);
  }

  /**
   * Returns translations
   * @since 2.3.5
   */

  public function build_translations()
  {
    return [
      "results" => __("results", "uipress"),
      "previous" => __("Previous", "uipress"),
      "next" => __("Next", "uipress"),
      "searchUsers" => __("Search users", "uipress"),
      "filterByRole" => __("Filter by role", "uipress"),
      "searchRoles" => __("Search roles", "uipress"),
      "tableOptions" => __("Table options", "uipress"),
      "order" => __("Order", "uipress"),
      "ascending" => __("Ascending", "uipress"),
      "descending" => __("Descending", "uipress"),
      "sortBy" => __("Sort By", "uipress"),
      "perPage" => __("Per page", "uipress"),
      "fields" => __("Fields", "uipress"),
      "dateCreated" => __("Date created", "uipress"),
      "on" => __("On", "uipress"),
      "after" => __("After", "uipress"),
      "before" => __("Before", "uipress"),
      "dateFilters" => __("Date filters", "uipress"),
      "dateFilters" => __("Date filters", "uipress"),
      "details" => __("Details", "uipress"),
      "accountCreated" => __("Account created", "uipress"),
      "name" => __("Name", "uipress"),
      "email" => __("Email", "uipress"),
      "recentPageViews" => __("Recent page views", "uipress"),
      "recentActivity" => __("Recent activity", "uipress"),
      "next" => __("Next", "uipress"),
      "previous" => __("Previous", "uipress"),
      "lastLogin" => __("Last login", "uipress"),
      "lastLoginCountry" => __("Login location", "uipress"),
      "noActivity" => __("No recent activity", "uipress"),
      "totalPosts" => __("Total posts", "uipress"),
      "totalComments" => __("Total comments", "uipress"),
      "userOptions" => __("User Options", "uipress"),
      "editUser" => __("Edit user", "uipress"),
      "firstName" => __("First name", "uipress"),
      "lastName" => __("Last name", "uipress"),
      "email" => __("Email", "uipress"),
      "assignRoles" => __("Assign roles", "uipress"),
      "roles" => __("Roles", "uipress"),
      "userNotes" => __("User notes", "uipress"),
      "cancel" => __("Cancel", "uipress"),
      "updateUser" => __("Update user", "uipress"),
      "editUser" => __("Edit user", "uipress"),
      "sendPasswordReset" => __("Send password reset", "uipress"),
      "sendMessage" => __("Send message", "uipress"),
      "deleteUser" => __("Delete user", "uipress"),
      "confirmUserDelete" => __("Are you sure you want to delete this user?", "uipress"),
      "confirmUserDeleteMultiple" => __("Are you sure you want to the selected users?", "uipress"),
      "confirmUserPassReset" => __("Are you sure you want to send password reset links to the selected users?", "uipress"),
      "recipient" => __("Recipient", "uipress"),
      "subject" => __("Subject", "uipress"),
      "message" => __("Message", "uipress"),
      "sendMessage" => __("Send message", "uipress"),
      "replyTo" => __("Reply to email", "uipress"),
      "newUser" => __("New user", "uipress"),
      "saveUser" => __("Save user", "uipress"),
      "password" => __("Password", "uipress"),
      "username" => __("Username", "uipress"),
      "roleName" => __("Role name", "uipress"),
      "editRole" => __("Edit role", "uipress"),
      "saveRole" => __("Save role", "uipress"),
      "capabilities" => __("Capabilities", "uipress"),
      "adminWarning" => __(
        "You are currently editing the administrator role. This is usually the most important role on the site so please make sure not to remove nessecary capabilities.",
        "uipress"
      ),
      "deleteRole" => __("Delete role", "uipress"),
      "confirmRoleDelete" => __("Are you sure you want to delete this role?", "uipress"),
      "confirmRoleDeleteMultiple" => __("Are you sure you want to delete these roles?", "uipress"),
      "roleOptions" => __("Role options", "uipress"),
      "clone" => __("Clone", "uipress"),
      "newRole" => __("New role", "uipress"),
      "roleLabel" => __("Role label", "uipress"),
      "roleLabelDescription" => __("Single word, no spaces. Underscores and dashes allowed", "uipress"),
      "copy" => __("copy", "uipress"),
      "searchRoles" => __("search roles", "uipress"),
      "searchHistory" => __("Search history", "uipress"),
      "allActions" => __("All actions", "uipress"),
      "addCustomCapability" => __("Add custom capability (Single word, no spaces)", "uipress"),
      "addCapability" => __("Add capability", "uipress"),
      "rolesSelected" => __("roles selected", "uipress"),
      "deleteSelected" => __("Delete selected", "uipress"),
      "deselect" => __("Deselect", "uipress"),
      "deleteUsers" => __("Delete users", "uipress"),
      "clearSelection" => __("Clear selection", "uipress"),
      "recipients" => __("recipients", "uipress"),
      "users" => __("users", "uipress"),
      "Users" => __("Users", "uipress"),
      "replaceExistingRoles" => __("Replace existing roles", "uipress"),
      "updateRoles" => __("Update roles", "uipress"),
      "usersSelected" => __("users selected", "uipress"),
      "chooseImage" => __("Choose image", "uipress"),
      "profileImage" => __("Profile image", "uipress"),
      "groups" => __("Groups", "uipress"),
      "allUsers" => __("All users", "uipress"),
      "noGroup" => __("No group", "uipress"),
      "noGroupCreated" => __("No groups created yet.", "uipress"),
      "groups" => __("Groups", "uipress"),
      "newGroup" => __("New group", "uipress"),
      "name" => __("Name", "uipress"),
      "color" => __("Color", "uipress"),
      "createGroup" => __("Create group", "uipress"),
      "groupName" => __("Group name", "uipress"),
      "editGroup" => __("Edit group", "uipress"),
      "updateGroup" => __("Update group", "uipress"),
      "removeFromGroup" => __("Remove from group", "uipress"),
      "userGroups" => __("User groups", "uipress"),
      "assignGroups" => __("Assign groups", "uipress"),
      "searchGroups" => __("Search groups", "uipress"),
      "proFeature" => __("Pro feature", "uipress"),
      "proFeatureUpgrade" => __("Upgrade to UiPress Pro to unlock the user management and activity logs", "uipress"),
      "viewPlans" => __("View Uipress Pro plans", "uipress"),
      "groupIcon" => __("Group icon", "uipress"),
      "logoutEverywhere" => __("Logout everywhere else", "uipress"),
      "openProfile" => __("Open profile", "uipress"),
    ];
  }

  /**
   * Adds a module tag to uip-user-app
   * @since 2.3.5
   */

  public function add_type_attribute($tag, $handle, $src)
  {
    // if not your script, do nothing and return original $tag
    if ("uip-user-app" !== $handle) {
      return $tag;
    }
    // change the script tag by adding type="module" and return it.
    $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    return $tag;
  }

  /**
   * Builds users page
   * @since 2.3.5
   */

  public function build_user_page()
  {
    ///LOAD UP WP IMAGE MODALS
    wp_enqueue_media(); ?>
	<style>#wpcontent{padding:0;}#wpfooter{display:none}</style>
	<div id="uip-user-management"></div>
	<?php
  }

  /**
   * Allow custom images as avatar
   * @since 2.3.5
   */

  function uip_allow_custom_avatars_url($url, $id_or_email, $args)
  {
    $user = false;

    if (is_numeric($id_or_email)) {
      $id = (int) $id_or_email;
      $user = get_user_by("id", $id);
    } elseif (is_object($id_or_email)) {
      if (!empty($id_or_email->user_id)) {
        $id = (int) $id_or_email->user_id;
        $user = get_user_by("id", $id);
      }
    } else {
      $user = get_user_by("email", $id_or_email);
    }

    if ($user && is_object($user)) {
      $thepath = get_user_meta($user->data->ID, "uip_profile_image", true);

      if ($thepath) {
        $url = $thepath;
      }
    }

    return $url;
  }

  /**
   * Allow custom images as avatar
   * @since 2.3.5
   */

  public function uip_allow_custom_avatars($avatar, $id_or_email, $size, $default, $alt)
  {
    $user = false;

    if (is_numeric($id_or_email)) {
      $id = (int) $id_or_email;
      $user = get_user_by("id", $id);
    } elseif (is_object($id_or_email)) {
      if (!empty($id_or_email->user_id)) {
        $id = (int) $id_or_email->user_id;
        $user = get_user_by("id", $id);
      }
    } else {
      $user = get_user_by("email", $id_or_email);
    }

    if ($user && is_object($user)) {
      $thepath = get_user_meta($user->data->ID, "uip_profile_image", true);

      if ($thepath) {
        $avatar = $thepath;
        $avatar = $avatar;
        $avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
      }
    }

    return $avatar;
  }

  /**
   * Gets capabilities from exisitng roles
   * Original code modified from members plugin by Justin Tadlock
   * @since 2.3.5
   */

  public function uip_get_role_capabilities()
  {
    // Set up an empty capabilities array.
    $categories = [
      "read" => [
        "shortname" => "read",
        "name" => __("Read", "uipress"),
        "caps" => [],
        "icon" => "bookmark",
      ],
      "edit" => [
        "shortname" => "edit",
        "name" => __("Edit", "uipress"),
        "caps" => [],
        "icon" => "edit_note",
      ],
      "publish" => [
        "shortname" => "publish",
        "name" => __("Publish", "uipress"),
        "caps" => [],
        "icon" => "publish",
      ],
      "create" => [
        "shortname" => "create",
        "name" => __("Create", "uipress"),
        "caps" => [],
        "icon" => "add_circle",
      ],
      "delete" => [
        "shortname" => "delete",
        "name" => __("Delete", "uipress"),
        "caps" => [],
        "icon" => "delete",
      ],
      "view" => [
        "shortname" => "view",
        "name" => __("View", "uipress"),
        "caps" => [],
        "icon" => "visibility",
      ],
      "manage" => [
        "shortname" => "manage",
        "name" => __("Manage", "uipress"),
        "caps" => [],
        "icon" => "tune",
      ],
      "export" => [
        "shortname" => "export",
        "name" => __("Export", "uipress"),
        "caps" => [],
        "icon" => "file_download",
      ],
      "import" => [
        "shortname" => "import",
        "name" => __("Import", "uipress"),
        "caps" => [],
        "icon" => "file_upload",
      ],
      "custom" => [
        "shortname" => "custom",
        "name" => __("Custom", "uipress"),
        "caps" => [],
        "icon" => "settings",
      ],
    ];
    $capabilities = [];

    global $wp_roles;

    $usercaps = [];
    // Loop through each role object because we need to get the caps.
    foreach ($wp_roles->role_objects as $key => $role) {
      // Make sure that the role has caps.
      if (is_array($role->capabilities)) {
        // Add each of the role's caps (both granted and denied) to the array.
        foreach ($role->capabilities as $cap => $grant) {
          $usercaps[] = $cap;
        }
      }
    }

    $postypeCaps = $this->uip_post_type_caps();

    $allcaps = array_merge($usercaps, $postypeCaps);
    $allcaps = array_unique($allcaps);

    foreach ($allcaps as $cap) {
      if (strpos($cap, "view") !== false) {
        $categories["view"]["caps"][] = $cap;
      } elseif (strpos($cap, "read") !== false) {
        $categories["read"]["caps"][] = $cap;
      } elseif (strpos($cap, "edit") !== false) {
        $categories["edit"]["caps"][] = $cap;
      } elseif (strpos($cap, "delete") !== false || strpos($cap, "remove") !== false) {
        $categories["delete"]["caps"][] = $cap;
      } elseif (
        strpos($cap, "manage") !== false ||
        strpos($cap, "install") !== false ||
        strpos($cap, "update") !== false ||
        strpos($cap, "switch") !== false ||
        strpos($cap, "moderate") !== false ||
        strpos($cap, "activate") !== false
      ) {
        $categories["manage"]["caps"][] = $cap;
      } elseif (strpos($cap, "export") !== false) {
        $categories["export"]["caps"][] = $cap;
      } elseif (strpos($cap, "import") !== false) {
        $categories["import"]["caps"][] = $cap;
      } elseif (strpos($cap, "publish") !== false) {
        $categories["publish"]["caps"][] = $cap;
      } elseif (strpos($cap, "create") !== false || strpos($cap, "upload") !== false) {
        $categories["create"]["caps"][] = $cap;
      } else {
        $categories["custom"]["caps"][] = $cap;
      }
    }

    // Return the capabilities array, making sure there are no duplicates.
    return $categories;
  }

  /**
   * Gets capabilities for post types
   * Original code modified from members plugin by Justin Tadlock
   * @since 2.3.5
   */

  public function uip_post_type_caps()
  {
    $postypecaps = [];
    foreach (get_post_types([], "objects") as $type) {
      // Skip revisions and nave menu items.
      if (in_array($type->name, ["revision", "nav_menu_item", "custom_css", "customize_changeset"])) {
        continue;
      }

      $post_type = $type->name;
      // Get the post type caps.
      $caps = (array) get_post_type_object($post_type)->cap;

      // remove meta caps.
      unset($caps["edit_post"]);
      unset($caps["read_post"]);
      unset($caps["delete_post"]);

      // Get the cap names only.
      $caps = array_values($caps);

      // If this is not a core post/page post type.
      if (!in_array($post_type, ["post", "page"])) {
        // Get the post and page caps.
        $post_caps = array_values((array) get_post_type_object("post")->cap);
        $page_caps = array_values((array) get_post_type_object("page")->cap);

        // Remove post/page caps from the current post type caps.
        $caps = array_diff($caps, $post_caps, $page_caps);
      }

      // If attachment post type, add the `unfiltered_upload` cap.
      if ("attachment" === $post_type) {
        $caps[] = "unfiltered_upload";
      }

      if (is_array($caps)) {
        foreach ($caps as $cap) {
          $postypecaps[] = $cap;
        }
      }
    }

    // Make sure there are no duplicates and return.
    return array_unique($postypecaps);
  }

  /**
   * Gets users recent page views
   * @since 2.3.5
   */
  public function get_user_activity($activityPage, $userID = null)
  {
    $args = [
      "post_type" => "uip-history",
      "post_status" => "publish",
      "author" => $userID,
      "posts_per_page" => 10,
      "paged" => $activityPage,
    ];

    $theposts = new WP_Query($args);
    $foundPosts = $theposts->get_posts();
    $actions = [];

    if (is_array($foundPosts)) {
      foreach ($foundPosts as $action) {
        $temp = $this->format_user_activity($action);
        array_push($actions, $temp);
      }
    }

    $data["list"] = $actions;
    $data["totalFound"] = $theposts->found_posts;
    $data["totalPages"] = $theposts->max_num_pages;

    return $data;
  }

  public function format_user_activity($action)
  {
    $returnData = [];
    $type = get_post_meta($action->ID, "uip-history-type", true);
    $context = get_post_meta($action->ID, "uip-history-context", true);
    $ip = get_post_meta($action->ID, "uip-history-ip", true);

    //POST TIME
    $view_time = get_post_timestamp($action->ID);
    $human_time = human_time_diff($view_time);

    //GET AUTHOR DETAILS
    $authorID = get_post_field("post_author", $action->ID);
    $user_meta = get_userdata($authorID);

    if ($user_meta) {
      $username = $user_meta->user_login;
      $roles = $user_meta->roles;
      $image = get_avatar_url($authorID, ["default" => "retro"]);
    } else {
      $username = __("User no longer exists", "uipress");
      $roles = [];
      $image = get_avatar_url($authorID, ["default" => "retro"]);
    }

    $returnData["human_time"] = sprintf(__("%s ago", "uipress"), $human_time);
    $returnData["ip_address"] = $ip;
    $returnData["id"] = $action->ID;
    $returnData["user"] = $username;
    $returnData["user_id"] = $authorID;
    $returnData["image"] = $image;
    $returnData["roles"] = $roles;
    $returnData["time"] = get_post_time(get_option("time_format"), false, $action->ID);
    $returnData["date"] = date(get_option("date_format"), $view_time);

    if ($type == "page_view") {
      $returnData["title"] = __("Page view", "uipress");
      $returnData["type"] = "primary";
      $returnData["meta"] = __("Viewed page", "uipress") . " <a class='uip-link-muted uip-no-underline uip-text-bold' href='{$context["url"]}'>{$context["title"]}</a>";
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "post_created") {
      $post_id = $context["post_id"];
      $url = get_the_permalink($post_id);
      $title = get_the_title($post_id);
      $returnData["title"] = __("Post created", "uipress");
      $returnData["type"] = "primary";
      $returnData["meta"] = __("Created post", "uipress") . " <a class='uip-link-muted uip-no-underline uip-text-bold' href='{$url}'>{$title}</a>";
      $returnData["links"] = [
        [
          "name" => __("View page", "uipress"),
          "url" => $url,
        ],
        [
          "name" => __("Edit page", "uipress"),
          "url" => get_edit_post_link($post_id),
        ],
      ];
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "post_updated") {
      $post_id = $context["post_id"];
      $url = get_the_permalink($post_id);
      $title = get_the_title($post_id);
      $returnData["title"] = __("Post modified", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] = __("Modified post", "uipress") . " <a class='uip-link-muted uip-no-underline uip-text-bold' href='{$url}'>{$title}</a>";
      $returnData["links"] = [
        [
          "name" => __("View page", "uipress"),
          "url" => $url,
        ],
        [
          "name" => __("Edit page", "uipress"),
          "url" => get_edit_post_link($post_id),
        ],
      ];
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "post_trashed") {
      $post_id = $context["post_id"];
      $url = get_the_permalink($post_id);
      $title = get_the_title($post_id);
      $returnData["title"] = __("Post moved to trash", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] = __("Moved post to trash", "uipress") . " <a class='uip-link-muted uip-no-underline uip-text-bold' href='{$url}'>{$title}</a>";
      $returnData["links"] = [
        [
          "name" => __("Edit page", "uipress"),
          "url" => get_edit_post_link($post_id),
        ],
      ];
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "post_deleted") {
      $returnData["title"] = __("Post deleted", "uipress");
      $returnData["type"] = "danger";
      $returnData["meta"] = __("Deleted post", "uipress") . " <strong>{$context["title"]}</strong> (ID:{$context["post_id"]})";
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "post_status_change") {
      $post_id = $context["post_id"];
      $url = get_the_permalink($post_id);
      $title = get_the_title($post_id);
      $returnData["title"] = __("Post status change", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] =
        sprintf(__("Post status changed from %s to %s", "uipress"), $context["old_status"], $context["new_status"]) .
        " <a class='uip-link-muted uip-no-underline uip-text-bold' href='{$url}'>{$title}</a> (ID:{$context["post_id"]})";
      $returnData["links"] = [
        [
          "name" => __("View page", "uipress"),
          "url" => $url,
        ],
        [
          "name" => __("Edit page", "uipress"),
          "url" => get_edit_post_link($post_id),
        ],
      ];
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "new_comment") {
      $post_id = $context["post_id"];
      $url = get_the_permalink($post_id);
      $title = get_the_title($post_id);

      $returnData["title"] = __("Posted a comment", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] = __("Posted a comment on post", "uipress") . " <a class='uip-link-muted uip-no-underline uip-text-bold' href='{$url}'>{$title}</a>";
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];

      $com = get_comment($context["comment_id"]);

      if ($com) {
        $comlink = get_comment_link($com);
        $editlink = get_edit_comment_link($context["comment_id"]);
        $returnData["links"] = [
          [
            "name" => __("View comment", "uipress"),
            "url" => $comlink,
          ],
          [
            "name" => __("Edit comment", "uipress"),
            "url" => $editlink,
          ],
        ];
      }
    }

    if ($type == "trash_comment") {
      $post_id = $context["post_id"];
      $url = get_the_permalink($post_id);
      $title = get_the_title($post_id);

      $returnData["title"] = __("Trashed a comment", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] = __("Moved a comment to the trash", "uipress");
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];

      $com = get_comment($context["comment_id"]);

      if ($com) {
        $comlink = get_comment_link($com);
        $editlink = get_edit_comment_link($context["comment_id"]);

        $returnData["links"] = [
          [
            "name" => __("View comment", "uipress"),
            "url" => $comlink,
          ],
          [
            "name" => __("Edit comment", "uipress"),
            "url" => $editlink,
          ],
        ];
      }
    }

    if ($type == "delete_comment") {
      $com = $context["comment_id"];

      $returnData["title"] = __("Deleted a comment", "uipress");
      $returnData["type"] = "danger";
      $returnData["meta"] = __("Permanently deleted a comment", "uipress") . " (ID:{$com})";
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "plugin_activated") {
      $returnData["title"] = __("Plugin activated", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] = sprintf(__("A plugin called %s was activated", "uipress"), $context["plugin_name"]);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "plugin_deactivated") {
      $returnData["title"] = __("Plugin deactivated", "uipress");
      $returnData["type"] = "danger";
      $returnData["meta"] = sprintf(__("A plugin called %s was deactivated", "uipress"), $context["plugin_name"]);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "user_login") {
      $returnData["title"] = __("User logged in", "uipress");
      $returnData["type"] = "primary";
      $returnData["meta"] = sprintf(__("Logged in with ip address %s. Country: %s", "uipress"), $context["ip"], $context["country"]);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "user_logout") {
      $returnData["title"] = __("User logged out", "uipress");
      $returnData["type"] = "primary";
      $returnData["meta"] = sprintf(__("Logged out with ip address %s. Country: %s", "uipress"), $context["ip"], $context["country"]);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "option_change") {
      if (is_array($context)) {
        $oldvalue = $context["old_value"];
        $newvalue = $context["new_value"];

        if (is_array($oldvalue) || is_object($oldvalue)) {
          $oldvalue = json_encode($oldvalue, JSON_PRETTY_PRINT);
        }

        if (is_array($newvalue) || is_object($newvalue)) {
          $newvalue = json_encode($newvalue, JSON_PRETTY_PRINT);
        }

        if (strlen($oldvalue) > 20) {
          $fullvalue = $oldvalue;
          $short = substr($oldvalue, 0, 20) . " ... ";
          $oldvalue = "<inline-drop>";
          $oldvalue .= "<trigger><strong>{$short}</strong></trigger>";
          $oldvalue .= "<drop-content class='uip-padding-xs uip-shadow uip-border-round uip-max-h-200 uip-max-w-300 uip-overflow-auto uip-background-default' style='left:50%;transform:translateX(-50%)'><pre>{$fullvalue}</pre><drop-content>";
          $oldvalue .= "</inline-drop>";
        }

        if (strlen($newvalue) > 20) {
          $fullvalue = $newvalue;
          $short = substr($newvalue, 0, 20) . " ... ";
          $newvalue = "<inline-drop >";
          $newvalue .= "<trigger><strong>{$short}</strong></trigger>";
          $newvalue .= "<drop-content class='uip-padding-xs uip-shadow uip-border-round uip-max-h-200 uip-max-w-300 uip-overflow-auto uip-background-default' style='left:50%;transform:translateX(-50%)'><pre>{$fullvalue}</pre><drop-content>";
          $newvalue .= "</inline-drop>";
        }

        $returnData["title"] = __("Site option changed", "uipress");
        $returnData["type"] = "danger";
        $returnData["meta"] = sprintf(__("Site option (%s) was changed from %s to %s", "uipress"), $context["option_name"], $oldvalue, $newvalue);
        $returnData["action"] = $returnData["title"];
        $returnData["description"] = $returnData["meta"];
      } else {
        $returnData["title"] = __("Site option changed", "uipress");
        $returnData["type"] = "danger";
        $returnData["meta"] = sprintf(__("Site option was changed. %s", "uipress"), $context);
        $returnData["action"] = $returnData["title"];
        $returnData["description"] = $returnData["meta"];
      }
    }

    if ($type == "option_added") {
      $newvalue = $context["new_value"];

      if (is_array($newvalue) || is_object($newvalue)) {
        $newvalue = json_encode($newvalue);
      }
      $returnData["title"] = __("Site option changed", "uipress");
      $returnData["type"] = "danger";
      $returnData["meta"] = sprintf(__("Site option (%s) was added with a value of (%s)", "uipress"), $context["option_name"], $newvalue);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "attachment_uploaded") {
      $returnData["title"] = __("Uploaded attachment", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] = sprintf(__("Attachment called (%s) was uploaded to (%s). Attachment ID: %s", "uipress"), $context["name"], $context["path"], $context["image_id"]);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];

      $attachment = get_edit_post_link($context["image_id"], "&");

      if ($attachment) {
        $returnData["links"] = [
          [
            "name" => __("View attachment", "uipress"),
            "url" => $attachment,
          ],
        ];
      }
    }

    if ($type == "attachment_deleted") {
      $returnData["title"] = __("Deleted attachment", "uipress");
      $returnData["type"] = "danger";
      $returnData["meta"] = sprintf(__("Attachment called (%s) was deleted. Attachment ID: %s", "uipress"), $context["name"], $context["image_id"]);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "user_created") {
      $returnData["title"] = __("User created", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] = sprintf(__("New user created with username (%s) and email (%s)", "uipress"), $context["username"], $context["email"]);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "user_deleted") {
      $returnData["title"] = __("User deleted", "uipress");
      $returnData["type"] = "danger";
      $returnData["meta"] = sprintf(__("A user with username (%s) and email (%s) was deleted", "uipress"), $context["username"], $context["email"]);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    if ($type == "user_updated") {
      $oldvalue = $context["old_value"];
      $newvalue = $context["new_value"];

      if (is_array($oldvalue) || is_object($oldvalue)) {
        $oldvalue = json_encode($oldvalue, JSON_PRETTY_PRINT);
      }

      if (is_array($newvalue) || is_object($newvalue)) {
        $newvalue = json_encode($newvalue, JSON_PRETTY_PRINT);
      }

      if (strlen($oldvalue) > 20) {
        $fullvalue = $oldvalue;
        $short = substr($oldvalue, 0, 20) . " ... ";
        $oldvalue = "<inline-drop>";
        $oldvalue .= "<trigger><strong>{$short}</strong></trigger>";
        $oldvalue .= "<drop-content class='uip-padding-xs uip-shadow uip-border-round uip-max-h-200 uip-max-w-300 uip-overflow-auto uip-background-default' style='left:50%;transform:translateX(-50%)'><pre>{$fullvalue}</pre><drop-content>";
        $oldvalue .= "</inline-drop>";
      }

      if (strlen($newvalue) > 20) {
        $fullvalue = $newvalue;
        $short = substr($newvalue, 0, 20) . " ... ";
        $newvalue = "<inline-drop >";
        $newvalue .= "<trigger><strong>{$short}</strong></trigger>";
        $newvalue .= "<drop-content class='uip-padding-xs uip-shadow uip-border-round uip-max-h-200 uip-max-w-300 uip-overflow-auto uip-background-default' style='left:50%;transform:translateX(-50%)'><pre>{$fullvalue}</pre><drop-content>";
        $newvalue .= "</inline-drop>";
      }

      $returnData["title"] = __("User updated", "uipress");
      $returnData["type"] = "warning";
      $returnData["meta"] = sprintf(__("A user with username (%s) and email (%s) was updated from (%s) to (%s)", "uipress"), $context["username"], $context["email"], $oldvalue, $newvalue);
      $returnData["action"] = $returnData["title"];
      $returnData["description"] = $returnData["meta"];
    }

    return $returnData;
  }

  /**
   * Gets users recent page views
   * @since 2.3.5
   */
  public function get_user_page_views($userID)
  {
    $recent_page_views = get_user_meta($userID, "recent_page_views", true);
    $page_views = [];

    if (is_array($recent_page_views)) {
      foreach ($recent_page_views as $view) {
        $view_time = $view["time"];
        $human_time = human_time_diff($view_time);

        $view["human_time"] = sprintf(__("%s ago", "uipress"), $human_time);
        array_push($page_views, $view);
      }
    }

    $page_views = array_reverse($page_views);

    return $page_views;
  }

  public function returnDateFilter($date, $type, $args)
  {
    if ($type == "on") {
      $year = date("Y", strtotime($date));
      $month = date("m", strtotime($date));
      $day = date("d", strtotime($date));

      $args["date_query"] = [
        "year" => $year,
        "month" => $month,
        "day" => $day,
      ];
    } else {
      if ($type == "before") {
        $args["date_query"] = [
          [
            "before" => date("Y-m-d", strtotime($date)),
            "inclusive" => true,
          ],
        ];
      } elseif ($type == "after") {
        $args["date_query"] = [
          [
            "after" => date("Y-m-d", strtotime($date)),
            "inclusive" => true,
          ],
        ];
      }
    }

    return $args;
  }

  /**
   * Builds colums for user table
   * @since 2.3.5
   */

  public function uip_format_user_data($all_users)
  {
    $allusers = [];
    foreach ($all_users as $user) {
      $user_meta = get_userdata($user->ID);
      $first_name = $user_meta->first_name;
      $last_name = $user_meta->last_name;
      $full_name = $first_name . " " . $last_name;
      $roles = $user->roles;

      //$hasimage = get_avatar($user->ID);
      $image = get_avatar_url($user->ID, ["default" => "retro"]);

      $expiry = get_user_meta($user->ID, "uip-user-expiry", true);
      $last_login = get_user_meta($user->ID, "uip_last_login_date", true);
      $group = get_user_meta($user->ID, "uip_user_group", true);

      if ($last_login) {
        $last_login = date(get_option("date_format"), strtotime($last_login));
      }

      $dateformat = get_option("date_format");
      $formattedCreated = date($dateformat, strtotime($user->user_registered));

      $temp["username"] = $user->user_login;
      $temp["user_email"] = $user->user_email;
      $temp["name"] = $full_name;
      $temp["first_name"] = $user->first_name;
      $temp["last_name"] = $user->last_name;
      $temp["uip_last_login_date"] = $last_login;
      $temp["roles"] = $roles;
      $temp["image"] = $image;
      $temp["initial"] = strtoupper($user->user_login[0]);
      $temp["user_id"] = $user->ID;
      $temp["expiry"] = $expiry;
      $temp["user_registered"] = $formattedCreated;
      $temp["uip_user_group"] = $group;
      $allusers[] = $temp;
    }

    return $allusers;
  }

  /**
   * Builds colums for user table
   * @since 2.3.5
   */

  public function uip_build_user_table_columns()
  {
    return [
      [
        "name" => "username",
        "label" => __("Username", "uipress"),
        "active" => true,
        "mobile" => true,
      ],
      [
        "name" => "name",
        "label" => __("Name", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
      [
        "name" => "user_email",
        "label" => __("Email", "uipress"),
        "active" => true,
        "mobile" => false,
      ],

      [
        "name" => "first_name",
        "label" => __("First Name", "uipress"),
        "active" => false,
        "mobile" => false,
      ],
      [
        "name" => "last_name",
        "label" => __("Last Name", "uipress"),
        "active" => false,
        "mobile" => false,
      ],
      [
        "name" => "uip_last_login_date",
        "label" => __("Last Login", "uipress"),
        "active" => false,
        "mobile" => false,
      ],
      [
        "name" => "roles",
        "label" => __("Roles", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
      [
        "name" => "uip_user_group",
        "label" => __("Groups", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
      [
        "name" => "user_id",
        "label" => __("User ID", "uipress"),
        "active" => false,
        "mobile" => false,
      ],
      [
        "name" => "user_registered",
        "label" => __("User created", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
    ];
  }

  /**
   * Builds colums for user table
   * @since 2.3.5
   */

  public function uip_build_role_table_columns()
  {
    return [
      [
        "name" => "label",
        "label" => __("Role name", "uipress"),
        "active" => true,
        "mobile" => true,
      ],
      [
        "name" => "name",
        "label" => __("Role", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
      [
        "name" => "users",
        "label" => __("Users", "uipress"),
        "active" => true,
        "mobile" => false,
      ],

      [
        "name" => "granted",
        "label" => __("Permissions granted", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
    ];
  }

  /**
   * Builds colums for user table
   * @since 2.3.5
   */

  public function uip_build_activity_table_columns()
  {
    return [
      [
        "name" => "id",
        "label" => __("ID", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
      [
        "name" => "user",
        "label" => __("User", "uipress"),
        "active" => true,
        "mobile" => true,
      ],
      [
        "name" => "ip_address",
        "label" => __("IP Address", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
      [
        "name" => "date",
        "label" => __("Date", "uipress"),
        "active" => true,
        "mobile" => true,
      ],

      [
        "name" => "time",
        "label" => __("Time", "uipress"),
        "active" => true,
        "mobile" => false,
      ],

      [
        "name" => "action",
        "label" => __("Action", "uipress"),
        "active" => true,
        "mobile" => true,
      ],
      [
        "name" => "description",
        "label" => __("Description", "uipress"),
        "active" => true,
        "mobile" => false,
      ],
    ];
  }

  /**
   * Returns list of history actions
   * @since 2.3.5
   */

  public function uip_return_history_actions()
  {
    return [
      [
        "name" => "page_view",
        "label" => __("Page view", "uipress"),
      ],
      [
        "name" => "post_created",
        "label" => __("Post created", "uipress"),
      ],
      [
        "name" => "post_updated",
        "label" => __("Post updated", "uipress"),
      ],
      [
        "name" => "post_trashed",
        "label" => __("Post trashed", "uipress"),
      ],
      [
        "name" => "post_deleted",
        "label" => __("Post deleted", "uipress"),
      ],
      [
        "name" => "post_status_change",
        "label" => __("Post status change", "uipress"),
      ],
      [
        "name" => "trash_comment",
        "label" => __("Trashed comment", "uipress"),
      ],
      [
        "name" => "delete_comment",
        "label" => __("Deelete comment", "uipress"),
      ],

      [
        "name" => "plugin_activated",
        "label" => __("Plugin activated", "uipress"),
      ],
      [
        "name" => "plugin_deactivated",
        "label" => __("Plugin deactivated", "uipress"),
      ],
      [
        "name" => "user_login",
        "label" => __("User login", "uipress"),
      ],
      [
        "name" => "user_logout",
        "label" => __("User logout", "uipress"),
      ],
      [
        "name" => "option_change",
        "label" => __("Option change", "uipress"),
      ],
      [
        "name" => "option_added",
        "label" => __("Site option added", "uipress"),
      ],
      [
        "name" => "attachment_uploaded",
        "label" => __("Attachmnet uploaded", "uipress"),
      ],
      [
        "name" => "attachment_deleted",
        "label" => __("Attachmnet deleted", "uipress"),
      ],
      [
        "name" => "user_created",
        "label" => __("User created", "uipress"),
      ],
      [
        "name" => "user_deleted",
        "label" => __("User deleted", "uipress"),
      ],
      [
        "name" => "user_updated",
        "label" => __("User updated", "uipress"),
      ],
    ];
  }
}
