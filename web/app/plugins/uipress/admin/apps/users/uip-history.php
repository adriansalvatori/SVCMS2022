<?php

// If this file is called directly, abort.
if (!defined("ABSPATH")) {
  exit();
}

//HOOK INTO DELAYED FUNCTIONS
add_action("uip_delay_history_event", "uip_deleyed_new_history_event", 10, 4);

/**
 * Creates new history event
 * @since 2.3.5
 */
function uip_deleyed_new_history_event($type, $context, $userID, $ip)
{
  $postTitle = $type . "-" . time();

  if ($userID == null) {
    $userID = get_current_user_id();
  }

  // Create post object
  $newHistory = [
    "post_title" => $postTitle,
    "post_type" => "uip-history",
    "post_status" => "publish",
    "post_author" => $userID,
  ];

  // Insert the post into the database
  $HistoryId = wp_insert_post($newHistory);

  if ($HistoryId && $HistoryId > 0) {
    update_post_meta($HistoryId, "uip-history-type", $type);
    update_post_meta($HistoryId, "uip-history-context", $context);
    update_post_meta($HistoryId, "uip-history-ip", $ip);
  }
}

class uip_history
{
  /**
   * Starts plugin
   * @since 1.0
   */
  public function start()
  {
    add_action("init", [$this, "register_history_type"]);

    //TRACK WORDPRESS ACTIONS
    add_action("wp_footer", [$this, "track_user_views"]);
    add_action("admin_footer", [$this, "track_user_views"]);
    //POST HISTORY
    add_action("save_post", [$this, "post_created"], 10, 3);
    add_action("transition_post_status", [$this, "post_status_changed"], 10, 3);
    add_action("wp_trash_post", [$this, "post_trashed"], 10);
    add_action("before_delete_post", [$this, "post_deleted"], 10);
    //COMMENTED HISTORY
    add_action("comment_post", [$this, "new_comment"], 10, 2);
    add_action("trash_comment", [$this, "trash_comment"], 10, 2);
    add_action("delete_comment", [$this, "delete_comment"], 10, 2);
    //PLUGINS
    add_action("activated_plugin", [$this, "plugin_activated"], 10, 2);
    add_action("deactivated_plugin", [$this, "plugin_deactivated"], 10, 2);
    //LOGIN
    add_action("wp_login", [$this, "user_last_login"], 10, 2);
    add_action("clear_auth_cookie", [$this, "user_logout"], 10);
    //WP OPTIONS
    add_action("updated_option", [$this, "uip_site_option_change"], 10, 3);
    add_action("added_option", [$this, "uip_site_option_added"], 10, 2);
    //IMAGES
    add_filter("wp_generate_attachment_metadata", [$this, "uip_log_image_upload"], 10, 3);
    add_filter("delete_attachment", [$this, "uip_log_image_delete"], 10, 2);
    //USERS
    add_filter("wp_create_user", [$this, "uip_log_new_user"], 10, 3);
    add_filter("wp_insert_user", [$this, "uip_log_new_user_insert"], 10, 1);
    add_filter("delete_user", [$this, "uip_log_new_user_delete"], 10, 3);
    add_filter("profile_update", [$this, "uip_log_user_update"], 10, 3);
    add_filter("user_register", [$this, "uip_log_user_register"], 10, 2);

    ///SCHEDULE HISTORY DELETION
    add_filter("cron_schedules", [$this, "uip_cron_schedules"]);
    if (!wp_next_scheduled("uip_cleanup_activity")) {
      wp_schedule_event(time(), "daily", "uip_cleanup_activity");
    }

    add_action("uip_cleanup_activity", [$this, "uip_remove_old_activity"]);
  }

  /**
   * Deletes old history
   * @since 2.3.5
   */
  public function uip_remove_old_activity()
  {
    $utils = new uipress_util();
    $expiry = $utils->get_option("uipusers", "history-expiry");

    if (!$expiry || !is_numeric($expiry)) {
      $expiry = 60;
    }

    $args = [
      "posts_per_page" => -1,
      "post_type" => "uip-history",
      "date_query" => [
        "before" => date("Y-m-d", strtotime("-" . $expiry . " days")),
      ],
    ];

    $history_query = new WP_Query($args);
    $all_history = $history_query->get_posts();

    $formatted = [];
    foreach ($all_history as $action) {
      wp_delete_post($action->ID);
    }

    error_log("uip history cleanup completed");
  }

  /**
   * Adds custom cron schedules
   * @since 2.3.5
   */
  function uip_cron_schedules($schedules)
  {
    if (!isset($schedules["1min"])) {
      $schedules["1min"] = [
        "interval" => 60,
        "display" => __("Once every 1 minutes"),
      ];
    }
    return $schedules;
  }

