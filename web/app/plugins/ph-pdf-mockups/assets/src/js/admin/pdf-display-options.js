import pdfOptions from "../vue/pdf-options.vue";

export default function(instance, id) {
  // bail if not pdf
  if (instance?.image?.type !== "pdf") {
    return;
  }

  // force integer since it's a nested object
  instance.options.pdf_width = parseInt(instance.options.pdf_width);
  instance.form.pdf_width = parseInt(instance.form.pdf_width);

  // push our component to the form slot
  (instance.$slots.form || (instance.$slots.form = [])).push(
    instance.$createElement(pdfOptions, {
      // pass local options from display
      props: {
        form: instance.form,
      },
      // update local options when component updates
      on: {
        update: (width) => {
          instance.form.pdf_width = width;
        },
      },
    })
  );
}
