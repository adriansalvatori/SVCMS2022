/**
 * Process PDF at a specific size
 *
 * @param {object} pdfDoc PDF.js object
 * @param {object} model Backbone image model
 * @param {integer} width Width of the image
 */
import get from "../functions/get";

export default function(pdfDoc, model, width = 500) {
  // process vue or backbone model
  let page =
    get(["$attrs", "pdf_page"], model) ||
    get(["attributes", "pdf_page"], model) ||
    1;

  // get page, then render thumbnail
  pdfDoc.getPage(page).then(page => {
    // calculate width
    var originalViewport = page.getViewport(1);
    var scale = width / originalViewport.width;
    var viewport = page.getViewport(scale);

    // thumb width
    var thumbScale = 500 / originalViewport.width;
    var thumbViewport = page.getViewport(thumbScale);

    // full size
    var canvas = jQuery("<canvas/>")[0];
    var context = canvas.getContext("2d");
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    // thumbnail size
    var thumbCanvas = jQuery("<canvas/>")[0];
    var thumbContext = thumbCanvas.getContext("2d");
    thumbCanvas.height = thumbViewport.height;
    thumbCanvas.width = thumbViewport.width;

    // full size
    var render = {};
    render.pageNumber = page.render({
      canvasContext: context,
      viewport: viewport
    });

    // thumbnail size
    var renderThumb = {};
    renderThumb.pageNumber = page.render({
      canvasContext: thumbContext,
      viewport: thumbViewport
    });

    // after render, save as attachment.
    render.pageNumber.then(() => {
      if (typeof model.set === "function") {
        model.set("url", canvas.toDataURL());
      } else {
        model.url = canvas.toDataURL();
      }
      canvas.remove();
    });

    // after render, save as attachment.
    renderThumb.pageNumber.then(() => {
      if (typeof model.set === "function") {
        model.set("thumbnail", thumbCanvas.toDataURL());
      } else {
        model.url = thumbCanvas.toDataURL();
      }
      thumbCanvas.remove();
    });
  });
}
