ph.api.views.CommentAttachmentThumbnail = ph.api.View.extend({
  template: wp.template("comment-file-attachment-thumb"),
  className: "ph-file-attachment-thumbnail",

  events: {
    "click .ph-close-icon": "deleteAttachment"
  },

  initialize: function() {
    this.listenTo(this.model, "sync", this.render); // re-render after sync
  },

  deleteAttachment: function() {
    vex.dialog.confirm({
      message: PH_Settings.translations.are_you_sure,
      callback: function(value) {
        if (value) {
          this.model.destroy();
          // Remove view from DOM
          this.remove();
          Backbone.View.prototype.remove.call(this);
        }
      }.bind(this)
    });

    return false;
  }
});
