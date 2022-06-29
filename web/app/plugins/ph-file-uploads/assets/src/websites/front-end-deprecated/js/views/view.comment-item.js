import "./view.comment-attachment-thumbnail";

/**
 * View to show our individual comment item
 *
 * @since 1.0
 */
ph.api.views.CommentItem = ph.api.views.CommentItem.extend({
  initialize: function() {
    this.constructor.__super__.initialize.apply(this, arguments);

    this.setAttachments(); // sets placeholders
    this.listenTo(
      this.model.get("attachments"),
      "update reset",
      this.setAttachments
    );
  },

  setAttachments: function() {
    var views = [];
    this.model.get("attachments").forEach(function(attachment) {
      views.push(
        new ph.api.views.CommentAttachmentThumbnail({
          model: attachment
        })
      );
    }, this);
    this.views.set(".ph-comment-attachment-container", views);
  }
});
