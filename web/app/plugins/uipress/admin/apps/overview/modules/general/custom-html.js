export function moduleName() {
  return "custom-html";
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
        strippedShort: this.formatHTML(),
        shortCode: "",
        sub: true,
      };
    },
    mounted: function () {
      this.loading = false;

      if (this.cardOptions.shortcode) {
        this.strippedShort = this.cardOptions.shortcode.replace(/\\(.)/gm, "$1");
      }
    },
    watch: {
      strippedShort: function (newValue, oldValue) {
        if (newValue != oldValue) {
          this.cardOptions.shortcode = newValue;
        }
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
      getDataFromComp(code) {
        return code;
      },
      formatHTML() {
        if (this.cardData.shortcode && this.cardData.shortcode != "") {
          return this.cardData.shortcode.replace(/\\(.)/gm, "$1");
        } else {
          return "";
        }
      },
    },
    template:
      '<div class="uip-padding-s">\
        <premium-overlay v-if="sub && overviewData.account != true" :translations="overviewData.translations"></premium-overlay>\
        <div v-if="!overviewData.ui.editingMode" v-html="strippedShort">\
        </div>\
        <div v-if="overviewData.ui.editingMode" >\
          <div class="uip-margin-bottom-s">\
              <div class="uip-text-bold uip-margin-bottom-xs" for="form-stacked-text">{{overviewData.translations.title}}</div>\
              <div class="">\
                  <input class=""  type="text" v-model="cardOptions.name" :placeholder="overviewData.translations.title">\
              </div>\
          </div>\
          <div class="uip-margin-bottom-s">\
              <div class="uip-text-bold uip-margin-bottom-xs" for="form-stacked-text">HTML</div>\
              <div class="" ondragstart="return false;" ondrop="return false;">\
                  <code-flask  language="HTML"  :usercode="strippedShort" \
                  @code-change="strippedShort = getDataFromComp($event)"></code-flask>\
              </div>\
          </div>\
        </div>\
		 </div>',
  };
  return compData;
}
