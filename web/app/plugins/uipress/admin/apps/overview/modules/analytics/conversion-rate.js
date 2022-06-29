export function moduleName() {
  return "conversion-rate";
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
        conversionRate: 0,
        conversionRateComp: 0,
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
        let formatteddata = [];
        let formatteddataComp = [];

        if (!pageviewsdata.timeline.purchaserConversionRate) {
          return chartdataset;
        }

        for (var i = 0; i < pageviewsdata.timeline.purchaserConversionRate.length; i++) {
          formatteddata[i] = (pageviewsdata.timeline.purchaserConversionRate[i] * 100).toFixed(2);
        }

        for (var i = 0; i < pageviewsdatacomp.timeline.purchaserConversionRate.length; i++) {
          formatteddataComp[i] = (pageviewsdatacomp.timeline.purchaserConversionRate[i] * 100).toFixed(2);
        }

        chartdataset = {
          labels: pageviewsdata.dates,
          datasets: [
            {
              label: self.overviewData.translations.conversionRate,
              fill: true,
              chartTitle: self.overviewData.translations.conversionRate,
              data: formatteddata,
              toolTipLabels: pageviewsdata.dates,
              backgroundColor: ["rgba(12, 92, 239, 0.05)"],
              borderColor: ["rgba(12, 92, 239, 1)"],
              borderWidth: 2,
            },
            {
              label: self.overviewData.translations.conversionRate,
              fill: true,
              data: formatteddataComp,
              toolTipLabels: pageviewsdatacomp.dates,
              backgroundColor: ["rgba(247, 127, 212, 0)"],
              borderColor: ["rgb(247, 127, 212)"],
              borderWidth: 2,
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
          self.loading = false;
          self.error = true;
          self.errorMsg = self.returnGAdata.message;
          return;
        }

        //IF NO ACCOUNT
        if (self.returnGAdata.no_account && self.returnGAdata.no_account == true) {
          self.loading = false;
          self.analytics = false;
          return;
        }

        //ONLY WORKS WITH GA4
        if (!self.returnGAdata.gafour) {
          self.error = true;
          self.errorMsg = self.overviewData.translations.requiresGAfour;
          return;
        }

        self.analytics = true;
        self.tableData = self.returnGAdata.timeline;
        self.chartData = self.createChartData();

        if (self.tableData.report.timeline.purchaserConversionRate && self.returnGAdata.timeline.report_comparison.timeline.purchaserConversionRate) {
          var totalRows = self.tableData.report.timeline.purchaserConversionRate.length;
          var averageSession = self.tableData.report.totals.purchaserConversionRate / totalRows;

          self.conversionRate = (averageSession * 100).toFixed(2);

          var totalRows = self.returnGAdata.timeline.report_comparison.timeline.purchaserConversionRate.length;
          var averageSession = self.returnGAdata.timeline.report_comparison.totals.purchaserConversionRate / totalRows;

          self.conversionRateComp = (averageSession * 100).toFixed(2);
        }
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
              <div class="uip-margin-right-s uip-text-xxl uip-text-emphasis uip-text-bold">{{conversionRate}}%</div>\
              <div class="uip-background-green-wash uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-text-green uip-margin-right-xs" \
              :class="{\'uip-background-red-wash uip-text-danger\' : conversionRateComp < conversionRateComp}">\
                <span v-if="conversionRate > conversionRateComp" class="material-icons-outlined" >expand_less</span>\
                <span v-if="conversionRate < conversionRateComp" class="material-icons-outlined" >expand_more</span>\
              </div>\
              <div class="uip-text-bold uip-text-green" \
              :class="{\'uip-text-danger\' : conversionRateComp < conversionRateComp}">{{tableData.report.totals_change.purchaserConversionRate}}%</div>\
            </div>\
            <div class="uip-margin-bottom-m">\
                <div class="uip-text-muted">{{overviewData.translations.comparedTo}}: {{overviewData.dateRange.startDate_comparison}} - {{overviewData.dateRange.endDate_comparison}} ({{conversionRateComp}}%)</div>\
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

export default function () {
  console.log("Loaded");
}
