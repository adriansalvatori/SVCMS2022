<?php
if (!defined("ABSPATH")) {
  exit();
}

class uipress_users_ajax extends uipress_users
{
  /**
   * Adds ajax actions
   * @since 2.3.5
   */

  public function ajax_actions()
  {
    ///AJAX
    add_action("wp_ajax_uip_get_user_table_data", [$this, "uip_get_user_table_data"]);
    add_action("wp_ajax_uip_get_activity_table_data", [$this, "uip_get_activity_table_data"]);
    add_action("wp_ajax_uip_get_role_table_data", [$this, "uip_get_role_table_data"]);
    add_action("wp_ajax_uip_get_user_data", [$this, "uip_get_user_data"]);
    add_action("wp_ajax_uip_get_user_roles", [$this, "uip_get_user_roles"]);
    add_action("wp_ajax_uip_update_user", [$this, "uip_update_user"]);
    add_action("wp_ajax_uip_batch_update_roles", [$this, "uip_batch_update_roles"]);
    add_action("wp_ajax_uip_update_role", [$this, "uip_update_role"]);
    add_action("wp_ajax_uip_create_role", [$this, "uip_create_role"]);
    add_action("wp_ajax_uip_delete_role", [$this, "uip_delete_role"]);
    add_action("wp_ajax_uip_delete_roles", [$this, "uip_delete_roles"]);
    add_action("wp_ajax_uip_add_new_user", [$this, "uip_add_new_user"]);
    add_action("wp_ajax_uip_reset_password", [$this, "uip_reset_password"]);
    add_action("wp_ajax_uip_password_reset_multiple", [$this, "uip_password_reset_multiple"]);
    add_action("wp_ajax_uip_delete_user", [$this, "uip_delete_user"]);
    add_action("wp_ajax_uip_delete_multiple_users", [$this, "uip_delete_multiple_users"]);
    add_action("wp_ajax_uip_send_message", [$this, "uip_send_message"]);
    add_action("wp_ajax_uip_add_custom_capability", [$this, "uip_add_custom_capability"]);
    add_action("wp_ajax_uip_logout_user_everywhere", [$this, "uip_logout_user_everywhere"]);
  }

  /**
   * Gets data for user table
   * @since 2.3.5
   */

  public function uip_get_user_table_data()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $page = $utils->clean_ajax_input($_POST["tablePage"]);
      $filters = $utils->clean_ajax_input($_POST["filters"]);
      $options = $utils->clean_ajax_input($_POST["options"]);

      //SET SEARCH QUERY
      $s_query = "";
      if (isset($filters["search"])) {
        $s_query = $filters["search"];
      }

      //SET ROLE FILTERS
      $roles = [];
      if (isset($filters["roles"]) && is_array($filters["roles"])) {
        $roles = $filters["roles"];
      }

      //SET DIRECTION
      $direction = "ASC";
      if (isset($options["direction"]) && $options["direction"] != "") {
        $direction = $options["direction"];
      }

      //SET DIRECTION
      $perpage = "20";
      if (isset($options["perPage"]) && $options["perPage"] != "") {
        $perpage = $options["perPage"];
      }

      $args = [
        "number" => $perpage,
        "role__in" => $roles,
        "search" => "*" . $s_query . "*",
        "paged" => $page,
        "order" => $direction,
      ];

      //SET ORDERBY
      $sortBy = "username";
      if (isset($options["sortBy"]) && $options["sortBy"] != "") {
        $sortBy = $options["sortBy"];
      }

      //SET FOLDER FILTERS
      if (isset($filters["activeGroup"]) && $filters["activeGroup"] != "" && $filters["activeGroup"] != "all") {
        if ($filters["activeGroup"] == "nofolder") {
          $args["meta_query"] = [
            "relation" => "OR",
            [
              "key" => "uip_user_group",
              "compare" => "NOT EXISTS",
            ],
            [
              "key" => "uip_user_group",
              "value" => "",
              "compare" => "=",
            ],
            [
              "key" => "uip_user_group",
              "value" => "",
              "compare" => "[]",
            ],
          ];
        } else {
          $args["meta_query"] = [
            [
              "key" => "uip_user_group",
              "value" => '"' . $filters["activeGroup"] . '"',
              "compare" => "LIKE",
            ],
          ];
        }
      }

