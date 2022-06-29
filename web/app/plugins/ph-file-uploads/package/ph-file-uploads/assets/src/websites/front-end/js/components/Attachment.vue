<template>
  <div class="ph-file-attachment-thumbnail">
    <div class="thumb-icon" :style="[url ? {'background-image': `url(${url})`} : {}]">
      <template v-if="progress < 100">
        <div class="ph-loading-image light">
          <div class="ph-loading-image-dots"></div>
        </div>
        <div class="ph-upload-progress">
          <div class="ph-upload-progress-indicator" :style="{width: `${progress}%`}"></div>
        </div>
      </template>

      <template v-else>
        <div
          class="ph-close-icon ph-tooltip-wrap"
          v-if="canDelete"
          @click.prevent="$emit('delete')"
        >
          <svg viewBox="0 0 512 512" id="ion-android-close" width="9" height="9">
            <!--prettyhtml-ignore-->
            <path d="M405 136.798L375.202 107 256 226.202 136.798 107 107 136.798 226.202 256 107 375.202 136.798 405 256 285.798 375.202 405 405 375.202 285.798 256z" />
          </svg>
          <div class="ph-tooltip">{{ __('Delete', 'project-huddle') }}</div>
        </div>

        <div class="ph-generic-attachment-icon" v-if="!url">{{ extension }}</div>

        <a :href="sourceUrl" :download="title" class="ph-download" target="_blank">
          <svg viewBox="0 0 512 512" id="ion-android-download" width="18" height="18" fill="#fff">
            <path
              d="M403.002 217.001C388.998 148.002 328.998 96 256 96c-57.998 0-107.998 32.998-132.998 81.001C63.002 183.002 16 233.998 16 296c0 65.996 53.999 120 120 120h260c55 0 100-45 100-100 0-52.998-40.996-96.001-92.998-98.999zM224 268v-76h64v76h68L256 368 156 268h68z"
            />
          </svg>
        </a>
      </template>
    </div>
    <div class="ph-attachment-title" :data-text="title || false">{{title}}</div>
  </div>
</template>

<script>
import _get from "lodash.get";
import fileExtension from "file-extension";

export default {
  props: {
    id: Number,
    media: Object,
    progress: Number,
    canDelete: Boolean
  },

  computed: {
    sourceUrl() {
      return _get(this, "media.source_url");
    },

    url() {
      return _get(this, "media.media_details.sizes.thumbnail.source_url");
    },

    extension() {
      return this.sourceUrl && fileExtension(this.sourceUrl);
    },

    title() {
      return _get(this, "media.title.rendered");
    }
  }
};
</script>