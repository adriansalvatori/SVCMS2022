const { get } = ph.util;
import getUrl from "../functions/get-url";

/**
 * Sets a pdf page url as a thumbnail image using pdf.js
 * @param {object} instance
 */
export default function(image) {
  let attached = get(["_embedded", "wp:featuredmedia", 0], image);
  // bail if not a pdf
  if (
    get(["mime_type"], attached) != "application/pdf" &&
    get(["mime"], attached) != "application/pdf"
  ) {
    return;
  }

  // get source
  var url = get(["source_url"], attached) || get(["url"], attached);
  var page = get(["pdf_page"], image);

  if (!url) {
    console.log("Pdf url is not set", image);
  }
  if (!page) {
    console.log("Pdf page is not set", image);
  }

  let width = ph.hooks.applyFilters("ph.pdf.thumbnailwidth", 500, image);

  getUrl(url, page, width * 2).then(canvasURL => {
    image.$update({
      _mediaURL: canvasURL
    });
  });
}
