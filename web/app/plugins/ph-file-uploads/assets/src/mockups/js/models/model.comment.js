/**
 * Hook into website thread initialize
 */
ph.api.hooks.addAction(
  "ph.api.models.Comment.initialize",
  "ph.api.plugins.fileUploads",
  function(model) {
    model.initializeAttachments();
  }
);

/**
 * Extend model prototype functions with our own
 */
_.extend(ph.api.models.Comment.prototype, {
  validate: function(attrs, options) {
    if (!attrs.content || attrs.content === "<p><br></p>") {
      if (!attrs.attachment_ids || attrs.attachment_ids.length === 0) {
        return PH_Settings.translations.no_comment_text;
      }
    }
  },

  initializeAttachments: function() {
    this.set("attachment_ids", this.get("attachment_ids") || []);
    // set empty attachment objects collection
    this.set(
      "attachments",
      new ph.api.collections.Media(this.get("attachment_ids"))
    ); // start attachments

    // we'll delay getting attachments until the comment bubble is shown
    this.listenTo(this.collection, "show", this.getAttachments);

    // get attachments when a comment is added
    this.listenTo(this, "add", this.getAttachments);

    // remove attachment id from comment when an attachment is destroyed
    this.listenTo(this.get("attachments"), "destroy", this.removeAttachmentID);

    // fetch attachments
    _.bindAll(this, "getAttachments");
  },

  // fetch the attachments for a comment
  getAttachments: function() {
    // bail if no attachments
    if (_.isEmpty(this.get("attachment_ids"))) {
      return;
    }

    // already fetched
    if (
      this.get("attachments") &&
      this.get("attachments").first() &&
      this.get("attachments")
        .first()
        .get("id")
    ) {
      return;
    }

    // fetch attachments by ids
    this.get("attachments").fetch({
      data: {
        include: this.get("attachment_ids") // only include these attachment ids
      },
      reset: true // start from scratch
    });
  },

  /**
   * Remove attachment id when attachment is destroyed
   * @param model
   */
  removeAttachmentID: function(model) {
    this.save(
      {
        attachment_ids: _.without(this.get("attachment_ids"), model.id)
      },
      {
        patch: true
      }
    );
  }
});
