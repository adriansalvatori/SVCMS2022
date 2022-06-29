<?php
if (!defined("ABSPATH")) {
  exit();
}

class uipress_overview
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
   * Loads menu actions
   * @since 1.0
   */

  public function run()
  {
    ///REGISTER THIS COMPONENT
    add_filter("uipress_register_settings", [$this, "overview_settings_options"], 1, 2);
    ///ADD ACTIOS FOR OVERVIEW PAGE
    add_action("plugins_loaded", [$this, "add_overview_functions"]);

    //AJAX
    add_action("wp_ajax_uipress_get_posts", [$this, "uipress_get_posts"]);
    add_action("wp_ajax_uipress_get_comments", [$this, "uipress_get_comments"]);
    add_action("wp_ajax_uipress_get_system_health", [$this, "uipress_get_system_health"]);
    add_action("wp_ajax_uipress_save_dash", [$this, "uipress_save_dash"]);
    add_action("wp_ajax_uipress_get_shortcode", [$this, "uipress_get_shortcode"]);
    add_action("wp_ajax_uipress_reset_overview", [$this, "uipress_reset_overview"]);
    add_action("wp_ajax_uipress_import_default_layout", [$this, "uipress_import_default_layout"]);

    add_action("wp_ajax_uip_build_global_data_object", [$this, "uip_build_global_data_object"]);
    ///ADD DATA FILTERS
    add_filter("uip_filter_data_object", [$this, "uipress_get_system_info"], 1, 2);
    add_filter("uip_filter_data_object", [$this, "uipress_get_system_health"], 1, 2);
    add_filter("uip_filter_data_object", [$this, "uip_get_inspirational_quote"], 1, 2);

    ///
    add_filter("uipress_register_card", [$this, "register_default_cards"], 1, 1);
  }

  /**
   * Adds actions for overview page
   * @since 1.4
   */

  public function add_overview_functions()
  {
    if (!is_admin()) {
      return;
    }

    $utils = new uipress_util();
    $overviewOn = $utils->get_option("overview", "status");
    $overviewDisabledForUser = $utils->valid_for_user($utils->get_option("overview", "disabled-for", true));

    if ($overviewOn == "true" || $overviewDisabledForUser) {
      return;
    }

    add_action("admin_menu", [$this, "add_menu_item"]);
    add_action("network_admin_menu", [$this, "add_menu_item"]);

    if (isset($_GET["page"])) {
      if ($_GET["page"] == "uip-overview") {
        add_action("admin_enqueue_scripts", [$this, "add_scripts"], 0);
        add_action("admin_head", [$this, "add_components"], 0);
        add_action("wp_print_scripts", [$this, "uip_dequeue_script"], 100);
      }
    }
  }
  /**
   * Dequeue scripts that cause compatibility issues
   * @since 1.4
   */
  public function uip_dequeue_script()
  {
    wp_dequeue_script("wp-ultimo");
    wp_dequeue_script("wu-admin");
    wp_dequeue_script("wu-vue");
    wp_dequeue_script("vuejs");
    wp_deregister_script("wu-vue");
  }

  /**
   * Returns settings options for settings page
   * @since 2.2
   */
  public function overview_settings_options($settings, $network)
  {
    $utils = new uipress_util();
    $allOptions = $utils->get_options_object();

    ///////FOLDER OPTIONS
    $moduleName = "overview";
    $category = [];
    $options = [];
    //
    $category["module_name"] = $moduleName;
    $category["label"] = __("Overview", "uipress");
    $category["description"] = __("Creates the overview page.", "uipress");
    $category["icon"] = "analytics";

    $temp = [];
    $temp["name"] = __("Disable Overview Page?", "uipress");
    $temp["description"] = __("Overview page will be hidden when this option is activated.", "uipress");
    $temp["type"] = "switch";
    $temp["optionName"] = "status";
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"]);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Overview Page Disabled For", "uipress");
    $temp["description"] = __("Overview Page will be disabled for any users or roles you select", "uipress");
    $temp["type"] = "user-role-select";
    $temp["optionName"] = "disabled-for";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], true);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Who can edit the overview page?", "uipress");
    $temp["description"] = __("Any role or user chosen here will be able to edit the overview page. If none are chosen it will fall back to administrators only", "uipress");
    $temp["type"] = "user-role-select";
    $temp["optionName"] = "disable-edit-for";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], true);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Default report start date", "uipress");
    $temp["description"] = __("By default the overview date range will start 7 days before current date. Add a number of days between 1 and 90 to change length of date range", "uipress");
    $temp["type"] = "text";
    $temp["optionName"] = "start-day";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], false);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Default report end date offset", "uipress");
    $temp["description"] = __("By default the overview date range will end on the current day (0). To end the report on yesterday for example, enter 1", "uipress");
    $temp["type"] = "text";
    $temp["optionName"] = "end-day";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], false);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Reporting Currency Symbol", "uipress");
    $temp["description"] = __("If you are using google analytics commerce reports, enter the currency symbol your reports are in here", "uipress");
    $temp["type"] = "text";
    $temp["optionName"] = "reporting-currency";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], false);
    $options[$temp["optionName"]] = $temp;

    $temp = [];
    $temp["name"] = __("Custom welcome message", "uipress");
    $temp["description"] = __("Add a custom welcome message here to displayed on the overview page", "uipress");
    $temp["type"] = "code-block";
    $temp["language"] = "HTML";
    $temp["optionName"] = "custom-welcome";
    $temp["premium"] = true;
    $temp["value"] = $utils->get_option_value_from_object($allOptions, $moduleName, $temp["optionName"], false);
    $options[$temp["optionName"]] = $temp;

    $category["options"] = $options;
    $settings[$moduleName] = $category;

    return $settings;
  }

  /**
   * Returns recent posts
   * @since 2.3.0.3
   */
  public function uipress_import_default_layout()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uipress-overview-security-nonce", "security") > 0) {
      echo $this->return_default_layout();
    }
    die();
  }

  /**
   * Returns recent posts
   * @since 2.3.0.3
   */
  public function uip_build_global_data_object()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uipress-overview-security-nonce", "security") > 0) {
      $dateObject = $this->utils->clean_ajax_input($_POST["dateRange"]);

      $data = [];

      $gloablObject = apply_filters("uip_filter_data_object", $data, $dateObject);

      echo json_encode($gloablObject);
    }
    die();
  }

  /**
   * Returns recent posts
   * @since 2.2
   */
  public function uipress_get_posts()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uipress-overview-security-nonce", "security") > 0) {
      $dates = $this->utils->clean_ajax_input($_POST["dates"]);
      $page = $this->utils->clean_ajax_input($_POST["currentPage"]);

      $startDate = date("Y-m-d", strtotime($dates["startDate"]));
      $endDate = date("Y-m-d", strtotime($dates["endDate"]));

      $args = [
        "post_type" => "any",
        "post_status" => "publish",
        "posts_per_page" => 5,
        "paged" => $page,
        "date_query" => [
          [
            "after" => $startDate,
            "before" => $endDate,
            "inclusive" => true,
          ],
        ],
      ];

      wp_reset_query();
      $theposts = new WP_Query($args);
      $foundPosts = $theposts->get_posts();

      $formatted = [];

      foreach ($foundPosts as $apost) {
        $postdate = human_time_diff(get_the_date("U", $apost), current_time("timestamp")) . " " . __("ago", "uipress");
        $author_id = $apost->post_author;
        $author_meta = get_the_author_meta("user_nicename", $author_id);

        $temp = [];
        $temp["title"] = html_entity_decode(get_the_title($apost));
        $temp["href"] = get_the_permalink($apost);
        $temp["author"] = html_entity_decode($author_meta);
        $temp["date"] = $postdate;
        $temp["type"] = get_post_type($apost);

        $formatted[] = $temp;
      }

      $returndata = [];

      $returndata["message"] = __("Posts fetched", "uipress");
      $returndata["posts"] = $formatted;
      $returndata["totalFound"] = $theposts->found_posts;
      $returndata["maxPages"] = $theposts->max_num_pages;
      $returndata["testdate"] = $startDate;

      $returndata["nocontent"] = "false";
      if ($theposts->found_posts < 1) {
        $returndata["nocontent"] = __("No posts posted during the date range.", "uipress");
      }

      echo json_encode($returndata);
    }
    die();
  }

  public function uipress_save_dash()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uipress-overview-security-nonce", "security") > 0) {
      $cards = $this->utils->clean_ajax_input_html($_POST["cards"]);
      $network = $this->utils->clean_ajax_input_html($_POST["network"]);

      if (!$cards && !is_array($cards)) {
        $message = __("Unable to save dash at this time", "uipress");
        $returndata["error"] = $message;
        echo json_encode($returndata);
        die();
      }

      $settings["cards"] = $cards;
      update_option("uip-overview", $settings);

      $returndata = [];
      $returndata["message"] = __("Dashboard settings saved", "uipress");
      echo json_encode($returndata);
    }

    die();
  }

  public function uipress_reset_overview()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uipress-overview-security-nonce", "security") > 0) {
      $settings = get_option("uip-overview");
      $settings["cards"] = false;
      update_option("uip-overview", $settings);

      $returndata = [];
      $returndata["message"] = __("Dashboard settings reset", "uipress");
      echo json_encode($returndata);
    }

    die();
  }

  public function uipress_get_shortcode()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uipress-overview-security-nonce", "security") > 0) {
      $shortcode = $this->utils->clean_ajax_input($_POST["shortCode"]);

      if (!$shortcode) {
        $message = __("Unable to load shortcode at this time", "uipress");
        echo $this->utils->ajax_error_message($message);
        die();
      }

      $data = do_shortcode(stripslashes($shortcode));

      if (!$data) {
        $message = __("Unable to load shortcode at this time", "uipress");
        echo $this->utils->ajax_error_message($message);
        die();
      }

      $returndata = [];
      $returndata["shortCode"] = $data;
      $returndata["message"] = __("Shortcode loaded", "uipress");
      $returndata["test"] = stripslashes($shortcode);
      echo json_encode($returndata);
    }

    die();
  }

  public function uipress_get_comments()
  {
    if (defined("DOING_AJAX") && DOING_AJAX && check_ajax_referer("uipress-overview-security-nonce", "security") > 0) {
      $dates = $this->utils->clean_ajax_input($_POST["dates"]);
      $page = $this->utils->clean_ajax_input($_POST["currentPage"]);

      $startDate = date("Y-m-d", strtotime($dates["startDate"]));
      $endDate = date("Y-m-d", strtotime($dates["endDate"]));

      $args = [
        "type" => "comment",
        "status" => "approve",
        "number" => 1000,
        "date_query" => [
          [
            "after" => $startDate,
            "before" => $endDate,
            "inclusive" => true,
          ],
        ],
      ];

      $maxperpage = 5;
      $currentStart = $page * $maxperpage - $maxperpage;
      $currentEnd = $currentStart + $maxperpage + 1;

      $comments_query = new WP_Comment_Query();
      $comments = $comments_query->query($args);

      $formatted = [];
      $count = 0;

      foreach (array_slice($comments, $currentStart) as $acomment) {
        if ($count == 5) {
          break;
        }

        $comment_date = get_comment_date("Y-m-y", $acomment->comment_ID);
        $string = "";

        if ($comment_date != date("Y-m-d")) {
          $string = __("ago", "uipress");
        }

        $commentdate = human_time_diff(get_comment_date("U", $acomment->comment_ID), current_time("timestamp")) . " " . $string;
        $author = $acomment->comment_author;
        $user = get_user_by("login", $author);
        $thepostid = $acomment->comment_post_ID;
        $commentlink = get_comment_link($acomment);
        $img = false;

        $arg = [
          "default" => "noimage",
          "size" => "200",
        ];

        $img = get_avatar_url(get_current_user_id(), $arg);

        if (strpos($img, "noimage") !== false) {
          $img = false;
        }

        if (!$img) {
          if (strpos($author, " ") !== false) {
            $parts = str_split($author, 1);
            $parts = explode(" ", $author);
            $first = str_split($parts[0]);
            $first = $first[0];

            $name_string = $first;
          } else {
            $parts = str_split($author, 1);
            $name_string = $parts[0];
          }
        }

        $fullcontent = get_comment_text($acomment->comment_ID);

        if (strlen($fullcontent) > 40) {
          $shortcontent = substr(get_comment_text($acomment->comment_ID), 0, 40) . "...";
        } else {
          $shortcontent = $fullcontent;
        }

        $temp = [];
        $temp["title"] = html_entity_decode(get_the_title($thepostid));
        $temp["href"] = $commentlink;
        $temp["author"] = $author;
        $temp["date"] = $commentdate;
        $temp["text"] = html_entity_decode(esc_html($shortcontent));

        if ($img) {
          $temp["img"] = $img;
        } else {
          $temp["initials"] = $name_string;
        }

        $formatted[] = $temp;
        $count += 1;
      }

      $returndata = [];
      $totalcomments = count($comments);

      $returndata["message"] = __("Posts fetched", "uipress");
      $returndata["posts"] = $formatted;
      $returndata["totalFound"] = $totalcomments;
      $returndata["maxPages"] = ceil($totalcomments / $maxperpage);

      $returndata["nocontent"] = "false";
      if ($totalcomments < 1) {
        $returndata["nocontent"] = __("No comments during the date range.", "uipress");
      }

      echo json_encode($returndata);
    }
    die();
  }

  public function uipress_get_system_info($dataObject, $dateObject)
  {
    if (!is_array($dataObject)) {
      $dataObject = [];
    }

    $wp_v = get_bloginfo("version");
    $phph_v = phpversion();
    $plugins = get_plugins();
    $activePlugins = get_option("active_plugins");
    $inactive = count($plugins) - count($activePlugins);

    $holder = [];

    $temp = [];
    $temp["name"] = __("Core version", "uipress");
    $temp["version"] = get_bloginfo("version");
    $holder[] = $temp;

    $temp = [];
    $temp["name"] = __("PHP version", "uipress");
    $temp["version"] = $phph_v;
    $holder[] = $temp;

    $temp = [];
    $temp["name"] = __("Active Plugins", "uipress");
    $temp["version"] = count($activePlugins);
    $holder[] = $temp;

    $temp = [];
    $temp["name"] = __("Inactive Plugins", "uipress");
    $temp["version"] = $inactive;
    $holder[] = $temp;

    $temp = [];
    $temp["name"] = __("Installed Themes", "uipress");
    $temp["version"] = count(wp_get_themes());
    $holder[] = $temp;

    $returndata["posts"] = $holder;

    $dataObject["system_info"] = $returndata;
    return $dataObject;
  }

  public function uip_get_inspirational_quote($dataObject, $dateObject)
  {
    if (!is_array($dataObject)) {
      $dataObject = [];
    }
    ///CHECKED IF WE HAVE CACHED QUOTE
    $cachedQuote = get_transient("uip-inspirational-quote");
    if ($cachedQuote && is_array($cachedQuote)) {
      $dataObject["inspirationalQuote"] = $cachedQuote;
      return $dataObject;
    }

    $remote = wp_remote_get("https://zenquotes.io/api/today", [
      "timeout" => 10,
      "headers" => [
        "Accept" => "application/json",
      ],
    ]);

    if (!is_wp_error($remote) && isset($remote["response"]["code"]) && $remote["response"]["code"] == 200 && !empty($remote["body"])) {
      $remote = json_decode($remote["body"]);
      $returndata = $remote;
      set_transient("uip-inspirational-quote", $remote, 3 * HOUR_IN_SECONDS);
    } else {
      $returndata["error"] = true;
      $returndata["message"] = __("Unable to fetch quote of the day", "uipress");
    }

    $dataObject["inspirationalQuote"] = $returndata;

    return $dataObject;
  }

  public function uipress_get_system_health($dataObject, $dateObject)
  {
    if (!is_array($dataObject)) {
      $dataObject = [];
    }
    $sitehealth = get_transient("health-check-site-status-result");

    $issue_counts = [];

    if (false !== $sitehealth) {
      $issue_counts = json_decode($sitehealth, true);
    }

    if (!is_array($issue_counts) || !$issue_counts) {
      $issue_counts = [
        "good" => 0,
        "recommended" => 0,
        "critical" => 0,
      ];
    }

    $issues_total = $issue_counts["recommended"] + $issue_counts["critical"];
    $returndata = [];

    $chartData = [];
    $chartLabels = [];

    $colors = ["rgb(12, 92, 239)", "rgba(250, 160, 90, 1)", "rgba(240, 80, 110,1)"];

    $temp = [];
    $temp["name"] = __("Passed Checks", "uipress");
    $temp["value"] = $issue_counts["good"];
    $temp["color"] = $colors[0];
    array_push($chartData, $issue_counts["good"]);
    array_push($chartLabels, $temp["name"]);
    $returndata["issues"][] = $temp;

    $temp = [];
    $temp["name"] = __("Recommended", "uipress");
    $temp["value"] = $issue_counts["recommended"];
    $temp["color"] = $colors[1];
    array_push($chartData, $issue_counts["recommended"]);
    array_push($chartLabels, $temp["name"]);
    $returndata["issues"][] = $temp;

    $temp = [];
    $temp["name"] = __("Critical", "uipress");
    $temp["value"] = $issue_counts["critical"];
    $temp["color"] = $colors[2];
    array_push($chartData, $issue_counts["critical"]);
    array_push($chartLabels, $temp["name"]);
    $returndata["issues"][] = $temp;

    $returndata["colours"]["bgColors"] = ["#0c5cef", "rgba(250, 160, 90, 0.5)", "rgba(240, 80, 110, 0.5)"];
    $returndata["colours"]["borderColors"] = ["rgba(12, 92, 239, 1)"];

    if ($issue_counts["critical"] + $issue_counts["recommended"] > 0) {
      $returndata["message"] = sprintf(__("Take a look at the %d items on the", "uipress"), $issue_counts["critical"] + $issue_counts["recommended"]);
      $returndata["linkMessage"] = __("Site Health screen", "uipress");
      $returndata["healthUrl"] = esc_url(admin_url("site-health.php"));
    }

    $returndata["dataSet"] = [
      "labels" => $chartLabels,
      "datasets" => [
        [
          "label" => __("Device Visits", "uipress"),
          "fill" => true,
          "data" => $chartData,
          "backgroundColor" => $colors,
          "borderWidth" => 0,
        ],
      ],
    ];

    $returndata["unformatted"] = $chartData;
    $returndata["labels"] = $chartLabels;

    $output = [];

    $dataObject["system_health"] = $returndata;
    return $dataObject;
  }

  public function add_components()
  {
    $modules = $this->get_modules(); ?>
    
    <script>
    const uipOverviewMods = [];
    </script>
    <script type="module" id="uip-modules-loader">
      
      <?php foreach ($modules as $key => $value) {
        $importname = str_replace("-", "_", $value["moduleName"]); ?>  
      import * as <?php echo $importname; ?> from '<?php echo $value["componentPath"] . "?v=" . $this->version; ?>';
      uipOverviewMods['<?php echo $key; ?>'] = <?php echo $importname; ?>;
      <?php
      } ?>
      uip_build_overview();
    </script> 
    <?php
  }

  /**
   * Enqueue Admin Bar 2020 scripts
   * @since 1.4
   */

  public function add_scripts()
  {
    wp_register_style("uip-daterangepicker", $this->pathURL . "admin/apps/overview/css/daterangepicker.css", [], $this->version);
    wp_enqueue_style("uip-daterangepicker");

    wp_register_style("uip-codejar", $this->pathURL . "admin/apps/overview/css/highlight.css", [], $this->version);
    wp_enqueue_style("uip-codejar");

    $modules = $this->get_modules();
    $settings = $this->build_overview_data();
    $translations = $this->build_translations();

    //CODEFLASK
    wp_enqueue_script("uip-codejar-js", $this->pathURL . "admin/apps/overview/js/codejar-alt.js", ["jquery"], $this->version);
    wp_enqueue_script("uip-highlight-js", $this->pathURL . "admin/apps/overview/js/highlight.js", ["jquery"], $this->version);

    //VUE
    wp_enqueue_script("vue-menu-creator-js", $this->pathURL . "admin/apps/overview/js/vue-menu-creator.js", ["jquery"], $this->version, false);
    wp_enqueue_script("sortable-js", $this->pathURL . "admin/apps/overview/js/sortable.js", ["jquery"], $this->version, false);
    wp_enqueue_script("vue-sortable-js", $this->pathURL . "admin/apps/overview/js/vuedraggable.umd.js", ["jquery"], $this->version, false);

    ///CHART JS
    wp_enqueue_script("uip-charts", $this->pathURL . "admin/apps/overview/js/charts-3.js", ["jquery"], $this->version, false);
    wp_enqueue_script("uipress-chart-geo", $this->pathURL . "admin/apps/overview/js/chartjs-geo.min.js", ["uip-charts"], $this->version, false);
    //MOMENT
    wp_enqueue_script("uip-moment", $this->pathURL . "admin/apps/overview/js/moment.min.js", ["jquery"], $this->version);
    //LITE PICKER
    wp_enqueue_script("uipress-date-picker", $this->pathURL . "admin/apps/overview/js/litepicker.js", ["jquery"], $this->version);
    wp_enqueue_script("uipress-date-ranges", $this->pathURL . "admin/apps/overview/js/litepicker-ranges.js", ["jquery"], $this->version);

    ///OVERVIEW SCRIPTS
    wp_enqueue_script("admin-overview-app", $this->pathURL . "admin/apps/overview/js/admin-overview-app.min.js", ["jquery"], $this->version, true);
    wp_localize_script("admin-overview-app", "uipress_overview_ajax", [
      "ajax_url" => admin_url("admin-ajax.php"),
      "security" => wp_create_nonce("uipress-overview-security-nonce"),
      "options" => json_encode($settings),
      "modules" => json_encode($modules),
      "translations" => json_encode($translations),
    ]);
  }

  public function build_translations()
  {
    $translations = [];
    $translations["cardWidth"] = __("Card width", "uipress");
    $translations["columnWidth"] = __("Column width", "uipress");
    $translations["columnSettings"] = __("Column settings", "uipress");
    $translations["columnSize"] = __("Column Size", "uipress");
    $translations["remove"] = __("Remove Card", "uipress");
    $translations["deleteCol"] = __("Delete Column", "uipress");
    $translations["inTheLast"] = __("In the", "uipress");
    $translations["days"] = __("day range", "uipress");
    $translations["xxsmall"] = __("xxsmall (1/6)", "uipress");
    $translations["xsmall"] = __("xsmall (1/5)", "uipress");
    $translations["small"] = __("small (1/4)", "uipress");
    $translations["smallmedium"] = __("small medium (1/3)", "uipress");
    $translations["medium"] = __("medium (1/2)", "uipress");
    $translations["mediumlarge"] = __("medium large (2/3)", "uipress");
    $translations["large"] = __("large (3/4)", "uipress");
    $translations["xlarge"] = __("xlarge (1/1)", "uipress");
    $translations["emptycolumn"] = __("I am an empty column. Drag cards into me.", "uipress");
    $translations["colAdded"] = __("Column Added", "uipress");
    $translations["addCard"] = __("Add card", "uipress");
    $translations["sectionAdded"] = __("Section added", "uipress");
    $translations["searchCards"] = __("Search Cards", "uipress");
    $translations["premium"] = __("Pro", "uipress");
    $translations["title"] = __("Title", "uipress");
    $translations["shortcode"] = __("Shortcode", "uipress");
    $translations["videourl"] = __("Video URL", "uipress");
    $translations["embedType"] = __("Embed Type", "uipress");
    $translations["premiumFeature"] = __("UiPress Pro feature", "uipress");
    $translations["upgradMsg"] = __("Upgrade to one of our premium plans to unlock this feature.", "uipress");
    $translations["html"] = __("HTML", "uipress");
    $translations["cardAdded"] = __("Card Added", "uipress");
    $translations["bgcolor"] = __("Card Background colour", "uipress");
    $translations["colorPlace"] = __("# Hex code only (#fff)", "uipress");
    $translations["lightText"] = __("Use light color for text", "uipress");
    $translations["chartType"] = __("Chart Type", "uipress");
    $translations["lineChart"] = __("Line Chart", "uipress");
    $translations["barChart"] = __("Bar Chart", "uipress");
    $translations["vsPrevious"] = __("vs previous", "uipress");
    $translations["vsdays"] = __("days", "uipress");
    $translations["doughnut"] = __("Doughnut", "uipress");
    $translations["polarArea"] = __("Polar Area", "uipress");
    $translations["bar"] = __("Bar", "uipress");
    $translations["horizbar"] = __("Horizontal Bar", "uipress");
    $translations["country"] = __("Country", "uipress");
    $translations["visits"] = __("Visits", "uipress");
    $translations["change"] = __("Change", "uipress");
    $translations["removeBackground"] = __("No Background", "uipress");
    $translations["showmap"] = __("Hide Map", "uipress");
    $translations["noaccount"] = __("Connect your account to use this card", "uipress");
    $translations["hbar"] = __("Horizontal Bar", "uipress");
    $translations["hidechart"] = __("Hide Chart", "uipress");
    $translations["source"] = __("Source", "uipress");
    $translations["page"] = __("Page", "uipress");
    $translations["product"] = __("Product", "uipress");
    $translations["sold"] = __("Sold", "uipress");
    $translations["value"] = __("Value", "uipress");
    $translations["woocommerce"] = __("WooCommerce is required to use this card", "uipress");
    $translations["validJSON"] = __("Please select a valid JSON file", "uipress");
    $translations["fileBig"] = __("File is to big", "uipress");
    $translations["layoutImported"] = __("Layout Imported", "uipress");
    $translations["layoutExportedProblem"] = __("Unable to import layout", "uipress");
    $translations["confirmReset"] = __("Are you sure you want to clear the current layout? There is no undo.", "uipress");
    $translations["availableCards"] = __("Available Cards", "uipress");
    $translations["eventCount"] = __("Event Count", "uipress");
    $translations["eventName"] = __("Event Name", "uipress");
    $translations["channel"] = __("Channel", "uipress");
    $translations["sessions"] = __("Sessions", "uipress");
    $translations["pageViews"] = __("Page Views", "uipress");
    $translations["pageViewsComparison"] = __("Page Views  (comparison period)", "uipress");
    $translations["siteUsers"] = __("Site Users", "uipress");
    $translations["siteUsersComparison"] = __("Site Users (comparison period)", "uipress");
    $translations["to"] = __("To", "uipress");
    $translations["from"] = __("From", "uipress");
    $translations["dateRange"] = __("Date Range", "uipress");
    $translations["customComparisonDates"] = __("Custom Comparison Dates", "uipress");
    $translations["apply"] = __("Apply", "uipress");
    $translations["comparedTo"] = __("vs", "uipress");
    $translations["customRange"] = __("Custom Range", "uipress");
    $translations["today"] = __("Today", "uipress");
    $translations["yesterday"] = __("Yesterday", "uipress");
    $translations["lastSevenDays"] = __("Last 7 days", "uipress");
    $translations["lastThirtyDays"] = __("Last 30 days", "uipress");
    $translations["lastSixtyDays"] = __("Last 60 days", "uipress");
    $translations["lastNinetyDays"] = __("Last 90 days", "uipress");
    $translations["thisMonth"] = __("This Month", "uipress");
    $translations["lastMonth"] = __("Last Month", "uipress");
    $translations["purchases"] = __("Purchases", "uipress");
    $translations["addToCart"] = __("Added to cart", "uipress");
    $translations["checkoutStarted"] = __("Checkout started", "uipress");
    $translations["conversionRate"] = __("Conversion Rate", "uipress");
    $translations["conversionRate"] = __("Conversion Rate", "uipress");
    $translations["siteRevenue"] = __("Site Revenue", "uipress");
    $translations["siteRevenueComparison"] = __("Site Revenue (comparison)", "uipress");
    $translations["ecommerceOverview"] = __("Ecommerce Overview", "uipress");
    $translations["visitsByDevice"] = __("Visits By Device", "uipress");
    $translations["deviceCategory"] = __("Device Category", "uipress");
    $translations["activeUsers"] = __("Active Users", "uipress");
    $translations["productName"] = __("Product Name", "uipress");
    $translations["sales"] = __("Sales", "uipress");
    $translations["productName"] = __("Product Name", "uipress");
    $translations["quantitySold"] = __("Quantity Sold", "uipress");
    $translations["totalRevenue"] = __("Revenue", "uipress");
    $translations["columnRemoved"] = __("Column Removed", "uipress");
    $translations["sectionRemoved"] = __("Section Removed", "uipress");
    $translations["viewPlans"] = __("View Plans", "uipress");
    $translations["analyticsDataUnavailable"] = __("Unable to fetch analytics data, please try again later", "uipress");
    $translations["columnBgColor"] = __("Column background colour", "uipress");
    $translations["deleteColumn"] = __("Delete Column", "uipress");
    $translations["hideTitle"] = __("Hide card title", "uipress");
    $translations["siteHealth"] = __("Site Health", "uipress");
    $translations["conversions"] = __("Conversions", "uipress");
    $translations["requiresGAfour"] = __("This card only works with google analytics 4 accounts", "uipress");
    $translations["requiresUA"] = __("This card only works with universal analytics propeties.", "uipress");
    $translations["orders"] = __("Orders", "uipress");
    $translations["ordersComp"] = __("Orders (comparison period).", "uipress");
    $translations["revenue"] = __("Revenue", "uipress");
    $translations["revenueComp"] = __("Revenue (comparison period).", "uipress");
    $translations["currentPeriod"] = __("Current Period", "uipress");
    $translations["comparisonPeriod"] = __("Comparison Period", "uipress");
    $translations["matchHeight"] = __("Match Card Height", "uipress");
    $translations["add"] = __("Add", "uipress");
    $translations["newToDo"] = __("What would you like to do?", "uipress");
    $translations["todo"] = __("To do", "uipress");
    $translations["done"] = __("Done", "uipress");
    $translations["size"] = __("Size", "uipress");
    $translations["backgroundColour"] = __("Background Colour", "uipress");
    $translations["customClasses"] = __("Custom CSS class", "uipress");
    $translations["addNewCard"] = __("Add New Card", "uipress");
    $translations["settings"] = __("Settings", "uipress");
    $translations["cardSettings"] = __("Card Settings", "uipress");
    $translations["moveColumnDown"] = __("Move Column Down", "uipress");
    $translations["moveColumnUp"] = __("Move Column Up", "uipress");
    $translations["addNewColumn"] = __("Add new column", "uipress");
    $translations["sectionSettings"] = __("Section Settings", "uipress");
    $translations["toggleSection"] = __("Toggle section visibility", "uipress");
    $translations["pageSettings"] = __("Page Settings", "uipress");
    $translations["inTheLastx"] = __("In the last", "uipress");
    $translations["daysMultiple"] = __("days", "uipress");
    $translations["somethingWrong"] = __("Something has gone wrong", "uipress");
    $translations["nomatomoaccount"] = __("Connect your matomo site to use this card", "uipress");
    $translations["connectMatomo"] = __("Connect Matomo Analytics", "uipress");
    $translations["authToken"] = __("Auth Token", "uipress");
    $translations["matomoURL"] = __("Matomo Url", "uipress");
    $translations["siteID"] = __("Matomo Site ID", "uipress");
    $translations["save"] = __("Save", "uipress");
    $translations["changeMatomoLong"] = __("Click below to change matomo account", "uipress");
    $translations["changeMatomo"] = __("Change account", "uipress");
    $translations["city"] = __("City", "uipress");
    $translations["averagePageLoad"] = __("Average Page Load", "uipress");
    $translations["bounceRate"] = __("Bounce Rate", "uipress");
    $translations["exitRate"] = __("Exit Rate", "uipress");
    $translations["averageTimeOnPage"] = __("Average Time On Page", "uipress");
    $translations["referer"] = __("Referer", "uipress");
    $translations["sectionName"] = __("Section name", "uipress");
    $translations["sectionDescription"] = __("Section description", "uipress");

    return $translations;
  }

  public function get_modules()
  {
    $cards = [];
    $extended_cards = apply_filters("uipress_register_card", $cards);

    return $extended_cards;
  }

  public function register_default_cards($cards)
  {
    if (!is_array($cards)) {
      $cards = [];
    }

    $scriptPath = plugins_url("modules/general/", __FILE__);

    $temp = [];
    $temp["name"] = __("Recently Published", "uipress");
    $temp["moduleName"] = "recent-posts";
    $temp["description"] = __("Display posts, pages and CPTs published within the date range.", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = false;
    $temp["componentPath"] = $scriptPath . "recent-posts.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("Recent Comments", "uipress");
    $temp["moduleName"] = "recent-comments";
    $temp["description"] = __("Displays total comments and recent comments published within the date range.", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = false;
    $temp["componentPath"] = $scriptPath . "recent-comments.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("System Info", "uipress");
    $temp["moduleName"] = "system-info";
    $temp["description"] = __("Displays info our about your cms and server setup.", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = false;
    $temp["componentPath"] = $scriptPath . "system-info.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("Site Health", "uipress");
    $temp["moduleName"] = "site-health";
    $temp["description"] = __("Displays info our about your sites health.", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = false;
    $temp["componentPath"] = $scriptPath . "site-health.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("Date", "uipress");
    $temp["moduleName"] = "calendar";
    $temp["description"] = __("Displays current time and date", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = false;
    $temp["componentPath"] = $scriptPath . "calendar.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("Video", "uipress");
    $temp["moduleName"] = "custom-video";
    $temp["description"] = __("Displays a custom video", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = true;
    $temp["componentPath"] = $scriptPath . "custom-video.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("Shortcode", "uipress");
    $temp["moduleName"] = "shortcode";
    $temp["description"] = __("Outputs a WordPress shortcode to the card", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = true;
    $temp["componentPath"] = $scriptPath . "shortcode.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("Custom HTML", "uipress");
    $temp["moduleName"] = "custom-html";
    $temp["description"] = __("Outputs custom HTML to the card", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = true;
    $temp["componentPath"] = $scriptPath . "custom-html.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("Quote of the day", "uipress");
    $temp["moduleName"] = "inspirational-quote";
    $temp["description"] = __("Shows a random quote of the day", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = false;
    $temp["componentPath"] = $scriptPath . "inspirational-quote.min.js";
    $cards[] = $temp;

    $temp = [];
    $temp["name"] = __("To do list", "uipress");
    $temp["moduleName"] = "todo-list";
    $temp["description"] = __("A simple todo list card", "uipress");
    $temp["category"] = __("General", "uipress");
    $temp["premium"] = false;
    $temp["componentPath"] = $scriptPath . "todo-list.min.js";

    $cards[] = $temp;

    return $cards;
  }

  public function check_for_google_account()
  {
    $optionname = "admin2020_google_analytics";
    $a2020_options = get_option("uipress-overview");

    if (isset($a2020_options["analytics"]["view_id"]) && $a2020_options["analytics"]["refresh_token"]) {
      $view = $a2020_options["analytics"]["view_id"];
      $code = $a2020_options["analytics"]["refresh_token"];
    } else {
      return false;
    }

    if (!$view || $view == "" || !$code || $code == "") {
      return false;
    }

    return true;
  }

  public function build_overview_data()
  {
    $settings = [];
    $debug = new uipress_debug();
    $current_user = wp_get_current_user();
    $username = $current_user->user_login;
    $first = $current_user->user_firstname;
    $last = $current_user->user_lastname;
    $startday = $this->utils->get_option("overview", "start-day");
    $endday = $this->utils->get_option("overview", "end-day");
    $currency = $this->utils->get_option("overview", "reporting-currency");

    if (!$currency) {
      $currency = '$';
    }

    if ($first == "" || $last == "") {
      $name_string = str_split($username, 1);
      $name_string = $name_string[0];
      $displayname = $username;
    } else {
      $name_string = str_split($first, 1)[0] . str_split($last, 1)[0];
      $displayname = $first;
    }
    if ($first == "") {
      $displayname = $username;
    }

    if (is_numeric($startday) && $startday > 0 && $startday <= 90) {
      $startdiff = $startday;
    } else {
      $startdiff = 13;
    }

    if (is_numeric($endday) && $endday > 0 && $endday <= 90) {
      $enddiff = $endday;
    } else {
      $enddiff = 0;
    }

    $daterange = [];
    $daterange["endDate"] = date("Y-m-d", strtotime(date("Y-m-d", strtotime("-" . $enddiff . " day"))));
    $daterange["startDate"] = date("Y-m-d", strtotime($daterange["endDate"] . " -" . $startdiff . " day"));

    $earlier = new DateTime($daterange["endDate"]);
    $later = new DateTime($daterange["startDate"]);

    $days_difference = $later->diff($earlier)->format("%a");

    if ($days_difference == 0 || !$days_difference) {
      $days_difference = 0;
    }

    $daterange["endDate_comparison"] = date("Y-m-d", strtotime($daterange["startDate"] . " - 1 days"));
    $daterange["startDate_comparison"] = date("Y-m-d", strtotime($daterange["endDate_comparison"] . " - " . $days_difference . " days"));

    $settings["user"]["username"] = $displayname;
    $settings["user"]["initial"] = mb_convert_encoding($name_string, "UTF-8", "HTML-ENTITIES");
    $settings["user"]["welcomemessage"] = __("Hello", "uipress");
    $settings["user"]["date"] = date(get_option("date_format"));
    $settings["user"]["dateRange"] = $daterange;
    $settings["user"]["dateFormat"] = get_option("date_format");
    $settings["user"]["currency"] = $currency;
    $settings["dataConnect"] = $debug->check_network_connection();
    $settings["canEdit"] = $this->can_edit_overview();
    $settings["analyticsAccount"] = $this->check_for_google_account();
    $settings["network"] = is_network_admin();

    $google_icon = esc_url($this->pathURL . "/assets/img/ga_btn_light.png");
    $google_icon_hover = esc_url($this->pathURL . "/assets/img/ga_btn_dark.png");

    $settings["googliconNoHover"] = $google_icon;
    $settings["googliconHover"] = $google_icon_hover;

    $uipDashCards = $this->utils->get_overview_template();

    if (is_array($uipDashCards)) {
      $tempcards = $uipDashCards;

      if (is_array($tempcards) && is_array(json_decode(json_encode($tempcards)))) {
        $cards = $tempcards;
      } else {
        $cards = [];
      }
    } else {
      $cards = [];
    }

    if (!is_array($cards)) {
      $cards = [];
    }

    $settings["cards"]["formatted"] = $cards;
    //$settings["cards"]["formatted"] = [];

    return $settings;
  }

  public function can_edit_overview()
  {
    $enabledFor = $this->utils->get_option("overview", "disable-edit-for");

    if (empty($enabledFor)) {
      if (current_user_can("administrator")) {
        return true;
      } else {
        return false;
      }
    }

    if (!is_array($enabledFor)) {
      if (current_user_can("administrator")) {
        return true;
      } else {
        return false;
      }
    }

    if (!function_exists("wp_get_current_user")) {
      if (current_user_can("administrator")) {
        return true;
      } else {
        return false;
      }
    }

    $current_user = wp_get_current_user();

    $current_name = $current_user->display_name;
    $current_roles = $current_user->roles;
    $formattedroles = [];
    $all_roles = wp_roles()->get_names();

    if (in_array($current_name, $enabledFor)) {
      return true;
    }

    ///MULTISITE SUPER ADMIN
    if (is_super_admin() && is_multisite()) {
      if (in_array("Super Admin", $enabledFor)) {
        return true;
      } else {
        return false;
      }
    }

    ///NORMAL SUPER ADMIN
    if ($current_user->ID === 1) {
      if (in_array("Super Admin", $enabledFor)) {
        return true;
      } else {
        return false;
      }
    }

    foreach ($current_roles as $role) {
      $role_name = $all_roles[$role];
      if (in_array($role_name, $enabledFor)) {
        return true;
      }
    }
  }

  /**
   * Adds overview menu item
   * @since 1.4
   */

  public function add_menu_item()
  {
    add_menu_page(__("Overview", "uipress"), __("Overview", "uipress"), "read", "uip-overview", [$this, "build_overview"], "dashicons-chart-bar", 0);
    return;
  }

  public function build_overview()
  {
    ?>
		
		<style>
			  #wpcontent{
				  padding-left: 0;
			  }
		</style>
		
		<div class="uip-padding-m uip-text-normal uip-body-font uip-fade-in" id="overview-app">
			
			
			<div v-if="!loading" class="uip-w-100p uip-hidden" :class="{'uip-nothidden' : !loading}">
				<?php $this->build_head(); ?>
        <?php $this->build_categories(); ?>
        <?php $this->build_welcome_message(); ?>
				<?php $this->build_cards(); ?>
			</div>
		</div>
		<?php
  }

  public function build_head()
  {
    ?>
		
		<div v-if="uipOverview.data.ui.editingMode" 
		class="uip-position-fixed uip-background-default uip-border-bottom uip-padding-s uip-right-0 uip-flex uip-flex-right uip-z-index-99" 
		style="top:var(--uip-toolbar-height);left:var(--uip-menu-width);">
    
        <div class="uip-flex-grow">
          <button @click="resetOverview()" 
          class="uip-button-danger"><?php _e("Clear Layout", "uipress"); ?></button>
        </div>
				
				<button  @click="newSection()"
				class="uip-button-default uip-margin-right-xs"><?php _e("New Section", "uipress"); ?></button>
				
				
        
        <button  @click="saveDash()"
        class="uip-button-primary uip-margin-right-xs"><?php _e("Save changes", "uipress"); ?></button>
        
        <button  @click="uipOverview.data.ui.editingMode = false;"
        class="uip-button-default"><span class="material-icons-outlined">close</span></button>
        
				
		</div>
		
		
		<div v-if="!uipOverview.data.ui.editingMode" class="uip-flex uip-margin-bottom-m uip-flex-wrap uip-flex-start">
			
			
				
			<div class="uip-flex uip-flex-center uip-flex-grow uip-min-w-200 uip-margin-bottom-s">
				
				<div class="uip-background-dark uip-h-50 uip-w-50 uip-border-circle uip-flex uip-flex-center uip-flex-middle  uip-margin-right-s">
					<span class="uip-text-inverse uip-text-bold uip-text-xl uip-text-lowercase" style="height: 23px;">{{uipOverview.settings.user.initial}}</span>
				</div>
					
				<div >
					<div class="uip-text-bold uip-text-xl uip-text-emphasis uip-margin-bottom-xxs">{{uipOverview.settings.user.welcomemessage}} {{uipOverview.settings.user.username}}</div>
					<div class="uip-text-muted">{{uipOverview.settings.user.date}}</div>
				</div>
				
			</div>
			
			
			
			<div class="" :class="{'uk-margin-top' : isSmallScreen()}">
					
            <date-range-picker-new :dates="uipOverview.data.dateRange"  :translations="uipOverview.data.translations" @date-change="masterDateChange($event)"></date-range-picker-new>
          
				
			</div>
			
			<div v-if="uipOverview.data.ui.editingMode" class="uip-overview-edit-header uk-flex uk-flex-right uk-background-default a2020-border bottom">
				
				<button  @click="newSection()"
				class="uk-button uk-button-small uk-margin-right"><?php _e("New Section", "uipress"); ?></button>
				
				<button  @click="saveDash()"
				class="uk-button uk-button-primary uk-button-small uk-margin-right"><?php _e("Save changes", "uipress"); ?></button>
				
				<button  @click="uipOverview.data.ui.editingMode = false;"
				class="uk-button uk-button-secondary uk-button-small"><?php _e("Exit edit mode", "uipress"); ?></button>
				
			</div>
			
			<div class="uip-margin-left-xs">
        <uip-dropdown-new type="icon" icon="tune" pos="botton-left" buttonsize="normal" :tooltip="true" :tooltiptext="uipOverview.data.translations.pageSettings" >    
          <div class="uip-padding-s">
					
					  <div class="uip-text-bold uip-text-emphasis uip-text-l uip-margin-bottom-s">
              <?php _e("Settings", "uipress"); ?>
            </div>
					  
					  <div v-if="uipOverview.settings.canEdit" class="uip-margin-bottom-s">
						  <div class="uip-margin-bottom-xs"><?php _e("Editing Mode", "uipress"); ?></div>
						  
						  <label class="uip-switch">
						    <input type="checkbox" v-model="uipOverview.data.ui.editingMode">
						    <span class="uip-slider"></span>
						  </label>
					  </div>
					  
					  
					  
					  <div v-if="uipOverview.settings.analyticsAccount" class="uip-margin-bottom-s">
						  <button @click="removeGoogleAccount()" class="uip-button-default">
							  <?php _e("Disconnect Analytics", "uipress"); ?>
						  </button>
					  </div>
					  
					  <template v-if="uipOverview.settings.canEdit && uipOverview.data.account == true" >
						  
					  
						  <div  class="uip-margin-bottom-s">
							  <button @click="exportCards()" class="uip-button-default uip-flex">
								  <span class="material-icons-outlined uip-margin-right-xs">file_download</span>
								  <span><?php _e("Export Layout", "uipress"); ?></span>
								  <a href="" id="uip_export_dash"></a>
							  </button>
						  </div>
						  
						  <div class="uip-margin-bottom-s">
							  <button  class="uip-button-default uip-flex">
								  <label class="uip-flex">
									  <span class="material-icons-outlined uip-margin-right-xs">file_upload</span>
									  <?php _e("Import Layout", "uipress"); ?>
									  <input hidden accept=".json" type="file" single="" id="uipress_import_cards" @change="importCards()">
								  </label>
							  </button>
						  </div>
					  
					  </template>
					  
            
            <div v-if="uipOverview.data.account != true">
              
              <a href="https://uipress.co/pricing/" target="_BLANK" class="uip-no-underline uip-border-round uip-background-primary-wash uip-text-bold uip-text-emphasis uip-display-block" style="padding: var(--uip-padding-button)">
                <div class="uip-flex">
                  <span class="material-icons-outlined uip-margin-right-xs">redeem</span> 
                  <span><?php _e("Unlock Export and Import features with pro", "uipress"); ?></span>
                </div> 
              </a>
              
            </div>
					
          </div>
				</uip-dropdown-new>
			</div>
			
		</div>
		
		
		<?php
  }

  public function build_categories()
  {
    ?>
    <div  v-if="!uipOverview.data.ui.editingMode && categoriesWithUID.length > 1" class="uip-flex uip-flex-row uip-gap-xs uip-margin-bottom-m uip-flex-wrap">
      <div class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xs uip-text-muted uip-text-bold uip-no-wrap"
      :class="{'uip-background-dark uip-text-inverse hover:uip-background-secondary' : uipOverview.data.ui.activeTab == 'Home'}"
      @click="uipOverview.data.ui.activeTab = 'Home'">
        <?php _e("Home", "uipress"); ?>
      </div>
      <template v-for='(category, index) in categoriesWithUID'>
        <div class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xs uip-text-muted uip-text-bold uip-no-wrap"
        :class="{'uip-background-dark uip-text-inverse hover:uip-background-secondary' : uipOverview.data.ui.activeTab == category.uid}"
        @click="uipOverview.data.ui.activeTab = category.uid">
          {{category.name}}
        </div>
      </template>
    </div>
    
    <?php
  }

  public function build_cards()
  {
    if (1 == 2) {
      $token_auth = "946787f6feb7b52aebe44248460aa4af";

      // we call the REST API and request the 100 first keywords for the last month for the idsite=62
      $url = "https://demo.uipress.co/analytics/matomo/";
      $url .= "?module=API&method=UserCountry.getCountry";
      $url .= "&idSite=1&period=month&date=today";
      $url .= "&format=JSON&filter_limit=10";
      $url .= "&token_auth=$token_auth";

      //$fetched = file_get_contents($url);
      //$content = json_decode($fetched, true);

      $remote = wp_remote_get($url, [
        "timeout" => 10,
        "headers" => [
          "Accept" => "application/json",
        ],
      ]);

      //print_r(json_decode($remote["body"]);

      echo "<pre>" . print_r(json_decode($remote["body"]), true) . "</pre>";
    }

    $previewImage = $this->pathURL . "assets/img/overview-example.png";
    ?>
    <div v-if="cardsWithIndex.length < 1 && !uipOverview.data.ui.editingMode">
      <div class="uip-margin-auto uip-max-w-600 uip-padding-m">
        <div class="uip-margin-bottom-l">
          <img class="uip-w-100p uip-border-round uip-shadow"src="<?php echo $previewImage; ?>">
        </div>
        <div class="uip-text-emphasis uip-text-bold uip-text-xxl uip-text-center uip-margin-bottom-m"><?php _e("It's a little quiet around here", "uipress"); ?></div>
        <div class="uip-text-center uip-text-l uip-margin-bottom-m uip-text-muted">
          <?php _e("Looks like you haven't created a dashboard yet. Enter edit mode to get started or import a default template", "uipress"); ?>
        </div>
        <div class="uip-flex uip-flex-middle uip-gap-m">
          <button class="uip-button-primary" @click="import_default_layout()"><?php _e("Import default template", "uipress"); ?></button>
          <button v-if="uipOverview.settings.canEdit" class="uip-button-secondary" @click="uipOverview.data.ui.editingMode = true;"><?php _e("Enter edit mode", "uipress"); ?></button>
        </div>
      </div>
    </div>
    
    
    <!-- SECTIONS -->
     
    <div v-else class="uip-grid-large">
		  <div v-for='(category, index) in cardsWithIndex' class="uip-margin-top-m uip-margin-bottom-m" 
      :class="sectionWidthClass(category)">
			  
			  <div>
          
          <!--SECTION OPTIONS -->
          <div class="uip-flex uip-margin-top-l uip-padding-s uip-flex-right uip-border-dashed uip-border-round uip-gap-xs" v-if="uipOverview.data.ui.editingMode" style="border-bottom:none;">
            
            
            <div class="uip-flex-grow uip-flex uip-gap-xs">
              <uip-tooltip :tooltiptext="uipOverview.data.translations.moveColumnDown">
                <button  @click="moveColumnDown(index)" :disabled="index == uipOverview.settings.cards.formatted.length - 1"
                class="uip-button-default uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xxs material-icons-outlined  material-icons-outlined">expand_more</button>
              </uip-tooltip>
                
              <uip-tooltip :tooltiptext="uipOverview.data.translations.moveColumnUp">
                <button  @click="moveColumnUp(index)" :disabled="index == 0"
                class="uip-button-default uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xxs material-icons-outlined  material-icons-outlined">expand_less</button>
              </uip-tooltip>
              
            </div>
                              
            <!-- ADD NEW COLUMN -->  
            <uip-tooltip :tooltiptext="uipOverview.data.translations.addNewColumn">
              <button @click="addNewColumn(category.columns)"
              class="uip-button-default uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xxs material-icons-outlined  material-icons-outlined">add</button>
            </uip-tooltip>
              
              
            
           <!-- SECTION OPTIONS -->
            <uip-dropdown-new type="icon" icon="more_horiz" pos="botton-left" buttonsize="small" :tooltip="true" :tooltiptext="uipOverview.data.translations.sectionSettings" >  
              
              <div class="uip-padding-s">
                <div  class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">
                  <div class="uip-text-bold"><?php _e("Match Column Height", "uipress"); ?></div>
                  <label class="uip-switch">
                    <input type="checkbox" v-model="category.matchHeight">
                    <span class="uip-slider"></span>
                  </label>
                </div>
                
                <div  class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">
                  <div class="uip-text-bold"><?php _e("Hide section title", "uipress"); ?></div>
                  <label class="uip-switch">
                    <input type="checkbox" v-model="category.hideTitle">
                    <span class="uip-slider"></span>
                  </label>
                </div>
                
                <div  class="uip-flex uip-flex-column uip-gap-xs">
                  <div class="uip-text-bold"><?php _e("Section Width", "uipress"); ?></div>
                  <select class="uk-select uk-form-small uk-margin-small uip-margin-bottom-s" v-model="category.size">
                      <option value="xxsmall">{{uipOverview.data.translations.xxsmall}}</option>
                      <option value="xsmall">{{uipOverview.data.translations.xsmall}}</option>
                      <option value="small">{{uipOverview.data.translations.small}}</option>
                      <option value="small-medium">{{uipOverview.data.translations.smallmedium}}</option>
                      <option value="medium">{{uipOverview.data.translations.medium}}</option>
                      <option value="medium-large">{{uipOverview.data.translations.mediumlarge}}</option>
                      <option value="large">{{uipOverview.data.translations.large}}</option>
                      <option value="xlarge">{{uipOverview.data.translations.xlarge}}</option>
                  </select>
                </div>
              </div>
              
              <div class="uip-padding-s uip-border-top">
                <button  @click="deleteSection(index)"
                class="uip-button-danger ">
                  <?php _e("Remove Section", "uipress"); ?></button>
              </div>
            </uip-dropdown-new>
              
          </div>
          <!--END SECTION OPTIONS -->
			    
          <div :class="{'uip-border-dashed uip-padding-s uip-border-round' : uipOverview.data.ui.editingMode}">
            
			      <div class="uip-border-round"
			      :class="{'uip-background-muted uip-padding-s' : !category.open}">
				      <div class="uip-flex" >
				      
					      
					      
                
                <div class="uip-flex-grow" v-if="uipOverview.data.ui.editingMode">	
                  <!-- EDIT CAT TITLE -->
                  <div  class="uip-flex uip-flex-center uip-margin-bottom-xxs">
                    <span class="material-icons-outlined uip-margin-right-s uip-text-xl">edit</span>
                    <input class="uip-blank-input uip-text-xl" v-model="category.name" type="text">
                  </div>
                  <!-- EDIT CAT DESC -->
                  <textarea class="uip-w-300" v-model="category.desc" type="text" style="padding:0;background:none;border:none;"></textarea>
                </div>
                
                
                
                <div v-if="!uipOverview.data.ui.editingMode && category.hideTitle != 'true' && category.hideTitle != true" class="uip-flex-grow">	
                  <!-- CAT TITLE -->
                  <div class="uip-text-bold uip-text-xl uip-text-emphasis uip-margin-bottom-xxs">
                    {{category.name}}
                  </div>
                  
                  <!-- CAT DESC -->
                  <div v-if="!uipOverview.data.ui.editingMode" class="uip-text-muted uip-w-100-p">
                    {{category.desc}}
                  </div>
                </div>
					      
					      <div v-if="!uipOverview.data.ui.editingMode && category.hideTitle != 'true' && category.hideTitle != true">
						      
                  <uip-tooltip v-if="category.open" :tooltiptext="uipOverview.data.translations.toggleSection">
						        <span  @click="category.open = !category.open" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xxs material-icons-outlined">
                      expand_more
                    </span>
                  </uip-tooltip>
                  
                  <uip-tooltip v-if="!category.open" :tooltiptext="uipOverview.data.translations.toggleSection">
                    <span  @click="category.open = !category.open" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xxs material-icons-outlined">
                      chevron_left
                    </span>
                  </uip-tooltip>
                  
					      </div>
				      </div>
              
			      </div>
			      
			      
			      <div v-if="category.open"  class="uip-grid uip-flex uip-flex-wrap uip-margin-top-m uip-row-gap-m" >
				      
				      <template v-for="(column, index) in category.columns" :key="column.id">
					      <div :class="['uip-width-' + column.size, { 'uip-edit-col' : uipOverview.data.ui.editingMode, 'uip-empty-col' : !column.cards || column.cards.length < 1},{'uip-flex-start' : category.matchHeight != 'true' && category.matchHeight != true}, column.classes]" >
                
                
                  <div class="card-wrap uip-border-round" :style="'background: ' + column.bgColor"
                  :class="[{'uip-border-dashed uip-border-round': uipOverview.data.ui.editingMode}]" >
                    
                    <!-- COLUMN OPTIONS -->
						        <col-editor 
						        v-if="uipOverview.data.ui.editingMode" 
						        :modules="uipOverview.modules"
                    :id="column.id"
						        :premium="uipOverview.settings.dataConnect"
						        @remove-col="removeCol(category.columns, index)" 
						        :column="column" :translations="uipOverview.data.translations" @col-change="column = getdatafromComp($event)"></col-editor>
						        
						        
						        <draggable 
						          v-model="column.cards" 
						          :component-data="setDragData(column)"
						          handle=".drag-handle"
						          group="uip-cards"
						          @start="drag=true" 
						          @end="drag=false" 
						          @change="logDrop"
						          :item-key="returnCardKey">
                      
						          <template 
						          #item="{element, index}">
							          
							          <div class="top-level-card "
                        :id="[element.compName + '-' + element.uid]"
							          :class="['uip-width-' + element.size]">
								          <div class="uip-card" 
								          :class="{'uip-no-background uip-no-border' : element.nobg && element.nobg != 'false'}"
								          :style="{'background-color' : element.bgColor}">
                          
                          
                            <!--NOT EDIT MODE TITLE -->
                            <div v-if="uipOverview.data.ui.editingMode != true && element.hideTitle != 'true' && element.hideTitle != true" class="uip-padding-s"
                            :class="{'uip-light-text' : element.lightDark && element.lightDark != 'false'}">
                              <span class="uip-text-bold uip-text-normal uip-text-l uip-text-muted">{{element.name}}</span>
                            </div>
                            
                            <!--EDIT MODE TITLE BLOCK -->
									          <div v-if="uipOverview.data.ui.editingMode == true" class="uip-padding-s">
										          <div class="uip-flex uip-flex-center">
											          
											          <div :class="{'uip-light-text' : element.lightDark && element.lightDark != 'false'}" class="uip-flex-grow">
												          <div class="uip-text-bold uip-text-normal drag-title uip-text-l uip-flex uip-flex-center uip-text-muted">
													          <span v-if="uipOverview.data.ui.editingMode" class="material-icons-outlined uip-margin-right-xs drag-handle" style="cursor:pointer">drag_indicator</span>
													          <span>{{element.name}}</span>
												          </div>
											          </div>
											          
											          <card-options  :translations="uipOverview.data.translations" :card="column.cards[index]" :cardindex="index" 
												         @remove-card="removeCard(column, index)" 
												         @card-change="column.cards[index] = getdatafromComp($event)"></card-options>
										          </div>
									          </div>
                            
                            <!--CARD CONTENT -->
									          <div :class="{'uip-light-text' : element.lightDark && element.lightDark != 'false'}">
									          <component :is="element.compName" v-bind="{ cardData: JSON.parse(JSON.stringify(element)), overviewData: uipOverview.data}"
                            @card-change="column.cards[index] = getdatafromComp($event)"></component>
									          </div>
									          
								          </div>
							          </div>
							          
							          
							          
						          </template>
						          
                      <template #footer>
                        <p class="uip-text-muted uip-text-center" v-if="!column.cards || column.cards.length < 1 && uipOverview.data.ui.editingMode">
                          {{uipOverview.data.translations.emptycolumn}}
                        </p>
                      </template>
						          
						        </draggable>
                  
                  
                  </div>
						      
						      
						      
					      </div>
						      
				      </template>
				      
			      </div>
			      
          </div>
			    
        </div>
			  
		  </div>
    
  </div>
		
		
		
		<?php
  }

  public function build_welcome_message()
  {
    $code = stripslashes($this->utils->get_option("overview", "custom-welcome"));

    if ($code != "" && $code) { ?>
			<div class="uip-card uip-position-relative uip-text-normal" id="uipress-welcome-message">
				<div class="uip-position-absolute uip-right-0 uip-top-0 uip-padding-xs">
          <span onclick="jQuery('#uipress-welcome-message').remove();" class="material-icons-outlined uip-background-muted uip-padding-xxs uip-border-round hover:uip-background-grey uip-cursor-pointer">close</span>
				</div>	
				<div class="uip-padding-s">
					<?php echo $code; ?>
				</div>
			</div>
			
			<?php }
  }

  public function return_default_layout()
  {
    $defaults = '[
      {
        "name": "Site Overview",
        "desc": "General Site stats",
        "open": "true",
        "size": "xlarge",
        "id": 0,
        "columns": [
          {
            "size": "small",
            "cards": [
              { "name": "Quote of the day", "compName": "inspirational-quote", "size": "xlarge", "lightDark": "false", "nobg": "true", "id": "000Quote of the day", "uid": "ABeNhmLzdxoGLRTCNlpR" }
            ],
            "matchHeight": "false",
            "bgColor": "#d5d0b53d",
            "id": "00"
          },
          {
            "size": "small",
            "id": "01",
            "cards": [
              {
                "name": "Site Health",
                "compName": "site-health",
                "size": "xlarge",
                "id": "010Site Health",
                "uid": "wbxjTBF8USTpOvaS5aYYDWSMvSr0xT",
                "lightDark": "false",
                "nobg": "true",
                "hideTitle": "false"
              }
            ],
            "bgColor": "#c9c4e530",
            "matchHeight": "true"
          },
          {
            "size": "small",
            "cards": [{ "name": "Recent Comments", "compName": "recent-comments", "size": "xlarge", "nobg": "true", "id": "020Recent Comments", "uid": "CmFdp2o0vcy0g0BSZvP6k94sTRLvkk" }],
            "bgColor": "#c9c4e530",
            "id": "02"
          },
          {
            "size": "small",
            "cards": [
              { "name": "Recently Published", "compName": "recent-posts", "size": "xlarge", "nobg": "true", "hideTitle": "false", "id": "030Recently Published", "uid": "2Dk8l2CFnTTfH3GBzoMyHkCMe1s4mj" }
            ],
            "bgColor": "#c9c4e530",
            "id": "03"
          }
        ],
        "matchHeight": "true",
        "uid": "AcnrgtuHUvPl0T2mYM3k"
      },
      {
        "name": "Visitor Analytics",
        "desc": "Shows statistics about site users and traffic",
        "open": "true",
        "columns": [
          {
            "size": "small",
            "id": "10",
            "cards": [
              { "name": "Site Users", "compName": "site-users", "size": "xlarge", "id": "100Site Users", "uid": "uBuqkykXawpSxI69LWyC", "nobg": "false", "lightDark": "false" },
              {
                "name": "Event Count By Event Name",
                "compName": "event-count",
                "size": "xlarge",
                "id": "101Event Count By Event Name",
                "uid": "CmqDrSiTfXa6gFRZAFJpOPI0dSehl2",
                "hideTitle": "false",
                "nobg": "false",
                "lightDark": "false"
              }
            ],
            "matchHeight": "true"
          },
          {
            "size": "medium",
            "cards": [
              { "name": "Page Views", "compName": "page-views", "size": "xlarge", "id": "110Page Views", "uid": "naV3DgTGtJ7hHXGAlLfq" },
              { "name": "Visits By Page", "compName": "page-traffic", "size": "medium", "id": "111Visits By Page", "uid": "VS5w66uihspizk6a2wArWNuzN5PEt5", "nobg": "false", "lightDark": "false" },
              { "name": "Site visits by device", "compName": "site-devices", "size": "medium", "id": "112Site visits by device", "uid": "rmKdI347h6zAlWFlzaUD", "nobg": "false", "lightDark": "false" }
            ],
            "id": "11",
            "matchHeight": "false"
          },
          {
            "size": "small",
            "cards": [
              {
                "name": "Visits by Country",
                "compName": "country-visits",
                "size": "xlarge",
                "nobg": false,
                "lightDark": true,
                "bgColor": "#171528",
                "id": "120Visits by Country",
                "uid": "BxRFO3VPnE3wQpqn6r6Y9OY0tgu59g",
                "hideTitle": "false"
              }
            ],
            "bgColor": "",
            "id": "12"
          }
        ],
        "size": "xlarge",
        "id": 1,
        "uid": "vS6AxSg7QoZBeuLOr1NK",
        "matchHeight": "false"
      },
      {
        "name": "User Aquisition",
        "desc": "Overview of where your site users are coming from",
        "open": "true",
        "columns": [
          {
            "size": "small",
            "cards": [{ "name": "Traffic Sources", "compName": "traffic-sources", "size": "xlarge", "id": "200Traffic Sources", "uid": "eo1gOWpXjrG2HqnHG4NTD6EtjYKJSd" }],
            "id": "20",
            "matchHeight": "true"
          },
          {
            "size": "small",
            "cards": [
              { "name": "Engagement Rate", "compName": "engagement-rate", "size": "xlarge", "id": "210Engagement Rate", "uid": "PJGNdUQZ4J5Htxa1zcgmIYJMnhZd9T" },
              { "name": "New vs Returning Users", "compName": "new-returning", "size": "xlarge", "id": "211New vs Returning Users", "uid": "rMbAHeqoYzFeqkAiH8K2S7TDJxX5vv" }
            ],
            "id": "21"
          },
          {
            "size": "small",
            "cards": [
              { "name": "Sessions by channel grouping", "compName": "sessions-channel", "size": "xlarge", "id": "220Sessions by channel grouping", "uid": "LtMDkdRJEFGTCjdV45LU" },
              { "name": "Session Duration", "compName": "session-duration", "size": "xlarge", "id": "221Session Duration", "uid": "FnPAUtPt4AeuBeBMSedPphHcl2AHMW" }
            ],
            "id": "22"
          },
          { "size": "small", "cards": [{ "name": "Top Cities", "compName": "city-visits", "size": "xlarge", "id": "230Top Cities", "uid": "6CEhDNvwFf6EM2YrhRNIcV4uCrYwcl" }], "id": "23" }
        ],
        "size": "xlarge",
        "id": 2,
        "uid": "hVxAAXdNINW2ZSbwdeC1GvXEhmz516"
      },
      {
        "name": "Site revenue",
        "desc": "Statistics about revenue and popular products",
        "open": "true",
        "columns": [
          {
            "size": "xlarge",
            "cards": [
              { "name": "Ecommerce Overview", "compName": "cart-checkout-purchase", "size": "xlarge", "id": "300Ecommerce Overview", "uid": "PsQsQkdbnYvZQPhvyxtO0k8pB1UOJt" },
              { "name": "Site Revenue", "compName": "site-revenue", "size": "medium", "id": "301Site Revenue", "uid": "JS9VLElm6xx1JS9TUSYSFmcgYZttEC" },
              { "name": "Top Products", "compName": "top-products", "size": "medium", "id": "302Top Products", "uid": "7JQIvi90MyZp3TNSU2jPd1f1eivt4z" },
              { "name": "Conversion Rate", "compName": "conversion-rate", "size": "xlarge", "id": "303Conversion Rate", "uid": "6AqQHoDG9qkn9Bt3FnQGoXnJDuRcRF" }
            ],
            "id": "30",
            "matchHeight": "true"
          }
        ],
        "size": "medium",
        "id": 3,
        "uid": "yEhBTbzjC9WnPkuBcpCoFZjl4gA6AJ"
      },
      {
        "name": "Real Time",
        "desc": "Last 30 minutes of user activity",
        "open": "true",
        "columns": [
          {
            "size": "xlarge",
            "cards": [
              {
                "name": "Page Views in the last 30 minutes",
                "compName": "active-page-views",
                "size": "xlarge",
                "nobg": "true",
                "id": "400Page Views in the last 30 minutes",
                "uid": "29WgsUWiwlzd2mMk1y7M2xS4PHXhDb"
              }
            ],
            "bgColor": "#c9c4e530",
            "id": "40"
          },
          {
            "size": "xlarge",
            "cards": [
              { "name": "User in the last 30 minutes", "compName": "active-now", "size": "medium", "id": "410User in the last 30 minutes", "uid": "Y2dQiDU3KlkOfDjBWYN3TtfvqJNg0X" },
              {
                "name": "Conversions in the last 30 minutes",
                "compName": "active-conversions",
                "size": "medium",
                "bgColor": "",
                "id": "411Conversions in the last 30 minutes",
                "uid": "mcoEK9RWhVUpLhJodFcbi2YMbrzeH0"
              }
            ],
            "id": "41"
          }
        ],
        "size": "medium",
        "id": 4,
        "uid": "HNMPFybi91UlKeJtsgqaQ0Icyl3cjJ"
      }
    ]';

    return $defaults;
  }
}
