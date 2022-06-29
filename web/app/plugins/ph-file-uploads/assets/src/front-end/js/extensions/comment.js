const { extendComponent } = ph.components;
import Attachments from "../components/CommentAttachments.vue";
import _get from "lodash.get";

extendComponent("shared.comment", {
  mounted() {
    // insert upload preview component
    this.insertComponent({
      ref: "content",
      component: Attachments,
      data: {
        propsData: {
          comment: this.comment,
        },
      },
      key: `attachments-${this.comment.id}`,
    });

    // fetch attachments!
    if (this.comment.attachment_ids.length) {
      this.$http
        .get("/media/", {
          params: {
            include: this.comment.attachment_ids,
          },
        })
        .then(([data]) => {
          _.each(data, (attachment) => {
            attachment.post = 0;
          });
          this.$store.dispatch("entities/insertOrUpdate", {
            entity: "media",
            data,
          });
        });
    }
  },
});
