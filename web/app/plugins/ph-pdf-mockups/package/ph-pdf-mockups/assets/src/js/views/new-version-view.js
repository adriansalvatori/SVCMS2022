import PDFJS from "pdfjs-dist";
import newPdfForm from "../vue/new-pdf.vue";
import { __ } from "@wordpress/i18n";

Huddle.NewVersionMenuItemView = Huddle.NewVersionMenuItemView.extend({
  initialize: function() {
    this.constructor.__super__.initialize.apply(this, arguments);

    this.listenTo(this, "selected:newVersion", this.newPDFVersion);
  },

  /**
   * Handle new pdfs
   */
  newPDFVersion: function() {
    var selection = this.version_file.state().get("selection");
    this.newPDFVersion = [];

    if (selection.length) {
      selection.map(attachment => {
        // bail for pdfs
        if (attachment.get("subtype") !== "pdf") {
          return;
        }

        // need to defer this or lockup will happen
        this.modal = new newPdfForm({
          el: document.createElement("div"),
          data: {
            programmatic: true,
            visible: true,
            version: true
          }
        });

        this.modal.$on("process", options => {
          this.processPDFVersion(attachment, options);
        });

        return false;
      });
    }
  },

  processPDFVersion: function(attachment, options) {
    var featured_media = this.model.get("featured_media");

    if (!featured_media) {
      ph.vue.prototype.$alert(
        __(
          "There is an invalid media ID. Please contact support for assistance!",
          "project-huddle"
        ),
        __("Something went wrong.", "project-huddle")
      );
    }

    PDFJS.getDocument(attachment.get("url"))
      .then(pdfDoc_ => {
        var pdfDoc = pdfDoc_;

        // return a promise when all pages are added
        return new Promise((resolve, reject) => {
          var existingPages = 0;
          var collectionpage = {};
          for (let i = 1; i <= pdfDoc.numPages; i++) {
            // get existing page
            collectionpage[i] = this.model.collection.findWhere({
              featured_media: featured_media,
              pdf_page: i
            });

            var title = attachment.get("title") + " - " + i;
            title = i < 1 ? title + " - " + i : title;

            collectionpage[i] &&
              collectionpage[i].set({
                image: new wp.api.models.Media(attachment.toJSON()),
                url: "",
                featured_media: attachment.get("id"),
                type: "pdf",
                pdf_page: i,
                pdf_width: parseInt(options.width ? options.width : 1180),
                resolved: options.resolve,
                version: true,
                title: {
                  raw: title,
                  rendered: title
                },
                options: {
                  alignment: options.alignment, // left, right or center
                  size: options.size, // normal or scale
                  background_color: options.background_color // hex value
                }
              });

            existingPages++;
          }

          resolve(existingPages.length === pdfDoc.numPages);
        });
      })
      .then(resolved => {
        if (!resolved) {
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
        jQuery("#publish").removeAttr("disabled");
      });
  }
});
