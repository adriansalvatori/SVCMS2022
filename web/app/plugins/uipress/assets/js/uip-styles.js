const UIPstylesOptions = {
  data() {
    return {
      loading: true,
      screenWidth: window.innerWidth,
      translations: uipTranslations,
      masterPrefs: uipMasterPrefs,
      defaults: uipDefaults,
      preferences: uipUserPrefs,
      styles: [],
    };
  },
  watch: {},
  created: function () {
    window.addEventListener("resize", this.getScreenWidth);
  },
  computed: {
    formattedSettings() {
      return this.styles;
    },
  },
  mounted: function () {
    this.getOptions();
  },
  methods: {
    getScreenWidth() {
      this.screenWidth = window.innerWidth;
    },
    isSmallScreen() {
      if (this.screenWidth < 700) {
        return true;
      } else {
        return false;
      }
    },
    getOptions() {
      let self = this;
      data = {
        action: "uip_get_styles",
        security: uip_ajax.security,
      };
      jQuery.ajax({
        url: uip_ajax.ajax_url,
        type: "post",
        data: data,
        success: function (response) {
          data = JSON.parse(response);
          self.loading = false;
          if (data.error) {
            ///SOMETHING WENT WRONG
          } else {
            ///SOMETHING WENT RIGHT
            self.styles = data.styles;
          }
        },
      });
    },
    themImport(settings) {
      if (this.isJSON(settings)) {
        let imported = JSON.parse(settings);
        let self = this;
        console.log(self.styles);

        for (var category in imported) {
          let cat = imported[category];

          if (cat.options) {
            let options = cat.options;

            for (var option in options) {
              let opt = options[option];

              if (opt.value && opt.value != "") {
                self.styles[category].options[option].value = opt.value;
              }

              if (opt.darkValue && opt.darkValue != "") {
                self.styles[category].options[option].darkValue = opt.darkValue;
              }
            }
          } else {
            continue;
          }
        }
      }
    },
    isJSON(text) {
      try {
        let json = JSON.parse(text);
        return true;
      } catch (e) {
        return false;
      }
    },
    saveSettings() {
      let self = this;

      data = {
        action: "uip_save_styles",
        security: uip_ajax.security,
        options: self.styles,
      };
      jQuery.ajax({
        url: uip_ajax.ajax_url,
        type: "post",
        data: data,
        success: function (response) {
          data = JSON.parse(response);
          self.loading = false;
          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(self.translations.somethingWrong);
          } else {
            ///SOMETHING WENT RIGHT
            uipNotification(self.translations.settingsSaved);
          }
        },
      });
    },
    clearSettings() {
      let options = this.formattedSettings;

      for (const key in options) {
        cat = options[key].options;
        for (let p = 0; p < cat.length; p++) {
          option = cat[p];
          if (Array.isArray(option.value)) {
            option.value = [];
          } else {
            option.value = "";
          }

          if ("darkValue" in option) {
            if (Array.isArray(option.darkValue)) {
              option.darkValue = [];
            } else {
              option.darkValue = "";
            }
          }
        }
      }
    },
    exportSettings() {
      self = this;
      ALLoptions = JSON.stringify(this.formattedSettings);

      var today = new Date();
      var dd = String(today.getDate()).padStart(2, "0");
      var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
      var yyyy = today.getFullYear();

      date_today = mm + "_" + dd + "_" + yyyy;
      filename = "uip-styles-" + date_today + ".json";

      var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(ALLoptions);
      var dlAnchorElem = document.getElementById("uip-export-styles");
      dlAnchorElem.setAttribute("href", dataStr);
      dlAnchorElem.setAttribute("download", filename);
      dlAnchorElem.click();
    },
    importSettings() {
      self = this;

      var thefile = jQuery("#uip-import-settings")[0].files[0];

      if (thefile.type != "application/json") {
        uipNotification(self.translations.notValidJson);
        return;
      }

      if (thefile.size > 100000) {
        uipNotification(self.translations.fileToBig);
        return;
      }

      var file = document.getElementById("uip-import-settings").files[0];
      var reader = new FileReader();
      reader.readAsText(file, "UTF-8");

      reader.onload = function (evt) {
        json_settings = evt.target.result;
        parsed = JSON.parse(json_settings);

        if (parsed != null) {
          ///GOOD TO GO;
          self.styles = parsed;
          uipNotification(self.translations.stylesImported);
        } else {
          uipNotification(self.translations.somethingWrong);
        }
      };
    },
  },
};
const UIPstyles = uipVue.createApp(UIPstylesOptions);

