<template>
  <div
    class="ph-attachment-container"
    style="margin-bottom: 0;"
    v-if="thread && thread.attachment_ids.length"
  >
    <ThreadAttachment v-for="id in thread.attachment_ids" :key="id" :id="id" :thread="thread" />
  </div>
</template>

<script>
import ThreadAttachment from "./ThreadAttachment.vue";
import _get from "lodash.get";

export default {
  name: "threadAttachment",

  components: {
    ThreadAttachment
  },

  props: {
    id: Number | String
  },

  computed: {
    thread() {
      let getter =
        this.$store?.getters?.["entities/mockup-threads/find"] ||
        this.$store?.getters?.["entities/website-threads/find"];
      return getter ? getter(this.id) : {};
    }
  }
};
</script>