<?php
if (!defined('ABSPATH')) {
    die;
}
?>
<script>
  (function($){
      "use strict";
      guaven_woos.ignorelist=[<?php
      $ignorearr = explode(",", get_option('guaven_woos_ignorelist'));
      foreach ($ignorearr as $iarr) {
          echo '"' . addslashes($iarr) . '",';
      }
      ?>];
      guaven_woos.ajaxurl="<?php
      echo admin_url('admin-ajax.php');
      ?>";
      <?php
      if (get_option('guaven_woos_data_tracking') != '') {
          ?>
      guaven_woos.dttrr=1;
      if (typeof(Storage) !== "undefined") {
        guaven_woos.data = {
          "action": "guaven_woos_tracker",
          "ajnonce": "<?php
            $controltime = time();
            echo wp_create_nonce('guaven_woos_tracker_' . $controltime); ?>",
          "addcontrol": "<?php
            echo $controltime; ?>",
        };
      }
        <?php

      } else {
          echo 'guaven_woos.dttrr=0;';
      }
      if (is_singular('product') and intval(get_option('guaven_woos_data_trend_num')) > 0) {
          global $post;
          if (!empty($post->ID)){
          ?>
      setTimeout(function(){guaven_woos.send_trend(<?php
          echo $post->ID; ?>,guaven_woos.get_unid());  },1000);
      <?php
        }
      }

      if (get_option('guaven_woos_nojsfile') != '') {
          echo get_option('guaven_woos_js_data');
      }
      if (get_option('guaven_woos_custom_js') != '') {
          echo stripslashes(get_option('guaven_woos_custom_js'));
      }
      ?>
   })(jQuery);
</script>

<?php
if (get_option('guaven_woos_custom_css') != '') {
    echo '<style>' . stripslashes(get_option('guaven_woos_custom_css')) . '</style>';
}

if (get_option('guaven_woos_mobilesearch') != '') {
    ?>
 <div class="guaven_woos_mobilesearch">
  <p><a autocomplete="off"  onclick='guaven_woos.mobclose()' href="javascript://" class="guaven_woos_mobclose"><img src="<?php
    echo plugin_dir_url(__FILE__) . 'assets/close.png'; ?>" style="width:14px"></a></p>
  <form action="<?php echo home_url(); ?>" method="get">
    <input type="hidden" name="post_type" value="product">
    <span class="gws_clearable">
    <input name="s" id="guaven_woos_s" type="text">
    <i class="gws_clearable__clear">&times;</i>
  </span>
</form>
  </div>
  <?php
}
?>
 <div class="guaven_woos_suggestion"> </div>
