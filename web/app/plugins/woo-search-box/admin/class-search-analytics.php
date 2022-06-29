<?php

if (!defined('ABSPATH')) {
    die;
}

class Guaven_woo_search_analytics
{
    public function init()
    {
        if (get_option('guaven_woos_sa_table_done') != 1) {
          $this->reports_db_construct();
          update_option('guaven_woos_sa_table_done', 1);
        }
    }

    public function run()
    {
        $this->save_settings();
        $this->init();

        $limit = 'limit 1000';
        if (isset($_GET["showall"])) {
            $limit = '';
        }
        $tabledata = $this->make_table_data($limit);
        $chartdata = $this->make_chart_data($limit);
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/view-analytics.php';

        return;
    }

    public function make_table_data($limit)
    {
        global $wpdb;
        $date_and_state = $this->build_where();
        $flt            = $date_and_state[3];
        if ($flt == 'popular') {
            $sql = 'select *,count(*)  as date_or_count from ' . $wpdb->prefix . 'woos_search_analytics where 1=1
            ' . $date_and_state[0] . ' ' . $date_and_state[1] . ' ' . $date_and_state[2] . ' group by keyword order by date_or_count desc ' . $limit;
        } elseif ($flt == 'popular_uniq') {
            $sql = 'select *,count(keyword) as date_or_count from (select DISTINCT keyword,user_info,state,device_type,ID,created_date
  from ' . $wpdb->prefix . 'woos_search_analytics) a where 1=1 ' . $date_and_state[0] . ' ' . $date_and_state[1] . ' ' . $date_and_state[2] . '
  group by keyword order by date_or_count desc ' . $limit;
        } else {
            $sql = 'select user_info,device_type,keyword,state,created_date as date_or_count, ID
  from ' . $wpdb->prefix . 'woos_search_analytics where 1=1 ' . $date_and_state[0] . ' ' . $date_and_state[1] . ' ' . $date_and_state[2] . ' order by ID desc ' . $limit;
        }
        return $wpdb->get_results($sql);
    }

    public function make_chart_data($limit)
    {
        global $wpdb;
        $date_and_state    = $this->build_where();
        $sql               = 'select state,count(state) say,created_date,device_type
  from ' . $wpdb->prefix . 'woos_search_analytics where 1=1  ' . $date_and_state[0] . ' ' . $date_and_state[1] . '  ' . $date_and_state[2] . '
  group by state,created_date order by created_date asc ' . $limit;
        $chartres          = $wpdb->get_results($sql);
        $crkeys            = array();
        $crvalues          = array();
        $devicetype        = array();
        $devicetype_labels = '';
        $devicetype_values = '';
        foreach ($chartres as $key => $value) {
            $crkeys[$value->created_date]                  = "'" . $value->created_date . "'";
            $crvalues[$value->state][$value->created_date] = $value->say;
            if (empty($devicetype[$value->device_type])) {
                $devicetype[$value->device_type] = 0;
            }
            $devicetype[$value->device_type] += $value->say;
        }

        foreach ($devicetype as $key => $value) {
            $devicetype_labels .= "'" . $key . "',";
            $devicetype_values .= $value . ',';
        }

        $crdef = array();
        foreach ($crkeys as $key => $value) {
            $crdef['fail'][$key]      = 0;
            $crdef['success'][$key]   = 0;
            $crdef['corrected'][$key] = 0;
            $crdef['all'][$key]       = 0;
        }

        foreach ($crvalues as $key => $crvalue) {
            foreach ($crvalue as $ke => $crval) {
                $crdef[$key][$ke]  = $crval;
                $crdef['all'][$ke] = !empty($date_and_state) ? 0 : ($crdef['all'][$ke] + $crval);
            }
        }

        return array(
            $crkeys,
            $crdef,
            $devicetype_labels,
            $devicetype_values,
            $date_and_state[4]
        );
    }



    public function build_where()
    {
        $date_interval = (isset($_POST['days']) and $_POST['days'] > 0) ? (int) $_POST['days'] : 30;
        $date_sql      = ' and created_date between (CURDATE() - INTERVAL ' . $date_interval . ' DAY ) and CURDATE()';
        if (isset($_POST['state']) and in_array($_POST['state'], array(
            'success',
            'fail',
            'corrected'
        ))) {
            $state_sql = "and state='" . esc_sql($_POST['state']) . "'";
        } else {
            $state_sql = '';
        }
        $device_type = !empty($_POST["device_type"]) ? esc_sql($_POST["device_type"]) : '';
        $device_sql  = ' and device_type like "%' . $device_type . '%" ';

        $flt = isset($_GET['flt']) ? $_GET['flt'] : '';

        return array(
            $date_sql,
            $state_sql,
            $device_sql,
            $flt,
            $date_interval
        );
    }

    public function save_settings()
    {
        if (isset($_GET['removekeyword']) and isset($_GET['_wpnonce']) and wp_verify_nonce($_GET['_wpnonce'], 'removekeyword_nonve')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'woos_search_analytics';
            $wpdb->query($wpdb->prepare("delete from `$table_name` where ID=%d", $_GET['removekeyword']));
        }
        if (isset($_POST['guaven_woos_an_reset_nonce_f']) and wp_verify_nonce($_POST['guaven_woos_an_reset_nonce_f'], 'guaven_woos_an_reset_nonce')) {
            update_option('guaven_woos_sa_table_done', '');
            $this->init();
            add_settings_error('guaven_pnh_settings', esc_attr('settings_updated'), 'Success! All analytics data has been deleted', 'updated');
        } elseif (isset($_POST['guaven_woos_an_enable_nonce_f']) and wp_verify_nonce($_POST['guaven_woos_an_enable_nonce_f'], 'guaven_woos_an_enable_nonce')) {
            add_settings_error('guaven_pnh_settings', esc_attr('settings_updated'), 'Success! Search Analytics has been enabled', 'updated');
            update_option('guaven_woos_data_tracking', '1');
        } elseif (isset($_POST['guaven_woos_an_disable_nonce_f']) and wp_verify_nonce($_POST['guaven_woos_an_disable_nonce_f'], 'guaven_woos_an_disable_nonce')) {
            add_settings_error('guaven_pnh_settings', esc_attr('settings_updated'), 'Success! Search Analytics has been disabled', 'updated');
            update_option('guaven_woos_data_tracking', '');
        }
    }


    public function admin_menu()
    {
        $role_to_use_the_analytics=apply_filters('gws_role_to_use_the_plugin','manage_woocommerce');
        add_submenu_page('woocommerce', 'Guaven Woo Search Analytics', 'Search Analytics', $role_to_use_the_analytics, __FILE__, array(
            $this,
            'run'
        ));
    }

    private function reports_db_construct()
    {
      global $wpdb;
      $table_name      = $wpdb->prefix . 'woos_search_analytics';
      $charset_collate = $wpdb->get_charset_collate();
      global $wpdb;
      $wpdb->query("DROP TABLE IF EXISTS  `$table_name`;");
      $sql = " CREATE TABLE `$table_name` (
`ID` bigint(20) NOT NULL AUTO_INCREMENT,
`keyword` varchar(200) NOT NULL,
`created_date` date NOT NULL,
`device_type` varchar(200) NOT NULL,
`user_info` varchar(200) NOT NULL,
`state` varchar(10) NOT NULL,
`side` varchar(10) NOT NULL,
PRIMARY KEY (`ID`)
) $charset_collate;";
      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta($sql);
    }
}
