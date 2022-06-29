export function moduleData() {
  return {
    props: {
      data: Object,
      dataChange: Function,
    },
    data: function () {
      return {
        loading: true,
        modData: this.data,
        tableData: [],
        tablePage: 1,
        selectAll: false,
        queryRunning: false,
        tableFilters: {
          search: "",
          roles: [],
          dateCreated: {
            type: "on",
            date: "",
          },
        },
        tableOptions: {
          direction: "ASC",
          sortBy: "username",
          perPage: 20,
        },
        ui: {
          rolePanelOpen: false,
          activeRole: [],
          newRoleOpen: false,
          cloneRole: {},
        },
      };
    },
    mounted: function () {
      this.loading = false;
      this.getRoleData();
    },
    watch: {
      modData: {
        handler(newValue, oldValue) {
          this.dataChange(newValue);
        },
        deep: true,
      },
      tableFilters: {
        handler(newValue, oldValue) {
          this.getRoleData();
        },
        deep: true,
      },
      tableOptions: {
        handler(newValue, oldValue) {
          this.getRoleData();
        },
        deep: true,
      },
      selectAll: {
        handler(newValue, oldValue) {
          if (newValue == true) {
            this.selectAllUsers();
          } else {
            this.deSelectAllUsers();
          }
        },
      },
    },
    computed: {
      returnTableData() {
        return this.tableData;
      },
      totalSelected() {
        let self = this;
        let count = 0;
        for (var user in self.tableData.roles) {
          if (self.tableData.roles[user].selected && self.tableData.roles[user].selected == true) {
            count += 1;
          }
        }
        return count;
      },
      getTotalSelected() {
        let self = this;
        let selected = [];
        for (var user in self.tableData.roles) {
          if (self.tableData.roles[user].selected && self.tableData.roles[user].selected == true) {
            selected.push(self.tableData.roles[user]);
          }
        }
        return selected;
      },
    },
    methods: {
      selectAllUsers() {
        let self = this;
        for (var user in self.tableData.roles) {
          self.tableData.roles[user].selected = true;
        }
        self.selectAll = true;
      },
      deSelectAllUsers() {
        let self = this;
        for (var user in self.tableData.roles) {
          self.tableData.roles[user].selected = false;
        }
        self.selectAll = false;
      },

      changePage(direction) {
        if (direction == "next") {
          this.tablePage += 1;
        }
        if (direction == "previous") {
          this.tablePage = this.tablePage - 1;
        }
        this.getRoleData();
      },
      updateRoles(roles) {
        this.tableFilters.roles = roles;
      },
      getRoleData() {
        let self = this;

        if (self.queryRunning) {
          return;
        }
        self.queryRunning = true;
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_get_role_table_data",
            security: uip_user_app_ajax.security,
            tablePage: self.tablePage,
            filters: self.tableFilters,
            options: self.tableOptions,
          },
          success: function (response) {
            let data = JSON.parse(response);
            self.queryRunning = false;

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }

            let columns = [];
            if (self.tableData.columns && self.tableData.columns.length > 0) {
              columns = self.tableData.columns;
            }
            self.tableData = data.tableData;

            if (columns.length > 0) {
              self.tableData.columns = columns;
            }
          },
        });
      },
      deleteRole(role) {
        let self = this;
        if (!confirm(self.data.translations.confirmRoleDelete)) {
          return;
        }

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_delete_role",
            security: uip_user_app_ajax.security,
            role: role,
          },
          success: function (response) {
            let data = JSON.parse(response);

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }
            uipNotification(data.message, { pos: "bottom-left", status: "danger" });
            self.getRoleData();
          },
        });
      },
      bulkDeleteRoles() {
        let self = this;

        if (!confirm(self.data.translations.confirmRoleDeleteMultiple)) {
          return;
        }

        let allRoles = self.getTotalSelected;

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_delete_roles",
            security: uip_user_app_ajax.security,
            roles: allRoles,
          },
          success: function (response) {
            let data = JSON.parse(response);

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }
            uipNotification(data.message, { pos: "bottom-left", status: "danger" });

            if (data.undeleted.length > 0) {
              for (var error in data.undeleted) {
                let theerror = data.undeleted[error];
                let themessage = theerror.role + "<br>" + theerror.message;
                uipNotification(themessage);
              }
            }
            self.getRoleData();
          },
        });
      },
      openMessenger(user) {
        this.ui.messageRecipient = user;
        this.ui.messagePanelOpen = true;
      },
      cloneRole(role) {
        this.ui.cloneRole = role;
        this.ui.newRoleOpen = true;
      },
    },
    template:
      '<div class="uip-margin-top-m uip-text-normal">\
          <div class="uip-margin-bottom-s uip-flex uip-flex-between">\
            <div class="uip-flex uip-gap-xs">\
              <div class="uip-background-muted uip-padding-xs uip-border-round hover:uip-background-grey uip-flex uip-flex-center uip-max-w-400">\
                <span class="material-icons-outlined uk-form-icon uip-margin-right-xs">search</span>\
                <input class="uip-blank-input uip-w-100p" :placeholder="data.translations.searchRoles" v-model="tableFilters.search">\
              </div>\
            </div>\
            <div class="uip-flex uip-gap-xs">\
              <button class="uip-button-secondary uip-flex uip-gap-xxs uip-flex-center" type="button" @click="ui.newRoleOpen = true">\
                <span>{{data.translations.newRole}}</span>\
                <span class="material-icons-outlined">badge</span>\
              </button>\
              <offcanvas :open="ui.newRoleOpen" :closeOffcanvas="function() {ui.newRoleOpen = false}">\
                <new-role :resetclone="function() {ui.cloneRole = {}}" :appdata="data"  :clonerole="ui.cloneRole" :refreshTable="getRoleData" :closePanel="function() {ui.newRoleOpen = false}"></new-role>\
              </offcanvas>\
            </div>\
          </div>\
	  	    <table class="uip-w-100p uip-border-collapse uip-margin-bottom-s">\
              <thead>\
                  <tr class="uip-border-bottom" >\
                      <th class="uip-text-left uip-w-28 uip-padding-xs"><input type="checkbox" v-model="selectAll"></th>\
                      <template v-for="column in returnTableData.columns">\
                        <th class="uip-text-left uip-text-bold uip-text-muted uip-padding-xs" v-if="column.active" :class="{\'uip-hidden-small\' : !column.mobile}">\
                          {{column.label}}\
                        </th>\
                      </template>\
                      <th></th>\
                  </tr>\
              </thead>\
              <tbody>\
                  <template v-for="role in returnTableData.roles">\
                    <tr class="hover:uip-background-muted uip-cursor-pointer" @dblclick="ui.rolePanelOpen = true; ui.activeRole = role">\
                        <td class="uip-border-bottom uip-w-28 uip-padding-xs"><input class="" type="checkbox" v-model="role.selected"></td>\
                        <template v-for="column in returnTableData.columns">\
                          <td class="uip-text-left uip-padding-xs uip-border-bottom" v-if="column.active" :class="{\'uip-hidden-small\' : !column.mobile}">\
                            <div v-if="column.name == \'label\'" class="uip-flex uip-flex-center uip-gap-xs uip-text-bold">\
                              <span>{{role[column.name]}}</span>\
                            </div>\
                            <div v-else-if="column.name == \'roles\'" class="uip-flex uip-flex-wrap uip-gap-xs uip-row-gap-xxs">\
                              <div v-for="role in role.roles" class="uip-background-primary-wash uip-border-round uip-padding-left-xxs uip-padding-right-xxs uip-text-bold uip-text-s">\
                                {{role}}\
                              </div>\
                            </div>\
                            <div v-else>{{role[column.name]}}</div>\
                          </td>\
                        </template>\
                        <td class="uip-border-bottom">\
                          <div class="uip-flex">\
                            <dropdown type="icon" icon="more_horiz" pos="bottom-right" buttonsize="small" \
                            :tooltip="true" :tooltiptext="data.translations.roleOptions" :width="200">\
                              <div class="uip-padding-xs uip-flex uip-flex-column uip-row-gap-xxs uip-w-200">\
                                <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="ui.rolePanelOpen = true; ui.activeRole = role">\
                                  <div class="material-icons-outlined">edit</div>\
                                  <div>{{data.translations.editRole}}</div>\
                                </div>\
                                <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="cloneRole(role)">\
                                  <div class="material-icons-outlined">content_copy</div>\
                                  <div>{{data.translations.clone}}</div>\
                                </div>\
                                <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="deleteRole(role)">\
                                  <div class="material-icons-outlined">delete</div>\
                                  <div>{{data.translations.deleteRole}}</div>\
                                </div>\
                              </div>\
                            </dropdown>\
                          </div>\
                        </td>\
                    </tr>\
                  </template>\
              </tbody>\
          </table>\
	    </div>\
      <div class="uip-position-fixed uip-right-0 uip-bottom-0 uip-padding-m uip-z-index-9 uip-text-normal" v-if="totalSelected > 0">\
        <div class="uip-position-relative">\
          <dropdown type="text" icon="more_horiz" :buttontext="totalSelected + \' \' + data.translations.rolesSelected"\
          pos="bottom-right" buttonsize="medium" :tooltip="false" :width="200" buttonstyle="primary">\
            <div class="uip-padding-xs uip-w-200 uip-flex uip-flex-column uip-row-gap-xxs">\
              <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="bulkDeleteRoles()">\
                <div class="material-icons-outlined">delete</div>\
                <div>{{data.translations.deleteSelected}}</div>\
              </div>\
              <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="deSelectAllUsers()">\
                <div class="material-icons-outlined">backspace</div>\
                <div>{{data.translations.clearSelection}}</div>\
              </div>\
            </div>\
          </dropdown>\
        </div>\
      </div>\
      <offcanvas :open="ui.rolePanelOpen" :closeOffcanvas="function() {ui.rolePanelOpen = false}">\
        <role-panel :appdata="data" :activerole="ui.activeRole" :refreshTable="getRoleData" :closePanel="function() {ui.rolePanelOpen = false}"></role-panel>\
      </offcanvas>',
  };
  return compData;
}
