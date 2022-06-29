<template>
  <div>
    <ph-display-options title="PDF Options" dialog="true" version="true">
      <div class="option-form-row" slot="form" slot-scope="dialog">
        <el-form-item>
          <h3 class="ph-label">PDF Initial Display Width</h3>
          <p>Set the width of your pdf pages in pixels. This width ignores the retina option.</p>
          <el-slider
            v-model="dialog.localOptions.pdf_width"
            :step="10"
            :min="380"
            :max="5000"
            show-input
          ></el-slider>
        </el-form-item>
      </div>

      <el-row
        slot="form-bottom"
        type="flex"
        class="option-form-row"
        justify="space-between"
        v-if="version"
      >
        <el-col :span="10">
          <h3 class="ph-label">Resolve Conversations</h3>
          <p>Resolve all the conversations on this pdf file.</p>
        </el-col>
        <el-col :span="14" class="form-right">
          <el-tooltip
            :content="resolve ? 'Resolve All Conversations' : 'Don\'t resolve converations'"
            placement="top"
          >
            <el-switch v-model="resolve"></el-switch>
          </el-tooltip>
        </el-col>
      </el-row>

      <div slot="footer" slot-scope="options">
        <el-button @click="visible = false">Cancel</el-button>
        <el-button type="primary" @click="processPDF(options)">Insert PDF</el-button>
      </div>
    </ph-display-options>
  </div>
</template>

<script>
import { __ } from "@wordpress/i18n";

export default Vue.extend({
  data: function() {
    return {
      programmatic: false,
      resolve: false,
      version: false
    };
  },

  beforeMount() {
    this.programmatic && document.body.appendChild(this.$el);
  },

  methods: {
    close: function() {
      _.delay(() => {
        this.$destroy();
        if (typeof this.$el.remove !== "undefined") {
          this.$el.remove();
        } else if (typeof this.$el.parentNode !== "undefined") {
          this.$el.parentNode.removeChild(el);
        }
      }, 350);
    },
    processPDF: function(event) {
      this.close();

      this.$emit("process", event.options, this.resolve);
    },
    setDefault: function(dialog) {
      dialog.localOptions.pdfWidth = 1180;
    }
  }
});
</script>

<style lang="scss">
.ph-label {
  margin: 0;
  line-height: 1em;
}
.default-row {
  margin-bottom: 20px;
}
.muted {
  color: #999;
}
.option-form-row {
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f3f3f3;

  &:last-child {
    margin-bottom: 0;
    border: none;
    padding-bottom: 0;
  }
}
.form-right {
  text-align: right;
}
.el-input input {
  margin: 0;
}
.el-dialog__body {
  padding: 30px 35px;
}
.el-dialog__title {
  font-size: 22px;
}
</style>