/////////////////////////
//OUTPUTS UIPRESS SETTINGS
/////////////////////////
UIPstyles.component("output-options", {
  props: {
    translations: Object,
    alloptions: Object,
    activemodule: String,
    uipdata: Boolean,
  },
  data: function () {
    return {
      loading: true,
      settings: this.alloptions,
    };
  },
  watch: {
    alloptions: {
      handler(newValue, oldValue) {
        this.settings = newValue;
        this.formatStyles();
      },
      deep: true,
    },
  },
  mounted: function () {
    this.loading = false;
    this.formatStyles();
  },
  computed: {
    returnSettings() {
      return this.settings;
    },
  },
  methods: {
    formatStyles() {
      let styles = "";
      let globalStyles = "";
      let options = this.returnSettings;
      importurl = false;

      for (const key in options) {
        cat = options[key].options;
        for (let p = 0; p < cat.length; p++) {
          option = cat[p];

          if (option.cssVariable == "--uip-body-font-family") {
            if (option.value[0]) {
              formattedFont = "'" + option.value[0] + "', " + option.value[1];
              globalStyles = globalStyles + option.cssVariable + ":" + formattedFont + "!important;";

              fontURL = "https://fonts.googleapis.com/css2?family=" + option.value[0] + ":wght@300;400;700&display=swap";
              formattedURL = fontURL.replace(" ", "%20");
              importurl = "@import url('" + formattedURL + "');";
            }
          } else if (option.value != "") {
            styles = styles + option.cssVariable + ":" + option.value + ";";

            if (option.global == true) {
              globalStyles = globalStyles + option.cssVariable + ":" + option.value + "!important;";
            }
          }
        }
      }

      styles = 'html:not([data-theme="dark"]) {' + styles;

      if (importurl) {
        styles = importurl + styles;
      }

      styles = styles + "}";

      styles = styles + 'html[data-theme="dark"] {';

      for (const key in options) {
        cat = options[key].options;
        for (let p = 0; p < cat.length; p++) {
          option = cat[p];
          if (option.darkValue) {
            styles = styles + option.cssVariable + ":" + option.darkValue + ";";
          }
        }
      }

      styles = styles + "}";

      styles = styles + "html {" + globalStyles + "}";
      jQuery("#uip-variable-preview").html(styles);
    },
    setDataFromComp(data, option) {
      console.log(option);
      option = data;
    },
    getdatafromComp(data) {
      return data;
    },
    returnBackgroundVariable(option) {
      return "background-color:var(" + option.cssVariable + ");";
    },
  },
  template:
    '<template v-for="(item, index) in returnSettings">\
      <div class="uip-text-l uip-text-emphasis uip-text-bold uip-margin-bottom-s uip-padding-s uip-background-muted uip-border-round uip-flex uip-flex-between">\
        <span>{{item.label}}</span>\
        <div class="uip-flex">\
          <span v-if="item.preview" @click="item.showPreview = !item.showPreview" class="material-icons-outlined uip-margin-right-xs uip-cursor-pointer hover:uip-background-grey">preview</span>\
          <span class="material-icons-outlined uip-cursor-pointer" @click="item.hidden = false" v-if="item.hidden">chevron_left</span>\
          <span class="material-icons-outlined uip-cursor-pointer" @click="item.hidden = true" v-if="!item.hidden">expand_more</span>\
        </div>\
      </div>\
      <div class="uip-margin-bottom-l uip-padding-s" v-if="item.hidden != true">\
        <!-- PREVIEW -->\
        <div class="uip-margin-bottom-m" v-html="item.preview" v-if="item.showPreview">\
        </div>\
        <div class="uip-flex uip-margin-bottom-m">\
          <div class="uip-w-300"></div>\
          <div class="uip-w-200 uip-padding-left-l uip-text-bold uip-text-muted">{{translations.default}}</div>\
          <div class="uip-w-200 uip-padding-left-l uip-text-bold uip-text-muted">{{translations.darkMode}}</div>\
        </div>\
        <!-- OPTIONS BLOCK -->\
        <div  v-for="option in item.options"> \
          <!-- data connect -->\
          <div class="uip-flex uip-margin-bottom-xs" v-if="uipdata != true && option.premium == true">\
            <div class="uip-w-300">\
              <span class="uip-w-28 uip-h-28 uip-border-round" :style="{ background: option.cssVariable}"></span>\
              <span class="uip-text-bold uip-text-m uip-margin-bottom-xs">{{option.name}}</span>\
            </div>\
            <div class="uip-w-200 uip-padding-left-l uip-margin-bottom-s">\
              <feature-flag :translations="translations"></feature-flag>\
            </div>\
          </div>\
          <!-- data connect -->\
          <div class="uip-flex uip-margin-bottom-xs" v-else>\
            <div class="uip-w-300 uip-flex uip-flex-center uip-margin-bottom-xs">\
              <span class="uip-w-28 uip-h-28 uip-border-round uip-margin-right-xs uip-border" :style="returnBackgroundVariable(option)"></span>\
              <div class="uip-text-bold uip-text-m ">{{option.name}}</div>\
            </div>\
            <!-- COLOR -->\
            <template v-if="option.type == \'color\'">\
              <div class="uip-w-200 uip-padding-left-l">\
              <div class="uip-margin-bottom-xm uip-padding-xxs uip-border uip-border-round uip-w-200 uip-background-default uip-border-box">\
                <div class="uip-flex uip-flex-center">\
                  <span class="uip-margin-right-xs uip-text-muted">\
                      <uip-color-dropdown @color-change="option.value = getdatafromComp($event)" :color="option.value"></uip-color-dropdown>\
                  </span> \
                  <input v-model="option.value" type="search" placeholder="#HEX" class="uip-blank-input uip-margin-right-s " style="min-width:0;">\
                  <span class="uip-text-muted">\
                      <span class="material-icons-outlined uip-text-muted">color_lens</span>\
                  </span> \
                </div>\
              </div>\
            </div>\
            <div class="uip-w-200 uip-padding-left-l">\
              <div class="uip-margin-bottom-xs uip-padding-xxs uip-border uip-border-round uip-w-200 uip-background-default uip-border-box" >\
                <div class="uip-flex uip-flex-center">\
                  <span class="uip-margin-right-xs uip-text-muted">\
                      <uip-color-dropdown @color-change="option.darkValue = getdatafromComp($event)" :color="option.darkValue"></uip-color-dropdown>\
                  </span> \
                  <input v-model="option.darkValue" type="search" placeholder="#HEX" class="uip-blank-input uip-margin-right-s " style="min-width:0;">\
                  <span class="uip-text-muted">\
                      <span class="material-icons-outlined uip-text-muted">color_lens</span>\
                  </span> \
                </div>\
              </div>\
            </div>\
            </template>\
            <!-- COLOR -->\
            <!-- FONT -->\
            <div v-if="option.type == \'font\'" class="uip-w-200 uip-padding-left-l">\
              <font-select :selected="option.value" name="font" placeholder="Search Fonts" ></font-select>\
            </div>\
            <!-- FONT -->\
            <!-- TEXT -->\
            <div v-if="option.type == \'text\'" class="uip-w-200 uip-padding-left-l uip-margin-bottom-xs">\
              <input type="text" class="uip-w-100p" v-model="option.value" placeholder="px / %" >\
            </div>\
            <!-- TEXT -->\
          </div>\
        </div>\
        <!-- END OPTIONS BLOCK -->\
      </div>\
    </template>',
});

