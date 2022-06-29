<template>
  <div class="ph-file-attachment-thumbnail">
    <div class="thumb-icon">
      <div class="ph-loading-image light">
        <div class="ph-loading-image-dots"></div>
      </div>
      <div class="ph-upload-progress">
        <div class="ph-upload-progress-indicator" :style="{width: `${progress}%`}"></div>
      </div>
    </div>
    <div class="ph-attachment-title"></div>
  </div>
</template>

<script>
export default {
  props: {
    progress: Number
  }
};
</script>