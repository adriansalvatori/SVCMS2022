<template>
  <div class="ph-comment__attachment">
    <template v-if="loaded">
      <Attachment :media="media" :canDelete="canDelete" @delete="confirmTrash" />
    </template>
    <template v-else>
      <AttachmentLoading :progress="progress" />
    </template>
  </div>
</template>

<script>
import _get from "lodash.get";
import Attachment from "./Attachment.vue";
import AttachmentLoading from "./AttachmentLoading.vue";

export default {
  props: {
    id: Number | String,
    thread: Object
  },

  components: {
    Attachment,
    AttachmentLoading
  },

  data() {
    return {
      progress: 100
    };
  },

  mounted() {
    console.log(this.thread);
    if (!this.loaded) {
      this.upload();
    }
  },

  computed: {
    media() {
      // return this.$store.getters["entities/media/all"]();
      return this.$store.getters["entities/media/find"](this.id);
    },
    loaded() {
      return this.media && _.isNumber(this.media.id);
    },
    canDelete() {
      if (!this.loaded) {
        return false;
      }

      // let author delete
      if (
        _get(this, "$store.state.entities.users.me.id") == this.media.author
      ) {
        return true;
      }

      // let comment moderator delete
      if (
        _get(
          this,
          "$store.state.entities.users.me.capabilities.moderate_comments"
        )
      ) {
        return true;
      }

      return false;
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
      let link = _get(this.media, "_links.self[0].href");
      this.remove();

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
    },

    upload() {
      this.thread.$update({
        disabled: true
      });

      this.progress = 0;
      if (!this.media || !this.media.file) {
        return;
      }

      var formData = new FormData();

      let data = { file: this.media.file };
      if (this.media.post) {
        data.post = this.media.post;
      }

      _.each(data, function(value, key) {
        formData.append(key, value);
      });

      this.$http
        .post("/media", {
          data: formData,
          options: {
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            xhr: () => {
              // get the native XmlHttpRequest object
              var xhr = jQuery.ajaxSettings.xhr();
              // set the onprogress event handler
              xhr.upload.onprogress = event => {
                if (event.lengthComputable) {
                  // Trigger progress event on model for view updates
                  this.progress = (event.loaded / event.total) * 100;
                }
              };
              // set the onload event handler
              xhr.upload.onload = () => {
                this.progress = 100;
              };
              // return the customized object
              return xhr;
            }
          }
        })
        .then(([data]) => {
          this.progress = 100;

          this.$store
            .dispatch("entities/insertOrUpdate", {
              entity: "media",
              data
            })
            .then(() => {
              this.replace(data.id);
            });
        })
        .catch(err => {
          this.$error(err);
          this.media.$delete();
        })
        .finally(() => {
          this.thread.$update({
            disabled: false
          });
        });
    },

    replace(id) {
      let attachment_ids = _.without(this.thread.attachment_ids, this.media.id);
      attachment_ids.push(id);
      this.thread.$update({ attachment_ids }).then(() => {
        this.media.$delete();
      });
    },

    remove() {
      let attachment_ids = _.without(this.thread.attachment_ids, this.media.id);
      this.thread.$update({ attachment_ids }).then(() => {
        this.media.$delete();
      });
    }
  }
};
</script>