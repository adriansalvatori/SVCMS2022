export function moduleName() {
  return "inspirational-quote";
}

export function moduleData() {
  return {
    props: {
      cardData: Object,
      overviewData: Object,
    },
    data: function () {
      return {
        cardOptions: this.cardData,
        quotes: {
          a: "",
          q: "",
        },
      };
    },
    mounted: function () {
      this.getQuote();
    },
    watch: {
      overviewData: {
        handler(newValue, oldValue) {
          this.getQuote();
        },
        deep: true,
      },
    },
    methods: {
      getQuote() {
        let self = this;

        //CHECK IF WE ARE STILL LOADING
        if (self.overviewData.globalDataObject.loading) {
          return;
        }

        self.quotes = self.overviewData.globalDataObject.data.inspirationalQuote[0];
      },
    },
    template:
      '<div class="uip-padding-s" v-if="!overviewData.globalDataObject.loading">\
        <div class="uip-text-xxl uip-text-bold uip-block-quote uip-margin-bottom-m uip-text-italic">{{quotes.q}}</div>\
        <div class="">— {{quotes.a}}</div>\
		 </div>',
  };
  return compData;
}
