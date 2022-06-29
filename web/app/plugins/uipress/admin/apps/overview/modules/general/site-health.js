export function moduleName() {
  return "site-health";
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
        issues: [],
        loading: true,
        dataSets: [],
        chartLabels: [],
        ready: false,
        colours: {
          bgColors: [],
          borderColors: [],
        },
        message: "",
        linkMessage: "",
        healthUrl: "",
        colors: ["rgba(12, 92, 239, 1)", "rgb(250, 160, 90)", "rgb(240, 80, 110)"],
      };
    },
    mounted: function () {
      this.getPosts();
    },
    watch: {
      overviewData: {
        handler(newValue, oldValue) {
          this.getPosts();
        },
        deep: true,
      },
    },
    computed: {
      returnchartdata() {
        return this.chartData;
      },
      getTheDates() {
        return this.chartData;
      },
    },
    methods: {
      createChartDataDoughnut(data) {
        let chartdataset = [];
        let self = this;
        let total = 0;

        chartdataset = {
          labels: [],
          datasets: [],
        };

        for (var i = 0; i < data.unformatted.length; i++) {
          var item = data.unformatted[i];
          total += parseInt(item);
        }

        for (var i = 0; i < data.unformatted.length; i++) {
          var item = data.unformatted[i];
          var filler = total - parseInt(item);
          var temp = {
            label: data.labels[i],
            fill: true,
            data: [item, filler],
            backgroundColor: [self.colors[i], "rgba(169, 169, 169, 17%)"],
            borderColor: ["rgba(255,255,255,1)", "rgba(12, 92, 239, 0)"],
            borderWidth: 0,
            borderRadius: 20,
            chartTitle: self.overviewData.translations.siteHealth,
            toolTipLabels: data.labels[i],
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
      getPosts() {
        let self = this;
        self.ready = false;

        //CHECK IF WE ARE STILL LOADING
        if (self.overviewData.globalDataObject.loading) {
          return;
        }

        if (self.overviewData.globalDataObject.data.system_health) {
          let sysInfo = self.overviewData.globalDataObject.data.system_health;
          self.issues = sysInfo.issues;
          self.chartData = self.createChartDataDoughnut(sysInfo);

          if (sysInfo.message) {
            self.message = sysInfo.message;
            self.linkMessage = sysInfo.linkMessage;
            self.healthUrl = sysInfo.healthUrl;
          }

          self.ready = true;
        }
      },
    },
    template:
      '<div class="uip-padding-s">\
  	  	<loading-placeholder v-if="overviewData.globalDataObject.loading == true"></loading-placeholder>\
        <div class="uip-flex uip-grid " v-if="!overviewData.ui.editingMode && overviewData.globalDataObject.loading != true">\
          <div class="uip-width-medium">\
            <uip-chart v-if="ready" :dates="getTheDates" :removeLabels="true" type="doughnut" :chartData="chartData" :gridLines="false" cWidth="200px"></uip-chart>\
          </div>\
          <div class="uip-width-medium"><div v-for="item in issues" \
            class="uip-flex uip-flex-center uip-border-round uip-padding-xxs uip-flex uip-flex-column uip-flex-start" >\
              <div class="uip-flex uip-margin-bottom-xxs uip-flex-center">\
                <span :style="{\'background\' : item.color}" class="uip-margin-right-xxs uip-border-round uip-h-10 uip-w-10 uip-display-inline-block"></span>\
                <span class="uip-text-bold uip-text-l">{{item.value}}</span>\
              </div>\
              <div class="uip-text-bold uip-margin-right-xs uip-text-capitalize uip-text-muted">\
                <span>{{item.name}}</span>\
              </div>\
            </div>\
          </div>\
        </div>\
        <div v-if="message" class="uip-margin-top-m uip-text-muted">{{message}} <a :href="healthUrl">{{linkMessage}}</a></div>\
  		</div>',
  };
  return compData;
}

export default function () {
  console.log("Loaded");
}
