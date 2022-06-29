import "../views/view.file-upload-icon";
import "../views/view.attachment-thumbnail";

// extend comment bubble
ph.api.views.ThreadInfoPanel = ph.api.views.ThreadInfoPanel.extend({
  initialize: function() {
    this.constructor.__super__.initialize.apply(this, arguments);

    // this.addIcon();
    // this.listenTo(ph.api.me, "change:id", this.addIcon);

    // add thumbnail preview view when thumbnail is added
    this.listenTo(this.model.get("attachments"), "add", this.addThumbnail);

    // clear thumbnail preview views when comment is submitted
    this.listenTo(this.model, "submit", this.clearThumbnails);
  },

  // file upload icon on form
  addIcon: function() {
    if (ph.api.me.can('upload_files')) {
      this.views.add(
          ".info-actions",
          new ph.api.views.FileUpload({
            model: this.model
          })
      );
    }
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
