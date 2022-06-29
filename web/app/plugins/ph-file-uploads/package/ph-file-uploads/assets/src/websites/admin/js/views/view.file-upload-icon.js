import "./view.attachment-thumbnail";

ph.api.views.FileUpload = ph.api.View.extend({
  template: wp.template("file-upload"),

  className: "ph-upload",

  events: {
    "click .ph-add-file": "addFile",
    "change .ph-file-input": "uploadImage"
  },

  triggers: {
    'submit': 'clearInput'
  },

  initialize: function() {
    _.bindAll(this, "disableSubmit", "enableSubmit");
  },

  ready: function() {
    this.$submitButton = this.$el
      .closest(".ph-comment-wrapper")
      .find(".submit-comment");
  },

  addFile: function(e) {
    this.$(".ph-file-input").click();
    e.preventDefault();
    e.stopImmediatePropagation();
  },

  clearInput: function(e) {
    this.$(".ph-file-input").val('');
  },

  uploadImage: function(e) {
    if (e.target.files && e.target.files[0]) {
      this.disableSubmit();

      // refocus comment form
      this.model.set("focus", true);

      // add file
      var args = {
        file: this.$(".ph-file-input")[0].files[0]
      };
      // add post if we have one
      if (!this.model.isNew()) {
        args.post = this.model.get("id");
      }

      // create the attachment as soon as it's uploaded
      this.model.get("attachments").create(args, {
        // need to pass as form data
        formData: true,

        // re-enable submit when complete, no matter what
        complete: this.enableSubmit,
        success: this.enableSubmit,

        error: _.bind(function(model, response) {
          this.enableSubmit();
          this.model.get("attachments").remove(model);
          console.error(arguments);

          if ( ! _.isUndefined(response.responseJSON) && ! _.isUndefined(response.responseJSON.message) ) {
            alert(response.responseJSON.message);
          }
        }, this)
      });
    }
  },

  enableSubmit: function() {
    this.$submitButton && this.$submitButton.prop("disabled", false);
  },

  disableSubmit: function() {
    this.$submitButton && this.$submitButton.prop("disabled", true);
  }
});
