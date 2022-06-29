var mediaUploader;
const UIPsettingsOptions = {
  data() {
    return {
      loading: true,
      screenWidth: window.innerWidth,
      translations: uipTranslations,
      masterPrefs: uipMasterPrefs,
      defaults: uipDefaults,
      preferences: uipUserPrefs,
      network: uipNetwork,
      settingsObject: {
        menu: {},
        toolbar: {},
      },
      currentModule: "general",
    };
  },
  watch: {},
  created: function () {
    window.addEventListener("resize", this.getScreenWidth);
  },
  computed: {
    formattedSettings() {
      return this.settingsObject;
    },
  },
  mounted: function () {
    this.getOptions();

    let searchParams = new URLSearchParams(window.location.search);
    if (searchParams.has("section")) {
      let param = searchParams.get("section");
      this.currentModule = param;
    }
  },
  methods: {
    getScreenWidth() {
      this.screenWidth = window.innerWidth;
    },
    isSmallScreen() {
      if (this.screenWidth < 1000) {
        return true;
      } else {
        return false;
      }
    },
    activeModule(module, optionlink) {
      var searchParams = new URLSearchParams(window.location.search);
      searchParams.set("section", module);
      var newRelativePathQuery = window.location.pathname + "?" + searchParams.toString();

      if (optionlink) {
        newRelativePathQuery = newRelativePathQuery + "#" + optionlink;
      }
      history.pushState(null, "", newRelativePathQuery);

      this.currentModule = module;

      if (optionlink) {
        let chosenOptions = document.getElementById(optionlink);
        chosenOptions.classList.add("uip-focus-in");
        chosenOptions.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
      }
    },
    getOptions() {
      let self = this;
      data = {
        action: "uip_get_options",
        security: uip_ajax.security,
        network: this.network,
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
            self.settingsObject = data.options;
          }
        },
      });
    },
    saveSettings() {
      let self = this;

      data = {
        action: "uip_save_options",
        security: uip_ajax.security,
        options: self.settingsObject,
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
    confirmResetSettings() {
      let self = this;
      if (confirm(self.translations.confirmReset)) {
        self.resetSettings();
      }
    },
    resetSettings() {
      let self = this;

      data = {
        action: "uip_reset_options",
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
            uipNotification(self.translations.somethingWrong);
          } else {
            ///SOMETHING WENT RIGHT
            uipNotification(data.message);
            self.getOptions();
          }
        },
      });
    },
    exportSettings() {
      self = this;
      ALLoptions = JSON.stringify(self.settingsObject);

      var today = new Date();
      var dd = String(today.getDate()).padStart(2, "0");
      var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
      var yyyy = today.getFullYear();

      date_today = mm + "_" + dd + "_" + yyyy;
      filename = "uip-settings-" + date_today + ".json";

      var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(ALLoptions);
      var dlAnchorElem = document.getElementById("uip-export-settings");
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
          self.settingsObject = parsed;
          uipNotification(self.translations.settingsImported);
        } else {
          uipNotification(self.translations.somethingWrong);
        }
      };
    },
  },
};
const UIPsettings = uipVue.createApp(UIPsettingsOptions);

UIPsettings.component("settings-menu", {
  props: {
    translations: Object,
    alloptions: Object,
    updatemodule: Function,
    activemodule: String,
  },
  data: function () {
    return {
      loading: true,
      settings: this.alloptions,
    };
  },
  watch: {
    alloptions: {
      handler(val, oldVal) {
        this.settings = val;
      },
      deep: true,
    },
  },
  mounted: function () {
    this.loading = false;
  },
  computed: {
    returnSettings() {
      return this.settings;
    },
  },
  template:
    '<template v-for="cat in returnSettings">\
      <div class="uip-flex uip-margin-bottom-xxs" v-if="cat.module_name">\
          <a v-if="cat.label" href="#" @click="updatemodule(cat.module_name)" \
          class="uip-link-muted uip-text-m uip-no-outline uip-text-bold uip-no-underline uip-flex uip-padding-xxs uip-border-round hover:uip-background-muted uip-flex-grow"\
          :class="{\'uip-text-emphasis uip-background-muted\' : cat.module_name == activemodule}">\
            <span class="material-icons-outlined uip-margin-right-xs">{{cat.icon}}</span>\
            {{cat.label}}\
          </a>\
      </div>\
    </template>',
});