      //SET ORDER BY
      $metakeys = ["first_name", "last_name", "last_name", "uip_last_login_date", "uip_user_group"];

      if (in_array($sortBy, $metakeys)) {
        $args["orderby"] = "meta_value";
        $args["meta_key"] = $sortBy;
      } elseif ($sortBy == "roles") {
        $args["orderby"] = "meta_value";
        $args["meta_key"] = "wp_capabilities";
      } else {
        $args["orderby"] = $sortBy;
      }

      if (isset($filters["dateCreated"]) && is_array($filters["dateCreated"])) {
        if (isset($filters["dateCreated"]["date"]) && $filters["dateCreated"]["date"] != "") {
          $dateCreated = $filters["dateCreated"]["date"];
          $dataComparison = $filters["dateCreated"]["type"];

          $args = $this->returnDateFilter($dateCreated, $dataComparison, $args);
        }
      }

      $user_query = new WP_User_Query($args);
      $all_users = $user_query->get_results();
      $total_users = $user_query->get_total();

      $args = [
        "numberposts" => -1,
        "post_type" => "uip_user_group",
        "orderby" => "title",
        "order" => "ASC",
      ];

      $groups = get_posts($args);
      $formattedGroups = [];
      foreach ($groups as $group) {
        $temp = [];
        $temp["color"] = get_post_meta($group->ID, "color_tag", true);
        $temp["title"] = $group->post_title;
        $temp["id"] = $group->ID;
        $temp["icon"] = get_post_meta($group->ID, "group_icon", true);
        $formattedGroups[$group->ID] = $temp;
      }

      $returnData["tableData"]["totalFound"] = number_format($total_users);
      $returnData["tableData"]["users"] = $this->uip_format_user_data($all_users);
      $returnData["tableData"]["groups"] = $formattedGroups;

      $returnData["tableData"]["columns"] = $this->uip_build_user_table_columns();

