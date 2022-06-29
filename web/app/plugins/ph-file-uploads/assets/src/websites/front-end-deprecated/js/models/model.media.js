// mime type to extension
var mime = require('mime-to-extensions');

ph.api.models.Media = wp.api.models.Media.extend(_.defaults({
  // extend defaults
  defaults: _.defaults({}, wp.api.models.Media.prototype.defaults, {
    progress  : 0, // upload progress
    can_delete: false,
    thumbnail : '',
  }),

  requireForceForDelete: true, // need to force delete attachments

  initialize: function() {
    this.setExtension();
    this.setModeration();
    this.setThumbnail();

    this.listenTo(this, 'sync', this.setExtension); // set extension after sync
    this.listenTo(this, 'sync', this.setModeration); // set moderation after sync
    this.listenTo(this, 'sync', this.setThumbnail); // set thumbnail
    this.listenTo(this, 'progress', this.setProgress);
  },

  setModeration: function() {
    this.set({
      can_delete: this.canDelete(),
    });
  },

  // set deletion parameter (for vanity only)
  canDelete: function() {
    if (ph.api.me.can('moderate_comments')) {
      return true;
    }
    return this.get('author') === ph.api.me.get('id');
  },

  // set upload progress
  setProgress: function(progress) {
    this.set('progress', progress);
  },

  // set extension
  setExtension: function() {
    this.get('mime_type') &&
    this.set('extension', mime.extension(this.get('mime_type')));
  },

  // set thumbnail
  setThumbnail: function() {
    if (this.get('source_url')) {
      if (this.get('media_details') && this.get('media_details').sizes) {
        var url = this.get(
            'media_details').sizes.ph_comment_attachment ? this.get(
            'media_details').sizes.ph_comment_attachment.source_url : false;

        if ( url ) {
          this.set({
            'thumbnail': url,
          });
        }
      }
    }
  },
}, ph.api.Model));
