export default function(options) {
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
}
