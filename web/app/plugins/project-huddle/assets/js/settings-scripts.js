jQuery(document).ready(function($) {
  $(".color-picker").wpColorPicker();

  /**
   * Uploading Images
   */
  var file_frame;
  $('span#ph_section_one').closest("tr").addClass('comment_trigger');
  $('span#ph_section_two').closest("tr").addClass('approval_trigger');
  $('span#ph_section_three').closest("tr").addClass('resolve_trigger');
  $('span#shortcode_desp').closest("tr").addClass('short_desp');
  $('span#slack_terms').closest("tr").addClass('slack_heading');
  $('span#comment_text').closest("tr").addClass('comment_text');
  $('span#private_comment_check').closest("tr").addClass('private_comment_check');
  $('span#project_approval_text').closest("tr").addClass('project_approval_text');
  $('span#project_resolve_text').closest("tr").addClass('project_resolve_text');
  $('select#comment_status_access').closest("tr").addClass('ph-select-setting-css');
  $('select#private_comment_access').closest("tr").addClass('ph-select-setting-css');
  $('span#active_status_name').closest("td").addClass('active_status');
  $('span#resolve_status_name').closest("td").addClass('resolve_status');
  $('td.active_status').children("tr").addClass('active_status_lable_tooltip');
  $('td.resolve_status').children("tr").addClass('resolve_status_lable_tooltip');
  $('.wp-picker-container').closest("td").addClass('ph_color_picker_td');
  
  const { __ } = wp.i18n;
  $(".ph-css-multi-select2").select2({
    placeholder:  __('Select User Role', 'project-huddle')
  });

  $.fn.uploadMediaFile = function(button, preview_media) {
    var button_id = button.attr("id");
    var field_id = button_id.replace("_button", "");
    var preview_id = button_id.replace("_button", "_preview");

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
      title: $(this).data("uploader_title"),
      button: {
        text: $(this).data("uploader_button_text")
      },
      // only allow images here, no svgs etc
      library: {
        type: ["image/jpg", "image/jpeg", "image/png", "image/gif"]
      },
      multiple: false
    });

    // When an image is selected, run a callback.
    file_frame.on("select", function() {
      attachment = file_frame
        .state()
        .get("selection")
        .first()
        .toJSON();
      $("#" + field_id).val(attachment.id);
      if (preview_media) {
        $("#" + preview_id).attr("src", attachment.sizes.thumbnail.url);
      }
    });

    // Finally, open the modal
    file_frame.open();
  };

  $(".image_upload_button").click(function() {
    $.fn.uploadMediaFile($(this), true);
  });

  $(".image_delete_button").click(function() {
    $(this)
      .closest("td")
      .find(".image_data_field")
      .val("");
    $(this)
      .closest("td")
      .find(".image_preview")
      .attr("src", "");
    return false;
  });

  $("[data-required]").each(function() {
    var $this = $(this),
      r = $this.data("required"),
      required = $("#" + $this.data("required")),
      value = $this.data("required-value");

    if (required.find(":radio[value=" + value + "]").is(":checked")) {
      $('[data-required="' + r + '"]')
        .closest("tr")
        .show();
    } else {
      $('[data-required="' + r + '"]')
        .closest("tr")
        .hide();
    }

    required.change(function() {
      if (required.find(":radio[value=" + value + "]").is(":checked")) {
        $('[data-required="' + r + '"]')
          .closest("tr")
          .show(); 
      } else {
        $('[data-required="' + r + '"]')
          .closest("tr")
          .hide();
      }
    });
  });

// For slack settings page

if ($('#slack_comment input[type=checkbox]').prop('checked') == false) {
  $('span#comment_text').closest("tr").addClass('ph_slack_settings');
  $('tr.private_comment_check').addClass('ph_slack_settings');
}

$('#slack_comment input[type=checkbox]').on('click', function() {
  if ($(this).prop('checked')) {
      $('span#comment_text').closest("tr").removeClass('ph_slack_settings');
      $('tr.private_comment_check').removeClass('ph_slack_settings');
  } else {
      $('span#comment_text').closest("tr").addClass('ph_slack_settings');
      $('tr.private_comment_check').addClass('ph_slack_settings');
  }
});


if ($("#slack_thread_resolves input[type=checkbox]").prop('checked') == false) {
  $('span#project_resolve_text').closest("tr").addClass('ph_slack_settings');
}

$('#slack_thread_resolves input[type=checkbox]').on('click', function() {
  if ($(this).prop('checked')) {
      $('span#project_resolve_text').closest("tr").removeClass('ph_slack_settings');
  } else {
      $('span#project_resolve_text').closest("tr").addClass('ph_slack_settings');
  }
});

if ($('#slack_project_approvals input[type=checkbox]').prop('checked') == false) {
  $('span#project_approval_text').closest("tr").addClass('ph_slack_settings');
}

$('#slack_project_approvals input[type=checkbox]').on('click', function() {
  if ($(this).prop('checked')) {
      $('span#project_approval_text').closest("tr").removeClass('ph_slack_settings');
  } else {
      $('span#project_approval_text').closest("tr").addClass('ph_slack_settings');
  }
});

// Progress Status

if ($(
  `#progress_status_enable input[type=checkbox],
   #progress_status_color input[type=checkbox],
    #progress_status_name input[type=text],
     #progress_status_name input[type=color]`
).prop('checked') == true) {
  $('span#progress_status_name, span#progress_status_color').closest("tr").addClass('ph_slack_settings');
}

$('#progress_status_enable input[type=checkbox], #progress_status_color input[type=checkbox]').on('click', function() {
  if ($(this).prop('checked')) {
    $('span#progress_status_name, span#progress_status_color').closest("tr").addClass('ph_slack_settings');
  } else {
    $('span#progress_status_name, span#progress_status_color').closest("tr").removeClass('ph_slack_settings');
  }
});

// Review Status

var review_check = $('#review_status_enable input[type=checkbox]');
var review_color = $('#review_status_color input[type=checkbox]');
var review_name = $('#review_status_name input[type=text]');
var review_status = $('#review_status_name input[type=color]');

if ($.merge(review_check, review_color, review_name, review_status).prop('checked') == true) {
  $('span#review_status_name, span#review_status_color').closest("tr").addClass('ph_slack_settings');
}

$.merge(review_check, review_color).on('click', function() {
  if ($(this).prop('checked')) {
      $('span#review_status_name, span#review_status_color').closest("tr").addClass('ph_slack_settings');
    } else {
      $('span#review_status_name, span#review_status_color').closest("tr").removeClass('ph_slack_settings');
  }
});
});
