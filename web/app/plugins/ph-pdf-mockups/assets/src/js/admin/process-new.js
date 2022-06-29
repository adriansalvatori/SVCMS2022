import { __ } from "@wordpress/i18n";
import getDoc from "../functions/get-doc";

/**
 * Process PDF at a specific size
 *
 * @param {object} attachment Attachment object
 * @param {object} attrs Attributes to set
 */
export default async function(attachment, options) {
  // get pdf doc
  let pdfDoc = await getDoc(attachment.get("url"));

  if (pdfDoc.numPages > 30) {
    Vue.prototype.$alert(
      __(
        "This pdf document contains a large number of pages. It may fail when saving if you don't have enough server resources. You may want to break the pdf file up into separate files.",
        "project-huddle"
      ),
      __("Just a reminder", "project-huddle"),
      {
        confirmButtonText: __("OK", "project-huddle"),
      }
    );
  }

  try {
    let data = await processPages(pdfDoc, attachment, options);
    return data;
  } catch (e) {
    alert(
      "There was an error parsing the pdf file. Please resave the file and try again."
    );
    console.error(error);
  }
}

export async function processPages(doc, attachment, options) {
  var collection = [];

  for (let i = 1; i <= doc.numPages; i++) {
    collection.push({
      featured_media: attachment.get("id"),
      // dynamic title
      title: {
        rendered:
          i > 1 ? attachment.get("title") + " - " + i : attachment.get("title"),
      },
      type: "pdf",
      pdf_page: i,
      options,
      _embedded: {
        "wp:featuredmedia": [attachment.toJSON()],
      },
    });
  }

  return {
    collection,
    doc,
  };
}
