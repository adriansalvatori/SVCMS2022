export function moduleName() {
  return "bounce-rate";
}

export function moduleData() {
  return {
    props: {
      cardData: Object,
      overviewData: Object,
    },
    data: function () {
      return {
        formattedseconds: 0,
        formattedseconds_comp: 0,
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
      returnGAdata() {
        return this.overviewData.globalDataObject.data.analytics;
      },
      isGAconnected() {
        return this.analytics;
      },
    },
    methods: {
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

        //ONLY WORKS WITH UA
        if (self.returnGAdata.gafour == true) {
          self.error = true;
          self.errorMsg = self.overviewData.translations.requiresUA;
          return;
        }

        self.analytics = true;
        self.tableData = self.returnGAdata.timeline;

        if (self.tableData.report.timeline.bounceRate && self.returnGAdata.timeline.report_comparison.timeline.bounceRate) {
          var totalRows = self.tableData.report.timeline.bounceRate.length;
          var averageSession = self.tableData.report.totals.bounceRate / totalRows;

          self.formattedseconds = averageSession.toFixed(2);

          var totalRows = self.returnGAdata.timeline.report_comparison.timeline.bounceRate.length;
          var averageSession = self.returnGAdata.timeline.report_comparison.totals.bounceRate / totalRows;

          self.formattedseconds_comp = averageSession.toFixed(2);
        }
      },
      returnToFixed(item, dec) {
        return Number(item).toFixed(2);
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
              <div class="uip-margin-right-s uip-text-xxl uip-text-emphasis uip-text-bold">{{returnToFixed(tableData.report.totals.bounceRate, 2)}}%</div>\
              <div class="uip-background-green-wash uip-text-green uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-margin-right-xs" \
              :class="{\'uip-background-red-wash uip-text-danger\' : tableData.report.totals_change.bounceRate > 0}">\
                <span v-if="tableData.report.totals_change.bounceRate > 0" class="material-icons-outlined" >expand_less</span>\
                <span v-if="tableData.report.totals_change.bounceRate < 0" class="material-icons-outlined" >expand_more</span>\
              </div>\
              <div class="uip-text-bold uip-text-green" \
              :class="{\'uip-text-danger\' : tableData.report.totals_change.bounceRate > 0}">{{tableData.report.totals_change.bounceRate}}%</div>\
            </div>\
            <div class="">\
                <div class="uip-text-muted">{{overviewData.translations.comparedTo}}: {{overviewData.dateRange.startDate_comparison}} - {{overviewData.dateRange.endDate_comparison}} ({{returnToFixed(tableData.report_comparison.totals.bounceRate, 2)}}%)</div>\
            </div>\
          </div>\
        </template>\
     </div>',
  };
  return compData;
}
