export function moduleName() {
  return "cart-checkout-purchase";
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
        sub: true,
        analytics: false,
        error: false,
        errorMsg: "",
      };
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
    mounted: function () {
      this.getData();
    },
    computed: {
      getTheDates() {
        return this.tableData.report.dates;
      },
      returnGAdata() {
        return this.overviewData.globalDataObject.data.analytics;
      },
      isGAconnected() {
        return this.analytics;
      },
    },
    methods: {
      chartOptions() {
        let borderRadius = 8;
        let borderRadiusAllCorners = { topLeft: borderRadius, topRight: borderRadius, bottomLeft: borderRadius, bottomRight: borderRadius };

        return {
          chartTitle: this.overviewData.translations.ecommerceOverview,
          fill: true,
          borderColor: ["rgba(0,0,0,0)"],
          borderWidth: 2,
          minBarLength: 4,
          borderRadius: borderRadiusAllCorners,
          maxBarThickness: 12,
          borderSkipped: true,
          toolTipType: "label",
        };
      },
      createChartData() {
        let chartdataset = [];
        let self = this;
        let reportData = this.returnGAdata.timeline.report;
        let reportDatacomp = this.returnGAdata.timeline.report_comparison;

        let dataSetPurchases = self.chartOptions();
        dataSetPurchases.label = self.overviewData.translations.purchases;
        dataSetPurchases.data = reportData.timeline.ecommercePurchases;
        dataSetPurchases.backgroundColor = ["rgba(12, 92, 239, 1)"];
        dataSetPurchases.toolTipLabels = self.overviewData.translations.purchases;

        let dataSetCheckout = self.chartOptions();
        dataSetCheckout.label = self.overviewData.translations.checkoutStarted;
        dataSetCheckout.data = reportData.timeline.checkouts;
        dataSetCheckout.backgroundColor = ["rgba(12, 92, 239, 0.6)"];
        dataSetCheckout.toolTipLabels = self.overviewData.translations.checkoutStarted;

        let dataSetCart = self.chartOptions();
        dataSetCart.label = self.overviewData.translations.addToCart;
        dataSetCart.data = reportData.timeline.addToCarts;
        dataSetCart.backgroundColor = ["rgba(12, 92, 239,0.3)"];
        dataSetCart.toolTipLabels = self.overviewData.translations.addToCart;

        chartdataset = {
          labels: reportData.dates,
          datasets: [dataSetPurchases, dataSetCheckout, dataSetCart],
        };

        return chartdataset;
      },
      getData() {
        let self = this;
        self.error = false;

        //CHECK IF WE ARE STILL LOADING
        if (self.overviewData.globalDataObject.loading) {
          return;
        }

        //ANALYTICS SERVER ERROR
        if (!self.returnGAdata) {
          self.error = true;
          self.errorMsg = self.overviewData.translations.analyticsDataUnavailable;
          return;
        }

        //ANALYTICS ERROR
        if (self.returnGAdata.error) {
          self.error = true;
          self.errorMsg = self.returnGAdata.message;
          return;
        }
        //IF NO ACCOUNT
        if (self.returnGAdata.no_account && self.returnGAdata.no_account == true) {
          self.analytics = false;
          return;
        }

        self.analytics = true;
        self.tableData = self.returnGAdata.timeline;
        self.chartData = self.createChartData();
      },
    },
    template:
      '<div class="uip-padding-s uip-position-relative" :accountConnected="isGAconnected">\
        <div v-if="error" class="uip-background-red-wash uip-padding-s uip-border-round">{{errorMsg}}</div>\
        <premium-overlay v-if="sub && overviewData.account != true" :translations="overviewData.translations"></premium-overlay>\
        <template v-else>\
          <loading-placeholder v-if="overviewData.globalDataObject.loading == true"></loading-placeholder>\
          <connect-google-analytics @account-connected="getData()" :translations="overviewData.translations" v-if="overviewData.globalDataObject.loading != true && !isGAconnected && !error"></connect-google-analytics>\
          <div v-if="!overviewData.ui.editingMode && overviewData.globalDataObject.loading != true && isGAconnected" >\
            <div class="uip-flex uip-flex-row">\
              <!--TOTAL ADD TO CARTS -->\
              <div class="uip-margin-bottom-xs uip-margin-right-l">\
                <div class="uip-text-bold uip-text-muted uip-margin-bottom-xs uip-text-ellipsis uip-overflow-hidden uip-no-wrap">{{overviewData.translations.addToCart}}</div>\
                <div class="uip-flex uip-flex-column uip-gap-xs">\
                  <div class="uip-margin-right-xxs uip-text-xl uip-text-emphasis uip-text-bold">{{tableData.report.totals.addToCarts}}</div>\
                  <div class=" uip-text-green  uip-text-bold uip-flex " \
                  :class="{\'uip-text-danger\' : tableData.report.totals_change.addToCarts < 0}">\
                    <span v-if="tableData.report.totals_change.addToCarts > 0" class="material-icons-outlined" >expand_less</span>\
                    <span >{{tableData.report.totals_change.addToCarts}}%</span>\
                  </div>\
                </div>\
              </div>\
              <!--TOTAL CHECKOUTS -->\
              <div class="uip-margin-bottom-xs uip-margin-right-l">\
                <div class="uip-text-bold uip-text-muted uip-margin-bottom-xs uip-text-ellipsis uip-overflow-hidden uip-no-wrap">{{overviewData.translations.checkoutStarted}}</div>\
                <div class="uip-flex uip-flex-column uip-gap-xs">\
                  <div class="uip-margin-right-xxs uip-text-xl uip-text-emphasis uip-text-bold">{{tableData.report.totals.checkouts}}</div>\
                  <div class="uip-text-green uip-text-bold uip-flex " \
                  :class="{\' uip-text-danger\' : tableData.report.totals_change.checkouts < 0}">\
                    <span v-if="tableData.report.totals_change.checkouts > 0" class="material-icons-outlined" >expand_less</span>\
                    <span>{{tableData.report.totals_change.checkouts}}%</span>\
                  </div>\
                </div>\
              </div>\
              <!--TOTAL PURCHASES -->\
              <div class="uip-margin-bottom-xs">\
                <div class="uip-text-bold uip-text-muted uip-margin-bottom-xs uip-text-ellipsis uip-overflow-hidden uip-no-wrap">{{overviewData.translations.purchases}}</div>\
                <div class="uip-flex uip-flex-column uip-gap-xs">\
                  <div class="uip-margin-right-xxs uip-text-xl uip-text-emphasis uip-text-bold ">{{tableData.report.totals.ecommercePurchases}}</div>\
                  <div class=" uip-text-green  uip-text-bold uip-flex" \
                  :class="{\' uip-text-danger\' : tableData.report.totals_change.ecommercePurchases < 0}">\
                    <span v-if="tableData.report.totals_change.ecommercePurchases > 0" class="material-icons-outlined" >expand_less</span>\
                    <span>{{tableData.report.totals_change.ecommercePurchases}}%</span>\
                  </div>\
                </div>\
              </div>\
            </div>\
            <div class="uip-w-100p uip-margin-top-m">\
              <uip-chart :dates="getTheDates" v-if="overviewData.globalDataObject.loading != true" type="stacked-bar" :chartData="chartData"  :gridLines="true" cWidth="200px"></uip-chart>\
            </div>\
          </div>\
        </template>\
     </div>',
  };
  return compData;
}
