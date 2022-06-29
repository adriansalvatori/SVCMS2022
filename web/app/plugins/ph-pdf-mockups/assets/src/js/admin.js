// process pdf page as featured media
import "./global/image";

/**
 * Allow pdfs in file frame
 */
import fileFrame from "./admin/pdf-file-frame";
ph.hooks.addFilter("ph.file_frame.options", "ph-pdf-mockups", fileFrame);

/**
 * Only pdfs allowed for new versions
 */
import newVersionType from "./admin/pdf-new-version-type";
ph.hooks.addFilter(
  "ph.file_frame.newVersion.options",
  "ph-pdf-mockups",
  newVersionType
);

// add extra fields to save
import saveFields from "./admin/pdf-save-fields";
ph.hooks.addFilter("ph.image.formData", "ph-pdf-mockups", saveFields);

// handle new selected pdf
import selectedPDF from "./admin/pdf-new-selected";
ph.hooks.addAction("ph.image.new.selected", "ph-pdf-mockups", selectedPDF);

/**
 * Process new version selected
 */
import newVersion from "./admin/pdf-new-version";
ph.hooks.addAction(
  "ph.image.newVersion.selected",
  "ph-pdf-mockups",
  newVersion
);
