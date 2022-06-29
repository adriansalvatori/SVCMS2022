export function moduleName() {
  return "total-orders";
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
      this.loading = false;
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
      createChartData() {
        let chartdataset = [];
        let self = this;
        let pageviewsdata = this.returnWooData.totalOrders.dataSet;

        chartdataset = {
          labels: pageviewsdata.dates,
          datasets: [
            {
              label: self.overviewData.translations.orders,
              fill: true,
              data: pageviewsdata.data,
              backgroundColor: ["rgba(12, 92, 239, 0.05)"],
              borderColor: ["rgba(12, 92, 239, 1)"],
              borderWidth: 2,
              chartTitle: self.overviewData.translations.orders,
              toolTipLabels: pageviewsdata.dates,
              toolTipType: "dates",
            },
            {
              label: self.overviewData.translations.ordersComp,
              fill: true,
              data: pageviewsdata.data_comp,
              backgroundColor: ["rgba(247, 127, 212, 0)"],
              borderColor: ["rgb(247, 127, 212)"],
              borderWidth: 2,
              toolTipLabels: pageviewsdata.dates,
              toolTipType: "dates",
            },
          ],
        };

        return chartdataset;
      },
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
        self.chartData = self.createChartData();
      },
    },
    template:
      '<div class="uip-padding-s uip-position-relative">\
          <div v-if="broken == true" class="uip-background-red-wash uip-padding-s uip-border-round">{{overviewData.translations.somethingWrong}}</div>\
          <div v-if="wooError == true" class="uip-background-red-wash uip-padding-s uip-border-round">{{returnWooData.message}}</div>\
          <template v-else-if="broken != true">\
            <premium-overlay v-if="sub && overviewData.account != true" :translations="overviewData.translations"></premium-overlay>\
            <template v-else>\
                <loading-placeholder v-if="overviewData.globalDataObject.loading == true"></loading-placeholder>\
                <div v-if="!overviewData.ui.editingMode && overviewData.globalDataObject.loading != true">\
                  <div class="uip-flex uip-flex-center uip-margin-bottom-xs">\
                    <div class="uip-margin-right-s uip-text-xxl uip-text-emphasis uip-text-bold">{{tableData.totalOrders.numbers.total}}</div>\
                    <div class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold uip-flex"  :class="{\'uip-background-red-wash\' : tableData.change < 0}">\
                      <span v-if="tableData.totalOrders.numbers.change > 0" class="material-icons-outlined">expand_less</span>\
                      <span v-if="tableData.totalOrders.numbers.change < 0" class="material-icons-outlined">expand_more</span>\
                      {{tableData.totalOrders.numbers.change}}%\
                    </div>\
                  </div>\
                  <div class="uip-margin-top-m">\
                    <div class="uip-text-muted">{{overviewData.translations.comparedTo}}: {{overviewData.dateRange.startDate_comparison}} - {{overviewData.dateRange.endDate_comparison}} ({{tableData.totalOrders.numbers.total_comparison}})</div>\
                  </div>\
                  <div class="uip-w-100p">\
                    <uip-chart :dates="getTheDates" v-if="overviewData.globalDataObject.loading != true" type="line" :chartData="chartData"  :gridLines="true" cWidth="200px"></uip-chart>\
                  </div>\
                </div>\
            </template>\
          </template>\
     </div>',
  };
  return compData;
}