UIPstyles.component("uip-color-dropdown", {
  props: ["color"],
  data: function () {
    return {
      modelOpen: false,
    };
  },
  computed: {
    areWeOpen() {
      return this.modelOpen;
    },
  },
  mounted: function () {},
  methods: {
    onClickOutside(event) {
      const path = event.path || (event.composedPath ? event.composedPath() : undefined);
      // check if the MouseClick occurs inside the component
      if (path && !path.includes(this.$el) && !this.$el.contains(event.target)) {
        this.closeThisComponent(); // whatever method which close your component
      }
    },
    openThisComponent() {
      this.modelOpen = this.modelOpen != true; // whatever codes which open your component
      // You can also use Vue.$nextTick or setTimeout
      requestAnimationFrame(() => {
        document.documentElement.addEventListener("click", this.onClickOutside, false);
      });
    },
    closeThisComponent() {
      this.modelOpen = false; // whatever codes which close your component
      document.documentElement.removeEventListener("click", this.onClickOutside, false);
    },
    getdatafromComp(data) {
      return data;
    },
    returnData(data) {
      this.$emit("color-change", data);
    },
  },
  template:
    '<div class="uip-position-relative">\
      <span @click="openThisComponent" class="uip-border-circle uip-h-18 uip-w-18 uip-border uip-display-block uip-cursor-pointer" v-bind:style="{\'background-color\' : color}">\
      </span>\
      <div v-if="areWeOpen" \
      class="uip-position-absolute uip-padding-s uip-background-default uip-border-round uip-shadow uip-min-w-200 uip-z-index-9999">\
        <uip-color-picker @color-change="returnData(getdatafromComp($event))" :color="color"></uip-color-picker>\
      </div>\
    </div>',
});