  /**
   * Creates custom post type for history
   * @since 2.3.5
   */
  public function register_history_type()
  {
    $labels = [
      "name" => _x("History", "post type general name", "uipress"),
      "singular_name" => _x("history", "post type singular name", "uipress"),
      "menu_name" => _x("History", "admin menu", "uipress"),
      "name_admin_bar" => _x("History", "add new on admin bar", "uipress"),
      "add_new" => _x("Add New", "history", "uipress"),
      "add_new_item" => __("Add New History", "uipress"),
      "new_item" => __("New History", "uipress"),
      "edit_item" => __("Edit History", "uipress"),
      "view_item" => __("View History", "uipress"),
      "all_items" => __("All History", "uipress"),
      "search_items" => __("Search History", "uipress"),
      "not_found" => __("No History found.", "uipress"),
      "not_found_in_trash" => __("No History found in Trash.", "uipress"),
    ];
    $args = [
      "labels" => $labels,
      "description" => __("Description.", "Add New History"),
      "public" => false,
      "publicly_queryable" => false,
      "show_ui" => true,
      "show_in_menu" => false,
      "query_var" => false,
      "has_archive" => false,
      "hierarchical" => false,
      "supports" => ["title", "author"],
    ];
    register_post_type("uip-history", $args);
  }

