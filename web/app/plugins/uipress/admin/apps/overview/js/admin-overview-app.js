const uipressOverviewSettings = JSON.parse(uipress_overview_ajax.options);
const uipressOverviewModules = JSON.parse(uipress_overview_ajax.modules);
const uipressOverviewTranslations = JSON.parse(uipress_overview_ajax.translations);

const uipressOverviewArgs = {
  data() {
    return {
      loading: true,
      screenWidth: window.innerWidth,
      uipOverview: {
        data: {
          translations: uipressOverviewTranslations,
          dateRange: uipressOverviewSettings.user.dateRange,
          account: uipressOverviewSettings.dataConnect,
          globalDataObject: {
            data: [],
            loading: true,
          },
          ui: {
            editingMode: false,
            dateRangePicker: false,
            activeTab: "Home",
            currency: uipressOverviewSettings.user.currency,
          },
        },
        modules: uipressOverviewModules,
        settings: uipressOverviewSettings,
        dev: true,
      },
    };
  },
  created: function () {
    window.addEventListener("resize", this.getScreenWidth);
    var self = this;
  },
  computed: {
    originalMenu() {
      var originaltmen = this.master.menuItems;
      return originaltmen;
    },
    updateFromComponent(index, cardData) {
      console.log(index);
      console.log(cardData);
    },
    areWeEditing() {
      return this.uipOverview.data.ui.editingMode;
    },
    categoriesWithUID() {
      thecategories = this.uipOverview.settings.cards.formatted;
      let self = this;

      ///LOOP CATEGORYS
      thecategories.forEach(function (category, i) {
        if (!category.uid) {
          category.uid = self.makeid(30);
        }
      });

      return thecategories;
    },

    cardsWithIndex() {
      thecategories = this.uipOverview.settings.cards.formatted;
      newcats = [];
      let self = this;

      ///LOOP CATEGORYS
      thecategories.forEach(function (category, i) {
        var skip = false;

        if (self.uipOverview.data.ui.activeTab != "Home" && self.uipOverview.data.ui.activeTab != category.uid) {
          var skip = true;
        }

        if (!skip) {
          if (!category.columns) {
            category.columns = [];
          }
          thecolumns = category.columns;
          theCategoryIndex = i;
          tempColumns = [];
          category.id = theCategoryIndex;
          ///LOOP COLUMNS
          thecolumns.forEach(function (column, p) {
            thecards = column.cards;
            theColumnIndex = p;
            column.id = theCategoryIndex + "" + theColumnIndex;

            tempCards = [];

            if (!Array.isArray(thecards)) {
              thecards = [];
            }

            ///LOOP CARDS
            thecards.forEach(function (card, t) {
              theCardIndex = t;
              card.id = theCategoryIndex + "" + theColumnIndex + "" + theCardIndex + "" + card.name;
              if (!card.uid) {
                card.uid = self.makeid(30);
              }
              tempCards.push(card);
            });

            column.cards = tempCards;
            tempColumns.push(column);
          });
          category.columns = tempColumns;
          newcats.push(category);
        }
      });

      return newcats;
    },
  },
  mounted: function () {
    this.loading = false;
    this.build_global_data_object();
  },
  methods: {
    makeid(length) {
      var result = "";
      var characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
      var charactersLength = characters.length;
      for (var i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
      }
      return result;
    },
    log_dev_messages(message, type) {
      if (this.uipOverview.dev) {
        if (type == "error") {
          console.error(message);
        } else {
          console.log(message);
        }
      }
    },
    build_global_data_object() {
      let self = this;
      self.log_dev_messages("Started global data fetch", "message");
      self.uipOverview.data.globalDataObject.loading = true;

      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uip_build_global_data_object",
          security: uipress_overview_ajax.security,
          dateRange: self.uipOverview.data.dateRange,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            self.log_dev_messages(data.error, "error");
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            return;
          }
          self.globalDataObject = data;
          self.uipOverview.data.globalDataObject.data = data;
          self.uipOverview.data.globalDataObject.loading = false;
          console.log(self.globalDataObject);
          self.log_dev_messages("Finsihed global data fetch", "message");
        },
      });
    },
    import_default_layout() {
      let self = this;
      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uipress_import_default_layout",
          security: uipress_overview_ajax.security,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            return;
          }
          self.uipOverview.settings.cards.formatted = data;
          self.saveDash();
        },
      });
    },
    exportCards() {
      self = this;
      ALLoptions = JSON.stringify(self.uipOverview.settings.cards.formatted);

      var today = new Date();
      var dd = String(today.getDate()).padStart(2, "0");
      var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
      var yyyy = today.getFullYear();

      date_today = mm + "_" + dd + "_" + yyyy;
      filename = "uipress_dash_" + date_today + ".json";

      var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(ALLoptions);
      var dlAnchorElem = document.getElementById("uip_export_dash");
      dlAnchorElem.setAttribute("href", dataStr);
      dlAnchorElem.setAttribute("download", filename);
      dlAnchorElem.click();
    },
    importCards() {
      let self = this;
      let allTranslations = self.uipOverview.data.translations;

      var thefile = jQuery("#uipress_import_cards")[0].files[0];

      if (thefile.type != "application/json") {
        window.alert(allTranslations.validJSON);
        return;
      }

      if (thefile.size > 100000) {
        window.alert(allTranslations.fileBig);
        return;
      }

      var file = document.getElementById("uipress_import_cards").files[0];
      var reader = new FileReader();
      reader.readAsText(file, "UTF-8");

      reader.onload = function (evt) {
        json_settings = evt.target.result;
        parsed = JSON.parse(json_settings);

        if (parsed != null) {
          parsed.id = null;
          ///GOOD TO GO;
          self.uipOverview.settings.cards.formatted = parsed;
          uipNotification(allTranslations.layoutImported, { pos: "bottom-left", status: "success" });
          self.saveDash();
        } else {
          uipNotification(allTranslations.layoutExportedProblem, { pos: "bottom-left", status: "danger" });
        }
      };
    },
    isSmallScreen() {
      if (this.screenWidth < 1000) {
        return true;
      } else {
        return false;
      }
    },
    analyticsAcountConnected() {
      this.uipOverview.settings.analyticsAccount = true;
      this.build_global_data_object();
    },
    logDrop(evt) {
      console.log(evt);
      this.cardsWithIndex;
    },
    removeGoogleAccount() {
      self = this;

      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uipress_remove_google_account",
          security: uipress_overview_ajax.security,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            return;
          }

          self.build_global_data_object();
          self.uipOverview.settings.analyticsAccount = false;
          uipNotification(data.message, { pos: "bottom-left", status: "primary" });
        },
      });
    },
    resetOverview() {
      self = this;

      if (confirm(self.uipOverview.data.translations.confirmReset)) {
        self.forceReset();
      }
    },
    forceReset() {
      self = this;

      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uipress_reset_overview",
          security: uipress_overview_ajax.security,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            return;
          }
          self.uipOverview.settings.cards.formatted = [];
          uipNotification(data.message, { pos: "bottom-left", status: "primary" });
        },
      });
    },
    saveDash(notify) {
      self = this;

      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uipress_save_dash",
          security: uipress_overview_ajax.security,
          cards: self.uipOverview.settings.cards.formatted,
          network: self.uipOverview.settings.network,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            return;
          }

          if (notify != false) {
            uipNotification(data.message, { pos: "bottom-left", status: "primary" });
          }
        },
      });
    },
    getMenus() {
      self = this;

      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uipress_get_menus",
          security: uipress_overview_ajax.security,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            return;
          }

          self.user.allMenus = data.menus;
        },
      });
    },
    setCardIndex(option, index) {
      option.index = index;
      return index;
    },
    setDragData(column) {
      returnData = [];
      returnData.class = "uip-grid uip-card-area uip-row-gap-m uip-flex uip-flex-wrap uip-flex-baseline uip-flex-justify-baseline";
      if (column.matchHeight) {
        returnData.class = returnData.class + " uip-flex-grow uip-flex-stretch";
      }

      if (this.uipOverview.data.ui.editingMode) {
        returnData.class = returnData.class + " uip-padding-s";
      } else if (column.bgColor && column.bgColor != "") {
        returnData.class = returnData.class + " uip-padding-m";
      }

      return returnData;
    },
    moveColumnUp(index) {
      arr = this.uipOverview.settings.cards.formatted;
      new_index = index - 1;
      arr.splice(new_index, 0, arr.splice(index, 1)[0]);
    },
    moveColumnDown(index) {
      arr = this.uipOverview.settings.cards.formatted;
      new_index = index + 1;
      arr.splice(new_index, 0, arr.splice(index, 1)[0]);
    },

    addNewColumn(theColumn) {
      theColumn.push({ size: "small", cards: [] });
      uipNotification(this.uipOverview.data.translations.colAdded, { pos: "bottom-left", status: "primary" });
    },
    newSection() {
      let self = this;
      this.uipOverview.settings.cards.formatted.unshift({
        name: self.uipOverview.data.translations.sectionName,
        desc: self.uipOverview.data.translations.sectionDescription,
        open: true,
        columns: [],
        size: "xlarge",
      });
      uipNotification(this.uipOverview.data.translations.sectionAdded, { pos: "bottom-left", status: "primary" });
    },
    deleteSection(index) {
      this.uipOverview.settings.cards.formatted.splice(index, 1);
      uipNotification(this.uipOverview.data.translations.sectionRemoved, { pos: "bottom-left", status: "primary" });
    },
    removeCard(theParent, index) {
      theParent.cards.splice(index, 1);
    },
    removeCol(theParent, index) {
      theParent.splice(index, 1);
      uipNotification(this.uipOverview.data.translations.columnRemoved, { pos: "bottom-left", status: "primary" });
    },
    getdatafromComp(data) {
      return data;
    },
    masterDateChange(data) {
      this.uipOverview.data.dateRange.startDate = data.startDate;
      this.uipOverview.data.dateRange.endDate = data.endDate;
      this.uipOverview.data.dateRange.startDate_comparison = data.startDate_comparison;
      this.uipOverview.data.dateRange.endDate_comparison = data.endDate_comparison;
      this.build_global_data_object();
      this.log_dev_messages("Date Range Updated", "message");
    },
    returnCardKey(element) {
      return element.uid;
    },
    sectionWidthClass(section) {
      if (section.size && section.size != "") {
        return "uip-width-" + section.size;
      } else {
        return "uip-width-xlarge";
      }
    },
  },
};

