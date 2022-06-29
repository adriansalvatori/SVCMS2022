export function moduleData() {
  return {
    props: {
      activerole: Object,
      refreshTable: Function,
      closePanel: Function,
      appdata: Object,
    },
    data: function () {
      return {
        role: {
          editData: this.activerole,
        },
        activeCat: "read",
        allcaps: this.appdata.capabilities,
        customcap: "",
      };
    },
    mounted: function () {},
    computed: {
      totalAvailableCaps() {
        let allCaps = this.allcaps;
        let count = 0;
        for (var cat in allCaps) {
          let currentcat = allCaps[cat];
          let caps = currentcat.caps;
          count += caps.length;
        }
        return count;
      },
      totalAssignedCaps() {
        let allCaps = this.role.editData.caps;
        let count = 0;
        for (var cat in allCaps) {
          let currentcap = allCaps[cat];
          if (currentcap == true) {
            count += 1;
          }
        }
        return count;
      },
    },
    methods: {
      updateRole() {
        let self = this;

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_update_role",
            security: uip_user_app_ajax.security,
            role: self.role.editData,
            originalRoleName: self.activerole.name,
          },
          success: function (response) {
            let data = JSON.parse(response);

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }
            uipNotification(data.message, { pos: "bottom-left", status: "danger" });
          },
        });
      },
      addCustomCap() {
        let self = this;
        self.role.editData.caps[self.customcap] = false;

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_add_custom_capability",
            security: uip_user_app_ajax.security,
            role: self.role.editData,
            customcap: self.customcap,
          },
          success: function (response) {
            let data = JSON.parse(response);

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }
            uipNotification(data.message, { pos: "bottom-left", status: "danger" });
            self.customcap = "";
            self.allcaps = data.allcaps;
          },
        });
      },
      isInCaps(cap) {
        let currentcaps = this.role.editData.caps;
        if (currentcaps[cap] && currentcaps[cap] == true) {
          return true;
        } else {
          return false;
        }
      },
      toggleCap(cap) {
        let currentcaps = this.role.editData.caps;
        if (currentcaps[cap] && currentcaps[cap] == true) {
          this.role.editData.caps[cap] = false;
        } else {
          this.role.editData.caps[cap] = true;
        }
      },
    },
    template:
      '<div class="" >\
        <!-- EDITING USER -->\
        <div class="" >\
          <div class="uip-text-bold uip-text-xl uip-margin-bottom-m">{{appdata.translations.editRole}}</div>\
          <div class="uip-flex uip-flex-column uip-row-gap-s">\
            <div v-if="role.editData.name == \'administrator\'" class="uip-border-round uip-padding-xs uip-background-red-wash">\
              {{appdata.translations.adminWarning}}\
            </div>\
            <div class="uip-margin-bottom-s">\
              <div class="uip-margin-bottom-xs">{{appdata.translations.roleLabel}}</div>\
              <input type="text" class="uip-w-100p" v-model="role.editData.label">\
            </div>\
            <div>\
              <div class="uip-flex uip-margin-bottom-s uip-flex-middle uip-flex-center uip-flex-between uip-background-muted uip-border-rounded uip-padding-xs uip-border-round">\
                  <div class="uip-text-m uip-text-bold uip-flex-grow">{{appdata.translations.capabilities}}</div>\
                  <div class="uip-text-muted">{{totalAssignedCaps}} / {{totalAvailableCaps}}</div>\
              </div>\
              <div class="uip-flex uip-gap-xs uip-padding-xs">\
                <input :placeholder="appdata.translations.addCustomCapability" class="uip-input uip-flex-grow" type="text" v-model="customcap">\
                <button class="uip-button-default" @click="addCustomCap();">{{appdata.translations.addCapability}}</button>\
              </div>\
              <div class="uip-padding-xs uip-flex uip-gap-s">\
                <div class="uip-w-150 uip-flex uip-flex-column uip-gap-xxs">\
                  <template v-for="cat in allcaps">\
                    <div class="uip-padding-xxs uip-border-round uip-flex uip-gap-xxs hover:uip-background-muted uip-cursor-pointer" \
                    :class="{\'uip-background-muted uip-text-bold\' : activeCat == cat.shortname}" @click="activeCat = cat.shortname">\
                      <div class="material-icons-outlined">{{cat.icon}}</div>\
                      <div class="">{{cat.name}}</div>\
                    </div>\
                  </template>\
                </div>\
                <div class="uip-flex-grow uip-padding-xxs uip-flex uip-flex-column uip-row-gap-xxs">\
                  <template v-for="cap in allcaps[activeCat].caps">\
                    <div class="uip-flex uip-flex-between uip-flex-center uip-cursor-pointer" @click="toggleCap(cap)">\
                      <div class="">{{cap}}</div>\
                      <input type="checkbox" :checked="isInCaps(cap)">\
                    </div>\
                  </template>\
                </div>\
              </div>\
            </div>\
            <div class="uip-flex uip-flex-between uip-margin-top-m">\
              <button class="uip-button-default" @click="closePanel()">{{appdata.translations.cancel}}</button>\
              <button class="uip-button-primary" @click="updateRole()">{{appdata.translations.saveRole}}</button>\
            </div>\
          </div>\
        </div>\
      </div>',
  };
}
