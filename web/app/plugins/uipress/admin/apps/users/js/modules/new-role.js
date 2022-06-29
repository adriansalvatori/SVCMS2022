export function moduleData() {
  return {
    props: {
      refreshTable: Function,
      closePanel: Function,
      appdata: Object,
      clonerole: Object,
      resetclone: Function,
    },
    data: function () {
      return {
        role: {
          editData: {
            name: "",
            label: "",
            caps: {},
          },
        },
        activeCat: "read",
        allcaps: this.appdata.capabilities,
      };
    },
    mounted: function () {
      if (this.clonerole.caps) {
        this.role.editData.caps = this.clonerole.caps;
      }

      if (this.clonerole.name) {
        this.role.editData.name = this.clonerole.label + " " + this.appdata.translations.copy;
      }

      if (this.clonerole.label) {
        this.role.editData.label = this.clonerole.name + "_" + this.appdata.translations.copy;
      }

      this.resetclone();
    },
    computed: {
      totalAvailableCaps() {
        let allCaps = this.appdata.capabilities;
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
      saveRole() {
        let self = this;
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_create_role",
            security: uip_user_app_ajax.security,
            newrole: self.role.editData,
          },
          success: function (response) {
            let data = JSON.parse(response);

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }
            uipNotification(data.message, { pos: "bottom-left", status: "danger" });
            self.refreshTable();
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
          <div class="uip-text-bold uip-text-xl uip-margin-bottom-m">{{appdata.translations.newRole}}</div>\
          <div class="uip-flex uip-flex-column uip-row-gap-s">\
            <div class="uip-margin-bottom-s">\
              <div class="uip-margin-bottom-xs">{{appdata.translations.roleName}}</div>\
              <input type="text" class="uip-w-100p" v-model="role.editData.name">\
            </div>\
            <div class="uip-margin-bottom-s">\
              <div class="uip-margin-bottom-xs">{{appdata.translations.roleLabel}}</div>\
              <input type="text" class="uip-w-100p uip-margin-bottom-xs" v-model="role.editData.label">\
              <div class="uip-text-small uip-text-muted">{{appdata.translations.roleLabelDescription}}</div>\
            </div>\
            <div>\
              <div class="uip-flex uip-margin-bottom-s uip-flex-middle uip-flex-center uip-flex-between uip-background-muted uip-border-rounded uip-padding-xs uip-border-round">\
                  <div class="uip-text-m uip-text-bold uip-flex-grow">{{appdata.translations.capabilities}}</div>\
                  <div class="uip-text-muted">{{totalAssignedCaps}} / {{totalAvailableCaps}}</div>\
              </div>\
              <div class="uip-padding-xs uip-flex uip-gap-s">\
                <div class="uip-w-150 uip-flex uip-flex-column uip-gap-xxs">\
                  <template v-for="cat in appdata.capabilities">\
                    <div class="uip-padding-xxs uip-border-round uip-flex uip-gap-xxs hover:uip-background-muted uip-cursor-pointer" \
                    :class="{\'uip-background-muted uip-text-bold\' : activeCat == cat.shortname}" @click="activeCat = cat.shortname">\
                      <div class="material-icons-outlined">{{cat.icon}}</div>\
                      <div class="">{{cat.name}}</div>\
                    </div>\
                  </template>\
                </div>\
                <div class="uip-flex-grow uip-padding-xxs uip-flex uip-flex-column uip-row-gap-xxs">\
                  <template v-for="cap in appdata.capabilities[activeCat].caps">\
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
              <button class="uip-button-primary" @click="saveRole()">{{appdata.translations.saveRole}}</button>\
            </div>\
          </div>\
        </div>\
      </div>',
  };
}
