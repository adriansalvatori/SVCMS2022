export default function(data, image) {
  if (image.type === "pdf") {
    _.extend(data, {
      type: "pdf",
      pdf_width: image.pdf_width,
      pdf_page: image.pdf_page
    });
  }

  return data;
}