const uipressOverviewApp = Vue.createApp(uipressOverviewArgs);

uipressOverviewApp.config.errorHandler = function (err, vm, info) {
  console.log(err);
};

uipressOverviewApp.component("date-range-picker", {
  props: {
    dates: Object,
  },
  data: function () {
    return {
      thepicker: "",
      date: {
        startDate: this.dates.startDate,
        endDate: this.dates.endDate,
      },
    };
  },
  mounted: function () {
    let datepicker = this;

    const picker = new Litepicker({
      element: document.getElementById("uip-date-range"),
      singleMode: false,
      plugins: ["ranges"],
      numberOfColumns: 2,
      numberOfMonths: 2,
      startDate: datepicker.date.startDate,
      endDate: datepicker.date.endDate,
      format: "DD MMM, YYYY",
      maxDate: moment().format("DD MMM, YYYY"),
    });

    this.thepicker = picker;

    picker.on("selected", (date1, date2) => {
      // some action
      thedates = {
        startDate: picker.getStartDate().format("YYYY-MM-DD"),
        endDate: picker.getEndDate().format("YYYY-MM-DD"),
      };
      datepicker.returnNewDates(thedates);
    });
  },

  methods: {
    returnNewDates(dateObj) {
      this.$emit("date-change", dateObj);
    },
    showPicker() {
      this.thepicker.show();
    },
  },
  template:
    '<div class="uip-flex uip-flex-center uip-border-round uip-background-muted hover:uip-background-grey uip-cursor-pointer uip-padding-xs">\
      <span @click="showPicker()" class="material-icons-outlined uip-margin-right-xxs uip-text-muted">date_range</span>\
      <input  class="uip-blank-input uip-no-text-select  uip-cursor-pointer uip-w-190" type="text" id="uip-date-range" readonly>\
      <span @click="showPicker()" class="material-icons-outlined uip-margin-left-xxs uip-text-muted">expand_more</span>\
    </div>',
});