UIPstyles.component("uip-color-picker", {
  props: {
    color: String,
  },
  data: function () {
    return {
      modelOpen: this.isOpen,
    };
  },
  computed: {
    areWeOpen() {
      return this.modelOpen;
    },
  },
  mounted: function () {
    //let thepicker = new iro.ColorPicker(this.$el);
    picker = this.$el.getElementsByClassName("uip-color-picker")[0];
    let self = this;

    startColor = "";

    if (self.color) {
      startColor = self.color;
    }
    var colorPicker = new iro.ColorPicker(picker, {
      // Set the size of the color picker
      width: 250,
      // Set the initial color to pure red
      color: startColor,
      layout: [
        {
          component: iro.ui.Box,
        },
        {
          component: iro.ui.Slider,
          options: {
            id: "hue-slider",
            sliderType: "hue",
          },
        },
        {
          component: iro.ui.Slider,
          options: {
            sliderType: "saturation",
          },
        },
        {
          component: iro.ui.Slider,
          options: {
            sliderType: "value",
          },
        },
        {
          component: iro.ui.Slider,
          options: {
            sliderType: "alpha",
          },
        },
      ],
    });

    colorPicker.on("color:change", function (color) {
      self.$emit("color-change", color.rgbaString);
    });
  },
  methods: {},
  template: '<div><div class="uip-color-picker"></div></div>',
});

/////////////////////////
//FONT SELECT
/////////////////////////

