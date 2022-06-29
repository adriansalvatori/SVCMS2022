import PDFJS from "pdfjs-dist";

// Get pdf.js worker
console.log(phPdf);
PDFJS.GlobalWorkerOptions.workerSrc = phPdf.workerSrc;

/**
 * Processes the pdf page
 *
 * @param {string} source_url Source URL for the pdf
 * @param {object} model Image Model
 * @param {integer} size Width
 */
export default function(source_url, page, width = 500) {
  // store fetchd pdf in window to prevent duplicate fetches
  window.phPdfFetched = window.phPdfFetched || {};

  return new Promise((resolve, reject) => {
    if (!window.phPdfFetched[source_url]) {
      PDFJS.getDocument(source_url).then((_pdfDoc) => {
        const pdfDoc = _pdfDoc;
        window.phPdfFetched[source_url] = pdfDoc;

        renderUrl(pdfDoc, page, width).then((url) => {
          resolve(url);
        });
      });
    } else {
      renderUrl(window.phPdfFetched[source_url], page, width).then((url) => {
        resolve(url);
      });
    }
  });
}

/**
 *
 * @param {object} pdfDoc pdf.js document
 * @param {integer} page Page number
 * @param {integer} width Width in pixels to render
 */
export function renderUrl(pdfDoc, page, width) {
  return new Promise((resolve, reject) => {
    // get page, then render export url
    pdfDoc.getPage(page).then((page) => {
      // calculate width
      var originalViewport = page.getViewport(1);
      var scale = width / originalViewport.width;
      var viewport = page.getViewport(scale);

      // prepare dummy canvas using PDF page dimensions
      var canvas = jQuery("<canvas/>")[0];
      var context = canvas.getContext("2d");
      canvas.height = viewport.height;
      canvas.width = viewport.width;

      // Render PDF page into canvas context
      var render = {};
      render.pageNumber = page.render({
        canvasContext: context,
        viewport: viewport,
      });

      // after render, save as attachment.
      render.pageNumber.then(() => {
        let url = canvas.toDataURL();
        canvas.remove();
        resolve(url);
      });
    });
  });
}