uipressOverviewApp.component("date-range-picker-new", {
  props: {
    dates: Object,
    translations: Object,
  },
  data: function () {
    return {
      thepicker: "",
      picker_comp: "",
      formattedStartDate: "",
      formattedEndDate: "",
      dropOpen: false,
      date: {
        startDate: this.dates.startDate,
        endDate: this.dates.endDate,
        startDate_comparison: this.dates.startDate_comparison,
        endDate_comparison: this.dates.endDate_comparison,
      },
      comparison: {
        enabled: false,
      },
      preSetRanges: [
        {
          name: this.translations.today,
          start: moment().format("YYYY-MM-DD"),
          end: moment().format("YYYY-MM-DD"),
        },
        {
          name: this.translations.yesterday,
          start: moment().subtract(1, "days").format("YYYY-MM-DD"),
          end: moment().subtract(1, "days").format("YYYY-MM-DD"),
        },
        {
          name: this.translations.lastSevenDays,
          end: moment().format("YYYY-MM-DD"),
          start: moment().subtract(6, "days").format("YYYY-MM-DD"),
        },
        {
          name: this.translations.lastThirtyDays,
          end: moment().format("YYYY-MM-DD"),
          start: moment().subtract(29, "days").format("YYYY-MM-DD"),
        },
        {
          name: this.translations.lastSixtyDays,
          end: moment().format("YYYY-MM-DD"),
          start: moment().subtract(59, "days").format("YYYY-MM-DD"),
        },
        {
          name: this.translations.lastNinetyDays,
          end: moment().format("YYYY-MM-DD"),
          start: moment().subtract(89, "days").format("YYYY-MM-DD"),
        },
        {
          name: this.translations.thisMonth,
          end: moment().startOf("month").format("YYYY-MM-DD"),
          start: moment().format("YYYY-MM-DD"),
        },
        {
          name: this.translations.lastMonth,
          end: moment().subtract(1, "months").endOf("month").format("YYYY-MM-DD"),
          start: moment().subtract(1, "months").startOf("month").format("YYYY-MM-DD"),
        },
      ],
      selectedRange: this.translations.customRange,
      selectedCompRange: this.translations.customRange,
    };
  },
  mounted: function () {
    let datepicker = this;

    const picker = new Litepicker({
      element: document.getElementById("main-uip-date-range"),
      singleMode: false,
      numberOfColumns: 1,
      numberOfMonths: 1,
      inlineMode: true,
      startDate: datepicker.returnDateObject.startDate,
      endDate: datepicker.returnDateObject.endDate,
      format: "DD MMM, YYYY",
      maxDate: moment().format("DD MMM, YYYY"),
    });

    datepicker.thepicker = picker;

    picker.on("selected", (date1, date2) => {
      datepicker.date.startDate = picker.getStartDate().format("YYYY-MM-DD");
      datepicker.date.endDate = picker.getEndDate().format("YYYY-MM-DD");

      if (!datepicker.comparison.enabled) {
        var diff = datepicker.calculateDaysDiff(datepicker.date.startDate, datepicker.date.endDate);

        var enddateComp = moment(datepicker.date.startDate);
        enddateComp = enddateComp.subtract(1, "days");
        datepicker.date.endDate_comparison = enddateComp.format("YYYY-MM-DD");

        var startdateComp = moment(datepicker.date.startDate);
        startdateComp = startdateComp.subtract(diff, "days");
        datepicker.date.startDate_comparison = startdateComp.format("YYYY-MM-DD");

        picker_comp.setStartDate(startdateComp);
        picker_comp.setEndDate(enddateComp);
      }

      // some action
      //datepicker.returnNewDates(datepicker.date);
    });

    const picker_comp = new Litepicker({
      element: document.getElementById("comparison-uip-date-range"),
      singleMode: false,
      numberOfColumns: 1,
      numberOfMonths: 1,
      inlineMode: true,
      startDate: datepicker.returnDateObject.startDate_comparison,
      endDate: datepicker.returnDateObject.endDate_comparison,
      format: "DD MMM, YYYY",
      maxDate: moment().format("DD MMM, YYYY"),
    });

    datepicker.picker_comp = picker_comp;

    picker_comp.on("selected", (date1, date2) => {
      datepicker.date.startDate_comparison = picker_comp.getStartDate().format("YYYY-MM-DD");
      datepicker.date.endDate_comparison = picker_comp.getEndDate().format("YYYY-MM-DD");
      // some action
      //datepicker.returnNewDates(datepicker.date);
    });

    datepicker.formattedEndDate = datepicker.thepicker.getEndDate().format("DD MMM, YYYY");
    datepicker.formattedStartDate = datepicker.thepicker.getStartDate().format("DD MMM, YYYY");
  },
  computed: {
    returnDateObject() {
      return this.date;
    },
  },
  watch: {
    selectedRange: function (newValue, oldValue) {
      this.setDateFromRange(newValue);
    },
    selectedCompRange: function (newValue, oldValue) {
      this.setDateFromRangeComp(newValue);
    },
    date: {
      handler(newValue, oldValue) {
        this.formattedEndDate = this.thepicker.getEndDate().format("DD MMM, YYYY");
        this.formattedStartDate = this.thepicker.getStartDate().format("DD MMM, YYYY");
      },
      deep: true,
    },
  },
  methods: {
    returnNewDates(dateObj) {
      this.$emit("date-change", dateObj);
    },
    showPicker() {
      this.thepicker.show();
    },
    calculateDaysDiff(end, start) {
      var a = moment(start);
      var b = moment(end);
      return a.diff(b, "days") + 1;
    },
    setDateFromRange(range) {
      let datepicker = this;
      datepicker.thepicker.setDateRange(range.start, range.end, true);
      ///SET COMPARISON DATES
      if (!datepicker.comparison.enabled) {
        var diff = datepicker.calculateDaysDiff(range.start, range.end);

        var enddateComp = moment(range.start);
        enddateComp = enddateComp.subtract(1, "days");

        var startdateComp = moment(range.start);
        startdateComp = startdateComp.subtract(diff, "days");

        datepicker.picker_comp.setDateRange(startdateComp.format("YYYY-MM-DD"), enddateComp.format("YYYY-MM-DD"), true);
      }
    },

    setDateFromRangeComp(range) {
      let datepicker = this;
      datepicker.picker_comp.setDateRange(range.start, range.end, true);
    },
    onClickOutside(event) {
      const path = event.path || (event.composedPath ? event.composedPath() : undefined);
      // check if the MouseClick occurs inside the component
      if (path && !path.includes(this.$el) && !this.$el.contains(event.target)) {
        this.closeThisComponent(); // whatever method which close your component
      }
    },
    openThisComponent() {
      this.dropOpen = this.dropOpen != true; // whatever codes which open your component
      // You can also use Vue.$nextTick or setTimeout
      requestAnimationFrame(() => {
        document.documentElement.addEventListener("click", this.onClickOutside, false);
      });
    },
    closeThisComponent() {
      this.dropOpen = false; // whatever codes which close your component
      document.documentElement.removeEventListener("click", this.onClickOutside, false);
    },
  },
  template:
    '<div class="uip-position-relative">\
      <div class="uip-flex uip-flex-center uip-flex-between uip-border-round uip-background-muted hover:uip-background-grey uip-cursor-pointer uip-padding-xs" @click="openThisComponent()">\
        <span  class="material-icons-outlined uip-margin-right-xs uip-text-muted">date_range</span>\
        <span  class="uip-no-text-select  uip-cursor-pointer">{{formattedStartDate}}</span>\
        <span  class="material-icons-outlined uip-text-muted">arrow_right</span>\
        <span  class="uip-no-text-select  uip-cursor-pointer">{{formattedEndDate}}</span>\
        <span  v-if="!dropOpen" class="material-icons-outlined uip-margin-left-xs uip-text-muted">chevron_left</span>\
        <span  v-if="dropOpen" class="material-icons-outlined uip-margin-left-xs uip-text-muted">expand_more</span>\
      </div>\
      <!--START DROP -->\
      <div class="uip-position-absolute uip-z-index-9999 uip-hidden uip-right-0 uip-top-120p" :class="{\'uip-nothidden\' : dropOpen}">\
        <div class="uip-flex uip-flex-row uip-border uip-border-round uip-overflow-hidden">\
          <!--DATE RANGES -->\
          <div class="uip-background-default uip-flex uip-flex-row uip-gap-s uip-padding-m">\
            <div  id="main-date-select" >\
              <input  class="uip-blank-input uip-no-text-select uip-hidden uip-cursor-pointer uip-w-190" type="text" id="main-uip-date-range" readonly> \
            </div>\
            <div class="uip-hidden" id="comparison-date-select" :class="{\'uip-nothidden\' : comparison.enabled}">\
              <input  class="uip-blank-input uip-no-text-select uip-hidden uip-cursor-pointer uip-w-190" type="text" id="comparison-uip-date-range" readonly> \
            </div>\
          </div>\
          <!--END OF DATE RANGES -->\
          <!--OPTIONS -->\
          <div class="uip-background-muted uip-padding-m">\
            <div class="uip-text-muted uip-text-bold uip-margin-bottom-xxs">\
              {{translations.dateRange}}\
            </div>\
            <select v-model="selectedRange" class="uip-select uip-w-100p">\
              <option selected disabled>{{translations.customRange}}</option>\
              <template v-for="range in preSetRanges">\
                <option :value="range">{{range.name}}</option>\
              </template>\
            </select>\
            <div class="uip-flex uip-margin-top-s uip-gap-s">\
              <div class="">\
                <div class="uip-text-muted uip-text-bold uip-margin-bottom-xxs">\
                  {{translations.to}}\
                </div>\
                <input type="text" class="uip-w-125" v-model="date.startDate">\
              </div>\
              <div class="">\
                <div class="uip-text-muted uip-text-bold uip-margin-bottom-xxs">\
                  {{translations.from}}\
                </div>\
                <input type="text" class="uip-w-125" v-model="date.endDate">\
              </div>\
            </div>\
            <div class="uip-flex uip-margin-top-m uip-margin-bottom-m uip-gap-s uip-flex-center">\
              <div>\
                <label class="uip-switch">\
                  <input type="checkbox" v-model="comparison.enabled">\
                  <span class="uip-slider"></span>\
                </label>\
              </div>\
              <div class="">\
                {{translations.customComparisonDates}}\
              </div>\
            </div>\
            <!-- COMPARISON OPTIONS -->\
            <div class="" v-if="comparison.enabled">\
              <div class="uip-text-muted uip-text-bold uip-margin-bottom-xxs">\
                {{translations.dateRange}}\
              </div>\
              <select v-model="selectedCompRange" class="uip-select uip-w-100p">\
                <option selected disabled>{{translations.customRange}}</option>\
                <template v-for="range in preSetRanges">\
                  <option :value="range">{{range.name}}</option>\
                </template>\
              </select>\
              <div class="uip-flex uip-margin-top-s uip-gap-s">\
                <div class="">\
                  <div class="uip-text-muted uip-text-bold uip-margin-bottom-xxs">\
                    {{translations.to}}\
                  </div>\
                  <input type="text" class="uip-w-125" v-model="date.startDate_comparison">\
                </div>\
                <div class="">\
                  <div class="uip-text-muted uip-text-bold uip-margin-bottom-xxs">\
                    {{translations.from}}\
                  </div>\
                  <input type="text" class="uip-w-125" v-model="date.endDate_comparison">\
                </div>\
              </div>\
            </div>\
            <button class="uip-button-secondary uip-margin-top-m" type="button" @click="returnNewDates(date)">{{translations.apply}}</button>\
          </div>\
          <!--END OF OPTIONS -->\
        </div>\
      </div>\
      <!--END OF DROP -->\
    </div>',
});

uipressOverviewApp.component("create-date-range", {
  props: {
    dates: Object,
  },
  data: function () {
    return {
      thepicker: "",
      date: {
        startDate: this.dates.startDate,
        endDate: this.dates.endDate,
      },
    };
  },
  mounted: function () {
    let datepicker = this;

    const picker = new Litepicker({
      element: document.getElementById("uip-date-range"),
      singleMode: false,
      numberOfColumns: 1,
      numberOfMonths: 2,
      inlineMode: true,
      startDate: datepicker.date.startDate,
      endDate: datepicker.date.endDate,
      format: "DD MMM, YYYY",
      maxDate: moment().format("DD MMM, YYYY"),
    });

    this.thepicker = picker;

    picker.on("selected", (date1, date2) => {
      // some action
      thedates = {
        startDate: picker.getStartDate().format("YYYY-MM-DD"),
        endDate: picker.getEndDate().format("YYYY-MM-DD"),
      };
      datepicker.returnNewDates(thedates);
    });
  },

  methods: {
    returnNewDates(dateObj) {
      this.$emit("date-change", dateObj);
    },
  },
  template: '<input  class="uip-blank-input uip-no-text-select  uip-hidden uip-cursor-pointer uip-w-190" type="text" id="uip-date-range" readonly>',
});