UIPsettings.component("settings-menu-alt", {
  props: {
    translations: Object,
    alloptions: Object,
    updatemodule: Function,
    activemodule: String,
  },
  data: function () {
    return {
      loading: true,
      settings: this.alloptions,
    };
  },
  watch: {
    alloptions: {
      handler(val, oldVal) {
        this.settings = val;
      },
      deep: true,
    },
  },
  mounted: function () {
    this.loading = false;
  },
  computed: {
    returnSettings() {
      return this.settings;
    },
  },
  template:
    '<template v-for="cat in returnSettings">\
      <div class="uip-flex uip-flex-row uip-margin-bottom-xxs uip-margin-right-xxs" v-if="cat.module_name">\
          <span v-if="cat.label" href="#" @click="updatemodule(cat.module_name)" \
          class="uip-text-muted uip-text-m uip-cursor-pointer uip-text-bold uip-flex uip-padding-xs uip-border-round uip-flex-grow uip-background-muted"\
          :class="{\'uip-text-emphasis uip-background-primary uip-text-inverse\' : cat.module_name == activemodule}">\
            <span class="material-icons-outlined uip-margin-right-xs">{{cat.icon}}</span>\
            {{cat.label}}\
          </span>\
      </div>\
    </template>',
});

UIPsettings.component("settings-menu-vertical", {
  props: {
    translations: Object,
    alloptions: Object,
    updatemodule: Function,
    activemodule: String,
  },
  data: function () {
    return {
      loading: true,
      settings: this.alloptions,
      searchString: "",
    };
  },
  watch: {
    alloptions: {
      handler(val, oldVal) {
        this.settings = val;
      },
      deep: true,
    },
  },
  mounted: function () {
    this.loading = false;
  },
  computed: {
    returnSettings() {
      console.log(this.settings);
      return this.settings;
    },
  },
  methods: {
    ifInChildren(category) {
      if (!category.label) {
        return false;
      }
      item = category.label.toLowerCase();
      string = this.searchString.toLowerCase();
      let children = false;

      if (string.length < 1) {
        return true;
      }

      //IF CATEGORY NAME INCLUDES STRING
      if (item.includes(string)) {
        return true;
      }

      for (var option in category.options) {
        let currentOption = category.options[option];

        if (this.ifInSearch(currentOption)) {
          children = true;
          break;
        }
      }
      return children;
    },
    ifInSearch(option) {
      item = option.name.toLowerCase();
      desc = option.description.toLowerCase();
      string = this.searchString.toLowerCase();

      if (string.length < 1) {
        return true;
      }

      if (item.includes(string) || desc.includes(string)) {
        return true;
      } else {
        return false;
      }
    },
  },
  template:
    '<input type="search" v-model="searchString" :placeholder="translations.search" class="uip-w-100p uip-margin-bottom-m">\
    <template v-for="cat in returnSettings">\
      <div v-if="ifInChildren(cat)">\
        <div class="uip-flex uip-flex-row uip-margin-bottom-xxs uip-margin-right-xxs" v-if="cat.module_name">\
            <span v-if="cat.label" href="#" @click="updatemodule(cat.module_name)" \
            class="uip-text-muted uip-text-m uip-cursor-pointer uip-text-bold uip-flex uip-padding-xxs uip-border-round uip-flex-grow "\
            :class="{\'uip-text-emphasis uip-background-muted uip-text-emphasis\' : cat.module_name == activemodule}">\
              <span class="material-icons-outlined uip-margin-right-xs">{{cat.icon}}</span>\
              {{cat.label}}\
            </span>\
        </div>\
        <div v-if="cat.module_name == activemodule || searchString.length > 0" class="uip-padding-xxs uip-margin-top-xs" style="margin-left:28px;">\
          <template v-for="option in cat.options">\
            <div v-if="ifInSearch(option)" class="uip-text-muted uip-margin-bottom-xs uip-cursor-pointer uip-link-muted uip-max-w-200 uip-overflow-hidden uip-no-wrap uip-text-ellipsis" @click="updatemodule(cat.module_name, option.optionName)">{{option.name}}</div>\
          </template>\
        </div>\
      </div>\
    </template>',
});

