import processNew from "./process-new";
import newPdfForm from "../vue/new-pdf.vue";

export default function(selection, Vue) {
  let newPDFs = [];

  console.log("new");

  if (selection.length) {
    selection.map((attachment) => {
      // bail for pdfs
      if (attachment.get("subtype") !== "pdf") {
        return;
      }
      newPDFs.push(attachment);
    });

    if (!newPDFs.length) {
      return;
    }

    // when modal process is clicked, process each pdf file with options
    _.each(newPDFs, (element) => {
      processNew(element, PH?.image_defaults).then((data) => {
        ph.models.Image.insert({
          data: data.collection,
        });
      });
    });
  }
}