uipressOverviewApp.component("uip-offcanvas-no-icon", {
  props: {
    title: String,
    open: Boolean,
  },
  data: function () {
    return {
      create: {
        open: this.open,
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
      this.offcanvas.open = false;
    },
  },
  template:
    '<div v-if="offcanvas.open" class="uip-position-fixed uip-w-100p uip-h-viewport uip-hidden uip-text-normal" \
      style="background:rgba(0,0,0,0.3);z-index:99999;top:0;left:0;right:0;max-height:100vh" \
      :class="{\'uip-nothidden\' : offcanvas.open}">\
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

uipressOverviewApp.component("loading-placeholder", {
  data: function () {
    return {};
  },
  methods: {
    doStuff() {},
  },
  template:
    '<svg class="uip-w-100p" role="img" width="340" height="84" aria-labelledby="loading-aria" viewBox="0 0 340 84" preserveAspectRatio="none">\
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

uipressOverviewApp.component("connect-google-analytics", {
  emits: ["account-connected"],
  props: {
    translations: Object,
  },
  data: function () {
    return {
      imgloading: false,
      googliconNoHover: uipressOverviewSettings.googliconNoHover,
      googliconHover: uipressOverviewSettings.googliconHover,
    };
  },
  mounted: function () {},
  computed: {
    returnHoverImg() {
      return this.googliconHover;
    },
    returnNoHoverImg() {
      return this.googliconNoHover;
    },
    isLoading() {
      return this.imgloading;
    },
  },
  methods: {
    gauthWindow() {
      let self = this;
      var url =
        "https://accounts.google.com/o/oauth2/auth/oauthchooseaccount?response_type=code&client_id=285756954789-dp7lc40aqvjpa4jcqnfihcke3o43hmt1.apps.googleusercontent.com&redirect_uri=https://analytics.uipress.co&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fanalytics.readonly&access_type=offline&approval_prompt=force&flowName=GeneralOAuthFlow";

      var y = window.outerHeight / 2 + window.screenY - 600 / 2;
      var x = window.outerWidth / 2 + window.screenX - 450 / 2;

      var newWindow = window.open(url, "name", "height=600,width=450,top=" + y + ", left=" + x);

      if (window.focus) {
        newWindow.focus();
      }

      window.onmessage = function (e) {
        if (e.origin == "https://analytics.uipress.co" && e.data) {
          try {
            var analyticsdata = JSON.parse(e.data);

            if (analyticsdata.code && analyticsdata.view) {
              newWindow.close();
              self.uip_save_analytics(analyticsdata);
            }
          } catch (err) {
            ///ERROR
          }
        }
      };
    },
    uip_save_analytics(anadata) {
      let self = this;
      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uipress_save_analytics_account",
          security: uipress_overview_ajax.security,
          view: anadata.view,
          code: anadata.code,
          gafour: anadata.gafour,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            self.loading = false;
            return;
          }

          self.$root.analyticsAcountConnected();
          //this.$root.$emit("account-connected");
          uipNotification(data.message, { pos: "bottom-left", status: "success" });
          self.loading = false;
        },
      });
    },
  },
  template:
    '<div class="uip-background-green-wash uip-padding-xs uip-border-round uip-margin-bottom-s">{{translations.noaccount}}</div>\
    <loading-placeholder v-if="isLoading == true"></loading-placeholder>\
    <a v-if="!isLoading" class="uip-google-sign-in" href="#" @click="gauthWindow()">\
        <img class="uip-icon-no-hover" width="191" :src="returnNoHoverImg">\
        <img class="uip-icon-hover" width="191" :src="returnHoverImg">\
    </a>',
});

uipressOverviewApp.component("connect-matomo-analytics", {
  emits: ["account-connected"],
  props: {
    translations: Object,
  },
  data: function () {
    return {
      imgloading: false,
      authToken: "",
      matomoURL: "",
      siteID: "",
    };
  },
  mounted: function () {},
  computed: {
    returnHoverImg() {
      return this.googliconHover;
    },
    returnNoHoverImg() {
      return this.googliconNoHover;
    },
    isLoading() {
      return this.imgloading;
    },
  },
  methods: {
    saveMatomoDetails(anadata) {
      let self = this;

      if (self.authToken == "" || self.matomoURL == "" || self.siteID == "") {
        return;
      }

      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uipress_save_matomo_account",
          security: uipress_overview_ajax.security,
          authToken: self.authToken,
          matomoURL: self.matomoURL,
          siteID: self.siteID,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            self.loading = false;
            return;
          }

          self.$root.analyticsAcountConnected();
          //this.$root.$emit("account-connected");
          uipNotification(data.message, { pos: "bottom-left", status: "success" });
          self.loading = false;
        },
      });
    },
  },
  template:
    '<div class="uip-background-green-wash uip-padding-xs uip-border-round uip-margin-bottom-s">{{translations.nomatomoaccount}}</div>\
    <loading-placeholder v-if="isLoading == true"></loading-placeholder>\
    <div class="uip-flex"><uip-offcanvas type="text" :buttonText="translations.connectMatomo" :title="translations.connectMatomo" :translations="translations" icon="add" :tooltip="true" :tooltiptext="translations.connectMatomo">\
      <div class="uip-margin-bottom-s">\
        <div class="uip-text-muted uip-margin-bottom-xs">{{translations.authToken}}</div> \
        <input v-model="authToken" class="uip-w-100p uip-standard-input" type="text" :placeholder="translations.authToken" style="padding: 5px 8px;">\
      </div>\
      <div class="uip-margin-bottom-s">\
        <div class="uip-text-muted uip-margin-bottom-xs">{{translations.matomoURL}}</div> \
        <input v-model="matomoURL" class="uip-w-100p uip-standard-input" type="text" :placeholder="translations.matomoURL" style="padding: 5px 8px;">\
      </div>\
      <div class="uip-margin-bottom-m">\
        <div class="uip-text-muted uip-margin-bottom-xs">{{translations.siteID}}</div>\
        <input v-model="siteID" class="uip-w-100p uip-standard-input" type="text" :placeholder="translations.siteID" style="padding: 5px 8px;">\
      </div>\
      <div>\
        <button @click="saveMatomoDetails()" class="uip-button-primary uip-w-100p uip-text-center">{{translations.save}}</button>\
      </div>\
    </uip-offcanvas></div>',
});

uipressOverviewApp.component("change-matomo-analytics", {
  emits: ["account-connected"],
  props: {
    translations: Object,
  },
  data: function () {
    return {
      imgloading: false,
      authToken: "",
      matomoURL: "",
      siteID: "",
    };
  },
  mounted: function () {},
  computed: {
    returnHoverImg() {
      return this.googliconHover;
    },
    returnNoHoverImg() {
      return this.googliconNoHover;
    },
    isLoading() {
      return this.imgloading;
    },
  },
  methods: {
    saveMatomoDetails(anadata) {
      let self = this;

      if (self.authToken == "" || self.matomoURL == "" || self.siteID == "") {
        return;
      }

      jQuery.ajax({
        url: uipress_overview_ajax.ajax_url,
        type: "post",
        data: {
          action: "uipress_save_matomo_account",
          security: uipress_overview_ajax.security,
          authToken: self.authToken,
          matomoURL: self.matomoURL,
          siteID: self.siteID,
        },
        success: function (response) {
          data = JSON.parse(response);

          if (data.error) {
            ///SOMETHING WENT WRONG
            uipNotification(data.error, { pos: "bottom-left", status: "danger" });
            self.loading = false;
            return;
          }

          self.$root.analyticsAcountConnected();
          //this.$root.$emit("account-connected");
          uipNotification(data.message, { pos: "bottom-left", status: "success" });
          self.loading = false;
        },
      });
    },
  },
  template:
    '<div class="uip-background-green-wash uip-padding-xs uip-border-round uip-margin-bottom-s">{{translations.changeMatomoLong}}</div>\
    <loading-placeholder v-if="isLoading == true"></loading-placeholder>\
    <div class="uip-flex"><uip-offcanvas type="text" :buttonText="translations.changeMatomo" :title="translations.connectMatomo" :translations="translations" icon="add" :tooltip="true" :tooltiptext="translations.connectMatomo">\
      <div class="uip-margin-bottom-s">\
        <div class="uip-text-muted uip-margin-bottom-xs">{{translations.authToken}}</div> \
        <input v-model="authToken" class="uip-w-100p uip-standard-input" type="text" :placeholder="translations.authToken" style="padding: 5px 8px;">\
      </div>\
      <div class="uip-margin-bottom-s">\
        <div class="uip-text-muted uip-margin-bottom-xs">{{translations.matomoURL}}</div> \
        <input v-model="matomoURL" class="uip-w-100p uip-standard-input" type="text" :placeholder="translations.matomoURL" style="padding: 5px 8px;">\
      </div>\
      <div class="uip-margin-bottom-m">\
        <div class="uip-text-muted uip-margin-bottom-xs">{{translations.siteID}}</div>\
        <input v-model="siteID" class="uip-w-100p uip-standard-input" type="text" :placeholder="translations.siteID" style="padding: 5px 8px;">\
      </div>\
      <div>\
        <button @click="saveMatomoDetails()" class="uip-button-primary uip-w-100p uip-text-center">{{translations.save}}</button>\
      </div>\
    </uip-offcanvas></div>',
});

