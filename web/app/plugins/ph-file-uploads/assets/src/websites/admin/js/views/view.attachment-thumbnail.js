ph.api.views.AttachmentThumbnail = ph.api.View.extend({
  template: wp.template("file-attachment-thumb"),
  className: "ph-file-attachment-thumbnail",

  events: {
    "click .ph-close-icon": "deleteAttachment"
  },

  initialize: function() {
    this.listenTo(this.model, "sync change:progress progress", this.render); // re-render after sync
    this.listenTo(this.model, "error", this.remove);
  },

  deleteAttachment: function() {
    if (confirm(PH_Website_Settings.translations.are_you_sure)) {
      this.model.destroy();
      // Remove view from DOM
      this.remove();
      Backbone.View.prototype.remove.call(this);
    }
    return false;
  }
});
