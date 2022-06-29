export function moduleData() {
  return {
    props: {
      type: String,
      icon: String,
      pos: String,
      buttonsize: String,
      buttontext: String,
      tooltip: Boolean,
      tooltiptext: String,
      width: Number,
      buttonstyle: String,
    },
    data: function () {
      return {
        modelOpen: false,
        dropWidth: 0,
      };
    },
    destroyed() {
      window.removeEventListener("scroll", this.handleScroll, false);
    },
    methods: {
      handleScroll(event) {
        // Any code to be executed when the window is scrolled
        let self = this;

        var style = this.setPosition();
        let submenu = self.$el.getElementsByClassName("uip-dropdown-conatiner")[0];
        submenu.setAttribute("style", style);
      },
      onClickOutside(event) {
        const path = event.path || (event.composedPath ? event.composedPath() : undefined);
        // check if the MouseClick occurs inside the component
        if (path && !path.includes(this.$el) && !this.$el.contains(event.target)) {
          this.closeThisComponent(); // whatever method which close your component
        }
      },
      openThisComponent() {
        this.modelOpen = this.modelOpen != true; // whatever codes which open your component

        if (this.modelOpen == true) {
          window.addEventListener("scroll", this.handleScroll, false);
        }
        //this.setPosition();
        // You can also use Vue.$nextTick or setTimeout
        requestAnimationFrame(() => {
          document.documentElement.addEventListener("click", this.onClickOutside, false);
        });
      },
      closeThisComponent() {
        this.modelOpen = false; // whatever codes which close your component
        document.documentElement.removeEventListener("click", this.onClickOutside, false);
        window.removeEventListener("scroll", this.handleScroll, false);
      },
      setPosition() {
        if (!this.modelOpen) {
          return;
        }
        self = this;
        let returnDatat = 0;
        ///SET TOP

        if (!self.$el) {
          return;
        }

        let POStop = self.$el.getBoundingClientRect().bottom + 10;
        let POSright = self.$el.getBoundingClientRect().right - self.width - 20;

        setTimeout(function () {
          self.checkIfOffscreen();
        }, 1);

        //submenu = self.$el.getElementsByClassName("uip-dropdown-conatiner")[0];

        //submenu.setAttribute("style", "top:" + returnDatat + ";left:" + POSright + "px");
        return "top: " + POStop + "px;left:" + POSright + "px;";
      },
      checkIfOffscreen() {
        let self = this;

        if (!self.$refs.uipdrop) {
          return;
        }
        let drop = self.$refs.uipdrop;
        let bottom = drop.getBoundingClientRect().bottom + 500;

        if (bottom > window.innerHeight) {
          let POStop = window.innerHeight - self.$el.getBoundingClientRect().top + 10;
          drop.style.top = "auto";
          drop.style.bottom = POStop + "px";
        }
      },
      returnButtonSize() {
        let style = "";
        if (this.buttonsize && this.buttonsize == "small") {
          style = "uip-padding-xxs";
        } else if (this.buttonsize && this.buttonsize == "normal") {
          style = "uip-padding-xs";
        } else {
          style = "uip-padding-xs";
        }
        return style;
      },
      returnButtonClass() {
        let style = "";
        if (this.buttonsize && this.buttonsize == "small") {
          style = "uip-padding-xxs";
        } else if (this.buttonsize && this.buttonsize == "normal") {
          style = "uip-padding-xs";
        } else {
          style = "uip-padding-xs";
        }
        if (this.buttonstyle && this.buttonstyle == "primary") {
          return "uip-button-primary " + style;
        } else {
          return "uip-button-default " + style;
        }
      },
    },
    template:
      '<div class="uip-position-relative">\
        <div class="">\
          <tooltip v-if="tooltip" :tooltiptext="tooltiptext">\
            <div v-if="type == \'icon\'" @click="openThisComponent" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined" type="button" :class="returnButtonSize()">{{icon}}</div>\
            <div v-if="type == \'text\'" @click="openThisComponent" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer" type="button" :class="returnButtonSize()">{{buttontext}}</div>\
          </tooltip>\
          <div v-else-if="type == \'icon\'" @click="openThisComponent" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined" type="button" :class="returnButtonSize()">{{icon}}</div>\
          <button v-else-if="type == \'text\'" @click="openThisComponent" type="button" :class="returnButtonClass()">{{buttontext}}</button>\
        </div>\
        <div v-show="modelOpen" :style="setPosition()" ref="uipdrop"\
        class="uip-shadow uip-position-fixed uip-dropdown-conatiner uip-background-default uip-border-round uip-border uip-z-index-9999">\
          <slot></slot>\
        </div>\
      </div>',
  };
}
