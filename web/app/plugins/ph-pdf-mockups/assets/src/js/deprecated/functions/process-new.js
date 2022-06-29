import PDFJS from "pdfjs-dist";
PDFJS.GlobalWorkerOptions.workerSrc = phPdf.workerSrc;
import { __ } from "@wordpress/i18n";

/**
 * Process PDF at a specific size
 *
 * @param {object} attachment Attachment object
 * @param {object} attrs Attributes to set
 */
export default function(attachment, options) {
  return new Promise((resolve, reject) => {
    PDFJS.getDocument(attachment.get("url"))
      .then(pdfDoc_ => {
        var pdfDoc = pdfDoc_;
        if (pdfDoc.numPages > 30) {
          Vue.prototype.$alert(
            __(
              "This pdf document contains a large number of pages. It may fail when saving if you don't have enough server resources. You may want to break the pdf file up into separate files.",
              "project-huddle"
            ),
            __("Just a reminder", "project-huddle"),
            {
              confirmButtonText: __("OK", "project-huddle")
            }
          );
        }
        return processPages(pdfDoc, attachment, options);
      })
      .then(data => {
        resolve(data);
      })
      .catch(function(error) {
        alert(
          "There was an error parsing the pdf file. Please resave the file and try again."
        );
        console.error(error);
      });
  });
}

export function processPages(pdfDoc, attachment, options) {
  return new Promise((resolve, reject) => {
    var collectionpage = [];

    for (let i = 1; i <= pdfDoc.numPages; i++) {
      collectionpage[i] = {
        featured_media: attachment.get("id"),
        title: {
          rendered:
            i > 1
              ? attachment.get("title") + " - " + i
              : attachment.get("title")
        },
        type: "pdf",
        pdf_page: i,
        options: options,
        _embedded: {
          "wp:featuredmedia": [attachment.toJSON()]
        }
      };
    }

    resolve({
      collection: collectionpage,
      doc: pdfDoc
    });
  });
}
