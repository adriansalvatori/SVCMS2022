import processNew from "../functions/process-new";

export default function(selection, vue) {
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

    let processed = 0;

    // when modal process is clicked, process each pdf file with options

    let fullLoading = Vue.prototype.$loading({
      fullscreen: false,
      target: "#ph-project-gallery",
    });
    _.each(newPDFs, (element) => {
      processNew(element, options).then((data) => {
        // stop loading when all are processed
        if (processed++ == newPDFs.length - 1) {
          fullLoading.close();
        }
        ph.models.Image.insert({
          data: data.collection,
        });
      });
    });
  }
}
