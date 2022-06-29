import processPage from "./functions/process-page";

// hook into retina option and force for pdfs
ph.api.hooks.addFilter("ph.api.options.retina", "ph-pdf-mockups", function(
  value,
  view
) {
  if (view.model.get("type") === "pdf") {
    value = true;
  }
  return value;
});

// hook into get featured media function and get our pdf media
ph.api.hooks.addAction(
  "ph.api.models.MockupImage.getFeaturedMedia",
  "ph-pdf-mockups",
  function(model) {
    // make sure we have an image
    if (!model.get("image")) {
      return;
    }
    // make sure it's a file
    if (model.get("image").get("media_type") != "file") {
      return;
    }

    // make sure there's a source url
    if (!model.get("image").get("source_url")) {
      return;
    }

    // set width stored in model (allow filtering)
    let width = ph.api.hooks.applyFilters(
      "ph.pdf.width",
      model.get("options").pdf_width || 1180,
      model
    );

    processPage(
      model.get("image").get("source_url"),
      model,
      width * 2 * model.get("magnification")
    );
  }
);

// hook into initialize
ph.api.hooks.addAction(
  "ph.api.models.MockupImage.initialize",
  "ph-pdf-mockups",
  function(model) {
    model.pdfListeners();
  }
);

import { supported, vexDialog } from "./browser-support";

window.phPdfSupported = _.once(function() {
  if (!supported()) {
    vexDialog();
  }
});

// extend mockup image model
_.extend(ph.api.models.MockupImage.prototype, {
  pdfListeners: function() {
    this.listenTo(
      this,
      "change:magnification",
      _.debounce(this.changePDFQuality, 1000)
    );

    window.phPdfSupported();
  },
  changePDFQuality: function() {
    if (this.get("image").get("media_type") != "file") {
      return;
    }

    // set width stored in model (allow filtering)
    let width = ph.api.hooks.applyFilters(
      "ph.pdf.width",
      this.get("options").pdf_width || 1180,
      this
    );

    processPage(
      this.get("image").get("source_url"),
      this,
      width * 2 * Math.min(this.get("magnification"), 5)
    );
  }
});

ph.api.hooks.addFilter("ph.image.width", "ph-pdf-mockups", function(
  width,
  model
) {
  if (model.get("type") == "pdf") {
    let pdfWidth = model.get("options").pdf_width || 1180;
    pdfWidth = pdfWidth * model.get("magnification");
    return pdfWidth * 2;
  }
  return width;
});