uipressOverviewApp.component("card-options", {
  emits: ["remove-card", "card-change"],
  props: {
    translations: Object,
    card: Object,
    cardindex: Number,
  },
  data: function () {
    return {
      theCard: this.card,
      theIndex: this.cardindex,
      theID: this.card.id,
      theCardName: this.card.name,
    };
  },
  computed: {
    returnIndex() {
      return this.theIndex;
    },
    returnCard() {
      let self = this;
      return self.theCard;
    },
  },
  mounted: function () {
    datepicker = this;
  },
  methods: {
    removeCard() {
      this.$emit("remove-card");
    },
  },
  watch: {
    theCard: function (newValue, oldValue) {
      let self = this;
      this.$emit("card-change", self.returnCard);
    },
  },
  template:
    '<uip-dropdown-new type="icon" icon="more_horiz" pos="botton-left" buttonSize="small" :tooltip="true" :tooltiptext="translations.cardSettings" >\
      <div>\
        <div class="uip-padding-s">\
            <div class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">\
              <div class="uip-text-bold">{{translations.cardWidth}}</div>\
              <select class="uk-select uk-form-small uk-margin-small uip-w-150" v-model="returnCard.size">\
                  <option value="xxsmall">{{translations.xxsmall}}</option>\
                  <option value="xsmall">{{translations.xsmall}}</option>\
                  <option value="small">{{translations.small}}</option>\
                  <option value="small-medium">{{translations.smallmedium}}</option>\
                  <option value="medium">{{translations.medium}}</option>\
                  <option value="medium-large">{{translations.mediumlarge}}</option>\
                  <option value="large">{{translations.large}}</option>\
                  <option value="xlarge">{{translations.xlarge}}</option>\
              </select>\
            </div>\
            <div class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">\
                <div class="uip-text-bold ">{{translations.bgcolor}}</div>\
                <div class="uip-margin-bottom-xm uip-padding-xxs uip-border uip-border-round uip-w-150 uip-background-default uip-border-box">\
                <div class="uip-flex uip-flex-center">\
                  <span class="uip-margin-right-xs uip-text-muted">\
                      <label class="uip-border-circle uip-h-18 uip-w-18 uip-border uip-display-block" v-bind:style="{\'background-color\' : returnCard.bgColor}">\
                        <input\
                        type="color"\
                        v-model="returnCard.bgColor" style="visibility: hidden;">\
                      </label>\
                  </span> \
                  <input v-model="returnCard.bgColor" type="search" :placeholder="translations.colorPlace" class="uip-blank-input uip-margin-right-s " style="min-width:0;">\
                  <span class="uip-text-muted">\
                      <span class="material-icons-outlined uip-text-muted">color_lens</span>\
                  </span> \
                </div>\
              </div>\
            </div>\
            <div class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">\
              <div class="uip-text-bold ">{{translations.lightText}}</div>\
              <label class="uip-switch">\
                <input type="checkbox" v-model="returnCard.lightDark">\
                <span class="uip-slider"></span>\
              </label>\
            </div>\
            <div class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">\
              <div class="uip-text-bold ">{{translations.removeBackground}}</div>\
              <label class="uip-switch">\
                <input type="checkbox" v-model="returnCard.nobg">\
                <span class="uip-slider"></span>\
              </label>\
            </div>\
            <div class=" uip-flex uip-flex-column uip-gap-xs">\
              <div class="uip-text-bold ">{{translations.hideTitle}}</div>\
              <label class="uip-switch">\
                <input type="checkbox" v-model="returnCard.hideTitle">\
                <span class="uip-slider"></span>\
              </label>\
            </div>\
        </div>\
        <div class="uip-padding-s uip-border-top">\
          <button @click="removeCard()" class="uip-button-danger uip-w-100p uip-text-center">{{translations.remove}}</button>\
        </div>\
      </div>\
    </uip-dropdown-new>',
});

const highlight = (editor) => {
  editor.textContent = editor.textContent;
  hljs.highlightBlock(editor);
};

let editorOptions = {
  tab: " ".repeat(2), // default is \t
};

uipressOverviewApp.component("code-flask", {
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
    },
    testel() {
      let self = this;
      let editorblock = this.$refs.codeblock;
      let jar = new CodeJar(editorblock, highlight, editorOptions);

      jar.onUpdate((code) => {
        self.codeChange(code);
      });
    },
  },
  template: '<div class="editor " :class="language" ondragstart="return false;" ondrop="return false;" data-gramm="false" ref="codeblock">{{returnCode}}</div> ',
});

uipressOverviewApp.component("premium-overlay", {
  props: {
    translations: Object,
  },
  data: function () {
    return {};
  },
  methods: {},
  template:
    '<div class="uip-flex uip-flex-column">\
            <div class=" uip-background-green-wash uip-padding-s uip-border-round">\
              <div class="uip-text-emphasis uip-text-l uip-text-bold  uip-margin-bottom-xs">{{translations.premiumFeature}}</div>\
              <div class="uip-margin-bottom-s">{{translations.upgradMsg}}</div>\
              <a href="https://uipress.co/pricing" target="_BLANK"  class="uip-button-primary uip-no-underline uip-flex uip-flex-row">\
                <span class="material-icons-outlined uip-margin-right-xs">open_in_new</span>\
                <span>{{translations.viewPlans}}</span>\
              </a>\
            </div>\
      </div>',
});

uipressOverviewApp.component("col-editor", {
  props: {
    translations: Object,
    column: Object,
    modules: Object,
    premium: Boolean,
  },
  data: function () {
    return {
      theColumn: this.column,
    };
  },
  mounted: function () {},
  methods: {
    removeCol() {
      this.dropClosed = true;
      this.$emit("remove-col");
    },
    columnUpdated(column) {
      this.$emit("col-change", column);
    },
  },
  watch: {
    theColumn: {
      handler(newValue, oldValue) {
        this.$emit("col-change", this.theColumn);
      },
      deep: true,
    },
  },
  template:
    '<div class="">\
      <div class=" uip-padding-s uip-border-bottom-bottom" >\
        <div class="uip-flex uip-flex-center uip-flex-right">\
          <div class="uip-position-relative uip-margin-left-xs uip-margin-right-xs">\
            <uip-offcanvas :translations="translations" :title="translations.availableCards" icon="add" buttonsize="small" :tooltip="true" :tooltiptext="translations.addNewCard">\
              <card-selector :premium="premium" @card-added="columnUpdated($event)" :theColumn="theColumn" :translations="translations" :modules="modules"></card-selector>\
            </uip-offcanvas>\
          </div>\
          <!-- DROPDOWN -->\
          <uip-dropdown-new type="icon" icon="more_horiz" pos="botton-left" buttonSize="small" :tooltip="true" :tooltiptext="translations.columnSettings">\
            <div class="uip-padding-s">\
              <!-- COLUMN SIZE --> \
              <div class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">\
                <div class="uip-text-bold">{{translations.size}}</div>\
                <select class="uip-w-150" v-model="theColumn.size" style="height:100%;">\
                    <option value="xxsmall">{{translations.xxsmall}}</option>\
                    <option value="xsmall">{{translations.xsmall}}</option>\
                    <option value="small">{{translations.small}}</option>\
                    <option value="small-medium">{{translations.smallmedium}}</option>\
                    <option value="medium">{{translations.medium}}</option>\
                    <option value="medium-large">{{translations.mediumlarge}}</option>\
                    <option value="large">{{translations.large}}</option>\
                    <option value="xlarge">{{translations.xlarge}}</option>\
                </select>\
              </div>\
              <!-- COLUMN COLOR --> \
              <div class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">\
                <div class="uip-text-bold">{{translations.backgroundColour}}</div>\
                <div class="uip-padding-xxs uip-border uip-border-round uip-w-150 uip-background-default uip-border-box">\
                  <div class="uip-flex uip-flex-center">\
                    <span class="uip-margin-right-xs uip-text-muted">\
                        <label class="uip-border-circle uip-h-18 uip-w-18 uip-border uip-display-block" v-bind:style="{\'background-color\' : theColumn.bgColor}">\
                          <input\
                          type="color"\
                          v-model="theColumn.bgColor" style="visibility: hidden;">\
                        </label>\
                    </span> \
                    <input v-model="theColumn.bgColor" type="search" :placeholder="translations.colorPlace" class="uip-blank-input uip-margin-right-s " style="min-width:0;">\
                    <span class="uip-text-muted">\
                        <span class="material-icons-outlined uip-text-muted">color_lens</span>\
                    </span> \
                  </div>\
                </div>\
              </div>\
              <!-- COLUMN CSS --> \
              <div class="uip-margin-bottom-m uip-flex uip-flex-column uip-gap-xs">\
                <div class="uip-text-bold">{{translations.customClasses}}</div>\
                <input type="text" class="uip-w-150" v-model="theColumn.classes">\
              </div>\
              <!-- MATCH HEIGHT -->\
              <div class=" uip-flex uip-flex-column uip-gap-xs">\
                <div class="uip-text-bold uip-w-200">{{translations.matchHeight}}</div>\
                <label class="uip-switch">\
                  <input type="checkbox" v-model="theColumn.matchHeight">\
                  <span class="uip-slider"></span>\
                </label>\
              </div>\
            </div>\
            <div class="uip-border-top uip-padding-s">\
              <button @click="removeCol()" class="uip-button-danger uip-text-center uip-w-100p">\
                {{translations.deleteColumn}}\
              </button>\
            </div>\
          </uip-dropdown-new>\
          <!-- DROPDOWN -->\
        </div>\
      </div>\
    </div>',
});

