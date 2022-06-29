<?php
if (!defined('ABSPATH')) {
    die;
}
?>
<style>
.guaven_woos_an_left {float:left;width:35%} .guaven_woos_an_right {float:left;width:65%}
@media (max-width:1200px) {.guaven_woos_an_right,.guaven_woos_an_left {float:none;width:100%}}
</style>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div><h2>Guaven Woo Search Analytics</h2>

<?php
settings_errors();
?>


<?php
if (get_option('guaven_woos_data_tracking') != 1) {
    ?>
 <form action="" method="post" name="an_enable_form">
    <?php
    wp_nonce_field('guaven_woos_an_enable_nonce', 'guaven_woos_an_enable_nonce_f'); ?>
   <p>
    <label>
              <input type="submit" value="Enable Search Analytics" /> </label>
    </p>
    <small>Search Analytics Currently Disabled for Your Website. </small>

  </form>
  <?php

} else {
    ?>


  <div>
    <a href="?page=woo-search-box%2Fadmin%2Fclass-search-analytics.php&flt=recent">Recently searched keywords </a> |
    <a href="?page=woo-search-box%2Fadmin%2Fclass-search-analytics.php&flt=popular">Popular keywords(unfiltered) </a> |
    <a href="?page=woo-search-box%2Fadmin%2Fclass-search-analytics.php&flt=popular_uniq">Popular keywords(by uniq users)</a>
    <br><br>
    <form action="" method="post">
      <label>Last <input type="number" name="days" style="width:50px" value="<?php
    echo $chartdata[4]; ?>"/> days</label>,
      <select name="device_type">
      <option value="">Device type</option> <option value="">All</option> <option value="desktop">Desktop</option>  <option value="mobile">Mobile</option></select>
      <select name="state" onchange="this.form.submit()">
      <option value="">Select result state</option>
      <option value="all">All</option><option value="success">Successfull</option>
      <option value="corrected">Correcteds</option>
      <option value="fail">Notfounds</option></select>
        </form>
  </div>

  <div class="guaven_woos_an_left">


  <table style="width:100%">
    <tr>
      <th  style="width:20px"></th><th>Search String</th><th>Count or Date</th><th>State</th><th style="width:80px">Device</th>
      <th  style="width:20px;padding-right:40px"></th>
    </tr>
    <?php
    $sufaco = array();
    $i      = 0;
    foreach ($tabledata as $tdata) {
        $i++;
        echo '<tr><td>' . $i . '</td><td>' . esc_html(stripslashes($tdata->keyword)) . '</td><td>' . esc_html($tdata->date_or_count) . '</td><td>' . esc_html($tdata->state) . '</td>
    <td>' . esc_html($tdata->device_type) . '</td>
    <td> <a style="color:#c4c4c4;text-decoration:none" href="' . wp_nonce_url('?page=woo-search-box%2Fadmin%2Fclass-search-analytics.php&removekeyword=' . $tdata->ID, 'removekeyword_nonve') . '" title="Remove this keyword">x</a></td>  </tr>';
        if (!isset($sufaco[$tdata->state])) {
            $sufaco[$tdata->state] = 0;
        }
        $sufaco[$tdata->state]++;
    } ?>
 </table>
  </div>

  <div class="guaven_woos_an_right">
<?php
    if (!empty($chartdata[1])) {
        ?>
   <h3>Search volume for selected period </h3>

    <div class="ct-chart">

    </div>
    <small><span style="color:white;padding:5px;background:#f05b4f">successfull</span> <span style="color:white;padding:5px;background:#d70206">notfound</span>
      <span style="color:white;padding:5px;background:#f4c63d">corrected</span> <span style="color:white;padding:5px;background:#d17905">all</span></small>

  <hr /><br>
  <h3>Result summary for selected period </h3>

    <div class="ct-bar" style="width:100%">
    </div>
  <hr /><br>
    <h3><span style="color:#d70206">Desktop</span> vs <span style="color:#f05b4f">Mobile</span> for selected period </h3>
    <div class="ct-pie" style="width:100%">
    </div>

  <script>
<?php
        $i = 0;
        if (count($chartdata[0]) > 20) {
            foreach ($chartdata[0] as $key => $value) {
                $i++;
                if ($i != 1 and $i != count($chartdata[0]) and $i != round(0.5 * count($chartdata[0]))) {
                    $chartdata[0][$key] = "''";
                }
            }
        } ?>
 new Chartist.Line('.ct-chart', {
    labels: [<?php
        echo implode(",", $chartdata[0]); ?>],
    series: [<?php
        foreach ($chartdata[1] as $key => $value) {
            echo '[' . implode(",", $value) . '],
    ';
        } ?>]
  }, {
    fullWidth: true,
    chartPadding: {
      right: 40
    }
  });


  var data = {
    //labels: [<?php
        echo $chartdata[2]; ?>],
    series: [<?php
        echo $chartdata[3]; ?>]
  };
  var sum = function(a, b) { return a + b };
  new Chartist.Pie('.ct-pie', data, {
    height:'300px',
    labelInterpolationFnc: function(value) {
      return Math.round(value / data.series.reduce(sum) * 100) + '%';
    }
  });

  new Chartist.Bar('.ct-bar', {
    labels: ['Successfull', 'Corrected', 'Notfound'],
    series: [<?php
        echo "'" . (!empty($sufaco['success']) ? $sufaco['success'] : 0) . "','" . (!empty($sufaco['corrected']) ? $sufaco['corrected'] : 0) . "','" . (!empty($sufaco['fail']) ? $sufaco['fail'] : 0) . "'"; ?>]
  }, {
    distributeSeries: true
  });
  </script>
  <?php

    } else {
        echo '<br><h4>
  Not enough data for charts yet. Please come back later...
  </h4>';
    } ?>
 </div>

  <div class="clear clearfix"></div>
  <hr />
  <div style="margin-top:100px">


  <form action="" method="post" name="an_disable_form" style="float:left;padding-right:10px">
    <?php
    wp_nonce_field('guaven_woos_an_disable_nonce', 'guaven_woos_an_disable_nonce_f'); ?>

              <input type="submit" value="Disable Search Analytics" class="button button-default" />
  </form>
  <form action="" method="post" name="an_reset_form">
  <?php
    wp_nonce_field('guaven_woos_an_reset_nonce', 'guaven_woos_an_reset_nonce_f'); ?>
 <input type="submit" onclick="return confirm('Are you sure to reset all analytics data?')" class="button button-default" value="Delete all analytics data">
  </form>

</div>
  <?php

}
?>

</div>
