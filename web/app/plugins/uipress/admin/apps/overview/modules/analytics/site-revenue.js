export function moduleName() {
  return "site-revenue";
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
        currency: "",
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
      createChartData() {
        let chartdataset = [];
        let self = this;
        let pageviewsdata = this.returnGAdata.timeline.report;
        let pageviewsdatacomp = this.returnGAdata.timeline.report_comparison;

        chartdataset = {
          labels: pageviewsdata.dates,
          datasets: [
            {
              label: self.overviewData.translations.currentPeriod,
              chartTitle: self.overviewData.translations.siteRevenue,
              fill: true,
              data: pageviewsdata.timeline.totalRevenue,
              backgroundColor: ["rgba(12, 92, 239, 0.05)"],
              borderColor: ["rgba(12, 92, 239, 1)"],
              borderWidth: 2,
              toolTipLabels: pageviewsdata.dates,
              toolTipType: "dates",
            },
            {
              label: self.overviewData.translations.comparisonPeriod,
              fill: true,
              data: pageviewsdatacomp.timeline.totalRevenue,
              backgroundColor: ["rgba(247, 127, 212, 0)"],
              borderColor: ["rgb(247, 127, 212)"],
              borderWidth: 2,
              toolTipLabels: pageviewsdatacomp.dates,
              toolTipType: "dates",
            },
          ],
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
        self.currency = self.overviewData.ui.currency;
      },
      returnToFixed(item, dec) {
        return Number(item).toFixed(2);
      },
      returnFormattedTotal() {
        let total = parseInt(this.tableData.report.totals.totalRevenue);
        total = Number(total).toFixed(2);
        return parseInt(total).toLocaleString();
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
            <div class="uip-flex uip-flex-center uip-margin-bottom-xs">\
              <div class="uip-margin-right-s uip-text-xxl uip-text-emphasis uip-text-bold">{{currency}}{{returnFormattedTotal()}}</div>\
              <div class="uip-background-green-wash uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-text-green uip-margin-right-xs" \
              :class="{\'uip-background-red-wash uip-text-danger\' : tableData.report.totals_change.totalRevenue < 0}">\
                <span v-if="tableData.report.totals_change.totalRevenue > 0" class="material-icons-outlined" >expand_less</span>\
                <span v-if="tableData.report.totals_change.totalRevenue < 0" class="material-icons-outlined" >expand_more</span>\
              </div>\
              <div class="uip-text-bold uip-text-green" \
              :class="{\'uip-text-danger\' : tableData.report.totals_change.totalRevenue < 0}">{{tableData.report.totals_change.totalRevenue}}%</div>\
            </div>\
            <div class="uip-margin-bottom-m">\
                <div class="uip-text-muted">{{overviewData.translations.comparedTo}}: {{overviewData.dateRange.startDate_comparison}} - {{overviewData.dateRange.endDate_comparison}} ({{returnToFixed(tableData.report_comparison.totals.totalRevenue, 2)}})</div>\
            </div>\
            <div class="uip-w-100p">\
              <uip-chart :dates="getTheDates" v-if="overviewData.globalDataObject.loading != true" type="line" :chartData="chartData"  :gridLines="true" cWidth="200px"></uip-chart>\
            </div>\
          </div>\
        </template>\
		 </div>',
  };
  return compData;
}
