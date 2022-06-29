import newPdfForm from "../vue/new-pdf.vue";
import PDFJS from "pdfjs-dist";
PDFJS.GlobalWorkerOptions.workerSrc = phPdf.workerSrc;
import { __ } from "@wordpress/i18n";

export default function(selection, instance) {
  if (!instance.image || instance.image.type !== "pdf") {
    return;
  }

  // make sure it's a pdf file, if so, launch modal
  if (selection.length) {
    selection.map(attachment => {
      // bail for pdfs
      if (attachment.get("subtype") !== "pdf") {
        return;
      }

      // need to defer this or lockup will happen
      let modal = new newPdfForm({
        el: document.createElement("div"),
        data: {
          programmatic: true,
          version: true
        }
      });

      modal.$on("process", (display_options, resolve) => {
        processPDFVersion(attachment, display_options, resolve, instance);
      });

      return false;
    });
  }
}

export function processPDFVersion(
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

  let fullLoading = Vue.prototype.$loading({
    fullscreen: true
  });

  // attachment is a backbone model from WordPress
  PDFJS.getDocument(attachment.get("url"))
    .then(pdfDoc_ => {
      var pdfDoc = pdfDoc_;

      // return a promise when all pages are added
      return new Promise((resolve, reject) => {
        var collectionpage = {};

        var total = ph.models.Image.query()
          .where(image => {
            return image.featured_media == featured_media;
          })
          .get();

        // show notice if new pdf is different number of pages
        if (total.length !== pdfDoc.numPages) {
          ph.vue.prototype.$alert(
            __(
              "The new pdf version contains a different number of pages than the previous version. Extra pages are ignored to prevent issues with pages being addded or deleted.",
              "project-huddle"
            ),
            __("Just a reminder...", "project-huddle"),
            {
              confirmButtonText: __("OK", "project-huddle")
            }
          );
        }

        for (let i = 1; i <= pdfDoc.numPages; i++) {
          var title = attachment.get("title") + " - " + i;
          title = i < 1 ? title + " - " + i : title;

          // get item with same featured media and page in collection
          collectionpage[i] = ph.models.Image.query()
            .where(image => {
              return (
                image.featured_media == featured_media && image.pdf_page == i
              );
            })
            .get();

          // maybe it was deleted
          if (!collectionpage[i].length) {
            continue;
          }

          // update that item
          ph.models.Image.update({
            where: collectionpage[i][0].$id,
            data: {
              url: "",
              featured_media: attachment.get("id"),
              type: "pdf",
              _mediaURL: "",
              pdf_page: i,
              resolved: resolved,
              version: true,
              title: {
                raw: title,
                rendered: title
              },
              options: display_options,
              _embedded: {
                "wp:featuredmedia": [attachment.toJSON()]
              }
            }
          });
        }

        resolve();
      });
    })
    .then(resolved => {
      fullLoading.close();
      jQuery("#publish").removeAttr("disabled");
    });
}
