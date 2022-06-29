<template>
  <div class="ph-file-attachment-icon ph-cursor-pointer">
    <div class="ph-tooltip-wrap ph-add-file" @click.prevent="addFile">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        style="fill:none"
        width="16"
        height="16"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="feather feather-paperclip"
      >
        <path
          d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"
        />
      </svg>
      <div class="ph-tooltip">{{ __('Attach A File', 'project-huddle') }}</div>
    </div>

    <input
      ref="file"
      type="file"
      class="ph-file-input"
      :accept="accepts"
      @change="uploadImage"
      style="display: none;"
    />
  </div>
</template>

<script>
import _get from "lodash.get";

export default {
  computed: {
    accepts() {
      return PHF_Settings.types;
    }
  },

  methods: {
    addFile() {
      if (_get(this, "me.id")) {
        this.$refs.file.click();
      } else {
        ph.store.commit("ui/SET_DIALOG", {
          name: "register",
          success: this.addFile
        });
      }
    },

    uploadImage(e) {
      if (!this.$refs.file.files) {
        return;
      }

      this.$emit("upload", this.$refs.file.files);

      this.$refs.file.value = "";
    }
  }
};
</script>