<template>
  <div class="ph-comment__attachment">
    <Attachment v-if="media" :media="media" :canDelete="canDelete" @delete="confirmTrash" />
    <AttachmentLoading v-else />
  </div>
</template>

<script>
import _get from "lodash.get";
import Attachment from "./Attachment.vue";
import AttachmentLoading from "./AttachmentLoading.vue";

export default {
  props: {
    id: Number,
    comment: Object
  },

  components: {
    Attachment,
    AttachmentLoading
  },

  computed: {
    media() {
      return this.$store.getters["entities/media/find"](this.id);
    },

    isAuthor() {
      return (
        _get(this, "$store.state.entities.users.me.id") ==
        _get(this, "media.author")
      );
    },

    canModerate() {
      return _get(
        this,
        "$store.state.entities.users.me.capabilities.moderate_comments"
      );
    },

    canDelete() {
      return this.isAuthor || this.canModerate;
    }
  },

  methods: {
    confirmTrash() {
      this.$store.commit("ui/SET_DIALOG", {
        name: "dialog",
        title: this.__("Permanently delete this upload?", "project-huddle"),
        message: `<p>${this.__(
          "Are you sure you want to delete this file? This is permanent.",
          "project-huddle"
        )}</p>`,
        success: this.trash
      });
    },

    trash() {
      // remove from comment item
      this.comment.$dispatch("update", {
        where: this.comment.id,
        data: comment => {
          // TODO: Bug - this is not updating unless we do this method
          this.$delete(
            comment.attachment_ids,
            _.indexOf(comment.attachment_ids, this.media.id)
          );
        }
      });

      // perhaps tied to bug above?
      this.$parent.$forceUpdate();

      // save
      this.comment.$dispatch("patch", {
        comment: this.comment,
        data: {
          attachment_ids:
            this.comment.attachment_ids.length === 0
              ? ""
              : this.comment.attachment_ids
        }
      });

      // cache
      let attachment = this.media;
      let link = _get(this.media, "_links.self[0].href");

      // delete
      link &&
        this.$http
          .delete(link, {
            params: {
              force: true
            }
          })
          .then(([data]) => {
            this.$notification({
              text: this.__("Attachment deleted.", "project-huddle"),
              duration: 5000
            });
          })
          .catch(err => {
            this.$store.dispatch("entities/insert", {
              entity: "media",
              data: attachment
            }); //revert
            this.$error(err);
          });
    }
  }
};
</script>