UIPsettings.component("uip-offcanvas", {
  props: {
    icon: String,
    translations: Object,
    buttonsize: String,
    type: String,
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
          <span class="material-icons-outlined">{{icon}}</span>\
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
                <div class="uip-text-xl uip-text-bold uip-flex-grow">{{translations.settings}}</div>\
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

UIPsettings.component("uip-dropdown-new", {
  props: {
    type: String,
    icon: String,
    pos: String,
    translation: String,
    buttonsize: String,
    tooltip: Boolean,
    tooltiptext: String,
  },
  data: function () {
    return {
      modelOpen: false,
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
      submenu = self.$el.getElementsByClassName("uip-dropdown-conatiner")[0];
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
      returnDatat = 0;
      ///SET TOP

      if (!self.$el) {
        return;
      }

      let POStop = self.$el.getBoundingClientRect().bottom + 10;
      let POSright = self.$el.getBoundingClientRect().right - 252;

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
      if (this.buttonsize && this.buttonsize == "small") {
        return "uip-padding-xxs";
      } else if (this.buttonsize && this.buttonsize == "normal") {
        return "uip-padding-xs";
      } else {
        return "uip-padding-xxs";
      }
    },
  },
  template:
    '<div class="uip-position-relative ">\
      <div class="">\
        <div v-if="type == \'icon\'" @click="openThisComponent" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined" type="button" :class="returnButtonSize()">{{icon}}</div>\
        <div v-else @click="openThisComponent" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined" type="button" :class="returnButtonSize()">{{icon}}</div>\
      </div>\
      <div v-if="modelOpen" :style="setPosition()" ref="uipdrop"\
      class="uip-position-fixed uip-dropdown-conatiner uip-background-default uip-border-round uip-border uip-min-w-250 uip-z-index-9999 uip-scale-in">\
        <slot></slot>\
      </div>\
    </div>',
});

/////////////////////////
//OUTPUTS UIPRESS SETTINGS
/////////////////////////
UIPsettings.component("output-options", {
  props: {
    translations: Object,
    alloptions: Object,
    activemodule: String,
  },
  data: function () {
    return {
      loading: true,
      settings: this.alloptions,
    };
  },
  watch: {
    alloptions: function (newValue, oldValue) {
      this.settings = newValue;
    },
  },
  mounted: function () {
    this.loading = false;
  },
  computed: {
    returnSettings() {
      return this.settings;
    },
  },
  methods: {
    getDataFromComp(originalcode, editedcode) {
      return editedcode;
    },
    chooseImage(theOption) {
      self = this;
      mediaUploader = wp.media.frames.file_frame = wp.media({
        title: self.translations.chooseImage,
        button: {
          text: self.translations.chooseImage,
        },
        multiple: false,
      });
      mediaUploader.on("select", function () {
        var attachment = mediaUploader.state().get("selection").first().toJSON();
        theOption.value = attachment.url;
      });
      mediaUploader.open();
    },
    returnInputType(option) {
      if (option.password) {
        return "password";
      }
      return "text";
    },
    returnstate(state) {
      this.alloptions.dataConnect = state;
    },
  },
  template:
    '<output-licence :appData="alloptions" :translations="translations" v-if="activemodule == \'general\'" :returnstate="returnstate"></output-licence>\
    <div v-for="cat in returnSettings" class="uip-margin-top-l">\
      <div v-if="cat.module_name == activemodule" v-for="(option, index) in cat.options" :id="option.optionName">\
        <div class="uip-flex uip-margin-bottom-m uip-border-bottom uip-padding-bottom-m">\
          <div class="uip-w-300">\
            <div class="uip-text-bold uip-text-l uip-margin-bottom-xs uip-text-normal">{{option.name}}</div>\
            <div class="uip-text-muted">{{option.description}}</div>\
          </div>\
          <div class="uip-flex-grow uip-padding-left-l" v-if="option.premium == true && alloptions.dataConnect == !true">\
            <premium-feature :translations="translations"></premium-feature>\
          </div>\
          <div class="uip-flex-grow uip-padding-left-l" v-else>\
            <!-- SWITCH -->\
            <div v-if="option.type == \'switch\'" >\
              <label class="uip-switch">\
                <input type="checkbox" v-model="option.value">\
                <span class="uip-slider"></span>\
              </label>\
            </div>\
            <!-- SWITCH -->\
            <!-- COLOR -->\
            <div v-if="option.type == \'color\'" class="uip-margin-bottom-m uip-padding-xxs uip-background-default uip-border-round uip-w-200">\
              <div class="uip-flex uip-flex-center">\
                <span class="uip-margin-right-xs uip-text-muted uip-margin-right-s">\
                    <label class="uip-border-circle uip-h-18 uip-w-18 uip-border uip-display-block" v-bind:style="{\'background-color\' : option.value}">\
                      <input\
                      type="color"\
                      v-model="option.value" style="visibility: hidden;">\
                    </label>\
                </span> \
                <input v-model="option.value" type="search" placeholder="#HEX" class="uip-blank-input uip-margin-right-s " style="min-width:0;">\
                <span class="uip-text-muted">\
                    <span class="material-icons-outlined uip-text-muted">color_lens</span>\
                </span> \
              </div>\
            </div>\
            <!-- COLOR -->\
            <!-- ROLE SELECT -->\
            <div v-if="option.type == \'user-role-select\'">\
              <multi-select :selected="option.value"\
              :name="translations.chooseUserRole"\
              :single=\'false\'\
              :placeholder="translations.searchUserRole"></multi-select>\
            </div>\
            <!-- ROLE SELECT -->\
            <!-- POST TYPE SELECT -->\
            <div v-if="option.type == \'post-type-select\'">\
              <multi-select-posts :selected="option.value"\
              :name="translations.choosePostTypes"\
              :translations="translations"\
              :single=\'false\'\
              :placeholder="translations.searchPostTypes"></multi-select-posts>\
            </div>\
            <!-- POST TYPE SELECT -->\
            <!-- IMAGE -->\
            <div v-if="option.type == \'image\'" class="uip-display-inline-block">\
              <div v-if="!option.value" class="uip-flex uip-flex-center uip-flex-middle uip-background-default uip-border uip-padding-l uip-border-round uip-margin-bottom-xs uip-cursor-pointer" @click="chooseImage(option)">\
                <span class="uip-text-muted">{{translations.chooseImage}}</span>\
              </div>\
              <img v-if="option.value" class="uip-h-150 uip-border-round uip-margin-bottom-xs uip-cursor-pointer" :src="option.value"  @click="chooseImage(option)">\
              <div class="uip-flex">\
                <input class="uip-flex-grow uip-margin-right-xs uip-standard-input" type="text" placeholder="URL..." v-model="option.value">\
                <span class="uip-background-muted material-icons-outlined uip-padding-xxs uip-border-round hover:uip-background-grey uip-cursor-pointer uip-text-normal"\
                @click="option.value = \'\'">delete</span>\
              </div>\
            </div>\
            <!-- IMAGE -->\
            <!-- TEXT -->\
            <div v-if="option.type == \'text\'" class="uip-display-inline-block">\
              <input :type="returnInputType(option)" v-model="option.value" >\
            </div>\
            <!-- TEXT -->\
            <!-- NUMBER -->\
            <div v-if="option.type == \'number\'" class="uip-display-inline-block">\
              <input type="number" :min="option.minimum" v-model="option.value" >\
            </div>\
            <!-- NUMBER -->\
            <!-- TEXTAREA -->\
            <div v-if="option.type == \'textarea\'" class="uip-display-inline-block">\
              <textarea class="uip-w-300 uip-h-150" type="text" v-model="option.value"></textarea>\
            </div>\
            <!-- TEXTAREA -->\
            <!-- CODE -->\
            <div v-if="option.type == \'code-block\'" class="uip-display-inline-block">\
              <code-block :language="option.language" :usercode="option.value" @code-change="option.value = getDataFromComp(option.value, $event)"></code-block>\
            </div>\
            <!-- CODE -->\
            <!-- MULTIPLE TEXT -->\
            <div v-if="option.type == \'multiple-text\'" class="uip-w-300">\
              <button class="uip-button-default uip-margin-bottom-s" @click="option.value.push(\'\')">\
              {{translations.addFile}}</button>\
              <div v-for="(ascript,index) in option.value">\
                <div class="uip-flex uip-margin-bottom-s">\
                  <div class="uip-flex-grow">\
                    <input :placeholder="translations.urlToFile" \
                    class="uip-standard-input" v-model="option.value[index]" type="text">\
                  </div>\
                  <div class="uip-margin-left-xs">\
                    <span @click="option.value.splice(index, 1)"\
                    class="uip-background-muted material-icons-outlined uip-padding-xxs uip-border-round hover:uip-background-grey uip-cursor-pointer">delete</span>\
                  </div>\
                </div>\
              </div>\
            </div>\
            <!-- MULTIPLE TEXT -->\
          </div>\
        </div>\
      </div>\
    </div>',
});

/////////////////////////
//CREATES A CODE BLOCK
/////////////////////////
const highlight = (editor) => {
  editor.textContent = editor.textContent;
  hljs.highlightBlock(editor);
};

let editorOptions = {
  tab: " ".repeat(2), // default is \t
};

UIPsettings.component("code-block", {
  data: function () {
    return {
      created: false,
      unformatted: this.usercode,
    };
  },
  props: {
    language: String,
    usercode: String,
  },
  computed: {
    returnCode() {
      return this.unformatted;
    },
  },
  mounted: function () {
    this.testel();
  },
  methods: {
    codeChange(thecode) {
      this.$emit("code-change", thecode);
      //self.usercode = code;
    },
    //////TITLE: ADDS A SLECTED OPTION//////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
    testel() {
      self = this;
      const editor = this.$el;
      const jar = new CodeJar(editor, highlight, editorOptions);

      jar.onUpdate((code) => {
        this.codeChange(code);
      });
    },
  },
  template: '<div class="editor uip-w-400" :class="language"  data-gramm="false">{{returnCode}}</div> ',
});

/////////////////////////
//LICENCE ACTIVATION MODULE
/////////////////////////
UIPsettings.component("output-licence", {
  props: {
    translations: Object,
    appData: Object,
    returnstate: Function,
  },
  data: function () {
    return {
      licenceKey: "",
      connect: uipMasterPrefs.dataConnect,
    };
  },
  computed: {
    isActive() {
      return this.connect;
    },
  },
  mounted: function () {},
  methods: {
    checkProLicence() {
      self = this;
      jQuery.ajax({
        url: uip_ajax.ajax_url,
        type: "post",
        data: {
          action: "uip_check_licence_key",
          security: uip_ajax.security,
          key: self.licenceKey,
        },
        success: function (response) {
          data = JSON.parse(response);
          if (data.errorMessage) {
            ///SOMETHING WENT WRONG
            uipNotification(data.errorMessage);
            if (data.errors) {
              for (const key in data.errors) {
                cat = "**" + key + "** " + data.errors[key];
                ///SOMETHING WENT WRONG
                uipNotification(cat);
              }
            }
            return;
          }
          if (data.activated == true) {
            self.connect = true;
            uipNotification(data.message);
            self.returnstate(true);
          }
          //uipNotification(data);
        },
      });
    },
    removeLicence() {
      self = this;
      jQuery.ajax({
        url: uip_ajax.ajax_url,
        type: "post",
        data: {
          action: "uip_remove_licence_key",
          security: uip_ajax.security,
        },
        success: function (response) {
          data = JSON.parse(response);
          if (data.errorMessage) {
            ///SOMETHING WENT WRONG
            uipNotification(data.errorMessage);
            return;
          }
          self.connect = false;
          uipNotification(data.message);
          self.returnstate(false);
        },
      });
    },
  },
  template:
    '<div class="uip-margin-bottom-l uip-padding-s uip-border-round uip-background-primary-wash">\
      <div class="uip-margin-bottom-s" v-if="!isActive">\
        <div class="uip-text-bold uip-text-emphasis uip-text-l uip-margin-bottom-xs">UiPress Pro</div>\
        <div class="uip-text-muted">{{translations.addProLicence}}</div>\
      </div>\
      <div class="uip-flex" v-if="!isActive">\
        <div class="uip-padding-right-s">\
          <input v-model="licenceKey" class="uip-w-400" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" type="text">\
        </div>\
        <div>\
          <button class="uip-button-primary" type="button" @click="checkProLicence()">{{translations.activate}}</button>\
        </div>\
      </div>\
      <div class="uip-margin-bottom-s" v-if="isActive">\
        <div class="uip-text-bold uip-text-emphasis uip-text-l uip-margin-bottom-xs">{{translations.uipressPro}}</div>\
        <div class="uip-text-muted">{{translations.isActivated}}</div>\
      </div>\
      <div class="uip-flex" v-if="isActive">\
        <div>\
          <button class="uip-button-primary" type="button" @click="removeLicence()">{{translations.removeLicence}}</button>\
        </div>\
      </div>\
    </div>',
});
/////////////////////////
//Multi Select POST TYPES
/////////////////////////
UIPsettings.component("multi-select-posts", {
  data: function () {
    return {
      thisSearchInput: "",
      options: [],
      ui: {
        dropOpen: false,
      },
    };
  },
  props: {
    selected: Array,
    name: String,
    placeholder: String,
    single: Boolean,
    translations: Object,
  },
  computed: {
    formattedOptions() {
      return this.options;
    },
  },
  methods: {
    getPostTypes() {
      self = this;

      jQuery.ajax({
        url: uip_ajax.ajax_url,
        type: "post",
        data: {
          action: "uip_get_post_types",
          security: uip_ajax.security,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error);
            return;
          }

          self.options = data;
        },
      });
    },
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
        options.push(selectedoption);
      }
    },
    //////TITLE: REMOVES A SLECTED OPTION//////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
    removeSelected(option, options) {
      const index = options.indexOf(option);
      if (index > -1) {
        options = options.splice(index, 1);
      }
    },

    //////TITLE:  CHECKS IF SELECTED OR NOT//////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
    ifSelected(option, options) {
      const index = options.indexOf(option);
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
      item = option.toLowerCase();
      string = searchString.toLowerCase();

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
      this.getPostTypes();
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
      <div class="uip-margin-bottom-xs uip-padding-left-xxs uip-padding-right-xxs uip-padding-top-xxs uip-background-default uip-border uip-border-round uip-w-100p uip-max-w-400 uip-cursor-pointer uip-h-32 uip-border-box" :class="{\'uip-active-outline\' : ui.dropOpen}"> \
        <div class="uip-flex uip-flex-center">\
          <div class="uip-flex-grow uip-margin-right-s">\
            <div v-if="selected.length < 1" style="margin-top:2px;">\
              <span class="uk-text-meta">{{name}}...</span>\
            </div>\
            <span v-if="selected.length > 0" class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-display-inline-block uip-margin-right-xxs uip-margin-bottom-xxs">\
              <div class="uip-text-primary uip-text-bold">\
                {{selected.length + " " + translations.selected}}\
              </div>\
            </span>\
          </div>\
          <span v-if="!ui.dropOpen" class="material-icons-outlined uip-text-muted">expand_more</span>\
          <span v-if="ui.dropOpen" class="material-icons-outlined uip-text-muted">expand_less</span>\
        </div>\
      </div>\
      <div v-if="ui.dropOpen" class="uip-position-absolute uip-background-default uip-border-round uip-border uip-w-100p uip-max-w-400 uip-border-box uip-z-index-9">\
        <div class="uip-flex uip-background-default uip-padding-xs uip-border-bottom">\
          <span class="material-icons-outlined uip-text-muted uip-margin-right-xs">search</span>\
          <input class="uip-blank-input uip-flex-grow" type="search"  \
          :placeholder="placeholder" v-model="thisSearchInput" autofocus>\
        </div>\
        <div class="uip-max-h-280 uip-overflow-auto">\
          <template v-for="option in formattedOptions">\
            <div class="uip-background-default uip-padding-xs hover:uip-background-muted" \
            @click="addSelected(option.name, selected)" \
            v-if="ifInSearch(option.name, thisSearchInput)" \
            style="cursor: pointer">\
              <div class="uip-flex uip-flex-row uip-flex-center">\
                <div class="uip-flex uip-flex-center uip-flex-middle uip-margin-right-xs">\
                  <input type="checkbox" :name="option.name" :value="option.name" :checked="ifSelected(option.name, selected)">\
                </div>\
                <div class="uip-flex-grow">\
                  <div class="uip-text-bold uip-text-emphasis">{{option.label}}</div>\
                  <div class="uip-text-muted">{{option.name}}</div>\
                </div>\
              </div>\
            </div>\
          </template>\
        </div>\
      </div>\
    </div>',
});

/////////////////////////
//Multi Select Component
/////////////////////////
UIPsettings.component("multi-select", {
  data: function () {
    return {
      thisSearchInput: "",
      options: [],
      notFoundMessage: "",
      ui: {
        dropOpen: false,
        searching: false,
      },
    };
  },
  props: {
    selected: Array,
    name: String,
    placeholder: String,
    single: Boolean,
  },
  watch: {
    thisSearchInput: function (newValue, oldValue) {
      self = this;

      if (newValue.length > 0) {
        self.ui.searching = true;
        jQuery.ajax({
          url: uip_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_get_users_and_roles",
            security: uip_ajax.security,
            searchString: newValue,
          },
          success: function (response) {
            data = JSON.parse(response);

            if (data.error) {
              ///SOMETHING WENT WRONG
              UIkit.notification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }

            self.options = data.roles;
            self.notFoundMessage = data.notfound;
            self.ui.searching = false;
          },
        });
      }
    },
  },
  methods: {
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
    //////TITLE: REMOVES A SLECTED OPTION//////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////DESCRIPTION: ADDS A SELECTED OPTION FROM OPTIONS
    removeSelected(option, options) {
      const index = options.indexOf(option);
      if (index > -1) {
        options = options.splice(index, 1);
      }
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
      <div class="uip-margin-bottom-xs uip-padding-left-xxs uip-padding-right-xxs uip-padding-top-xxs uip-background-default uip-border uip-border-round uip-w-100p uip-max-w-400 uip-cursor-pointer uip-h-32 uip-border-box" :class="{\'uip-active-outline\' : ui.dropOpen}"> \
        <div class="uip-flex uip-flex-center">\
          <div class="uip-flex-grow uip-margin-right-s">\
            <div v-if="selected.length < 1" style="margin-top:2px;">\
              <span class="uip-text-muted">{{name}}...</span>\
            </div>\
            <span v-if="selected.length > 0" v-for="select in selected" class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-display-inline-block uip-margin-right-xxs uip-margin-bottom-xxs">\
              <div class="uip-text-primary uip-text-bold">\
                {{select}}\
                <span class="uip-margin-left-xxs uip-text-muted" href="#" @click="removeSelected(select,selected)">x</span>\
              </div>\
            </span>\
          </div>\
          <span v-if="!ui.dropOpen" class="material-icons-outlined uip-text-muted">expand_more</span>\
          <span v-if="ui.dropOpen" class="material-icons-outlined uip-text-muted">expand_less</span>\
        </div>\
      </div>\
      <div v-if="ui.dropOpen" class="uip-position-absolute uip-background-default uip-border uip-border-round uip-w-100p uip-max-w-400 uip-border-box uip-z-index-9">\
        <div class="uip-flex uip-background-default uip-border-bottom uip-padding-xs uip-border-round">\
          <span class="material-icons-outlined uip-text-muted uip-margin-right-xs">search</span>\
          <input class="uip-blank-input uip-flex-grow" type="search"  \
          :placeholder="placeholder" v-model="thisSearchInput" autofocus>\
        </div>\
        <div class="uip-loading-box" v-if="ui.searching">\
          <div class="uip-loader"></div>\
        </div>\
        <div class="uip-max-h-280 uip-overflow-auto">\
          <template v-for="option in options">\
            <div  class="uip-background-default uip-padding-xs hover:uip-background-muted " \
            @click="addSelected(option.name, selected)" \
            v-if="ifSelected(option.name, selected) && ifInSearch(option.name, thisSearchInput)" \
            style="cursor: pointer">\
              <div class="uip-flex uip-flex-row uip-flex-center">\
                <div class="uip-background-dark uip-border-circle uip-w-28 uip-h-28 uip-flex uip-flex-center uip-flex-middle uip-margin-right-xs">\
                  <span class="uip-text-inverse uip-text-m uip-no-select uip-line-height-0 material-icons-outlined">{{option.icon}}</span>\
                </div>\
                <div class="uip-flex-grow">\
                  <div class="uip-text-bold uip-text-emphasis">{{option.label}}</div>\
                  <div class="uip-text-muted">{{option.type}}</div>\
                </div>\
              </div>\
            </div>\
          </template>\
          <div v-if="options.length < 1 && thisSearchInput.length > 0" class="uip-padding-s uip-text-center uip-text-muted">\
           {{notFoundMessage + " "}}<span class="uip-text-bold">{{thisSearchInput}}</span>\
          </div>\
        </div>\
      </div>\
    </div>',
});

/////////////////////////
//FETCHES THE ADMIN MENU
/////////////////////////
UIPsettings.component("premium-feature", {
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
    '<a href="https://uipress.co/pricing/" target="_BLANK" class="uip-no-underline uip-padding-xs uip-border-round uip-background-primary-wash uip-text-bold uip-text-emphasis uip-display-inline-block">\
        <div class="uip-flex">\
  	    <span class="material-icons-outlined uip-margin-right-xs">redeem</span>\
    	  <span>\
    		  {{translations.preFeature}}\
    	  </span>\
        </div>\
  	</a>',
});

if (jQuery("#uip-settings").length > 0) {
  UIPsettings.mount("#uip-settings");
}
