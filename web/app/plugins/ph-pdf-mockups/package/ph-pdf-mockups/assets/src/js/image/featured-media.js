import processPage from "../functions/process-page";
const { get } = ph.util;

/**
 * Sets a pdf page url as a thumbnail image using pdf.js
 * @param {image model} image
 */
export default function(image) {
  // bail if not our type
  if (
    get(["image", "mime_type"], image) != "application/pdf" &&
    get(["mime"], image.attached_media) != "application/pdf"
  ) {
    return;
  }

  // get source
  var url = get(["image", "source_url"], image) || get(["image", "url"], image);

  window.phPdfSupported();

  let width = ph.hooks.applyFilters("ph.pdf.thumbnailwidth", 500, image);

  processPage(url, image, width * 2);
}
