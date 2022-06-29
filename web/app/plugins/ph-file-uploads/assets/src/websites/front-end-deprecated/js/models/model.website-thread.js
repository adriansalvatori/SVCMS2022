import "../collections/collection.media";

/**
 * Hook into website thread initialize
 */
ph.api.hooks.addAction("ph.api.models.WebsiteThread.initialize", "ph.api.plugins.fileUploads", function(model) {
  model.attachmentInitialize();
});

/**
 * Extend model prototype functions with our own
 */
_.extend(ph.api.models.WebsiteThread.prototype, {
  attachmentInitialize: function() {
    // set new default for attach
    ph.api.models.WebsiteThread.prototype.defaults.attach = [];

    // set attachments in models
    this.set(
      "attachments",
      new ph.api.collections.Media(this.get("attachments") || [])
    );

    // fetch attachments when bubble is shown
    this.listenTo(this, "change:show change:hover", this.maybeShowCollection);
    this.maybeShowCollection();

    // save attachment data with new comment
    ph.api.hooks.addFilter(
        'ph_new_comment_data',
        'ph.api.plugins.fileUploads',
        _.bind(this.attachments, this)
    );

    // maybe allow empty comment content if there are attachments
    ph.api.hooks.addFilter(
        'ph.api.validations.content_empty',
        'ph.api.plugins.fileUploads',
        _.bind(this.maybeAllowEmpty, this)
    )
  },

  /**
   * Bubble down show event onto collection
   */
  maybeShowCollection: function() {
    if (this.get("show") || this.get("hover")) {
      this.get("comments") && this.get("comments").trigger("show", this); // trigger fetch
    }
  },

  // add attachments data to comment model
  attachments: function(data, model) {
    // bail if not our model
    if (!this.isNew() && this.id !== model.id) {
      return data;
    }

    // add attachment ids
    data.attachment_ids = this.get("attachments").pluck("id");

    // reset
    this.get("attachments").reset();

    // return data
    return data;
  },

  // maybe allow empty comment content if there are attachments
  maybeAllowEmpty: function( allow, attrs ) {
    // content is not empty if attachments
    if ( attrs.attachment_ids && attrs.attachment_ids.length ) {
      return false;
    }

    // if it has comments, check those for empty attachments
    if ( attrs.comments ) {
      var empty = true;

      attrs.comments.forEach(function(comment){
        if ( comment.get('attachment_ids') && comment.get('attachment_ids').length ) {
          empty = false;
        }
      });

      if ( ! empty ) {
        return false;
      }
    }

    return allow;
  }
});
