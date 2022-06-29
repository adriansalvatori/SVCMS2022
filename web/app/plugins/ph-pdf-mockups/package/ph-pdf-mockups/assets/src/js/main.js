// check for browser support
import { supported, vueDialog } from "./browser-support";
const ns = "ph-pdf-mockups";

const { get } = ph.util;

// check for pdf support once
window.phPdfSupported = _.once(() => {
  if (!supported()) {
    vueDialog();
  }
});

/**
 * Add pdfs to library types
 */
ph.hooks.addFilter("ph.file_frame.options", ns, function(options) {
  // make sure it's defined
  if (_.isUndefined(options.library.type)) {
    options.library.type = [];
  }

  // convert to array
  if (typeof options.library.type === "string") {
    options.library.type = [options.library.type];
  }

  // add our type
  options.library.type.push("application/pdf");

  return options;
});

/**
 * Only pdfs allowed for new versions
 */
ph.hooks.addFilter("ph.file_frame.newVersion.options", ns, function(
  options,
  instance
) {
  // make sure it's defined
  if (_.isUndefined(options.library.type)) {
    options.library.type = [];
  }
  // new pdfs only for new versions
  if (instance.image.type == "pdf") {
    options.library.type = "application/pdf";
  }
  return options;
});

/**
 * Process new version selected
 */
import newVersion from "./image/new-version";
ph.hooks.addAction("ph.image.newVersion.selected", ns, newVersion);

// process pdf page as featured media
import featuredMedia from "./image/featured-media-admin";
ph.hooks.addAction("ph.image.getFeaturedMedia", ns, featuredMedia);

// handle new selected pdf
import selectedPDF from "./image/selected-pdf";
ph.hooks.addAction("ph.image.new.selected", ns, selectedPDF);

// add extra fields to save
import extraFields from "./image/extra-fields";
ph.hooks.addFilter("ph.image.formData", ns, extraFields);

import pdfOptions from "./vue/pdf-options.vue";

/**
 * Add the pdf width component to the displayOptions form slot
 */
ph.hooks.addAction("ph.displayOptions.mounted", ns, function(instance, id) {
  // bail if not pdf
  if (get(["image", "type"], instance) !== "pdf") {
    return;
  }

  // force integer since it's a nested object
  instance.options.pdf_width = parseInt(instance.options.pdf_width);
  instance.localOptions.pdf_width = parseInt(instance.localOptions.pdf_width);

  // push our component to the form slot
  (instance.$slots.form || (instance.$slots.form = [])).push(
    instance.$createElement(pdfOptions, {
      // pass local options from display
      props: {
        options: instance.localOptions
      },
      // update local options when component updates
      on: {
        update: width => {
          instance.localOptions.pdf_width = width;
        }
      }
    })
  );
});
