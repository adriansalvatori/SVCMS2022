import processNew from "../functions/process-new";
import newPdfForm from "../vue/new-pdf.vue";

export default function(selection, vue) {
  let newPDFs = [];

  if (selection.length) {
    selection.map(attachment => {
      // bail for pdfs
      if (attachment.get("subtype") !== "pdf") {
        return;
      }
      newPDFs.push(attachment);
    });

    if (!newPDFs.length) {
      return;
    }

    console.log("launch");
    // display a form to set all pdf display options
    let modal = new newPdfForm({
      el: document.createElement("div"),
      data: {
        programmatic: true,
        visible: true
      }
    });

    let processed = 0;

    // when modal process is clicked, process each pdf file with options
    modal.$on("process", options => {
      let fullLoading = Vue.prototype.$loading({
        fullscreen: false,
        target: "#ph-project-gallery"
      });
      _.each(newPDFs, element => {
        processNew(element, options).then(data => {
          // stop loading when all are processed
          if (processed++ == newPDFs.length - 1) {
            fullLoading.close();
          }
          console.log(data.collection);
          ph.models.Image.insert({
            data: data.collection
          });
        });
      });
    });
  }
}
