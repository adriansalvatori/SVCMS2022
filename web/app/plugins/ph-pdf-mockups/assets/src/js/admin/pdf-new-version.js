import PDFJS from "pdfjs-dist";
PDFJS.GlobalWorkerOptions.workerSrc = phPdf.workerSrc;
import { __ } from "@wordpress/i18n";
import getDoc from "../functions/get-doc";

export default function(selection, instance) {
  if (!instance.image || instance.image.type !== "pdf") {
    return;
  }

  // make sure it's a pdf file, if so, launch modal
  if (selection.length) {
    selection.map((attachment) => {
      // bail for pdfs
      if (attachment.get("subtype") !== "pdf") {
        return;
      }

      instance
        .$confirm(
          "Do you want to resolve all exising comments on the pdf?",
          "Warning",
          {
            confirmButtonText: "Yes",
            cancelButtonText: "No",
          }
        )
        .then(() => {
          processPDFVersion(attachment, PH?.image_defaults, true, instance);
        })
        .catch(() => {
          processPDFVersion(attachment, PH?.image_defaults, false, instance);
        });

      return false;
    });
  }
}

export async function processPDFVersion(
  attachment,
  display_options,
  resolved,
  instance
) {
  var featured_media = instance.image.featured_media;
  if (!featured_media) {
    ph.vue.prototype.$alert(
      __(
        "There is an invalid media ID. Please contact support for assistance!",
        "project-huddle"
      ),
      __("Something went wrong.", "project-huddle")
    );
  }

  let pdfDoc = await getDoc(attachment.get("url"));

  var total = ph.models.Image.query()
    .where((image) => {
      return image.featured_media == featured_media;
    })
    .count();

  // show notice if new pdf is different number of pages
  if (total !== pdfDoc.numPages) {
    ph.vue.prototype.$alert(
      __(
        "The new pdf version contains a different number of pages than the previous version. Extra pages are ignored to prevent issues with pages being addded or deleted.",
        "project-huddle"
      ),
      __("Just a reminder...", "project-huddle"),
      {
        confirmButtonText: __("OK", "project-huddle"),
      }
    );
  }

  for (let i = 1; i <= pdfDoc.numPages; i++) {
    var title = attachment.get("title") + " - " + i;
    title = i < 1 ? title + " - " + i : title;

    // get item with same featured media and page in collection
    let item = ph.models.Image.query()
      .where((image) => {
        return image.featured_media == featured_media && image.pdf_page == i;
      })
      .first();

    // maybe it was deleted
    if (!item || !item.id) {
      continue;
    }

    item.$update({
      url: "",
      featured_media: attachment.get("id"),
      type: "pdf",
      _mediaURL: "",
      pdf_page: i,
      resolved: resolved,
      version: true,
      title: {
        raw: title,
        rendered: title,
      },
      options: display_options,
      _embedded: {
        "wp:featuredmedia": [attachment.toJSON()],
      },
    });
  }

  jQuery("#publish").removeAttr("disabled");
}