UIPstyles.component("font-select", {
  data: function () {
    return {
      fontSearch: "",
      options: [],
      allFontsData: [],
      ui: {
        dropOpen: false,
      },
    };
  },
  props: {
    selected: Array,
    name: String,
    placeholder: String,
  },
  watch: {
    fontSearch: function (newValue, oldValue) {
      this.options = this.filterIt(this.allFontsData, this.fontSearch);
    },
    options: function (newValue, oldValue) {
      currentOptions = this.options.slice(0, 20);

      for (let index = 0; index < currentOptions.length; ++index) {
        currentFont = currentOptions[index];
        var css = "@import url('https://fonts.googleapis.com/css2?family=" + currentFont.fontName + ":wght@300;400;700&display=swap');";
        jQuery("<style/>").append(css).appendTo(document.head);
      }
    },
  },
  mounted: function () {
    //console.log(this.selected);
  },
  computed: {
    runitonce() {
      this.queryFonts();
    },
    allFonts() {
      this.runitonce;
      return this.options.slice(0, 30);
    },
  },
  methods: {
    queryFonts() {
      var self = this;

      jQuery.getJSON("https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=AIzaSyCsOWMT4eyd1vd4yN0-h7jZnXSCf2qDmio", function (fonts) {
        var filteredFonts = [];
        allfonts = fonts.items;
        formattedFonts = [];

        jQuery.each(allfonts, function (k, v) {
          temp = [];
          temp.fontName = v.family;
          temp.category = v.category;

          str = "";
          font = str.concat("'", temp.fontName, "', ", temp.category);

          temp.fontFamily = font;
          formattedFonts.push(temp);
        });

        listfonts = formattedFonts;
        self.allFontsData = listfonts;
        self.options = listfonts;
      });
    },
    //////TITLE: ADDS A SLECTED OPTION//////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
    addSelected(selectedoption, options) {
      if (this.single == true) {
        options[0] = selectedoption;
      } else {
        options.push(selectedoption);
      }
    },
    filterIt(arr, searchKey) {
      return arr.filter(function (obj) {
        return Object.keys(obj).some(function (key) {
          return obj[key].toLowerCase().includes(searchKey.toLowerCase());
        });
      });
    },
    //////TITLE: REMOVES A SLECTED OPTION//////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
    removeSelected(option, options) {
      this.selected[0] = "";
      this.selected[1] = "";
    },

    //////TITLE:  CHECKS IF SELECTED OR NOT//////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
    ifSelected(option, options) {
      const index = options.indexOf(option);
      if (index > -1) {
        return false;
      } else {
        return true;
      }
    },
    //////TITLE:  CHECKS IF IN SEARCH//////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////DESCRIPTION: CHECKS IF ITEM CONTAINS STRING
    ifInSearch(option, searchString) {
      item = option.toLowerCase();
      string = searchString.toLowerCase();

      if (item.includes(string)) {
        return true;
      } else {
        return false;
      }
    },
    saveFont(font, chosen) {
      this.selected[0] = font.fontName;
      this.selected[1] = font.category;
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
      <div class="uip-margin-bottom-xs uip-padding-left-xxs uip-padding-right-xxs uip-padding-top-xxs uip-background-default uip-border uip-border-round uip-w-200 uip-cursor-pointer uip-h-32 uip-border-box"> \
        <div class=" uip-flex ">\
          <div class="uip-flex-grow">\
            <span v-if="!selected[0]" class="selected-item" style="background: none;">\
              <span class="uk-text-meta">Select {{name}}...</span>\
            </span>\
            <span v-if="selected[0]"  class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-padding-bottom-remove uip-padding-top-remove uip-display-inline-block uip-margin-right-xxs uip-margin-bottom-xxs">\
              <span>\
                {{selected[0]}}\
                <a class="uk-margin-small-left" href="#" @click="removeSelected(select,selected)">x</a>\
              </span>\
            </span>\
          </div>\
          <span class="material-icons-outlined uip-text-muted">expand_more</span>\
        </div>\
      </div>\
      <div v-if="ui.dropOpen" class="uip-position-absolute uip-padding-s uip-background-default uip-border-round uip-border uip-shadow uip-w-400 uip-border-box uip-z-index-9">\
        <div class="uip-flex uip-background-muted uip-padding-xxs uip-margin-bottom-s uip-border-round">\
          <span class="material-icons-outlined uip-text-muted uip-margin-right-xs">search</span>\
          <input class="uip-blank-input uip-flex-grow" type="text" \
          :placeholder="placeholder" v-model="fontSearch">\
        </div>\
        <div class="uip-h-200 uip-overflow-auto">\
          <ul v-for="option in allFonts">\
            <li @click="saveFont(option, selected)" class="uip-text-l" v-bind:style="{ \'font-family\': option.fontFamily}">\
              <span class="uip-link-muted uip-no-underline">{{option.fontName}}</span>\
            </li>\
          </ul>\
        </div>\
      </div>\
    </div>',
});

UIPstyles.component("uip-offcanvas", {
  props: {
    icon: String,
    translations: Object,
    buttonsize: String,
    tooltip: Boolean,
    tooltiptext: String,
    type: String,
    buttontext: String,
    title: String,
  },
  data: function () {
    return {
      create: {
        open: false,
      },
    };
  },

  methods: {
    openOffcanvas() {
      this.create.open = true;
    },
    closeOffcanvas() {
      if (document.activeElement) {
        document.activeElement.blur();
      }
      this.create.open = false;
    },
    returnButtonSize() {
      if (this.buttonsize && this.buttonsize == "small") {
        return "uip-padding-xxs";
      } else {
        return "uip-padding-xs";
      }
    },
  },
  template:
    '<div class="">\
        <div @click="openOffcanvas()" :class="returnButtonSize()" type="button"\
        class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-flex" >\
          <span class="material-icons-outlined uip-margin-right-xxs">{{icon}}</span>\
          <span >{{buttontext}}</span>\
        </div>\
      </div>\
      <div v-if="create.open" class="uip-position-fixed uip-w-100p uip-h-viewport uip-hidden uip-text-normal" \
      style="background:rgba(0,0,0,0.3);z-index:99999;top:0;left:0;right:0;max-height:100vh" \
      :class="{\'uip-nothidden\' : create.open}">\
        <!-- MODAL GRID -->\
        <div class="uip-flex uip-w-100p uip-overflow-auto">\
          <div class="uip-flex-grow" @click="closeOffcanvas()" ></div>\
          <div class="uip-w-500 uip-background-default uip-padding-m uip-h-viewport uip-overflow-auto" >\
            <div  style="max-height: 100vh;">\
              <!-- OFFCANVAS TITLE -->\
              <div class="uip-flex uip-margin-bottom-s">\
                <div class="uip-text-xl uip-text-bold uip-flex-grow">{{title}}</div>\
                <div class="">\
                   <span @click="closeOffcanvas()"\
                    class="material-icons-outlined uip-background-muted uip-padding-xxs uip-border-round hover:uip-background-grey uip-cursor-pointer">\
                       close\
                    </span>\
                </div>\
              </div>\
              <slot></slot>\
            </div>\
          </div>\
        </div>\
      </div>',
});

UIPstyles.component("theme-selector", {
  props: {
    translations: Object,
  },
  data: function () {
    return {
      searchString: "",
      themes: [],
      loading: true,
    };
  },
  mounted: function () {
    datepicker = this;
    this.getThemes();
  },
  computed: {
    formattedThemes() {
      return this.themes;
    },
  },
  methods: {
    getThemes() {
      let self = this;
      data = {
        action: "uip_get_themes",
        security: uip_ajax.security,
      };
      jQuery.ajax({
        url: uip_ajax.ajax_url,
        type: "post",
        data: data,
        success: function (response) {
          data = JSON.parse(response);
          self.loading = false;
          if (data.error) {
            ///SOMETHING WENT WRONG
            console.log(data.message);
          } else {
            ///SOMETHING WENT RIGHT
            self.themes = data;
            console.log(self.themes);
          }
        },
      });
    },
    heartTheme(theme) {
      let self = this;
      data = {
        action: "uip_love_theme",
        security: uip_ajax.security,
        id: theme.id,
      };
      jQuery.ajax({
        url: uip_ajax.ajax_url,
        type: "post",
        data: data,
        success: function (response) {
          data = JSON.parse(response);
          console.log(data);
          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.message);
          } else if (data.state && data.state == "false") {
            uipNotification(data.message);
            theme.likes = parseInt(theme.likes) + 1;
          } else {
            ///SOMETHING WENT RIGHT
            uipNotification(data.message);
            theme.likes = parseInt(theme.likes) + 1;
          }
        },
      });
    },
    importTheme(theme) {
      let self = this;

      this.$emit("import-theme", theme.json);
      uipNotification(self.translations.themeImported, { pos: "bottom-left", status: "primary" });
      //this.$emit("remove-col");
    },
    isInSearch(theme) {
      thename = theme.theme_title.toLowerCase();
      desc = theme.description.toLowerCase();
      searchlc = this.searchString.toLowerCase();

      if (thename.includes(searchlc) || desc.includes(searchlc)) {
        return true;
      }

      return false;
    },
  },
  template:
    '<div style="padding-bottom:100px">\
        <div class="uip-margin-bottom-m uip-padding-xs uip-background-muted uip-border-round">\
          <div class="uip-flex uip-flex-center">\
            <span class="uip-margin-right-xs uip-text-muted">\
              <span class="material-icons-outlined">search</span>\
            </span> \
            <input type="search" :placeholder="translations.searchCards" class="uip-blank-input uip-flex-grow" \
            v-model="searchString" autofocus>\
          </div>\
        </div>\
        <div class="uip-w-100p uip-margin-padding-l">\
          <div class="uip-grid uip-grid-small uip-inline-flex uip-flex-wrap uip-flex-row ">\
            <loading-placeholder v-if="loading"></loading-placeholder>\
            <template v-for="theme in formattedThemes" >\
              <div v-if="isInSearch(theme)" class="uip-width-100p">\
                <div class="uip-border-round uip-background-muted uip-padding-s uip-margin-bottom-m uip-flex uip-flex-column">\
                  <img :src="theme.img" class="uip-w-100p uip-border-round uip-margin-bottom-s">\
                  <div class="uip-flex uip-flex-row uip-margin-bottom-s">\
                    <div class="uip-text-bold uip-text-emphasis  uip-flex-grow uip-text-l">{{theme.theme_title}}</div>\
                    <div class="uip-flex uip-gap-xxs">\
                      <span  v-for="type in theme.theme_type"\
                      class="uip-background-primary-wash uip-border-round uip-padding-left-xxs uip-padding-right-xxs uip-text-bold uip-text-xs">\
                        {{type}}\
                      </span>\
                    </div>\
                  </div>\
                  <div class="uip-text-default uip-margin-bottom-s">{{theme.description}}</div>\
                  <div class="uip-flex uip-flex-center uip-gap-xs">\
                    <div class="uip-flex uip-flex-end uip-flex-left uip-flex-grow">\
                      <button @click="importTheme(theme)" v-if="!theme.locked" class="uip-button-secondary">{{translations.importTheme}}</button>\
                      <a href="https://uipress.co/pricing" target="_BLANK" v-if="theme.locked" class="uip-button-primary uip-flex uip-no-underline">\
                        <span class="material-icons-outlined uip-margin-right-xxs">lock</span>\
                        <span> {{translations.proTemplate}}</span>\
                      </a>\
                    </div>\
                    <div v-if="theme.created_by" class="uip-text-muted uip-flex uip-gap-xxs">\
                     <span class="material-icons-outlined">person</span>\
                     <span>{{theme.created_by}}</span>\
                    </div>\
                    <div  class="uip-text-muted uip-flex uip-gap-xxs" @click="heartTheme(theme)">\
                     <span class="material-icons-outlined uip-cursor-pointer">favorite</span>\
                     <span>{{theme.likes}}</span>\
                    </div>\
                  </div>\
                </div>\
              </div>\
            </template>\
          </div>\
        </div>\
    </div>',
});

