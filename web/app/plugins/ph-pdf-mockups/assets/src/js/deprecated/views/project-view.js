import PDFJS from "pdfjs-dist";
import newPdfForm from "../vue/new-pdf.vue";
import processNew from "../functions/process-new";

import { __ } from "@wordpress/i18n";

Huddle.ProjectView = Huddle.ProjectView.extend({
  dialogAccepted: false,

  initialize: function() {
    this.constructor.__super__.initialize.apply(this, arguments);

    // set media when iframe is selected
    this.listenTo(this, "selected", this.newPDFSelection);

    wp.hooks.addFilter(
      "ph.image-form-data",
      "ph-pdf-mockups",
      this.extraFields
    );
    wp.hooks.addFilter(
      "ph.file_frame.version.options",
      "ph-pdf-mockups",
      this.onlyPDFVersion
    );

    wp.hooks.addFilter("ph.file_frame.options", "ph-pdf-mockups", this.addPDFs);
  },

  /**
   * Add pdfs to allowed file types
   *
   * @param options
   * @param view
   * @param event
   * @returns {*}
   */
  addPDFs: function(options, view, event) {
    // make sure it's defined
    if (_.isUndefined(options.library.type)) {
      options.library.type = [];
    }

    // convert to array
    if (typeof options.library.type === "string") {
      options.library.type = [options.library.type];
    }

    // add our type
    options.library.type.push("application/pdf");

    return options;
  },

  /**
   * PDFs can only be replaced by new pdfs
   * @param options
   * @param view
   * @param event
   * @returns {*}
   */
  onlyPDFVersion: function(options, view, event) {
    if (view.model.get("type") === "pdf") {
      options.library = {
        type: "application/pdf"
      };
    }

    return options;
  },

  /**
   * Add extra fields to save if it's a pdf
   *
   * @param data
   * @param model
   * @returns {*}
   */
  extraFields: function(data, model) {
    if (model.get("type") === "pdf") {
      data.type = "pdf"; // store type
      data.pdf_width = model.get("pdf_width"); // store width
      data.pdf_page = model.get("pdf_page"); // store pdf page
    }

    return data;
  },

  /**
   * Handle new pdfs
   */
  newPDFSelection: function() {
    var selection = this.file_frame.state().get("selection");
    this.newPDFs = [];

    if (selection.length) {
      selection.map(attachment => {
        // bail for pdfs
        if (attachment.get("subtype") !== "pdf") {
          return;
        }
        this.newPDFs.push(attachment);
      });

      if (!this.newPDFs.length) {
        return;
      }

      // need to defer this or lockup will happen
      this.modal = new newPdfForm({
        el: document.createElement("div"),
        data: {
          programmatic: true,
          visible: true
        }
      });

      this.modal.$on("process", options => {
        this.processEach(options);
      });
    }
  },

  processEach: function(options) {
    _.each(this.newPDFs, element => {
      this.processPDF(element, options);
    });
  },

  processPDF: function(attachment, options) {
    PDFJS.getDocument(attachment.get("url"))
      .then(pdfDoc_ => {
        var pdfDoc = pdfDoc_;

        if (pdfDoc.numPages > 30 && !this.dialogAccepted) {
          Vue.prototype.$confirm(
            __(
              "Your PDF is over 30 pages. The page may timeout due to the large amount of images being saved. Please break up your pdf document into smaller parts.",
              "project-huddle"
            ),
            __("Just a reminder", "project-huddle"),
            {
              cancelButtonText: __("Cancel", "project-huddle"),
              confirmButtonText: __("Proceed", "project-huddle"),
              callback: action => {
                if (action === "confirm") {
                  this.dialogAccepted = true;
                  this.processPDF(attachment, options);
                }
              }
            }
          );
          return;
        }

        // return a promise when all pages are added
        return new Promise((resolve, reject) => {
          var promises = [];
          var collectionpage = {};
          for (let i = 1; i <= pdfDoc.numPages; i++) {
            var title = attachment.get("title") + " - " + i;
            title = i < 1 ? title + " - " + i : title;

            // add image to collection and render
            collectionpage[i] = {
              featured_media: attachment.get("id"),
              title: {
                rendered: attachment.get("title")
              },
              type: "pdf",
              pdf_page: i,
              options: {
                alignment: options.alignment, // left, right or center
                size: options.size, // normal or scale
                background_color: options.background_color // hex value
              },
              _embedded: {
                "wp:featuredmedia": [attachment.toJSON()]
              }
            };

            return;

            pdfDoc.getPage(i).then(page => {
              var pageNumber = i;

              // calculate width
              var originalViewport = page.getViewport(1);
              var scale = 500 / originalViewport.width;
              var viewport = page.getViewport(scale);

              //
              // Prepare canvas using PDF page dimensions
              //
              var canvas = jQuery("<canvas/>")[0];
              var context = canvas.getContext("2d");
              canvas.height = viewport.height;
              canvas.width = viewport.width;

              //
              // Render PDF page into canvas context
              //
              var render = {};
              render.pageNumber = page.render({
                canvasContext: context,
                viewport: viewport
              });

              // after render, save as attachment.
              render.pageNumber.then(() => {
                var dataURL = canvas.toDataURL();
                // add image to collection and render
                this.model
                  .get("images")
                  .get(collectionpage[pageNumber])
                  .set({
                    url: dataURL,
                    pdf_width: parseInt(options.width ? options.width : 1180)
                  });

                promises.push(this);

                // once we do all promises, resolve
                if (promises.length >= pdfDoc.numPages) {
                  resolve(true);
                }
              });
            });
          }
        });
      })
      .then(() => {
        jQuery("#publish").removeAttr("disabled");
        jQuery("#publishing-action")
          .find(".spinner")
          .removeClass("is-active");
      })
      .catch(function(error) {
        Vue.prototype.$alert(
          __(
            "There was an error parsing the pdf file. Please resave the file and try again.",
            "project-huddle"
          ),
          __("There was a problem!", "project-huddle"),
          {
            confirmButtonText: __("OK", "project-huddle")
          }
        );
        console.error(error);
      });
  }
});
