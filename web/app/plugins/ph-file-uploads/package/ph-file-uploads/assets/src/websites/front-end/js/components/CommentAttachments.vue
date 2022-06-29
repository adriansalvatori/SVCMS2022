<template>
  <div class="ph-comment-attachment-container">
    <CommentAttachment
      class="ph-comment__attachment"
      v-for="id in attachment_ids"
      :key="id"
      :id="id"
      :comment="comment"
    />
  </div>
</template>

<script>
import CommentAttachment from "./CommentAttachment.vue";

export default {
  components: {
    CommentAttachment
  },
  props: {
    comment: Object
  },
  computed: {
    attachment_ids() {
      return this.comment.attachment_ids;
    }
  }
};
</script>