  /**
   * Capture Login Data
   * @since 1.0
   */
  public function user_last_login($user_login, $user)
  {
    update_user_meta($user->ID, "uip_last_login", time());
    update_user_meta($user->ID, "uip_last_login_date", date("Y-m-d"));

    $vis_ip = $this->getVisIPAddr();
    $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $vis_ip));
    $country = $ipdat->geoplugin_countryName;

    update_user_meta($user->ID, "uip_last_login_country", $country);

    $context["ip"] = $vis_ip;
    $context["country"] = $country;

    $this->create_new_history_event("user_login", $context, $user->ID);
  }

  /**
   * Get User IP
   * @since 1.0
   */
  public function getVisIpAddr()
  {
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
      $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
      $ip = $_SERVER["REMOTE_ADDR"];
    }

    $utils = new uipress_util();
    $anaomo = $utils->get_option("uipusers", "anonymize-ip");

    if ($anaomo) {
      return hash("ripemd160", $ip);
    } else {
      return $ip;
    }
  }
  /**
   * Tracks page views
   * @since 2.3.5
   */
  public function track_user_views()
  {
    //$startTime = microtime(true);

    if (defined("DOING_AJAX")) {
      return;
    }

    if (is_user_logged_in()) {
      $utils = new uipress_util();
      $disabled = $utils->get_option("uipusers", "recent-page-views-enabled");

      if ($disabled) {
        if (is_admin()) {
          $title = get_admin_page_title();
          $url = "//" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        } else {
          global $wp;
          $title = get_the_title();
          $url = home_url($wp->request);
        }

        $postTitle = "PageView " . time();
        $context["url"] = $url;
        $context["title"] = $title;

        $utils = new uipress_util();
        $pageviewsDisabled = $utils->get_option("uipusers", "history-page-views-disabled");

        if (!$pageviewsDisabled) {
          $this->create_new_history_event("page_view", $context);
        }

        $this->update_recent_views($url, $title);
      }
    }

    //echo "Elapsed time is: ". (microtime(true) - $startTime) ." seconds";
  }

  /**
   * Creates new history event
   * @since 2.3.5
   */
  public function create_new_history_event($type, $context, $userID = null)
  {
    $utils = new uipress_util();
    $cron = $utils->get_option("uipusers", "delay-history");

    if ($cron) {
      $args = [
        "type" => $type,
        "context" => $context,
        "userID" => get_current_user_id(),
        "ip" => $this->getVisIpAddr(),
      ];

      wp_schedule_single_event(time() + 10, "uip_delay_history_event", $args);
      return;
    }

    $postTitle = $type . "-" . time();

    if ($userID == null) {
      $userID = get_current_user_id();
    }

    // Create post object
    $newHistory = [
      "post_title" => $postTitle,
      "post_type" => "uip-history",
      "post_status" => "publish",
      "post_author" => $userID,
    ];

    // Insert the post into the database
    $HistoryId = wp_insert_post($newHistory);
    $userIP = $this->getVisIpAddr();

    if ($HistoryId && $HistoryId > 0) {
      update_post_meta($HistoryId, "uip-history-type", $type);
      update_post_meta($HistoryId, "uip-history-context", $context);
      update_post_meta($HistoryId, "uip-history-ip", $userIP);
    }
  }

  /**
   * Logs recent page views
   * @since 2.3.5
   */
  public function update_recent_views($url, $title)
  {
    $userID = get_current_user_id();
    $views = get_user_meta($userID, "recent_page_views", true);

    ///CHECK IF NO HISTORY
    if (!is_array($views)) {
      $views = [];
      $currentpage["title"] = $title;
      $currentpage["time"] = time();
      $currentpage["url"] = $url;
      array_push($views, $currentpage);
    } else {
      $length = count($views);

      ///ONLY KEEP 5 RECORDS
      if ($length > 4) {
        array_shift($views);
        $currentpage["title"] = $title;
        $currentpage["time"] = time();
        $currentpage["url"] = $url;
        array_push($views, $currentpage);
      } else {
        $currentpage["title"] = $title;
        $currentpage["time"] = time();
        $currentpage["url"] = $url;
        array_push($views, $currentpage);
      }
    }

    update_user_meta($userID, "recent_page_views", $views);
  }

  /**
   * Logs post creation / modification
   * @since 2.3.5
   */
  public function post_created($post_id, $post, $update)
  {
    if (get_post_type($post_id) == "uip-history") {
      return;
    }
    $context["title"] = $post->post_title;
    $context["url"] = get_permalink($post_id);
    $context["post_id"] = $post_id;

    if (!$update) {
      $this->create_new_history_event("post_created", $context);
    }
  }

  /**
   * Logs post status change
   * @since 2.3.5
   */
  public function post_status_changed($new_status, $old_status, $post)
  {
    if (get_post_type($post->ID) == "uip-history") {
      return;
    }

    if ($old_status != $new_status) {
      $context["title"] = $post->post_title;
      $context["url"] = get_permalink($post->ID);
      $context["post_id"] = $post->ID;
      $context["old_status"] = $old_status;
      $context["new_status"] = $new_status;
      $this->create_new_history_event("post_status_change", $context);
    }
  }

  /**Logs post trashing
   * @since 2.3.5
   */
  public function post_trashed($post_id)
  {
    if (get_post_type($post_id) == "uip-history") {
      return;
    }
    $context["title"] = get_the_title($post_id);
    $context["url"] = get_permalink($post_id);
    $context["post_id"] = $post_id;

    $this->create_new_history_event("post_trashed", $context);
  }

  /**
   * Logs post permanent delete
   * @since 2.3.5
   */
  public function post_deleted($post_id)
  {
    if (get_post_type($post_id) == "uip-history") {
      return;
    }

    if (wp_is_post_revision($post_id)) {
      return;
    }

    $context["title"] = get_the_title($post_id);
    $context["url"] = get_permalink($post_id);
    $context["post_id"] = $post_id;

    $this->create_new_history_event("post_deleted", $context);
  }

  /**
   * Logs new comment
   * @since 2.3.5
   */
  public function new_comment($comment_ID, $comment_approved)
  {
    $theComment = get_comment($comment_ID);
    $comment_post_id = $theComment->comment_post_ID;
    $context["author"] = $theComment->comment_author;
    $context["content"] = $theComment->comment_content;
    $context["comment_id"] = $comment_ID;
    $context["post_id"] = $comment_post_id;

    $this->create_new_history_event("new_comment", $context);
  }

  /**
   * Logs deleted comment
   * @since 2.3.5
   */
  public function trash_comment($comment_ID, $comment_approved)
  {
    $theComment = get_comment($comment_ID);
    $comment_post_id = $theComment->comment_post_ID;
    $context["author"] = $theComment->comment_author;
    $context["content"] = $theComment->comment_content;
    $context["comment_id"] = $comment_ID;
    $context["post_id"] = $comment_post_id;

    $this->create_new_history_event("trash_comment", $context);
  }

  /**
   * Logs deleted comment
   * @since 2.3.5
   */
  public function delete_comment($comment_ID, $comment_approved)
  {
    $theComment = get_comment($comment_ID);
    $comment_post_id = $theComment->comment_post_ID;
    $context["author"] = $theComment->comment_author;
    $context["content"] = $theComment->comment_content;
    $context["comment_id"] = $comment_ID;
    $context["post_id"] = $comment_post_id;

    $this->create_new_history_event("delete_comment", $context);
  }

  /**
   * Logs plugin activation
   * @since 2.3.5
   */
  public function plugin_activated($plugin, $network_activation)
  {
    $pluginObject = get_plugin_data(WP_PLUGIN_DIR . "/" . $plugin);
    $context["plugin_name"] = $pluginObject["Name"];
    $context["plugin_path"] = $plugin;

    $this->create_new_history_event("plugin_activated", $context);
  }

  /**
   * Logs plugin deactivation
   * @since 2.3.5
   */
  public function plugin_deactivated($plugin, $network_activation)
  {
    $pluginObject = get_plugin_data(WP_PLUGIN_DIR . "/" . $plugin);
    $context["plugin_name"] = $pluginObject["Name"];
    $context["plugin_path"] = $plugin;

    $this->create_new_history_event("plugin_deactivated", $context);
  }

  /**
   * Logs user logout
   * @since 2.3.5
   */
  public function user_logout()
  {
    $vis_ip = $this->getVisIPAddr();
    $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $vis_ip));
    $country = $ipdat->geoplugin_countryName;

    $context["ip"] = $vis_ip;
    $context["country"] = $country;

    $this->create_new_history_event("user_logout", $context, get_current_user_id());
  }

  /**
   * Logs option change
   * @since 2.3.5
   */
  public function uip_site_option_change($option_name, $old_value, $option_value)
  {
    if (strpos($option_name, "transient") !== false || strpos($option_name, "cron") !== false || strpos($option_name, "action_scheduler") !== false) {
      return;
    }

    $oldvalue = $old_value;
    $newvalue = $option_value;

    if (is_array($oldvalue)) {
      $oldvalue = json_encode($oldvalue);
    }

    if (is_array($newvalue)) {
      $newvalue = json_encode($newvalue);
    }

    if ($oldvalue == $newvalue) {
      return;
    }

    $context["option_name"] = $option_name;
    $context["old_value"] = $old_value;
    $context["new_value"] = $option_value;

    $this->create_new_history_event("option_change", $context, get_current_user_id());
  }

  /**
   * Logs option change
   * @since 2.3.5
   */
  public function uip_site_option_added($option_name, $option_value)
  {
    if (strpos($option_name, "transient") !== false || strpos($option_name, "cron") !== false || strpos($option_name, "action_scheduler") !== false) {
      return;
    }

    $newvalue = $option_value;

    if (is_array($newvalue)) {
      $newvalue = json_encode($newvalue);
    }

    $context["option_name"] = $option_name;
    $context["new_value"] = $option_value;

    $this->create_new_history_event("option_added", $context, get_current_user_id());
  }

  /**
   * Logs image upload
   * @since 2.3.5
   */
  public function uip_log_image_upload($metadata, $attachment_id, $context)
  {
    $data["name"] = get_the_title($attachment_id);
    $data["path"] = $metadata["file"];
    $data["image_id"] = $attachment_id;

    $this->create_new_history_event("attachment_uploaded", $data, get_current_user_id());

    return $metadata;
  }

  /**
   * Logs image delete
   * @since 2.3.5
   */
  public function uip_log_image_delete($attachment_id, $post)
  {
    $data["name"] = get_the_title($attachment_id);
    $data["image_id"] = $attachment_id;

    $this->create_new_history_event("attachment_deleted", $data, get_current_user_id());
  }

  /**
   * Logs user creation
   * @since 2.3.5
   */
  public function uip_log_new_user($username, $password, $email)
  {
    $data["username"] = $username;
    $data["email"] = $email;

    $this->create_new_history_event("user_created", $data, get_current_user_id());
  }

  /**
   * Logs user creation
   * @since 2.3.5
   */
  public function uip_log_user_register($userid, $userdata)
  {
    $userObj = new WP_User($userid);

    $data["username"] = $userObj->user_login;
    $data["email"] = $userObj->user_email;
    $data["user_id"] = $userid;

    $this->create_new_history_event("user_created", $data, get_current_user_id());
  }

  /**
   * Logs user creation
   * @since 2.3.5
   */
  public function uip_log_new_user_insert($user)
  {
    $data["username"] = $user->user_login;
    $data["email"] = $user->user_email;

    $this->create_new_history_event("user_created", $data, get_current_user_id());
  }

  /**
   * Logs user deletion
   * @since 2.3.5
   */
  public function uip_log_new_user_delete($id, $reassign, $user)
  {
    $data["username"] = $user->user_login;
    $data["email"] = $user->user_email;
    $data["user_id"] = $id;

    $this->create_new_history_event("user_deleted", $data, get_current_user_id());
  }

  /**
   * Logs user update
   * @since 2.3.5
   */
  public function uip_log_user_update($user_id, $old_user_data, $userdata)
  {
    $userObj = new WP_User($user_id);

    $data["username"] = $userObj->user_login;
    $data["email"] = $userObj->user_email;
    $data["user_id"] = $user_id;
    $data["old_value"] = $old_user_data;
    $data["new_value"] = $userdata;

    $this->create_new_history_event("user_updated", $data, get_current_user_id());
  }
}
