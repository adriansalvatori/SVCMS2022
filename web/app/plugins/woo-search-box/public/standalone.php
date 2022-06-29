<?php if (isset($_GET["s"])){$gws=esc_attr($_GET["s"]);} else $gws='';?>
<div id="guaven_woos_standalone">
<div style="width:100%" class="search-form" action="" method="get">
  <label>
    <input id="guaven_woos_standalone_s" class="search-field"
    style="width: 100%;float:left; padding: 13px 15px 12px 15px;" name="s" type="search"  
    placeholder="<?php echo __('Search...','guaven_woo_search');?>" autocomplete="off" value="<?php echo $gws;?>"></label>

</div>
</div>

<div style="min-height:500px">
<div class="guaven_woos_suggestion_standalone guaven_woos_suggestion"> </div>
<div id="guaven_woos_suggestion_standalone_bottom" style="display:block;clear:both"></div>
</div>

<script>
(function($){
  "use strict";
  var gws_maxcount_standalone=0;
  jQuery(function(){
    gws_maxcount_standalone=guaven_woos.maxcount;
    jQuery("#guaven_woos_standalone_s").trigger("keyup");
    setTimeout(function(){jQuery("#guaven_woos_standalone_s").focus();jQuery("#guaven_woos_standalone_s").focus();
  },500);


  jQuery.fn.isOnScreen = function(){
      var win = jQuery(window);
      var viewport = {
          top : win.scrollTop(),
          left : win.scrollLeft()
      };
      viewport.right = viewport.left + win.width();
      viewport.bottom = viewport.top + win.height();
      var bounds = this.offset();
      bounds.right = bounds.left + this.outerWidth();
      bounds.bottom = bounds.top + this.outerHeight();
      return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
  };
  var blocker_timeout='';
  var guaven_woos_suggestion_standalone_blocker=0;
    jQuery(window).scroll(function() {
      if (jQuery('#guaven_woos_suggestion_standalone_bottom').isOnScreen() == true) {
        if (guaven_woos_suggestion_standalone_blocker==0){
          guaven_woos_suggestion_standalone_blocker=1;
          guaven_woos.maxcount=guaven_woos.maxcount+gws_maxcount_standalone;
          guaven_woos.runner();
          jQuery("#guaven_woos_standalone_s").trigger("keyup");
          clearTimeout(blocker_timeout);
          blocker_timeout=setTimeout(function(){jQuery("#guaven_woos_standalone_s").focus();},500);
        }
      } else {guaven_woos_suggestion_standalone_blocker=0;}

    });
  });
})(jQuery);
</script>
