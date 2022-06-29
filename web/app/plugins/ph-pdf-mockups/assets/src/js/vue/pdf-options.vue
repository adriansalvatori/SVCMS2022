<template>
  <div class="option-form-row" slot="form">
    <el-form-item>
      <h3 class="ph-label">PDF Initial Display Width</h3>
      <p>Set the initial width of your pdf in pixels.</p>
      <el-slider v-model="options.pdf_width" :step="10" :min="0" :max="5000" show-input></el-slider>
      <div class="form-right">
        <el-button type="text" class="muted" @click="setDefault">Reset to Default</el-button>
      </div>
    </el-form-item>
  </div>
</template>

<script>
import { Button, FormItem, Slider } from "element-ui";

Vue.component(Button.name, Button);
Vue.component(FormItem.name, FormItem);
Vue.component(Slider.name, Slider);

export default {
  data() {
    return {
      originalWidth: 1180
    };
  },

  props: {
    options: {
      type: Object,
      default: PH.image_defaults
    }
  },

  mounted() {
    console.log("inserted");
  },

  watch: {
    "options.pdf_width"(width) {
      this.$emit("update", width);
    }
  },

  methods: {
    setDefault() {
      this.options.pdf_width = this.originalWidth;
    }
  }
};
</script>