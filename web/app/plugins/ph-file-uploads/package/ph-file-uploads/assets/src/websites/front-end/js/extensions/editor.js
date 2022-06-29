const { extendComponent } = ph.components;
import Upload from "../components/UploadIcon.vue";

// add icon to editor
extendComponent("shared.editor", {
  mounted() {
    this.insertIntoSlot("footer-right-after", Upload, {
      props: {
        thread: this.thread
      },
      on: {
        upload: files => {
          this.$emit("upload", files);
        }
      }
    });
  }
});
