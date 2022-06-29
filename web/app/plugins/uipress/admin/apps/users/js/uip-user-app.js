const uipUserData = JSON.parse(uip_user_app_ajax.appData);
//IMPORT MODULES

const uipUserAppArgs = {
  data() {
    return {
      loading: true,
      screenWidth: window.innerWidth,
      appData: uipUserData.app,
    };
  },
  created: function () {
    window.addEventListener("resize", this.getScreenWidth);
    var self = this;
  },
  computed: {},
  mounted: function () {
    this.loading = false;
  },
  methods: {
    updateGloablData(data) {
      this.appData = data;
    },
  },
  template:
    '<div class="uip-padding-m">\
      <div v-if="appData.dataConnect">\
  		  <build-navigation :data="appData" :dataChange="updateGloablData"></build-navigation>\
  		  <user-table v-if="appData.currentPage == \'users\'" :data="appData" :dataChange="updateGloablData"></user-table>\
        <role-table v-if="appData.currentPage == \'roles\'" :data="appData" :dataChange="updateGloablData"></role-table>\
        <activity-table v-if="appData.currentPage == \'activity\'" :data="appData" :dataChange="updateGloablData"></activity-table>\
      </div>\
      <div v-else>\
        <img class="uip-w-100p " :src="appData.previewImage">\
        <div class="uip-position-absolute uip-top-0 uip-bottom-0 uip-left-0 uip-right-0" \
        style="background: linear-gradient(0deg, rgba(255,255,255,1) 0%, rgba(255,255,255,0) 100%);"></div>\
        <div class="uip-position-absolute uip-top-0 uip-bottom-0 uip-left-0 uip-right-0 uip-flex uip-flex-center uip-flex-middle">\
          <div class="uip-background-default uip-border-round uip-padding-m uip-shadow uip-flex uip-flex-center uip-flex-column">\
            <div class="uip-flex uip-text-l uip-text-bold uip-margin-bottom-s">\
              <span class="material-icons-outlined uip-margin-right-xs">redeem</span>\
              <span>{{appData.translations.proFeature}}</span>\
            </div> \
            <p class="uip-text-normal uip-margin-bottom-m">{{appData.translations.proFeatureUpgrade}}</p>\
            <a href="https://uipress.co/pricing/" target="_BLANK" class="uip-button-primary uip-no-underline">{{appData.translations.viewPlans}}</a>\
          </div>\
        </div>\
      </div>\
    </div>',
};

const uipUserApp = uipVue.createApp(uipUserAppArgs);

//import to app
import * as navigation from "./modules/navigation.min.js";
import * as userTable from "./modules/user-table.min.js";
import * as roleSelect from "./modules/select-roles.min.js";
import * as dropdown from "./modules/dropdown.min.js";
import * as tooltip from "./modules/tooltip.min.js";
import * as offcanvas from "./modules/offcanvas.min.js";
import * as userPanel from "./modules/user-panel.min.js";
import * as userMessage from "./modules/user-message.min.js";
import * as newUser from "./modules/new-user.min.js";
import * as roleTable from "./modules/role-table.min.js";
import * as rolePanel from "./modules/role-panel.min.js";
import * as newRole from "./modules/new-role.min.js";
import * as activityTable from "./modules/activity-table.min.js";
import * as batchRoleUpdate from "./modules/batch-role-update.min.js";
import * as userGroups from "./modules/user-groups.min.js";
import * as groupTemplate from "./modules/group-template.min.js";
import * as groupSelect from "./modules/group-select.min.js";
import * as iconSelect from "./modules/icon-select.min.js";
///import components
uipUserApp.component("build-navigation", navigation.moduleData());
uipUserApp.component("user-table", userTable.moduleData());
uipUserApp.component("role-select", roleSelect.moduleData());
uipUserApp.component("dropdown", dropdown.moduleData());
uipUserApp.component("tooltip", tooltip.moduleData());
uipUserApp.component("offcanvas", offcanvas.moduleData());
uipUserApp.component("user-panel", userPanel.moduleData());
uipUserApp.component("user-message", userMessage.moduleData());
uipUserApp.component("new-user", newUser.moduleData());
uipUserApp.component("role-table", roleTable.moduleData());
uipUserApp.component("role-panel", rolePanel.moduleData());
uipUserApp.component("new-role", newRole.moduleData());
uipUserApp.component("activity-table", activityTable.moduleData());
uipUserApp.component("batch-role-update", batchRoleUpdate.moduleData());
uipUserApp.component("user-groups", userGroups.moduleData());
uipUserApp.component("group-template", groupTemplate.moduleData());
uipUserApp.component("group-select", groupSelect.moduleData());
uipUserApp.component("icon-select", iconSelect.moduleData());

uipUserApp.config.errorHandler = function (err, vm, info) {
  console.log(err);
};

uipUserApp.mount("#uip-user-management");
