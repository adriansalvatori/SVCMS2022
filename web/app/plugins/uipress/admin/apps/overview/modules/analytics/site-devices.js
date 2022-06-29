export function moduleName() {
  return "site-devices";
}

export function moduleData() {
  return {
    props: {
      cardData: Object,
      overviewData: Object,
    },
    data: function () {
      return {
        colors: ["rgba(12, 92, 239, 1)", "rgb(104, 152, 241)", "rgb(173, 197, 242)"],
        chartData: [],
        cardOptions: this.cardData,
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
      isGAconnected() {
        return this.analytics;
      },
      getTheDates() {
        return this.tableData.report.dates;
      },
      returnGAdata() {
        return this.overviewData.globalDataObject.data.analytics;
      },
    },
    methods: {
      createChartDataDoughnut() {
        let chartdataset = [];
        let self = this;
        let total = 0;

        chartdataset = {
          labels: [],
          datasets: [],
        };

        for (var i = 0; i < self.deviceData.length; i++) {
          var item = self.deviceData[i];
          total += parseInt(item.value);
        }

        for (var i = 0; i < self.deviceData.length; i++) {
          var item = self.deviceData[i];
          var filler = total - parseInt(item.value);
          var temp = {
            label: item.name,
            fill: true,
            data: [item.value, filler],
            backgroundColor: [self.colors[i], "rgba(169, 169, 169, 17%)"],
            borderColor: ["rgba(255,255,255,1)", "rgba(12, 92, 239, 0)"],
            borderWidth: 0,
            borderRadius: 20,
            chartTitle: self.overviewData.translations.visitsByDevice,
            toolTipLabels: item.name,
            toolTipType: "label",
          };

          var emptyData = {
            weight: 0.6,
          };

          chartdataset.datasets.push(temp);
          chartdataset.datasets.push(emptyData);

          chartdataset.labels.push(item.name);
        }

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

        self.analytics = true;
        self.deviceData = self.returnGAdata.devices.report.data;
        self.chartData = self.createChartDataDoughnut();
        self.tableData = self.returnGAdata.timeline;
      },
      returnWidth(perc) {
        return "width:" + perc + "%";
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
            <div class="uip-flex uip-grid uip-grid-small uip-flex-center">\
              <div class="uip-width-medium">\
                <uip-chart :removeLabels="true" :dates="getTheDates" v-if="overviewData.globalDataObject.loading != true" type="doughnut" :chartData="chartData"  :gridLines="false" cWidth="200px"></uip-chart>\
              </div>\
              <div class="uip-width-medium uip-flex uip-flex-column uip-margin-left-s" >\
                <div v-for="(item, index) in deviceData" \
                class="uip-flex uip-flex-center uip-border-round uip-padding-xs uip-flex uip-flex-column uip-flex-start" >\
                  <div class="uip-flex uip-margin-bottom-xxs">\
                    <span class="uip-text-bold uip-text-l">{{item.percent_total}}%</span>\
                  </div>\
                  <div class="uip-text-bold uip-margin-right-xs uip-text-capitalize uip-text-muted">\
                    <span :style="{\'background\' : colors[index]}" class="uip-margin-right-xxs uip-border-round uip-h-10 uip-w-10 uip-display-inline-block"></span>\
                    <span>{{item.name}}</span>\
                  </div>\
                </div>\
              </div>\
            </div>\
          </div>\
      </template>\
		 </div>',
  };
  return compData;
}