      echo json_encode($returnData);
    }
    die();
  }

  /**
   * Gets data for user table
   * @since 2.3.5
   */

  public function uip_get_activity_table_data()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $page = $utils->clean_ajax_input($_POST["tablePage"]);
      $filters = $utils->clean_ajax_input($_POST["filters"]);
      $options = $utils->clean_ajax_input($_POST["options"]);

      //SET SEARCH QUERY
      $s_query = "";
      if (isset($filters["search"])) {
        $s_query = $filters["search"];
      }

      //SET ROLE FILTERS
      $roles = [];
      if (isset($filters["roles"]) && is_array($filters["roles"])) {
        $roles = $filters["roles"];
      }

      //SET DIRECTION
      $direction = "ASC";
      if (isset($options["direction"]) && $options["direction"] != "") {
        $direction = $options["direction"];
      }

      //SET DIRECTION
      $perpage = "20";
      if (isset($options["perPage"]) && $options["perPage"] != "") {
        $perpage = $options["perPage"];
      }

      $args = [
        "posts_per_page" => $perpage,
        "orderby" => "date",
        "post_type" => "uip-history",
        "paged" => $page,
        "order" => $direction,
        "meta_query" => [
          "relation" => "AND",
        ],
      ];

      if (count($roles) > 0) {
        $userargs = [
          "role__in" => $roles,
          "fields" => ["ID"],
        ];

        $users = get_users($userargs);

        $ids = [];
        foreach ($users as $user) {
          $ids[] = $user->ID;
        }

        if (count($ids) === 0) {
          $ids = [0];
        }

        $args["author__in"] = $ids;
      }

      //SET SEARCH FILTER
      if ($s_query != "") {
        $query = [
          "relation" => "OR",
          [
            "key" => "uip-history-type",
            "value" => $s_query,
            "compare" => "LIKE",
          ],
          [
            "key" => "uip-history-context",
            "value" => $s_query,
            "compare" => "LIKE",
          ],
          [
            "key" => "uip-history-ip",
            "value" => $s_query,
            "compare" => "LIKE",
          ],
        ];

        array_push($args["meta_query"], $query);
      }

      //SET ACTION FILTER
      if (isset($filters["action"]) && $filters["action"] != "") {
        $action = $filters["action"];
        $query = [
          "key" => "uip-history-type",
          "value" => $action,
          "compare" => "=",
        ];

        array_push($args["meta_query"], $query);
      }

      if (isset($filters["dateCreated"]) && is_array($filters["dateCreated"])) {
        if (isset($filters["dateCreated"]["date"]) && $filters["dateCreated"]["date"] != "") {
          $dateCreated = $filters["dateCreated"]["date"];
          $dataComparison = $filters["dateCreated"]["type"];

          $args = $this->returnDateFilter($dateCreated, $dataComparison, $args);
        }
      }

      $history_query = new WP_Query($args);
      $all_history = $history_query->get_posts();
      $total_history = $history_query->found_posts;

      $formatted = [];
      foreach ($all_history as $action) {
        $formatted[] = $this->format_user_activity($action);
      }

      $returnData["tableData"]["totalFound"] = number_format($total_history);
      $returnData["tableData"]["activity"] = $formatted;
      $returnData["tableData"]["totalPages"] = $history_query->max_num_pages;

      $returnData["tableData"]["columns"] = $this->uip_build_activity_table_columns();
      $returnData["tableData"]["actions"] = $this->uip_return_history_actions();

      echo json_encode($returnData);
    }
    die();
  }

  /**
   * Gets data for role table
   * @since 2.3.5
   */

  public function uip_get_role_table_data()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $page = $utils->clean_ajax_input($_POST["tablePage"]);
      $filters = $utils->clean_ajax_input($_POST["filters"]);
      $options = $utils->clean_ajax_input($_POST["options"]);

      //SET SEARCH QUERY
      $s_query = "";
      if (isset($filters["search"])) {
        $s_query = $filters["search"];
      }

      global $wp_roles;

      $allroles = [];

      global $wp_roles;
      $all_roles = [];

      foreach ($wp_roles->roles as $key => $value) {
        $temp = [];

        if (!isset($value["name"]) || $value["name"] == "") {
          continue;
        }

        if ($s_query != "") {
          if (strpos(strtolower($value["name"]), strtolower($s_query)) === false) {
            continue;
          }
        }

        $temp["name"] = $key;
        $temp["label"] = $value["name"];
        $temp["caps"] = $value["capabilities"];
        $temp["granted"] = count($value["capabilities"]);

        $args = [
          "number" => -1,
          "role__in" => [$key],
        ];

        $user_query = new WP_User_Query($args);
        $temp["users"] = $user_query->get_total();
        array_push($all_roles, $temp);
      }

      usort($all_roles, function ($a, $b) {
        return strcmp($a["name"], $b["name"]);
      });

      $returnData["tableData"]["totalFound"] = count($wp_roles->role_objects);
      $returnData["tableData"]["roles"] = $all_roles;

      $returnData["tableData"]["columns"] = $this->uip_build_role_table_columns();

      echo json_encode($returnData);
    }
    die();
  }

  /**
   * Gets data for specific user
   * @since 2.3.5
   */

  public function uip_get_user_data()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $userID = $utils->clean_ajax_input($_POST["userID"]);
      $activityPage = $utils->clean_ajax_input($_POST["activityPage"]);

      $user_meta = get_userdata($userID);

      $first_name = $user_meta->first_name;
      $last_name = $user_meta->last_name;
      $full_name = $first_name . " " . $last_name;
      $roles = $user_meta->roles;

      //$hasimage = get_avatar($user->ID);
      $image = get_avatar_url($user_meta->ID, ["default" => "retro"]);

      $expiry = get_user_meta($user_meta->ID, "uip-user-expiry", true);
      $last_login = get_user_meta($user_meta->ID, "uip_last_login_date", true);
      $last_login_country = get_user_meta($user_meta->ID, "uip_last_login_country", true);
      $user_notes = get_user_meta($user_meta->ID, "uip_user_notes", true);
      $profileImage = get_user_meta($user_meta->ID, "uip_profile_image", true);
      $groups = get_user_meta($user_meta->ID, "uip_user_group", true);

      if (!is_array($groups)) {
        $groups = [];
      }

      if ($last_login) {
        $last_login = date(get_option("date_format"), strtotime($last_login));
      }

      if (!$last_login_country || $last_login_country == "") {
        $last_login_country = __("Unknown", "uipress");
      }

      $dateformat = get_option("date_format");
      $formattedCreated = date($dateformat, strtotime($user_meta->user_registered));

      $temp["username"] = $user_meta->user_login;
      $temp["user_email"] = $user_meta->user_email;
      $temp["name"] = $full_name;
      $temp["first_name"] = $user_meta->first_name;
      $temp["last_name"] = $user_meta->last_name;
      $temp["uip_last_login_date"] = $last_login;
      $temp["uip_last_login_country"] = $last_login_country;
      $temp["roles"] = $roles;
      $temp["image"] = $image;
      $temp["initial"] = strtoupper($user_meta->user_login[0]);
      $temp["user_id"] = $user_meta->ID;
      $temp["expiry"] = $expiry;
      $temp["user_registered"] = $formattedCreated;
      $temp["notes"] = $user_notes;
      $temp["uip_profile_image"] = $profileImage;
      $temp["uip_user_group"] = $groups;

      $args = [
        "user_id" => $userID,
        "count" => true,
      ];
      $comments = get_comments($args);

      $args = [
        "public" => true,
      ];

      $output = "names"; // 'names' or 'objects' (default: 'names')

      $post_types = get_post_types($args, $output);
      $formatted = [];
      foreach ($post_types as $type) {
        $formatted[] = $type;
      }

      $postcount = count_user_posts($userID, $formatted, true);

      $temp["totalComments"] = $comments;
      $temp["totalPosts"] = $postcount;

      $returndata["user"] = $temp;
      $returndata["recentPageViews"] = $this->get_user_page_views($userID);
      $returndata["history"] = $this->get_user_activity($activityPage, $userID);

      echo json_encode($returndata);
    }
    die();
  }

  /**
   * Gets data for user table
   * @since 2.3.5
   */

  public function uip_get_user_roles()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      global $wp_roles;
      $all_roles = [];

      foreach ($wp_roles->roles as $key => $value) {
        $temp = [];
        $temp["name"] = $key;
        $temp["label"] = $value["name"];
        array_push($all_roles, $temp);
      }

      usort($all_roles, function ($a, $b) {
        return strcmp($a["name"], $b["name"]);
      });

      $returnData["roles"] = $all_roles;

      echo json_encode($returnData);
    }
    die();
  }

  /**
   * Updates user info
   * @since 2.3.5
   */

  public function uip_update_user()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $user = $utils->clean_ajax_input($_POST["user"]);

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to edit users", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!filter_var($user["user_email"], FILTER_VALIDATE_EMAIL)) {
        $returndata["error"] = __("Email is not valid", "uipress");
        echo json_encode($returndata);
        die();
      }

      $user_info = get_userdata($user["user_id"]);
      $currentemail = $user_info->user_email;

      //CHECK IF SAME EMAIL - IF NOT CHECK IF NEW ONE EXISTS
      if ($currentemail != $user["user_email"]) {
        if (email_exists($user["user_email"])) {
          $returndata["error"] = __("Email already exists", "uipress");
          echo json_encode($returndata);
          die();
        }
      }

      wp_update_user([
        "ID" => $user["user_id"], // this is the ID of the user you want to update.
        "first_name" => $user["first_name"],
        "last_name" => $user["last_name"],
        "role" => "",
        "user_email" => $user["user_email"],
      ]);

      if (isset($user["roles"]) && is_array($user["roles"])) {
        $userObj = new WP_User($user["user_id"]);

        foreach ($user["roles"] as $role) {
          $userObj->add_role($role);
        }
      }

      update_user_meta($user["user_id"], "uip_user_notes", $user["notes"]);
      update_user_meta($user["user_id"], "uip_profile_image", $user["uip_profile_image"]);

      if (isset($user["uip_user_group"]) && is_array($user["uip_user_group"])) {
        update_user_meta($user["user_id"], "uip_user_group", $user["uip_user_group"]);
      }

      $returndata["message"] = __("User saved", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Batch updates roles
   * @since 2.3.5
   */

  public function uip_batch_update_roles()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $allUsers = $utils->clean_ajax_input($_POST["allRecipients"]);
      $settings = $utils->clean_ajax_input($_POST["settings"]);

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to edit users", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!is_array($allUsers) || count($allUsers) < 1) {
        $returndata["error"] = __("No users sent to update", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($settings["roles"]) || count($settings["roles"]) < 1) {
        $returndata["error"] = __("No roles sent to update", "uipress");
        echo json_encode($returndata);
        die();
      }

      foreach ($allUsers as $user) {
        $userObj = new WP_User($user["user_id"]);

        if ($settings["replaceExisting"] == "true") {
          $currentroles = $userObj->roles;

          foreach ($currentroles as $temprole) {
            $userObj->remove_role($temprole);
          }
        }

        foreach ($settings["roles"] as $role) {
          $userObj->add_role($role);
        }
      }

      $returndata["message"] = __("User roles updated", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Updates role info
   * @since 2.3.5
   */

  public function uip_update_role()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $newrole = $utils->clean_ajax_input($_POST["role"]);
      $ogrolename = $utils->clean_ajax_input($_POST["originalRoleName"]);

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to manage roles", "uipress");
        echo json_encode($returndata);
        die();
      }

      if ($ogrolename == "") {
        $returndata["error"] = __("Original role name required", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($newrole["label"]) || $newrole["label"] == "") {
        $returndata["error"] = __("Role name is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      $capabilities = [];
      if (is_array($newrole["caps"])) {
        foreach ($newrole["caps"] as $key => $value) {
          if ($value == "true" || $value === true) {
            $capabilities[$key] = true;
          } else {
            $capabilities[$key] = false;
          }
        }
      }

      remove_role($ogrolename);
      $status = add_role($ogrolename, $newrole["label"], $capabilities);

      if ($status == null) {
        $returndata["error"] = __("Something has gone wrong", "uipress");
        echo json_encode($returndata);
        die();
      }

      $returndata["message"] = __("Role saved", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Logsout user everywhere
   * @since 2.3.5
   */

  public function uip_logout_user_everywhere()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $userid = $utils->clean_ajax_input($_POST["userID"]);

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to do this", "uipress");
        echo json_encode($returndata);
        die();
      }

      global $wp_session;
      $user_id = $userid;
      $session = wp_get_session_token();
      $sessions = WP_Session_Tokens::get_instance($user_id);
      $sessions->destroy_others($session);

      $returndata["message"] = __("User logged out everywhere", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * creates new role
   * @since 2.3.5
   */

  public function uip_create_role()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $newrole = $utils->clean_ajax_input($_POST["newrole"]);

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to manage roles", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($newrole["name"]) || $newrole["name"] == "") {
        $returndata["error"] = __("Role name is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (strpos($newrole["label"], " ") !== false) {
        $returndata["error"] = __("Role label cannot contain spaces", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($newrole["label"]) || $newrole["label"] == "") {
        $returndata["error"] = __("Role label is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      $capabilities = [];
      if (is_array($newrole["caps"])) {
        foreach ($newrole["caps"] as $key => $value) {
          if ($value == "true" || $value == true) {
            $capabilities[$key] = true;
          } else {
            $capabilities[$key] = false;
          }
        }
      }

      $status = add_role(strtolower($newrole["label"]), $newrole["name"], $capabilities);

      if ($status == null) {
        $returndata["error"] = __("Unable to add role. Make sure role name is unique", "uipress");
        echo json_encode($returndata);
        die();
      }

      $returndata["message"] = __("Role created", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Updates role info
   * @since 2.3.5
   */

  public function uip_delete_role()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $role = $utils->clean_ajax_input($_POST["role"]);

      if (!current_user_can("delete_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to manage roles", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($role["name"]) || $role["name"] == "") {
        $returndata["error"] = __("Role name is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      $user = wp_get_current_user();
      $currentRoles = $user->roles;

      if (in_array($role["name"], $currentRoles)) {
        $returndata["error"] = __("You can't delete a role that is currently assigned to yourself", "uipress");
        echo json_encode($returndata);
        die();
      }

      remove_role($role["name"]);

      $returndata["message"] = __("Role deleted", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Deletes multiple roles
   * @since 2.3.5
   */

  public function uip_delete_roles()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $roles = $utils->clean_ajax_input($_POST["roles"]);

      if (!current_user_can("delete_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to manage roles", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!is_array($roles)) {
        $returndata["error"] = __("No roles to delete", "uipress");
        echo json_encode($returndata);
        die();
      }

      $errors = [];
      $user = wp_get_current_user();
      $currentRoles = $user->roles;

      foreach ($roles as $role) {
        if (!isset($role["name"]) || $role["name"] == "") {
          $errors[] = [
            "message" => __("Role name is required", "uipress"),
            "role" => $role["name"],
          ];
        }

        if (in_array($role["name"], $currentRoles)) {
          $errors[] = [
            "message" => __("You can't delete a role that is currently assigned to yourself", "uipress"),
            "role" => $role["name"],
          ];
          continue;
        }

        remove_role($role["name"]);
      }

      $returndata["message"] = __("Roles deleted", "uipress");
      $returndata["undeleted"] = $errors;
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Updates role info
   * @since 2.3.5
   */

  public function uip_add_custom_capability()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $role = $utils->clean_ajax_input($_POST["role"]);
      $customcap = $utils->clean_ajax_input($_POST["customcap"]);

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to delete this user", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($role["name"]) || $role["name"] == "") {
        $returndata["error"] = __("Role name is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($role["label"]) || $role["label"] == "") {
        $returndata["error"] = __("Role name is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (strpos($role["name"], " ") !== false) {
        $returndata["error"] = __("Role name cannot contain spaces", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (strpos($customcap, " ") !== false) {
        $returndata["error"] = __("Capability name cannot contain spaces", "uipress");
        echo json_encode($returndata);
        die();
      }

      $customcap = strtolower($customcap);

      $currentRole = get_role($role["name"]);
      $currentRole->add_cap($customcap, false);
      $currentcaps = $currentRole->capabilities;

      remove_role($role["name"]);
      $status = add_role($role["name"], $role["label"], $currentcaps);

      if ($status == null) {
        $returndata["error"] = __("Unable to add capability. Make sure role name is unique", "uipress");
        echo json_encode($returndata);
        die();
      }

      $returndata["message"] = __("Capability deleted", "uipress");
      $returndata["allcaps"] = $this->uip_get_role_capabilities();
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Updates user info
   * @since 2.3.5
   */

  public function uip_add_new_user()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $user = $utils->clean_ajax_input($_POST["user"]);

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to create users", "uipress");
        echo json_encode($returndata);
        die();
      }

      //CHECK USERNAME EXISTS
      if (username_exists($user["username"])) {
        $returndata["error"] = __("Username already exists", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!validate_username($user["username"])) {
        $returndata["error"] = __("Username is not valid", "uipress");
        echo json_encode($returndata);
        die();
      }

      //CHECK IF SAME EMAIL - IF NOT CHECK IF NEW ONE EXISTS
      if (email_exists($user["user_email"])) {
        $returndata["error"] = __("Email already exists", "uipress");
        echo json_encode($returndata);
        die();
      }

      //CHECK IF EMAIL IS VALID
      if (!filter_var($user["user_email"], FILTER_VALIDATE_EMAIL)) {
        $returndata["error"] = __("Email is not valid", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($user["password"]) || ($user["password"] = "")) {
        $returndata["error"] = __("Password is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      $user_id = wp_create_user($user["username"], $user["password"], $user["user_email"]);

      if (is_wp_error($user_id)) {
        $error_string = $user_id->get_error_message();
        $returndata["error"] = $error_string;
        echo json_encode($returndata);
        die();
      }

      wp_update_user([
        "ID" => $user_id, // this is the ID of the user you want to update.
        "first_name" => $user["first_name"],
        "last_name" => $user["last_name"],
        "role" => "",
        "user_email" => $user["user_email"],
      ]);

      if (isset($user["roles"]) && is_array($user["roles"])) {
        $userObj = new WP_User($user_id);

        foreach ($user["roles"] as $role) {
          $userObj->add_role($role);
        }
      }

      if (isset($user["notes"])) {
        update_user_meta($user_id, "uip_user_notes", $user["notes"]);
      }
      if (isset($user["uip_profile_image"])) {
        update_user_meta($user_id, "uip_profile_image", $user["uip_profile_image"]);
      }
      if (isset($user["uip_user_group"]) && is_array($user["uip_user_group"])) {
        update_user_meta($user_id, "uip_user_group", $user["uip_user_group"]);
      }

      $returndata["message"] = __("User saved", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Sends user reset pass
   * @since 2.3.5
   */

  public function uip_reset_password()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $user = $utils->clean_ajax_input($_POST["user"]);

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to edit this user", "uipress");
        echo json_encode($returndata);
        die();
      }

      $username = $user["username"];
      $status = retrieve_password($username);

      if ($status === true) {
        $returndata["message"] = __("Password reset link sent", "uipress");
        echo json_encode($returndata);
        die();
      } else {
        $returndata["error"] = __("Unable to send password reset email at the moment", "uipress");
        echo json_encode($returndata);
        die();
      }
    }
    die();
  }

  /**
   * Sends user reset pass
   * @since 2.3.5
   */

  public function uip_password_reset_multiple()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $allIDS = $utils->clean_ajax_input($_POST["allIDS"]);

      if (!is_array($allIDS)) {
        $returndata["message"] = __("No users sent to reset passwords!", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!current_user_can("edit_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to edit this user", "uipress");
        echo json_encode($returndata);
        die();
      }

      $errors = [];
      foreach ($allIDS as $userID) {
        $current = get_user_by("id", $userID);
        $username = $current->user_login;

        $status = retrieve_password($username);

        if (!$status) {
          $errors[] = [
            "message" => __("Unable to send password reset email", "uipress"),
            "user" => sprintf(__("User ID: %s", "uipress"), $userID),
          ];
          continue;
        }
      }

      $returndata["message"] = __("Password reset links succesfully sent", "uipress");
      $returndata["undeleted"] = $errors;
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Sends message to given user
   * @since 2.3.5
   */

  public function uip_send_message()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $message = $utils->clean_ajax_input_html($_POST["message"]);

      $allrecip = [];
      if (isset($_POST["allRecipients"])) {
        $allrecip = $utils->clean_ajax_input_html($_POST["allRecipients"]);
      }

      if (!isset($message["subject"]) || $message["subject"] == "") {
        $returndata["error"] = __("Subject is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($message["replyTo"]) || $message["replyTo"] == "") {
        $returndata["error"] = __("Reply to email is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!isset($message["message"]) || $message["message"] == "") {
        $returndata["error"] = __("Message is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      //ARE WE BATCHING
      $batchemail = false;
      if (is_array($allrecip) && count($allrecip) > 0) {
        $email = [];
        $batchemail = true;
        foreach ($allrecip as $user) {
          array_push($email, $user["user_email"]);
        }
      } else {
        if (!isset($message["recipient"]["user_email"]) || $message["recipient"]["user_email"] == "") {
          $returndata["error"] = __("No email to send message to.", "uipress");
          echo json_encode($returndata);
          die();
        }
        $email = $message["recipient"]["user_email"];
      }

      $subject = $message["subject"];
      $content = stripslashes(html_entity_decode($message["message"]));
      $replyTo = $message["replyTo"];

      $headers[] = "From: " . " " . get_bloginfo("name") . "<" . $replyTo . ">";
      $headers[] = "Reply-To: " . " " . $replyTo;
      $headers[] = "Content-Type: text/html; charset=UTF-8";

      $wrap = '<table style="box-sizing:border-box;border-color:inherit;text-indent:0;padding:0;margin:64px auto;width:464px"><tbody>';
      $wrapend = "</tbody></table>";
      $formatted = $wrap . $content . $wrapend;

      add_action("wp_mail_failed", [$this, "log_uip_mail_error"], 10, 1);

      if ($batchemail) {
        foreach ($email as $mail) {
          $headers[] = "Bcc: " . $mail;
        }

        $status = wp_mail($replyTo, $subject, $formatted, $headers);
      } else {
        $status = wp_mail($email, $subject, $formatted, $headers);
      }

      if (!$status) {
        $returndata["error"] = __("Unable to send mail at this time", "uipress");
        echo json_encode($returndata);
        die();
      }

      $returndata["message"] = __("Message sent", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  public function log_uip_mail_error($wp_error)
  {
    error_log(json_encode($wp_error));
  }

  /**
   * Deletes user
   * @since 2.3.5
   */

  public function uip_delete_user()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $userID = $utils->clean_ajax_input($_POST["userID"]);

      if (get_current_user_id() == $userID) {
        $returndata["message"] = __("You can't delete yourself!", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (current_user_can("delete_users")) {
        $status = wp_delete_user($userID);

        if ($status) {
          $returndata["message"] = __("User successfully deleted", "uipress");
          echo json_encode($returndata);
          die();
        } else {
          $returndata["error"] = __("You don't have sufficent priviledges to delete this user", "uipress");
          echo json_encode($returndata);
          die();
        }
      } else {
        $returndata["error"] = __("You don't have sufficent priviledges to delete this user", "uipress");
        echo json_encode($returndata);
        die();
      }
    }
    die();
  }

  /**
   * Deletes user
   * @since 2.3.5
   */

  public function uip_delete_multiple_users()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $allIDS = $utils->clean_ajax_input($_POST["allIDS"]);

      if (!is_array($allIDS)) {
        $returndata["message"] = __("No users sent to delete!", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!current_user_can("delete_users")) {
        $returndata["error"] = __("You don't have sufficent priviledges to delete this user", "uipress");
        echo json_encode($returndata);
        die();
      }

      $errors = [];
      foreach ($allIDS as $userID) {
        if (get_current_user_id() == $userID) {
          $errors[] = [
            "message" => __("You can't delete yourself", "uipress"),
            "user" => sprintf(__("User ID: %s", "uipress"), $userID),
          ];
          continue;
        }

        $status = wp_delete_user($userID);

        if (!$status) {
          $errors[] = [
            "message" => __("Unable to delete this user", "uipress"),
            "user" => sprintf(__("User ID: %s", "uipress"), $userID),
          ];
          continue;
        }
      }

      $returndata["message"] = __("Users successfully deleted", "uipress");
      $returndata["undeleted"] = $errors;
      echo json_encode($returndata);
      die();
    }
    die();
  }
}
