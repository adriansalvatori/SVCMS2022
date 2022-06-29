import PDFJS from "pdfjs-dist";
import processPDF from "./process";

// Get pdf.js worker
PDFJS.GlobalWorkerOptions.workerSrc = phPdf.workerSrc;

/**
 * Processes the pdf page
 *
 * @param {string} source_url Source URL for the pdf
 * @param {object} model Image Model
 * @param {integer} size Width
 */
export default function(source_url, model, size = 500) {
  // store fetchd pdf in window to prevent duplicate fetches
  window.phPdfFetched = window.phPdfFetched || {};

  if (!window.phPdfFetched[source_url]) {
    PDFJS.getDocument(source_url).then(pdfDoc => {
      window.phPdfFetched[source_url] = pdfDoc;
      processPDF(pdfDoc, model, size);
    });
  } else {
    processPDF(window.phPdfFetched[source_url], model, size);
  }
}
