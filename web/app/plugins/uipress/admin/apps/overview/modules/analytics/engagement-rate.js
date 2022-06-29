export function moduleName() {
  return "engagement-rate";
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

        //ONLY WORKS WITH GA4
        if (!self.returnGAdata.gafour) {
          self.error = true;
          self.errorMsg = self.overviewData.translations.requiresGAfour;
          return;
        }

        self.analytics = true;
        self.tableData = self.returnGAdata.timeline;

        if (self.tableData.report.timeline.engagementRate && self.returnGAdata.timeline.report_comparison.timeline.engagementRate) {
          var totalRows = self.tableData.report.timeline.engagementRate.length;
          var averageSession = self.tableData.report.totals.engagementRate / totalRows;

          self.formattedseconds = averageSession.toFixed(2);

          var totalRows = self.returnGAdata.timeline.report_comparison.timeline.engagementRate.length;
          var averageSession = self.returnGAdata.timeline.report_comparison.totals.engagementRate / totalRows;

          self.formattedseconds_comp = averageSession.toFixed(2);
        }
      },
      secondsToHms(d) {
        d = Number(d);
        var h = Math.floor(d / 3600);
        var m = Math.floor((d % 3600) / 60);
        var s = Math.floor((d % 3600) % 60);

        var hDisplay = h > 0 ? h + (h == 1 ? "h, " : "h, ") : "";
        var mDisplay = m > 0 ? m + (m == 1 ? "m, " : "m, ") : "";
        var sDisplay = s > 0 ? s + (s == 1 ? "s" : "s") : "";
        return hDisplay + mDisplay + sDisplay;
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
              <div class="uip-margin-right-s uip-text-xxl uip-text-emphasis uip-text-bold">{{formattedseconds}}%</div>\
              <div class="uip-background-green-wash uip-text-green uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-margin-right-xs" \
              :class="{\'uip-background-red-wash uip-text-danger\' : tableData.report.totals_change.engagementRate < 0}">\
                <span v-if="tableData.report.totals_change.engagementRate > 0" class="material-icons-outlined" >expand_less</span>\
                <span v-if="tableData.report.totals_change.engagementRate < 0" class="material-icons-outlined" >expand_more</span>\
              </div>\
              <div class="uip-text-bold uip-text-green" \
              :class="{\'uip-text-danger\' : tableData.report.totals_change.engagementRate < 0}">{{tableData.report.totals_change.engagementRate}}%</div>\
            </div>\
            <div class="">\
                <div class="uip-text-muted">{{overviewData.translations.comparedTo}}: {{overviewData.dateRange.startDate_comparison}} - {{overviewData.dateRange.endDate_comparison}} ({{formattedseconds_comp}}%)</div>\
            </div>\
          </div>\
        </template>\
     </div>',
  };
  return compData;
}
