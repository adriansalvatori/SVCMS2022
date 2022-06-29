<?php
if (!defined("ABSPATH")) {
  exit();
}

class uipress_user_groups
{
  public function __construct($version, $pluginName, $pluginPath, $textDomain, $pluginURL)
  {
    $this->version = $version;
    $this->pluginName = $pluginName;
    $this->path = $pluginPath;
    $this->pathURL = $pluginURL;
  }

  /**
   * Loads UiPress settings page
   * @since 2.2
   */

  public function start()
  {
    add_action("admin_init", [$this, "uip_create_user_groups_cpt"]);
    ///ajax
    add_action("wp_ajax_uip_get_user_groups", [$this, "uip_get_user_groups"]);
    add_action("wp_ajax_uip_create_user_group", [$this, "uip_create_user_group"]);
    add_action("wp_ajax_uip_delete_user_group", [$this, "uip_delete_user_group"]);
    add_action("wp_ajax_uip_update_user_group", [$this, "uip_update_user_group"]);
    add_action("wp_ajax_uip_move_user_group", [$this, "uip_move_user_group"]);
    add_action("wp_ajax_uip_move_users_to_group", [$this, "uip_move_users_to_group"]);
    add_action("wp_ajax_uip_remove_from_group", [$this, "uip_remove_from_group"]);
  }

  /**
   * Creates custom folder post type
   * @since 1.4
   */
  public function uip_create_user_groups_cpt()
  {
    $labels = [
      "name" => _x("Group", "post type general name", "uipress"),
      "singular_name" => _x("Group", "post type singular name", "uipress"),
      "menu_name" => _x("Groups", "admin menu", "uipress"),
      "name_admin_bar" => _x("Group", "add new on admin bar", "uipress"),
      "add_new" => _x("Add New", "Group", "uipress"),
      "add_new_item" => __("Add New Group", "uipress"),
      "new_item" => __("New Group", "uipress"),
      "edit_item" => __("Edit Group", "uipress"),
      "view_item" => __("View Group", "uipress"),
      "all_items" => __("All Groups", "uipress"),
      "search_items" => __("Search Groups", "uipress"),
      "not_found" => __("No Groups found.", "uipress"),
      "not_found_in_trash" => __("No Groups found in Trash.", "uipress"),
    ];
    $args = [
      "labels" => $labels,
      "description" => __("Add New Group", "uipress"),
      "public" => false,
      "publicly_queryable" => false,
      "show_ui" => false,
      "show_in_menu" => false,
      "query_var" => false,
      "has_archive" => false,
      "hierarchical" => false,
    ];
    register_post_type("uip_user_group", $args);
  }

  /**
   * Deletes folder from media panel
   * @since 2.2
   */
  public function uip_delete_user_group()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $folderID = $utils->clean_ajax_input($_POST["activeFolder"]);

      if (!is_numeric($folderID) && !$folderID > 0) {
        $returndata["error"] = __("No group to delete", "uipress");
        echo json_encode($returndata);
        die();
      }

      $currentParent = get_post_meta($folderID, "parent_folder", true);

      $status = wp_delete_post($folderID);

      if (!$status) {
        $returndata["error"] = __("Unable to delete the group", "uipress");
        echo json_encode($returndata);
        die();
      }

      $args = [
        "numberposts" => -1,
        "post_type" => "uip_user_group",
        "orderby" => "title",
        "order" => "ASC",
        "meta_query" => [
          [
            "key" => "parent_folder",
            "value" => $folderID,
            "compare" => "=",
          ],
        ],
      ];

      $folders = get_posts($args);

      foreach ($folders as $folder) {
        if ($currentParent) {
          update_post_meta($folder->ID, "parent_folder", $currentParent);
        } else {
          delete_post_meta($folder->ID, "parent_folder");
        }
      }
      ///QUERY USERS
      $args = [
        "number" => -1,
        "fields" => "ids",
        "meta_query" => [
          [
            "key" => "uip_user_group",
            "value" => '"' . $folderID . '"',
            "compare" => "LIKE",
          ],
        ],
      ];

      $user_query = new WP_User_Query($args);
      $all_users = $user_query->get_results();

      foreach ($all_users as $item) {
        $current = get_user_meta($item, "uip_user_group", true);
        if (!is_array($current)) {
          $current = [];
        }

        if (in_array($folderID, $current)) {
          $pos = array_search($folderID, $current);
          unset($current[$pos]);
          update_user_meta($item, "uip_user_group", $current);
        }
      }

