/**
 * Hook into website thread initialize
 */
ph.api.hooks.addAction('ph.api.models.Comment.initialize', "ph.api.plugins.fileUploads", function(model) {
  model.attachmentInitialize();
});

/**
 * Extend model prototype functions with our own
 */
_.extend(ph.api.models.Comment.prototype, {
  attachmentsFetched: false,

  attachmentInitialize: function() {
    // set empty attachment objects collection
    this.set(
        'attachments',
        new ph.api.collections.Media(this.get('attachment_ids') || []),
    ); // start attachments

    // we'll delay getting attachments until the comment bubble is shown
    this.listenTo(this.collection, 'show', this.getAttachments);

    // get attachments when a comment is added
    this.listenTo(this, 'add', this.getAttachments);

    // remove attachment id from comment when an attachment is destroyed
    this.listenTo(this.get('attachments'), 'destroy', this.removeAttachmentID);

    // fetch attachments
    _.bindAll(this, 'getAttachments');
  },

  // fetch the attachments for a comment
  getAttachments: function() {
    // bail if no attachments
    if (_.isEmpty(this.get('attachment_ids'))) {
      return;
    }

    if ( this.attachmentsFetched ) {
      return;
    }

    this.get('attachments').nonce();

    // fetch attachments by ids
    this.get('attachments').fetch({
      data : {
        include: this.get('attachment_ids'), // only include these attachment ids
      },
      reset: true, // start from scratch
    });
    this.attachmentsFetched = true;
  },

  /**
   * Remove attachment id when attachment is destroyed
   * @param model
   */
  removeAttachmentID: function(model) {
    var without = _.without(this.get('attachment_ids'), model.id);
    this.save({attachment_ids: without}, {patch: true});
  },
});
