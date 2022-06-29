export function moduleName() {
  return "active-now";
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
        let alllabels = [];
        let self = this;
        let plottingData = [];

        if (self.returnGAdata.error) {
          self.error = true;
          self.errorMsg = self.returnGAdata.message;
          return;
        }

        let thisreport = this.returnGAdata.active_now.report;

        for (var i = 0; i < 30; i++) {
          var numcount = i.toString() + "_key";
          if (i < 10) {
            numcount = "0" + i.toString() + "_key";
          }
          alllabels.push(i + " minutes ago");
          chartdataset[numcount] = 0;
        }

        for (var i = 0; i < thisreport.data.length; i++) {
          var item = thisreport.data[i];
          var key = item["minutesAgo"] + "_key";
          chartdataset[key] = item["activeUsers"];
        }

        for (var i = 0; i < 30; i++) {
          var numcount = i.toString() + "_key";
          if (i < 10) {
            numcount = "0" + i.toString() + "_key";
          }
          plottingData.push(chartdataset[numcount]);
        }

        chartdataset = {
          labels: alllabels.reverse(),
          datasets: [
            {
              label: self.overviewData.translations.activeUsers,
              fill: true,
              data: plottingData.reverse(),
              backgroundColor: ["rgba(12, 92, 239, 0.8)"],
              borderColor: ["rgba(12, 92, 239, 0)"],
              borderWidth: 2,
              borderRadius: 10,
              maxBarThickness: 20,
              chartTitle: self.overviewData.translations.activeUsers,
              toolTipLabels: alllabels,
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
        self.tableData = self.returnGAdata.active_now;
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
            <div class="uip-flex uip-flex-center uip-margin-bottom-m">\
              <div class="uip-margin-right-s uip-text-xxl uip-text-emphasis uip-text-bold">{{tableData.report.totals.activeUsers}}</div>\
            </div>\
            <div class="uip-w-100p">\
              <uip-chart :dates="getTheDates" v-if="overviewData.globalDataObject.loading != true" type="bar" :chartData="chartData"  :gridLines="true" cWidth="200px"></uip-chart>\
            </div>\
          </div>\
        </template>\
		 </div>',
  };
  return compData;
}