uipressOverviewApp.component("uip-offcanvas", {
  props: {
    icon: String,
    translations: Object,
    buttonsize: String,
    tooltip: Boolean,
    tooltiptext: String,
    type: String,
    buttonText: String,
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
        <uip-tooltip v-if="tooltip" :tooltiptext="tooltiptext">\
          <div @click="openOffcanvas()" :class="returnButtonSize()" type="button"\
          class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer" v-if="type == \'text\'" >{{buttonText}}</div>\
          <div v-else @click="openOffcanvas()" :class="returnButtonSize()" type="button"\
          class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined" >{{icon}}</div>\
        </uip-tooltip>\
        <div v-else @click="openOffcanvas()" :class="returnButtonSize()" type="button"\
        class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined" >{{icon}}</div>\
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

uipressOverviewApp.component("uip-tooltip", {
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
});

uipressOverviewApp.component("card-selector", {
  props: {
    translations: Object,
    theColumn: Object,
    modules: Object,
    premium: Boolean,
  },
  data: function () {
    return {
      theCol: this.theColumn,
      searchString: "",
    };
  },
  mounted: function () {
    datepicker = this;
  },
  methods: {
    addCard(card) {
      let self = this;

      if (!self.theCol.cards) {
        self.theCol.cards = [];
      }
      self.theCol.cards.push({ name: card.name, compName: card.moduleName, size: "xlarge" });
      this.$emit("card-added", self.theCol);
      uipNotification(self.translations.cardAdded, { pos: "bottom-left", status: "primary" });
      //this.$emit("remove-col");
    },
    isInSearch(currentModule, search) {
      thename = currentModule.name.toLowerCase();
      desc = currentModule.description.toLowerCase();
      cat = currentModule.category.toLowerCase();
      searchlc = search.toLowerCase();

      if (thename.includes(searchlc) || desc.includes(searchlc) || cat.includes(searchlc)) {
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
            <template v-for="module in modules" >\
              <div v-if="isInSearch(module, searchString)" class="uip-width-medium">\
                <div class="uip-border-round uip-background-muted uip-padding-s uip-margin-bottom-s uip-flex uip-flex-column">\
                  <div class="uip-flex uip-flex-row uip-margin-bottom-s">\
                    <div class="uip-text-bold uip-text-emphasis  uip-flex-grow">{{module.name}}</div>\
                    <div class="">\
                      <span class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold uip-text-primary">{{module.category}}</span>\
                    </div>\
                  </div>\
                  <div class="uip-text-default uip-margin-bottom-m">{{module.description}}</div>\
                  <div class="uip-flex uip-flex-grow uip-flex-end uip-flex-left">\
                    <button @click="addCard(module)" v-if="module.premium && premium == true" class="uip-button-secondary">{{translations.addCard}}</button>\
                    <button @click="addCard(module)" v-if="!module.premium" class="uip-button-secondary">{{translations.addCard}}</button>\
                    <a href="https://uipress.co/pricing" target="_BLANK" v-if="module.premium && premium == false" class="uip-button-primary uip-flex uip-no-underline">\
                      <span class="material-icons-outlined uip-margin-right-xs" style="font-size:20px;">lock</span>\
                      <span> {{translations.premium}}</span>\
                    </a>\
                  </div>\
                </div>\
              </div>\
            </template>\
          </div>\
        </div>\
    </div>',
});

uipressOverviewApp.component("uip-dropdown", {
  props: {
    type: String,
    icon: String,
    pos: String,
    translation: String,
  },
  data: function () {
    return {
      modelOpen: false,
    };
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
    getClass() {
      if (this.pos == "botton-left") {
        return "uip-margin-top-s uip-right-0";
      }
      if (this.pos == "botton-center") {
        return "uip-margin-top-s uip-right-center";
      }
    },
  },
  template:
    '<div class="uip-position-relative">\
      <div class="">\
        <div v-if="type == \'icon\'" @click="openThisComponent" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xs material-icons-outlined" type="button">{{icon}}</div>\
        <button v-if="type == \'button\'" @click="openThisComponent" class="uip-button-default material-icons-outlined" type="button">{{translation}}</button>\
      </div>\
      <div v-if="modelOpen" :class="getClass()"\
      class="uip-position-absolute uip-padding-s uip-background-default uip-border-round uip-shadow uip-min-w-250 uip-z-index-9999">\
        <slot></slot>\
      </div>\
    </div>',
});

uipressOverviewApp.component("uip-dropdown-new", {
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
        <uip-tooltip v-if="tooltip" :tooltiptext="tooltiptext">\
          <div v-if="type == \'icon\'" @click="openThisComponent" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined" type="button" :class="returnButtonSize()">{{icon}}</div>\
        </uip-tooltip>\
        <div v-else @click="openThisComponent" class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined" type="button" :class="returnButtonSize()">{{icon}}</div>\
      </div>\
      <div v-if="modelOpen" :style="setPosition()" ref="uipdrop"\
      class="uip-position-fixed uip-dropdown-conatiner uip-background-default uip-border-round uip-border uip-min-w-250 uip-z-index-9999 uip-scale-in">\
        <slot></slot>\
      </div>\
    </div>',
});

uipressOverviewApp.component("uip-chart", {
  props: {
    type: String,
    gridLines: Boolean,
    chartData: Object,
    dates: Object,
    colours: {
      bgColors: [],
      borderColors: [],
    },
    cWidth: String,
    borderWidth: Number,
    cutout: String,
    spacing: Number,
    borderradius: Number,
    removeLabels: Boolean,
  },
  data: function () {
    return {
      theCard: this.card,
      theDates: this.dates,
      defaultColors: {
        bgColors: ["rgba(255, 99, 132, 0.2)", "rgba(54, 162, 235, 0.2)", "rgba(255, 206, 86, 0.2)", "rgba(75, 192, 192, 0.2)", "rgba(153, 102, 255, 0.2)", "rgba(255, 159, 64, 0.2)"],
        borderColors: ["rgba(255, 99, 132, 1)", "rgba(54, 162, 235, 1)", "rgba(255, 206, 86, 1)", "rgba(75, 192, 192, 1)", "rgba(153, 102, 255, 1)", "rgba(255, 159, 64, 1)"],
      },
    };
  },
  mounted: function () {
    theChart = this;
    this.renderChart();
  },
  computed: {
    bgColors() {
      if (this.backgroundColors) {
        return this.backgroundColors;
      } else {
        return this.defaultColors;
      }
    },
    chartWidth() {
      if (this.cWidth) {
        return this.cWidth;
      } else {
        return "100%";
      }
    },
    displayLabels() {
      if (this.removeLabels == true) {
        return false;
      } else {
        return true;
      }
    },
  },
  methods: {
    getTooltip(context) {
      // Tooltip Element
      var tooltipEl = document.getElementById("chartjs-tooltip");

      // Create element on first render
      if (!tooltipEl) {
        tooltipEl = document.createElement("div");
        tooltipEl.id = "chartjs-tooltip";
        tooltipEl.innerHTML = "<div class='uip-background-default uip-boder uip-padding-s uip-shadow uip-border-round'></div>";
        document.body.appendChild(tooltipEl);
      }

      // Hide if no tooltip
      var tooltipModel = context.tooltip;
      if (tooltipModel.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
      }

      // Set caret Position
      tooltipEl.classList.remove("above", "below", "no-transform");
      if (tooltipModel.yAlign) {
        tooltipEl.classList.add(tooltipModel.yAlign);
      } else {
        tooltipEl.classList.add("no-transform");
      }

      function getBody(bodyItem) {
        return bodyItem.lines;
      }

      // Set Text
      if (tooltipModel.body) {
        var titleLines = tooltipModel.title || [];
        var bodyLines = tooltipModel.body.map(getBody);
        var dataPoints = tooltipModel.dataPoints;
        var toolTipTitle = dataPoints[0].dataset.chartTitle;
        var toolTipType = dataPoints[0].dataset.toolTipType;
        var currentPoint = "";

        var innerHtml = "";

        titleLines.forEach(function (title) {
          currentPoint = title;
        });

        if (toolTipType == "label") {
          innerHtml += "<div class='uip-text-bold uip-body-font uip-margin-bottom-s' style='font-size:13px;'>";
          innerHtml += "<span>" + toolTipTitle + "</span>";
          innerHtml += "<span class='uip-margin-left-xxs uip-text-muted'>" + currentPoint + "</span>";
          innerHtml += "</div>";
        } else {
          innerHtml += "<div class='uip-text-bold uip-body-font uip-margin-bottom-s' style='font-size:13px;'>" + toolTipTitle + "</div>";
        }

        dataPoints.forEach(function (body, i) {
          datasetLabel = body.label;
          datasetValue = body.formattedValue;
          dataset = body.dataset;
          pointIndex = body.dataIndex;

          if (dataset.toolTipType == "label") {
            metalabel = dataset.toolTipLabels;
          } else {
            metalabel = dataset.toolTipLabels[pointIndex];
          }

          if (dataset.toolTipLabels[pointIndex]) {
            innerHtml += '<div class="uip-margin-top-xs uip-body-font" style="font-size:13px;">';
            var borderColor = dataset.borderColor[0];
            var bgColor = dataset.backgroundColor[0];
            var style = "background:" + bgColor;
            style += "; border: 2px solid " + borderColor;
            style += "; border-radius: 50%";
            style += "; width: 7px";
            style += "; height: 7px";
            style += "; display: inline-block";
            innerHtml += '<div class="uip-flex uip-flex-row uip-flex-center uip-gap-xs">';
            innerHtml += '<span style="' + style + '"></span>';
            innerHtml += '<span class="uip-text-m uip-text-normal uip-text-bold uip-min-w-35">' + datasetValue + "</span>";
            innerHtml += '<span class="uip-text-m uip-text-muted">' + metalabel + "</span>";
            innerHtml += "</div>";
            innerHtml += "</div>";
          }
        });
        innerHtml += "";

        var tableRoot = tooltipEl.querySelector("div");
        tableRoot.innerHTML = innerHtml;

        var position = context.chart.canvas.getBoundingClientRect();
        var bodyFont = Chart.helpers.toFont(tooltipModel.options.bodyFont);

        // Display, position, and set styles for font
        tooltipEl.style.opacity = 1;
        tooltipEl.style.position = "absolute";
        tooltipEl.style.left = position.left + window.pageXOffset + tooltipModel.caretX + "px";
        tooltipEl.style.top = position.top + window.pageYOffset + tooltipModel.caretY + "px";
        tooltipEl.style.font = bodyFont.string;
        tooltipEl.style.padding = tooltipModel.padding + "px " + tooltipModel.padding + "px";
        tooltipEl.style.pointerEvents = "none";
      }
    },
    getOptions() {
      let self = this;
      let chartOptions = [];

      if (this.type == "doughnut") {
        options = {
          borderWidth: 0,
          cutout: "60%",
          spacing: 0,
          borderRadius: 0,
          layout: {
            padding: 0,
          },
          plugins: {
            legend: {
              display: self.displayLabels,
              position: "bottom",
              align: "left",
              labels: {
                padding: 10,
                usePointStyle: true,
              },
            },
            tooltip: {
              enabled: false,
              external: function (context) {
                self.getTooltip(context);
              },
            },
          },
          scales: {
            x: {
              ticks: {
                display: theChart.gridLines,
              },
              grid: {
                borderWidth: 0,
                display: theChart.gridLines,
              },
            },
            y: {
              beginAtZero: true,
              ticks: {
                display: theChart.gridLines,
              },
              grid: {
                borderWidth: 0,
                display: theChart.gridLines,
                border: theChart.gridLines,
              },
            },
          },
        };

        chartOptions = options;
      } else if (this.type == "stacked-bar") {
        options = {
          cutout: "0%",
          pointRadius: 0,
          pointHoverRadius: 5,
          interaction: {
            mode: "nearest",
          },
          hover: {
            intersect: false,
          },
          borderSkipped: false,
          plugins: {
            legend: {
              display: self.displayLabels,
              position: "bottom",
              align: "start",
              padding: 10,
              labels: {
                padding: 10,
                usePointStyle: true,
                pointStyle: "rectRounded",
              },
              title: {
                padding: 0,
                display: true,
              },
            },
            tooltip: {
              position: "average",
              backgroundColor: "#fff",
              padding: 20,
              bodySpacing: 10,
              bodyFont: {
                size: 12,
              },
              titleFont: {
                size: 14,
                weight: "bold",
              },
              mode: "index",
              intersect: false,
              xAlign: "left",
              yAlign: "center",
              caretPadding: 10,
              cornerRadius: 4,
              borderColor: "rgba(162, 162, 162, 0.2)",
              borderWidth: 1,
              titleColor: "#333",
              bodyColor: "#777",
              titleMarginBottom: 10,
              bodyFontSize: 100,
              usePointStyle: true,

              enabled: false,

              external: function (context) {
                self.getTooltip(context);
              },
            },
          },
          scales: {
            x: {
              stacked: true,
              ticks: {
                display: false,
              },
              grid: {
                borderWidth: 1,
                display: true,
                borderDash: [10, 8],
                color: "rgba(162, 162, 162, 0.4)",
              },
            },
            y: {
              beginAtZero: true,
              stacked: true,
              ticks: {
                display: false,
              },
              grid: {
                borderWidth: 0,
                display: false,
              },
            },
          },
        };

        chartOptions = options;
      } else {
        options = {
          cutout: "0%",
          spacing: 0,
          borderRadius: 0,
          tension: 0.1,
          pointRadius: 0,
          pointHoverRadius: 5,
          borderRadius: 4,
          animation: true,
          interaction: {
            mode: "nearest",
          },
          hover: {
            intersect: false,
          },
          borderSkipped: false,
          plugins: {
            legend: {
              display: self.displayLabels,
              position: "bottom",
              align: "start",
              padding: 10,
              labels: {
                padding: 10,
                usePointStyle: true,
                pointStyle: "rectRounded",
              },
              title: {
                padding: 0,
                display: true,
              },
            },
            tooltip: {
              position: "average",
              backgroundColor: "#fff",
              padding: 20,
              bodySpacing: 10,
              bodyFont: {
                size: 12,
              },
              titleFont: {
                size: 14,
                weight: "bold",
              },
              mode: "index",
              intersect: false,
              xAlign: "left",
              yAlign: "center",
              caretPadding: 10,
              cornerRadius: 4,
              borderColor: "rgba(162, 162, 162, 0.2)",
              borderWidth: 1,
              titleColor: "#333",
              bodyColor: "#777",
              titleMarginBottom: 10,
              bodyFontSize: 100,
              usePointStyle: true,

              enabled: false,

              external: function (context) {
                self.getTooltip(context);
              },
            },
          },
          scales: {
            x: {
              ticks: {
                display: false,
              },
              grid: {
                borderWidth: 1,
                display: true,
                borderDash: [10, 8],
                color: "rgba(162, 162, 162, 0.4)",
              },
            },
            y: {
              beginAtZero: true,
              ticks: {
                display: false,
              },
              grid: {
                borderWidth: 0,
                display: false,
              },
            },
          },
        };

        chartOptions = options;
      }

      if (self.type == "horizontalbar") {
        chartOptions.indexAxis = "y";
      }

      return chartOptions;
    },
    renderChart() {
      let theChart = this;
      let temptype = theChart.type;

      if (theChart.type == "horizontalbar") {
        theChart.chartData.datasets;
        temptype = "bar";
        let newdata = [];

        theChart.chartData.datasets.forEach(function (body, i) {
          body.axis = "y";
          newdata.push(body);
        });

        theChart.chartData.datasets = newdata;
      }

      if (theChart.type == "stacked-bar") {
        temptype = "bar";
      }

      var ctx = this.$refs.uipchart.getContext("2d");
      var myChart = new Chart(ctx, {
        type: temptype,
        data: theChart.chartData,
        options: theChart.getOptions(),
      });
    },
  },
  template: '<canvas :width="chartWidth" height="200" :dat-sd="dates.startDate" :dat-sed="dates.endDate" style="max-width:100% !important;" ref="uipchart"></canvas>',
});

uipressOverviewApp.component("uip-country-chart", {
  props: {
    type: String,
    cdata: Object,
    dates: Object,
    translations: Object,
  },
  data: function () {
    return {
      theCard: this.card,
      theDates: this.dates,
    };
  },
  mounted: function () {
    theChart = this;
    this.renderChart();
  },
  methods: {
    getTooltip(context) {
      // Tooltip Element
      var tooltipEl = document.getElementById("chartjs-tooltip");

      // Create element on first render
      if (!tooltipEl) {
        tooltipEl = document.createElement("div");
        tooltipEl.id = "chartjs-tooltip";
        tooltipEl.innerHTML = "<div class='uip-background-default uip-boder uip-padding-s uip-shadow uip-border-round'></div>";
        document.body.appendChild(tooltipEl);
      }

      // Hide if no tooltip
      var tooltipModel = context.tooltip;
      if (tooltipModel.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
      }

      // Set caret Position
      tooltipEl.classList.remove("above", "below", "no-transform");
      if (tooltipModel.yAlign) {
        tooltipEl.classList.add(tooltipModel.yAlign);
      } else {
        tooltipEl.classList.add("no-transform");
      }

      function getBody(bodyItem) {
        return bodyItem.lines;
      }

      // Set Text
      if (tooltipModel.body) {
        var titleLines = tooltipModel.title || [];
        var bodyLines = tooltipModel.body.map(getBody);

        currentIndex = tooltipModel.dataPoints[0].dataIndex;

        mastertitle = tooltipModel.dataPoints[0].dataset.toolTipLabels[currentIndex];

        var innerHtml = "";
        innerHtml += "<div class='uip-text-bold uip-margin-bottom-s uip-body-font' style='font-size:13px;'>" + mastertitle + "</div>";

        bodyData = tooltipModel.dataPoints;

        bodyData.forEach(function (body, i) {
          datasetLabel = body.label;
          datasetValue = body.formattedValue;

          innerHtml += '<div class="uip-margin-bottom-xs uip-body-font" style="font-size:13px;">';
          innerHtml += '<div class="uip-flex uip-flex-row uip-flex-center uip-gap-xs">';
          innerHtml += '<span class="uip-text-m uip-text-normal uip-text-bold uip-min-w-35">' + datasetValue + "</span>";
          innerHtml += '<span class="uip-text-m uip-text-muted">' + body.dataset.label + "</span>";
          innerHtml += "</div>";
          innerHtml += "</div>";
        });
        innerHtml += "";

        var tableRoot = tooltipEl.querySelector("div");
        tableRoot.innerHTML = innerHtml;

        var position = context.chart.canvas.getBoundingClientRect();
        var bodyFont = Chart.helpers.toFont(tooltipModel.options.bodyFont);

        // Display, position, and set styles for font
        tooltipEl.style.opacity = 1;
        tooltipEl.style.position = "absolute";
        tooltipEl.style.left = position.left + window.pageXOffset + tooltipModel.caretX + "px";
        tooltipEl.style.top = position.top + window.pageYOffset + tooltipModel.caretY + "px";
        tooltipEl.style.font = bodyFont.string;
        tooltipEl.style.padding = tooltipModel.padding + "px " + tooltipModel.padding + "px";
        tooltipEl.style.pointerEvents = "none";
      }

      // Display, position, and set styles for font
    },
    renderChart() {
      let theChart = this;
      const CountryNameData = theChart.cdata;

      if (!CountryNameData) {
        return;
      }

      fetch("https://unpkg.com/world-atlas/countries-50m.json")
        .then((r) => r.json())
        .then((data) => {
          const originalData = data;
          const countries = ChartGeo.topojson.feature(data, data.objects.countries).features;
          var ctx = this.$el.getContext("2d");
          let formatted = [];
          let dataFormatted = [];
          let simpleformatcol = [];

          countries.forEach(function (item) {
            item.properties.value = 0;
            data = {};
            data.feature = item;
            data.value = 0;
            simpleformat = [];

            latlong = item.geometry.coordinates[0][0][0];

            alllatlong = item.geometry.coordinates[0][0];

            if (!CountryNameData) {
              return;
            }

            if (CountryNameData[item.properties.name]) {
              if (alllatlong.length == 2) {
                simpleformat.latitude = latlong[1];
                simpleformat.longitude = latlong[0];
              } else {
                everyLat = 0;
                everyLong = 0;

                alllatlong.forEach(function (latobj) {
                  everyLat += latobj[1];
                  everyLong += latobj[0];
                });

                averageLat = everyLat / alllatlong.length;
                averageLong = everyLong / alllatlong.length;

                simpleformat.latitude = averageLat;
                simpleformat.longitude = averageLong;
              }

              thevalue = CountryNameData[item.properties.name];
              item.properties.value = parseInt(CountryNameData[item.properties.name]);

              data.value = parseInt(thevalue);

              simpleformat.value = 0;
              simpleformat.name = item.properties.name;
              simpleformat.description = item.properties.name;
              simpleformat.value = parseInt(thevalue);
            }

            if (item.properties.name == "United States of America") {
              if (CountryNameData["United States"]) {
                simpleformat.latitude = "41.500000";
                simpleformat.longitude = "-100.000000";

                thevalue = CountryNameData["United States"];
                item.properties.value = parseInt(CountryNameData["United States"]);

                data.value = parseInt(thevalue);
                simpleformat.value = 0;
                simpleformat.name = item.properties.name;
                simpleformat.description = item.properties.name;
                simpleformat.value = parseInt(thevalue);
              }
            }

            if (item.properties.name != "Antarctica") {
              formatted.push(item);
              dataFormatted.push(data);

              if (simpleformat === undefined || simpleformat.length == 00) {
                simpleformatcol.push(simpleformat);
              }
            }
          });

          const bubblechart = new Chart(ctx, {
            type: "bubbleMap",
            data: {
              labels: formatted.map((d) => d.properties.name),
              datasets: [
                {
                  label: theChart.translations.visits,
                  outline: formatted,
                  showOutline: true,
                  backgroundColor: "rgba(247, 127, 212, 0.3)",
                  outlineBackgroundColor: "rgba(115, 165, 255, 0.3)",
                  outlineBorderColor: "rgba(0,0,0,0)",
                  outlineBorderWidth: 2,
                  borderColor: "rgb(247, 127, 212)",
                  data: simpleformatcol,
                  toolTipLabels: formatted.map((d) => d.properties.name),
                },
              ],
            },
            options: {
              borderWidth: 2,
              plugins: {
                legend: {
                  display: false,
                },
                datalabels: {
                  align: "top",
                  formatter: (v) => {
                    return v.description;
                  },
                },
                tooltip: {
                  enabled: false,

                  external: function (context) {
                    theChart.getTooltip(context);
                  },
                },
              },
              scales: {
                xy: {
                  projection: "mercator",
                  backgroundColor: "rgb(222,0,0)",
                },
                r: {
                  size: [1, 20],
                },
              },
            },
          });

          return;
          const chart = new Chart(ctx, {
            type: "bubbleMap",
            data: {
              labels: formatted.map((d) => d.properties.name),
              datasets: [
                {
                  label: theChart.translations.visits,
                  data: dataFormatted,
                },
              ],
            },
            options: {
              borderWidth: 1.5,
              //borderColor: "#333",
              //borderRadius: 50,
              //showOutline: false,
              //showGraticule: false,
              //interpolate: (v) => (v < 0.5 ? "green" : "red"),
              plugins: {
                legend: {
                  display: false,
                },
                scale: {
                  //display: false,
                },
                tooltip: {
                  enabled: false,

                  external: function (context) {
                    theChart.getTooltip(context);
                  },
                },
              },
              scales: {
                xy: {
                  projection: "equalEarth",
                  //projectionScale: 1.2,
                  //projectionOffset: [0, 0],
                  //projection: "equirectangular",
                },
                color: {
                  //quantize: 6,
                  //display: false,
                  interpolate: (v) => {
                    if (v === 0) return "rgba(12, 92, 239, 0.1)";
                    if (v >= 0.1 && v < 0.2) return "rgba(12, 92, 239, 0.4)";
                    if (v >= 0.2 && v < 0.4) return "rgba(12, 92, 239, 0.6)";
                    if (v >= 0.4 && v < 0.6) return "rgba(12, 92, 239, 0.8";
                    if (v >= 0.6 && v < 0.8) return "rgba(12, 92, 239, 0.9)";
                    if (v >= 0.8) return "rgba(12, 92, 239, 1)";
                  },
                  legend: {
                    display: false,
                    position: "bottom-right",
                    align: "bottom",
                  },
                },
              },
            },
          });
        });

      ////
    },
  },
  template: '<canvas class="uip-margin-bottom-m" height="200" :dat-sd="dates.startDate" :dat-sed="dates.endDate"></canvas>',
});

uipressOverviewApp.component("uip-hover-dropdown", {
  props: {},
  data: function () {
    return {
      modelOpen: false,
    };
  },
  mounted: function () {
    this.getTop;
  },
  computed: {
    getTop() {
      self = this;
      returnDatat = 0;
      ///SET TOP
      let POStop = self.$el.getBoundingClientRect().top;
      let POSbottom = self.$el.getBoundingClientRect().bottom + 50;
      let POSright = self.$el.getBoundingClientRect().right;
      let POSleft = self.$el.getBoundingClientRect().POSleft;
      returnDatat = POStop + "px";

      //CHECK FOR OFFSCREEN

      submenu = self.$el.getElementsByClassName("uip-hover-dropdown")[0];
      let rect = submenu.getBoundingClientRect();

      submenu.setAttribute("style", "top:" + POStop + "px;left:" + POSleft + "px;");

      return;

      if (rect.bottom > (window.innerHeight - 50 || document.documentElement.clientHeight - 50)) {
        // Bottom is out of viewport
        submenu.setAttribute("style", "top: " + (POStop - rect.height) + "px;" + "left:" + POSleft + "px");
      }
    },
  },
  methods: {},
  template:
    '<div class="uip-position-absolute"><div class="uip-position-fixed uip-padding-m uip-background-default uip-border-round uip-shadow uip-w-250 uip-hover-dropdown" >\
          <slot></slot>\
      </div></div>',
});

uipressOverviewApp.component("draggable", vuedraggable);

//uipressOverviewApp.component("uip-chart", vue3chart3);

//import { Chart, registerables } from "chart.js";

//let Vue3ChartJs = import("../chartjs/vue3-chartjs.es.js");

//uipressOverviewApp.component("vue3-chart-js", Vue3ChartJs);

var fnWithForeach = async (modules) => {
  return await uipOverviewMods.forEach(async (amodule, index) => {
    //let theModule = await import(amodule.componentPath);
    let activated = await uipressOverviewApp.component(amodule.moduleName(), amodule.moduleData());
    if (index == modules.length - 1) {
      uipressOverviewApp.mount("#overview-app");
    }
  });
  return;
};

var fnWithForeach123 = async (modules) => {
  return await modules.forEach(async (amodule, index) => {
    let theModule = await import(amodule.componentPath);
    let activated = await uipressOverviewApp.component(theModule.moduleName(), theModule.moduleData());
    if (index == modules.length - 1) {
      uipressOverviewApp.mount("#overview-app");
    }
  });
  return;
};

async function uip_build_overviewddd() {
  let result = await fnWithForeach(uipressOverviewModules);
}

function uip_build_overview() {
  uipOverviewMods.forEach(function (item, index) {
    uipressOverviewApp.component(item.moduleName(), item.moduleData());
    if (index == uipOverviewMods.length - 1) {
      uipressOverviewApp.mount("#overview-app");
    }
  });
}
