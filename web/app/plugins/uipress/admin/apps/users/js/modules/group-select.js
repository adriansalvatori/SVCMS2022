export function moduleData() {
  return {
    props: {
      selected: Array,
      name: String,
      placeholder: String,
      single: Boolean,
      updategroups: Function,
      groups: Object,
    },
    data: function () {
      return {
        thisSearchInput: "",
        options: this.groups,
        selectedOptions: this.selected,
        ui: {
          dropOpen: false,
        },
      };
    },
    computed: {
      formattedOptions() {
        return this.options;
      },
    },
    watch: {
      selectedOptions: {
        handler(newValue, oldValue) {
          this.updategroups(this.selectedOptions);
        },
        deep: true,
      },
    },
    methods: {
      //////TITLE: ADDS A SELECTED OPTION//////////////////////////////////////////////////////////////////////////////////
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////
      /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
      addSelected(selectedoption, options) {
        //if selected then remove it
        if (this.ifSelected(selectedoption, options)) {
          this.removeSelected(selectedoption, options);
          return;
        }
        if (this.single == true) {
          options[0] = selectedoption;
        } else {
          options.push(selectedoption.toString());
        }
      },
      //////TITLE: REMOVES A SLECTED OPTION//////////////////////////////////////////////////////////////////////////////////
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////
      /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
      removeSelected(option, options) {
        let text = option.toString();
        let index = options.indexOf(text);
        if (index > -1) {
          options = options.splice(index, 1);
        }
      },

      //////TITLE:  CHECKS IF SELECTED OR NOT//////////////////////////////////////////////////////////////////////////////////
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////
      /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
      ifSelected(option, options) {
        let text = option.toString();
        let index = options.indexOf(text);
        if (index > -1) {
          return true;
        } else {
          return false;
        }
      },
      //////TITLE:  CHECKS IF IN SEARCH//////////////////////////////////////////////////////////////////////////////////
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////
      /////DESCRIPTION: CHECKS IF ITEM CONTAINS STRING
      ifInSearch(option, searchString) {
        let item = option.toLowerCase();
        let string = searchString.toLowerCase();

        if (item.includes(string)) {
          return true;
        } else {
          return false;
        }
      },
      onClickOutside(event) {
        const path = event.path || (event.composedPath ? event.composedPath() : undefined);
        // check if the MouseClick occurs inside the component
        if (path && !path.includes(this.$el) && !this.$el.contains(event.target)) {
          this.closeThisComponent(); // whatever method which close your component
        }
      },
      openThisComponent() {
        this.ui.dropOpen = true; // whatever codes which open your component
        // You can also use Vue.$nextTick or setTimeout
        requestAnimationFrame(() => {
          document.documentElement.addEventListener("click", this.onClickOutside, false);
        });
      },
      closeThisComponent() {
        this.ui.dropOpen = false; // whatever codes which close your component
        document.documentElement.removeEventListener("click", this.onClickOutside, false);
      },
    },
    template:
      '<div class="uip-position-relative" @click="openThisComponent">\
        <div class="uip-padding-xs uip-background-muted uip-border-round uip-w-200 uip-max-w-400 uip-cursor-pointer uip-border-box" :class="{\'uip-active-outline\' : ui.dropOpen}"> \
          <div class="uip-flex uip-flex-center">\
            <div class="uip-flex-grow uip-margin-right-s">\
              <div>\
                <span class="uk-text-meta">{{name}}...</span>\
              </div>\
            </div>\
            <span class="material-icons-outlined uip-text-muted">add</span>\
            <span v-if="selectedOptions.length > 0" class="uip-text-inverse uip-background-primary uip-border-round uip-text-s uip-w-18 uip-margin-left-xxs uip-text-center">\
              {{selectedOptions.length}}\
            </span>\
          </div>\
        </div>\
        <div v-if="ui.dropOpen" class="uip-position-absolute uip-background-default uip-border-round uip-border uip-w-400 uip-max-w-400 uip-border-box uip-z-index-9 uip-margin-top-xs uip-overflow-hidden">\
          <div class="uip-flex uip-background-default uip-padding-xs uip-border-bottom">\
            <span class="material-icons-outlined uip-text-muted uip-margin-right-xs">search</span>\
            <input class="uip-blank-input uip-flex-grow" type="search"  \
            :placeholder="placeholder" v-model="thisSearchInput" autofocus>\
          </div>\
          <div class="uip-max-h-280 uip-overflow-auto">\
            <template v-for="option in formattedOptions">\
              <div class="uip-background-default uip-padding-xs hover:uip-background-muted" \
              @click="addSelected(option.id, selectedOptions)" \
              v-if="ifInSearch(option.title, thisSearchInput)" \
              style="cursor: pointer">\
                <div class="uip-flex uip-flex-row uip-flex-center">\
                  <div class="uip-flex uip-flex-center uip-flex-middle uip-margin-right-xs">\
                    <input type="checkbox" :name="option.name" :value="option.name" :checked="ifSelected(option.id, selectedOptions)">\
                  </div>\
                  <div class="uip-flex-grow">\
                    <div class="uip-flex uip-gap-xxs uip-flex-center">\
                      <span class="material-icons-outlined" :style="\'color:\' + option.color">{{option.icon}}</span>\
                      <span class="uip-text-bold uip-text-emphasis">{{option.title}}</span>\
                    </div>\
                    <div class="uip-text-muted">ID: {{option.id}}</div>\
                  </div>\
                </div>\
              </div>\
            </template>\
          </div>\
        </div>\
      </div>',
  };
}
