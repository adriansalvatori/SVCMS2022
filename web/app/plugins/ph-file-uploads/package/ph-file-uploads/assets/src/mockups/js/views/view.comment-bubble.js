import "../views/view.attachment-thumbnail";
import FileUpload from "@common/file-upload-icon.vue";

// extend comment bubble
ph.api.views.CommentBubble = ph.api.views.CommentBubble.extend({
  initialize: function() {
    this.constructor.__super__.initialize.apply(this, arguments);

    // add thumbnail preview view when thumbnail is added
    this.listenTo(this.model.get("attachments"), "add", this.addThumbnail);

    // clear thumbnail preview views when comment is submitted
    this.listenTo(this.model, "submit", this.clearThumbnails);
  },

  ready() {
    // this.constructor.__super__.ready.apply(this, arguments);
    this.addIcon();
  },

  addIcon: function() {
    if (this.uploadIcon) {
      return;
    }

    // create subview
    this.$(".ph-form-controls-right").append(
      '<div id="ph-file-uploads-icon"></div>'
    );

    // file upload
    let upload = new FileUpload({
      el: this.$("#ph-file-uploads-icon")[0]
    });

    upload.$on("upload", files => {
      upload.disableSubmit();

      // add file
      var args = {
        file: files[0]
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
        complete: () => {
          upload.enableSubmit();
        },
        success: () => {
          upload.enableSubmit();
        },
        error: (model, response) => {
          upload.enableSubmit();
          this.model.get("attachments").remove(model);
          console.error(arguments);

          if (_get(response, "responseJSON.message")) {
            vex.dialog.alert(response.responseJSON.message);
          } else {
            alert(
              "Something went wrong. Please reload the page and try again."
            );
          }
        }
      });
    });
  },

  // Add the thumbnail preview views
  addThumbnail: function(model) {
    this.views.add(
      ".ph-attachment-container",
      new ph.api.views.AttachmentThumbnail({
        model: model
      })
    );
  },

  // clear thumbnail preview views
  clearThumbnails: function() {
    this.views.unset(this.views.get(".ph-attachment-container"));
  }
});
