/**
 * Only pdfs allowed for new versions
 */
export default function(options, instance) {
  // make sure it's defined
  if (_.isUndefined(options.library.type)) {
    options.library.type = [];
  }
  // new pdfs only for new versions
  if (instance.image.type == "pdf") {
    options.library.type = "application/pdf";
  }
  return options;
}
