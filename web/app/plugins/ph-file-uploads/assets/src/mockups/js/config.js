/**
 * Initialise the PH_API.
 */
function PH_API() {
  this.me = {};
  this.global = {};
  this.models = {};
  this.collections = {};
  this.views = {};
}

window.ph = window.ph || {};
ph.api = ph.api || new PH_API();

// add file uploads plugin to namespace
ph.api.plugins = ph.api.plugins || {};
ph.api.plugins.fileUploads = {};
