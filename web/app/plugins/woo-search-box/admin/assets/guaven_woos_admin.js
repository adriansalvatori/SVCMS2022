jQuery(function(){jQuery(".wp-submenu li a").each(function(){if(jQuery(this).html()=='Search Analytics') jQuery(this).parent().remove();})

jQuery(".dropdown dt a").on('click', function() {
  jQuery(".dropdown dd ul").slideToggle('fast');
});

jQuery(".dropdown dd ul li a").on('click', function() {
  jQuery(".dropdown dd ul").hide();
});

function getSelectedValue(id) {
  return jQuery("#" + id).find("dt a span.value").html();
}

jQuery(document).on('click', function(e) {
  var $clicked = jQuery(e.target);
  if (!$clicked.parents().hasClass("dropdown")) jQuery(".dropdown dd ul").hide();
});

var gws_wootags='';
function gws_mutliSelect_set(){
  gws_wootags='';
  jQuery('.multiSel').html("");
jQuery('.mutliSelect input[type="checkbox"]').each( function() {
  var title = jQuery(this).closest('.mutliSelect').find('input[type="checkbox"]').val(),
  title = jQuery(this).val() + ",";
  if (jQuery(this).is(':checked')) {
    var html = '<span title="' + title + '">' + title + '</span>';
    gws_wootags=gws_wootags+title;
    jQuery('.multiSel').append(html);
  }
});
}
gws_mutliSelect_set();
jQuery('.mutliSelect input[type="checkbox"]').on('click', function() {
  gws_mutliSelect_set();
  jQuery('#guaven_woos_wootags').val(gws_wootags);
});
});

// ajax on dismissed notice
jQuery(function($) {
  $( document ).on( 'click', '.guaven-woos-notice .notice-dismiss', function () {
    // Read the "data-notice" information to track which notice
    // is being dismissed and send it via AJAX
    var type = $( this ).closest( '.guaven-woos-notice' ).data( 'notice' );
    $.ajax( ajaxurl, {
      type: 'POST',
      data: {
        action: guaven_woos_notice_dismissed.action,
        type: type,
        nonce: guaven_woos_notice_dismissed.nonce
      }
    });
  });
});