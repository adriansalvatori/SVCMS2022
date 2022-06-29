export function moduleData() {
  return {
    props: {
      tooltiptext: String,
    },
    data: function () {
      return {
        showTip: false,
        translation: this.tooltiptext,
        tipWidth: 100,
      };
    },
    watch: {
      showTip: function (newValue, oldValue) {
        let self = this;
        setTimeout(function () {
          self.setPosition();
        }, 1);
      },
    },
    computed: {},
    methods: {
      setPosition() {
        self = this;

        if (!this.showTip) {
          return;
        }

        if (self.$el == null) {
          return;
        }

        let thetip = self.$refs.dynamictip;
        self.tipWidth = thetip.getBoundingClientRect().width;

        let posWidth = self.$el.getBoundingClientRect().width;
        let posHeight = self.$el.getBoundingClientRect().height;
        let halfWidth = posWidth / 2;
        let POStop = self.$el.getBoundingClientRect().top - posHeight - 5;
        let POSright = self.$el.getBoundingClientRect().left - self.tipWidth / 2 + halfWidth;

        self.$refs.dynamictip.style.top = POStop + "px";
        self.$refs.dynamictip.style.left = POSright + "px";
      },
      justTheTip() {
        this.showTip = true;
      },
      hideTheTip() {
        this.showTip = false;
      },
    },
    template:
      '<div class="" @mouseenter="justTheTip()" @mouseleave="hideTheTip()">\
          <slot></slot>\
          <div v-if="showTip" class="uip-position-fixed uip-tooltip" ref="dynamictip">{{translation}}</div>\
      </div>',
  };
}
