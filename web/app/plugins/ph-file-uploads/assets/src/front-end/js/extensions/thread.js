/**
 * Extend the ThreadBody.vue component
 */
const { extendComponent } = ph.components;
import ThreadAttachments from "../components/ThreadAttachments.vue";

extendComponent("thread.body", {
  data() {
    return {
      attachments: [],
    };
  },

  mounted() {
    // add attachments on upload
    this.$refs.editor.$on("upload", this.uploadFile);

    // insert upload preview component
    this.insertComponent({
      ref: "form",
      component: ThreadAttachments,
      data: {
        // remember, this is not dynamic
        propsData: {
          id: this.thread.id,
        },
      },
      key: `test`,
    });

    // add attachment ids to new comment submit
    ph.hooks.addFilter(
      "ph_new_comment_data",
      "ph.file-uploads",
      (data, instance) => {
        data.attachment_ids = this.thread.attachment_ids;
        this.thread.$update({
          attachment_ids: [],
        });
        return data;
      }
    );
  },

  methods: {
    async uploadFile(files) {
      let { media } = await this.$store.dispatch("entities/insert", {
        entity: "media",
        data: {
          post: this.thread.id || 0,
          file: files[0],
        },
      });
      this.thread.$update({
        attachment_ids: [
          ...this.thread.attachment_ids,
          ..._.pluck(media, "id"),
        ],
      });
    },
  },
});
