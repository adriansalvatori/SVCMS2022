export function moduleName() {
  return "average-order-value";
}

export function moduleData() {
  return {
    props: {
      cardData: Object,
      overviewData: Object,
    },
    data: function () {
      return {
        chartData: [],
        cardOptions: this.cardData,
        numbers: [],
        sub: true,
        analytics: false,
        error: false,
        errorMsg: "",
        woocommerce: true,
        broken: false,
      };
    },
    mounted: function () {
      this.getData();
    },
    watch: {
      overviewData: {
        handler(newValue, oldValue) {
          this.getData();
        },
        deep: true,
      },
      cardOptions: {
        handler(newValue, oldValue) {
          this.$emit("card-change", newValue);
        },
        deep: true,
      },
    },
    computed: {
      getTheDates() {
        return this.overviewData.dateRange;
      },
      getPostsOnce() {
        this.getPosts();
      },
      returnWooData() {
        return this.overviewData.globalDataObject.data.woocommerce;
      },
      formattedPosts() {
        this.getPostsOnce;
        return this.recentPosts;
      },
      wooError() {
        if (this.woocommerce == true) {
          return false;
        } else {
          return true;
        }
      },
    },
    methods: {
      getData() {
        let self = this;
        self.broken = false;

        //CHECK IF WE ARE STILL LOADING
        if (self.overviewData.globalDataObject.loading) {
          return;
        }

        ///CHECK IF WOO IS INSTALLED
        if (self.returnWooData.error) {
          self.woocommerce = false;
          return;
        }

        if (!self.returnWooData) {
          self.broken = true;
          return;
        }

        self.tableData = self.returnWooData;
      },
    },
    template:
      '<div class="uip-padding-s uip-position-relative">\
          <div v-if="broken == true" class="uip-background-red-wash uip-padding-s uip-border-round">{{overviewData.translations.somethingWrong}}</div>\
          <div v-if="wooError == true" class="uip-background-red-wash uip-padding-s uip-border-round">{{returnWooData.message}}</div>\
          <template v-else-if="broken != true">\
            <premium-overlay v-if="sub && overviewData.account != true && !wooError" :translations="overviewData.translations"></premium-overlay>\
            <template v-else>\
              <loading-placeholder v-if="overviewData.globalDataObject.loading == true"></loading-placeholder>\
              <div v-if="overviewData.globalDataObject.loading != true && !overviewData.ui.editingMode">\
                <div class="uip-flex uip-flex-center uip-margin-bottom-xs">\
                  <div class="uip-margin-right-s uip-text-xxl uip-text-emphasis uip-text-bold">{{tableData.averageOrder.numbers.total}}</div>\
                  <div class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold uip-flex"  :class="{\'uip-background-red-wash\' : tableData.change < 0}">\
                    <span v-if="tableData.averageOrder.numbers.change > 0" class="material-icons-outlined">expand_less</span>\
                    <span v-if="tableData.averageOrder.numbers.change < 0" class="material-icons-outlined">expand_more</span>\
                    {{tableData.averageOrder.numbers.change}}%\
                  </div>\
                </div>\
                <div>\
                  <div class="uip-text-muted">{{overviewData.translations.comparedTo}}: {{overviewData.dateRange.startDate_comparison}} - {{overviewData.dateRange.endDate_comparison}} ({{tableData.averageOrder.numbers.total_comparison}})</div>\
                </div>\
              </div>\
            </template>\
          </template>\
		 </div>',
  };
  return compData;
}
