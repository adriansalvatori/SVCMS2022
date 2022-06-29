// process pdf page as featured media
import getDoc from "../functions/get-doc";

const { extendComponent } = ph.components;

extendComponent("mockup.image", {
  data() {
    return {
      pdfRendering: false,
    };
  },
  watch: {
    load(val) {
      val && this.renderPdf();
    },
    "image._embedded": {
      deep: true,
      handler(val, prev) {
        if (
          val?.["wp:featuredmedia"]?.[0]?.id &&
          val?.["wp:featuredmedia"]?.[0]?.id !==
            prev?.["wp:featuredmedia"]?.[0]?.id
        ) {
          this.image.canvas = null;
          this.renderPdf();
        }
      },
    },
  },

  methods: {
    async renderPdf() {
      let page;
      if (
        this.image?._embedded?.["wp:featuredmedia"]?.[0]?.mime_type !==
          "application/pdf" &&
        this.image?._embedded?.["wp:featuredmedia"]?.[0]?.mime !==
          "application/pdf"
      ) {
        return;
      }
      if (this.pdfRendering) {
        return;
      }
      this.pdfRendering = true;
      this.$emit("update:loaded", false);

      let url =
        this.image?._embedded?.["wp:featuredmedia"]?.[0]?.source_url ||
        this.image?._embedded?.["wp:featuredmedia"]?.[0]?.url;

      if (!this.image.canvas && url) {
        page = await getDoc(url, this.image.pdf_page);

        if (_.isFunction(this.image.$update) && this.image.id) {
          await this.image.$update({
            canvas: page,
            retina: true,
          });
        } else {
          this.image.canvas = page;
          this.image.retina = true;
        }
      } else {
        page = this.image.canvas;
      }

      if (!page) {
        return;
      }

      let size =
        this.size === "thumb" ? 200 : this.image?.options?.pdf_width || 1180;

      size = size * window.devicePixelRatio;
      var originalViewport = page.getViewport({ scale: 1 });
      var scale = size / originalViewport.width;
      var viewport = page.getViewport({ scale });

      var canvas = this.$refs.canvas;
      console.log(this);
      var context = canvas.getContext("2d");
      canvas.height = viewport.height;
      canvas.width = viewport.width;

      var renderTask = page.render({
        canvasContext: context,
        viewport: viewport,
      });

      renderTask.promise.then(() => {
        this.$emit("update:loaded", true);
        this.pdfRendering = false;
      });
    },
  },
});

ph.hooks.addFilter("ph.image.width", "ph-pdf-mockups", function(width, model) {
  if (model?.type === "pdf") {
    let pdfWidth = model?.options?.pdf_width || 1180;
    return pdfWidth;
  }
  return width;
});
