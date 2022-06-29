export function moduleName() {
  return "shortcode";
}

export function moduleData() {
  return {
    props: {
      cardData: Object,
      overviewData: Object,
    },
    data: function () {
      return {
        cardOptions: this.cardData,
        loading: true,
        strippedShort: "",
        shortCode: "",
        sub: true,
      };
    },
    mounted: function () {
      this.loading = false;
      this.getShortCode();

      if (this.cardOptions.shortcode) {
        this.strippedShort = this.cardOptions.shortcode.replace(/\\(.)/gm, "$1");
      }
    },
    watch: {
      strippedShort: function (newValue, oldValue) {
        this.cardOptions.shortcode = this.strippedShort;
      },
      cardOptions: {
        handler(newValue, oldValue) {
          this.$emit("card-change", newValue);
        },
        deep: true,
      },
    },
    computed: {},
    methods: {
      getShortCode() {
        let self = this;
        if (!this.cardOptions.shortcode || this.cardOptions.shortcode.length < 1) {
          return;
        }

        self.loading = true;
        jQuery.ajax({
          url: uipress_overview_ajax.ajax_url,
          type: "post",
          data: {
            action: "uipress_get_shortcode",
            security: uipress_overview_ajax.security,
            shortCode: self.cardOptions.shortcode.replace(/\\(.)/gm, "$1"),
          },
          success: function (response) {
            var responseData = JSON.parse(response);

            if (responseData.error) {
              ///SOMETHING WENT WRONG
              UIkit.notification(responseData.error, { pos: "bottom-left", status: "danger" });
              self.loading = false;
              return;
            }

            self.shortCode = responseData.shortCode;
            self.loading = false;
          },
        });
      },
    },
    template:
      '<div class="uip-padding-s" style="position:relative">\
        <loading-placeholder v-if="loading == true"></loading-placeholder>\
        <premium-overlay v-if="sub && overviewData.account != true" :translations="overviewData.translations"></premium-overlay>\
        <div v-else>\
          <div v-if="!overviewData.ui.editingMode" style="padding-top:15px;" v-html="shortCode">\
          </div>\
          <div v-if="overviewData.ui.editingMode"  >\
            <div class="uip-margin-bottom-s">\
                <div class="uip-text-bold uip-margin-bottom-xs" for="form-stacked-text">{{overviewData.translations.title}}</div>\
                <div>\
                    <input class="uk-input uk-form-small"  type="text" v-model="cardOptions.name" :placeholder="overviewData.translations.title">\
                </div>\
            </div>\
            <div class="uip-margin-bottom-s">\
                <div class="uip-text-bold uip-margin-bottom-xs" for="form-stacked-text">{{overviewData.translations.shortcode}}</div>\
                <div>\
                    <input class="uk-input uk-form-small"  v-model="strippedShort" type="text" :placeholder="overviewData.translations.shortcode">\
                </div>\
            </div>\
          </div>\
        </div>\
		 </div>',
  };
  return compData;
}