UIPstyles.component("loading-placeholder", {
  data: function () {
    return {};
  },
  methods: {
    doStuff() {},
  },
  template:
    '<svg class="uip-w-100p uip-margin-bottom-m" role="img" width="340" height="84" aria-labelledby="loading-aria" viewBox="0 0 340 84" preserveAspectRatio="none">\
      <title id="loading-aria">Loading...</title>\
      <rect x="0" y="0" width="100%" height="100%" clip-path="url(#clip-path)" style=\'fill: url("#fill");\'></rect>\
      <defs>\
        <clipPath id="clip-path">\
          <rect x="0" y="0" rx="3" ry="3" width="67" height="11" />\
          <rect x="76" y="0" rx="3" ry="3" width="140" height="11" />\
          <rect x="127" y="48" rx="3" ry="3" width="53" height="11" />\
          <rect x="187" y="48" rx="3" ry="3" width="72" height="11" />\
          <rect x="18" y="48" rx="3" ry="3" width="100" height="11" />\
          <rect x="0" y="71" rx="3" ry="3" width="37" height="11" />\
          <rect x="18" y="23" rx="3" ry="3" width="140" height="11" />\
          <rect x="166" y="23" rx="3" ry="3" width="173" height="11" />\
        </clipPath>\
        <linearGradient id="fill">\
          <stop offset="0.599964" stop-color="rgba(156, 155, 155, 13%)" stop-opacity="1">\
            <animate attributeName="offset" values="-2; -2; 1" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>\
          </stop>\
          <stop offset="1.59996" stop-color="rgba(156, 155, 155, 20%)" stop-opacity="1">\
            <animate attributeName="offset" values="-1; -1; 2" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>\
          </stop>\
          <stop offset="2.59996" stop-color="rgba(156, 155, 155, 13%)" stop-opacity="1">\
            <animate attributeName="offset" values="0; 0; 3" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>\
          </stop>\
        </linearGradient>\
      </defs>\
    </svg>\
    <svg class="uip-w-100p" role="img" width="340" height="84" aria-labelledby="loading-aria" viewBox="0 0 340 84" preserveAspectRatio="none">\
      <title id="loading-aria">Loading...</title>\
      <rect x="0" y="0" width="100%" height="100%" clip-path="url(#clip-path)" style=\'fill: url("#fill");\'></rect>\
      <defs>\
        <clipPath id="clip-path">\
          <rect x="0" y="0" rx="3" ry="3" width="67" height="11" />\
          <rect x="76" y="0" rx="3" ry="3" width="140" height="11" />\
          <rect x="127" y="48" rx="3" ry="3" width="53" height="11" />\
          <rect x="187" y="48" rx="3" ry="3" width="72" height="11" />\
          <rect x="18" y="48" rx="3" ry="3" width="100" height="11" />\
          <rect x="0" y="71" rx="3" ry="3" width="37" height="11" />\
          <rect x="18" y="23" rx="3" ry="3" width="140" height="11" />\
          <rect x="166" y="23" rx="3" ry="3" width="173" height="11" />\
        </clipPath>\
        <linearGradient id="fill">\
          <stop offset="0.599964" stop-color="rgba(156, 155, 155, 13%)" stop-opacity="1">\
            <animate attributeName="offset" values="-2; -2; 1" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>\
          </stop>\
          <stop offset="1.59996" stop-color="rgba(156, 155, 155, 20%)" stop-opacity="1">\
            <animate attributeName="offset" values="-1; -1; 2" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>\
          </stop>\
          <stop offset="2.59996" stop-color="rgba(156, 155, 155, 13%)" stop-opacity="1">\
            <animate attributeName="offset" values="0; 0; 3" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>\
          </stop>\
        </linearGradient>\
      </defs>\
    </svg>',
});

/////////////////////////
//FETCHES THE ADMIN MENU
/////////////////////////
UIPstyles.component("feature-flag", {
  props: {
    translations: Object,
  },
  data: function () {
    return {
      loading: true,
    };
  },
  mounted: function () {},
  methods: {},
  template:
    '<a href="https://uipress.co/pricing/" target="_BLANK" class="uip-no-underline uip-border-round uip-background-primary-wash uip-text-bold uip-text-emphasis uip-display-inline-block" style="padding: var(--uip-padding-button)">\
      <div class="uip-flex">\
        <span class="material-icons-outlined uip-margin-right-xs">redeem</span> \
        <span>{{translations.preFeature}}</span>\
      </div> \
    </a>',
});

if (jQuery("#uip-styles").length > 0) {
  UIPstyles.mount("#uip-styles");
}