      $returndata["message"] = __("Group deleted", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Moves folder from media panel
   * @since 2.2
   */
  public function uip_move_user_group()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $folderToMove = $utils->clean_ajax_input($_POST["folderiD"]);
      $destination = $utils->clean_ajax_input($_POST["destinationId"]);

      if ($folderToMove == $destination) {
        $returndata["error"] = __("Unable to move folder group itself", "admin2020");
        echo json_encode($returndata);
        die();
      }

      $currentParent = get_post_meta($folderToMove, "parent_folder", true);

      if ($destination == "toplevel") {
        $status = delete_post_meta($folderToMove, "parent_folder");
      } else {
        $status = update_post_meta($folderToMove, "parent_folder", $destination);
      }

      if ($status != true) {
        $returndata["error"] = __("Unable to move group", "admin2020");
        echo json_encode($returndata);
        die();
      }

      ///CHECK IF WE NEED TO MAKE SUB FOLDERS TOP LEVEL
      if (!$currentParent || $currentParent == "") {
        $args = [
          "numberposts" => -1,
          "post_type" => "uip_user_group",
          "orderby" => "title",
          "order" => "ASC",
          "meta_query" => [
            [
              "key" => "parent_folder",
              "value" => $folderToMove,
              "compare" => "=",
            ],
          ],
        ];

        $folders = get_posts($args);

        foreach ($folders as $folder) {
          delete_post_meta($folder->ID, "parent_folder");
        }
      }

      $returndata["message"] = __("Group moved", "admin2020");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Removes content and folders from folders
   * @since 2.2
   */
  public function uip_remove_from_group()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $contentIds = $utils->clean_ajax_input($_POST["items"]);
      $type = $utils->clean_ajax_input($_POST["itemtype"]);

      if (!is_array($contentIds)) {
        $returndata["error"] = __("No item to move", "uipress");
        echo json_encode($returndata);
        die();
      }

      foreach ($contentIds as $contentId) {
        if ($type == "content") {
          $status = delete_post_meta($contentId, "uip_user_group");
        }
        if ($type == "folder") {
          $status = delete_post_meta($contentId, "parent_folder");
        }
      }

      $returndata["message"] = __("Item moved", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }
  /**
   * Moves content to folder from media panel
   * @since 2.2
   */
  public function uip_move_users_to_group()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $contentIds = $utils->clean_ajax_input($_POST["contentID"]);
      $destination = $utils->clean_ajax_input($_POST["destinationId"]);

      if (!is_array($contentIds)) {
        $returndata["error"] = __("No users to move", "uipress");
        echo json_encode($returndata);
        die();
      }

      foreach ($contentIds as $contentId) {
        $current = get_user_meta($contentId, "uip_user_group", true);
        if (!is_array($current)) {
          $current = [];
        }

        ///SETTING FOLDER
        if (!in_array($destination, $current)) {
          array_push($current, $destination);
          $status = update_user_meta($contentId, "uip_user_group", $current);
        }
      }

      $returndata["message"] = __("User moved", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }
  /**
   * Updates folder from media panel
   * @since 2.2
   */
  public function uip_update_user_group()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $folder = $utils->clean_ajax_input($_POST["folderInfo"]);

      $foldername = $folder["title"];
      $folderid = $folder["id"];
      $foldertag = $folder["color"];
      $icon = $folder["icon"];

      $my_post = [
        "post_title" => $foldername,
        "post_status" => "publish",
        "ID" => $folderid,
      ];

      // Insert the post into the database.
      $thefolder = wp_update_post($my_post);

      if (!$thefolder) {
        $returndata = [];
        $returndata["error"] = __("Something went wrong", "uipress");
        echo json_encode($returndata);
        die();
      }

      update_post_meta($folderid, "color_tag", $foldertag);
      update_post_meta($folderid, "group_icon", $icon);

      $returndata = [];
      $returndata["message"] = __("Group updated", "uipress");
      echo json_encode($returndata);
    }
    die();
  }

  /**
   * Creates folder from media panel
   * @since 2.2
   */
  public function uip_create_user_group()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();
      $folderInfo = $utils->clean_ajax_input($_POST["folderInfo"]);
      $parent = $utils->clean_ajax_input($_POST["parent"]);

      $name = $folderInfo["name"];
      $color = $folderInfo["color"];

      if (!$name) {
        $returndata["error"] = __("Title is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      if (!$color) {
        $returndata["error"] = __("Colour is required", "uipress");
        echo json_encode($returndata);
        die();
      }

      $my_post = [
        "post_title" => $name,
        "post_status" => "publish",
        "post_type" => "uip_user_group",
      ];

      // Insert the post into the database.
      $thefolder = wp_insert_post($my_post);

      if (!$thefolder) {
        $returndata["error"] = __("Unable to create group", "uipress");
        echo json_encode($returndata);
        die();
      }

      update_post_meta($thefolder, "color_tag", $color);
      update_post_meta($thefolder, "group_icon", $folderInfo["icon"]);

      if (is_numeric($parent) && $parent > 0) {
        update_post_meta($thefolder, "parent_folder", $parent);
      }

      $returndata["message"] = __("Group created", "uipress");
      echo json_encode($returndata);
      die();
    }
    die();
  }

  /**
   * Build content for front end folders
   * @since 2.2
   */

  public function uip_get_user_groups()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uip-user-app-security-nonce", "security") > 0) {
      $utils = new uipress_util();

      $args = [
        "numberposts" => -1,
        "post_type" => "uip_user_group",
        "orderby" => "title",
        "order" => "ASC",
      ];

      $folders = get_posts($args);
      $structure = [];
      $folderIDS = [];

      $metaQuery = ["relation" => "OR"];
      foreach ($folders as $folder) {
        $folderIDS[] = strval($folder->ID);

        $metaQuery[] = [
          "key" => "uip_user_group",
          "value" => '"' . $folder->ID . '"',
          "compare" => "LIKE",
        ];
      }

      ///QUERY USERS
      $args = [
        "number" => -1,
        "fields" => "ids",
        "meta_query" => $metaQuery,
      ];

      $user_query = new WP_User_Query($args);
      $all_users = $user_query->get_results();
      $total_users = $user_query->get_total();

      $contentCount = [];

      foreach ($all_users as $item) {
        $groupID = get_user_meta($item, "uip_user_group", true);
        if (is_array($groupID)) {
          foreach ($groupID as $group) {
            if (isset($contentCount[$group])) {
              $contentCount[$group] += 1;
            } else {
              $contentCount[$group] = 1;
            }
          }
        } else {
          if (isset($contentCount[$groupID])) {
            $contentCount[$groupID] += 1;
          } else {
            $contentCount[$groupID] = 1;
          }
        }
      }

      foreach ($folders as $folder) {
        $parent_folder = get_post_meta($folder->ID, "parent_folder", true);

        if (!$parent_folder) {
          $structure[] = $this->build_folder_structure($folder, $folders, $contentCount);
        }
      }

      ///QUERY CONTENT
      $args = [
        "number" => -1,
        "fields" => "ids",
        "meta_query" => [
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
            "value" => "a:0:{}",
            "compare" => "=",
          ],
        ],
      ];

      $user_query = new WP_User_Query($args);
      $nofolder = $user_query->get_total();

      $returnata["folders"] = $structure;
      $returnata["mediaCount"] = get_option("user_count");
      $returnata["noFolderCount"] = $nofolder;
      echo json_encode($returnata);
    }
    die();
  }

  /**
   * Build data structure for folders array
   * @since 2.2
   */
  public function build_folder_structure($folder, $folders, $contentcount)
  {
    $temp = [];
    $foldercolor = get_post_meta($folder->ID, "color_tag", true);
    $top_level = get_post_meta($folder->ID, "parent_folder", true);
    $icon = get_post_meta($folder->ID, "group_icon", true);
    $title = $folder->post_title;

    $temp["title"] = $title;
    $temp["color"] = $foldercolor;
    $temp["id"] = $folder->ID;
    $temp["icon"] = $icon;
    $temp["count"] = 0;

    if (isset($contentcount[$folder->ID])) {
      $temp["count"] = $contentcount[$folder->ID];
    }

    foreach ($folders as $aFolder) {
      $folderParent = get_post_meta($aFolder->ID, "parent_folder", true);

      if ($folderParent == $folder->ID) {
        $temp["subs"][] = $this->build_folder_structure($aFolder, $folders, $contentcount);
      }
    }

    return $temp;
  }
}
