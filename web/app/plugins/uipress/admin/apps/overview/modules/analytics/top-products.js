export function moduleName() {
  return "top-products";
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
      returnGAdata() {
        return this.overviewData.globalDataObject.data.analytics;
      },
      getTheDates() {
        return this.dateRange;
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

        self.analytics = true;
        self.tableData = self.returnGAdata.products.report.data;
        self.currency = self.overviewData.ui.currency;

        var totalSales = 0;
        for (var i = 0; i < self.tableData.length; i++) {
          totalSales += parseInt(self.tableData[i].itemRevenue);
        }
        for (var i = 0; i < self.tableData.length; i++) {
          var item = self.tableData[i];
          var percent = (item.itemRevenue / totalSales) * 100;
          self.tableData[i].percent_total = percent;
        }
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
            <div class="uip-w-100p uip-min-w-300 uip-overflow-auto">\
              <div class="uip-flex uip-margin-bottom-s">\
                  <div class="uip-text-muted uip-text-bold uip-flex-grow">{{overviewData.translations.productName}}</div>\
                  <div class="uip-text-muted uip-text-bold uip-margin-left-s uip-w-70 uip-text-right">{{overviewData.translations.quantitySold}}</div>\
                  <div class="uip-text-muted uip-text-bold uip-margin-left-s uip-w-70 uip-text-right">{{overviewData.translations.totalRevenue}}</div>\
                  <div class="uip-text-muted uip-text-bold uip-margin-left-s uip-w-70 uip-text-right">{{overviewData.translations.change}}</div>\
              </div>\
              <div v-for="item in tableData" class="uip-margin-bottom-s">\
                  <div class="uip-flex uip-flex-center uip-margin-bottom-xs">\
                    <div class="uip-flex-grow uip-flex uip-flex-center uip-overflow-hidden uip-text-ellipsis uip-no-wrap uip-overflow-hidden uip-text-ellipsis uip-no-wrap">\
                      <div class="uip-overflow-hidden uip-text-ellipsis uip-no-wrap uip-max-w-200">{{item.name}}</div>\
                    </div>\
                    <div class="uip-margin-left-s uip-w-70 uip-text-right uip-text-bold uip-flex-no-shrink">{{parseInt(item.transactions).toLocaleString()}}</div>\
                    <div class="uip-margin-left-s uip-w-70 uip-text-right uip-text-bold uip-flex-no-shrink">{{currency}}{{parseInt(item.itemRevenue).toLocaleString()}}</div>\
                    <div class="uip-margin-left-s uip-w-70  uip-text-right uip-flex uip-flex-right uip-flex-no-shrink">\
                      <div class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-text-xs" \
                      :class="{\'uip-background-red-wash\' : item.change < 0}">\
                        <span v-if="item.change > 0" class="material-icons-outlined" >expand_less</span>\
                        <span v-if="item.change < 0" class="material-icons-outlined" >expand_more</span>\
                        {{item.change}}%\
                      </div>\
                    </div>\
                  </div>\
                  <div class="uip-w-100p uip-h-2 uip-background-muted uip-border-round uip-overflow-hidden">\
                    <div class="uip-background-primary uip-h-100p" :style="returnWidth(item.percent_total)"></div>\
                  </div>\
              </div>\
            </div>\
          </div>\
        </template>\
		 </div>',
  };
  return compData;
}
