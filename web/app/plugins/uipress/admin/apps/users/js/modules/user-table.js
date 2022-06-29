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
          activeGroup: "all",
        },
        tableOptions: {
          direction: "ASC",
          sortBy: "username",
          perPage: 20,
        },
        ui: {
          offcanvas: {
            userPanelOpen: false,
            messagePanelOpen: false,
            newUserOpen: false,
            batchRolePanelOpen: false,
          },
          activeUser: 0,
          groupsOpen: false,
          messageRecipient: [],
          batchMessageRecipient: [],
          groups: [],
        },
      };
    },
    mounted: function () {
      this.loading = false;
      this.getUserData();
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
          this.getUserData();
        },
        deep: true,
      },
      tableOptions: {
        handler(newValue, oldValue) {
          this.getUserData();
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
        for (var user in self.tableData.users) {
          if (self.tableData.users[user].selected && self.tableData.users[user].selected == true) {
            count += 1;
          }
        }
        return count;
      },
      getTotalSelected() {
        let self = this;
        let selected = [];
        for (var user in self.tableData.users) {
          if (self.tableData.users[user].selected && self.tableData.users[user].selected == true) {
            selected.push(self.tableData.users[user].user_id);
          }
        }
        return selected;
      },
      getTotalSelectedUsers() {
        let self = this;
        let selected = [];
        for (var user in self.tableData.users) {
          if (self.tableData.users[user].selected && self.tableData.users[user].selected == true) {
            selected.push(self.tableData.users[user]);
          }
        }
        return selected;
      },
    },
    methods: {
      selectAllUsers() {
        let self = this;
        for (var user in self.tableData.users) {
          self.tableData.users[user].selected = true;
        }
        self.selectAll = true;
      },
      deSelectAllUsers() {
        let self = this;
        for (var user in self.tableData.users) {
          self.tableData.users[user].selected = false;
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
        this.getUserData();
      },
      updateRoles(roles) {
        this.tableFilters.roles = roles;
      },
      getUserData() {
        let self = this;

        if (self.queryRunning) {
          return;
        }
        self.queryRunning = true;
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_get_user_table_data",
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
            self.selectAll = false;
          },
        });
      },
      sendPasswordReset(user) {
        let self = this;
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_reset_password",
            security: uip_user_app_ajax.security,
            user: user,
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
      deleteUser(userID) {
        let self = this;
        if (!confirm(self.data.translations.confirmUserDelete)) {
          return;
        }

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_delete_user",
            security: uip_user_app_ajax.security,
            userID: userID,
          },
          success: function (response) {
            let data = JSON.parse(response);

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }
            uipNotification(data.message, { pos: "bottom-left", status: "danger" });
            self.getUserData();
          },
        });
      },
      deleteMultiple() {
        let self = this;
        if (!confirm(self.data.translations.confirmUserDeleteMultiple)) {
          return;
        }

        let allIDS = self.getTotalSelected;

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_delete_multiple_users",
            security: uip_user_app_ajax.security,
            allIDS: allIDS,
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
                let themessage = theerror.user + "<br>" + theerror.message;
                uipNotification(themessage);
              }
            }

            self.getUserData();
          },
        });
      },
      sendMultiplePasswordReset() {
        let self = this;
        if (!confirm(self.data.translations.confirmUserPassReset)) {
          return;
        }

        let allIDS = self.getTotalSelected;

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_password_reset_multiple",
            security: uip_user_app_ajax.security,
            allIDS: allIDS,
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
      openMessenger(user) {
        this.ui.messageRecipient = user;
        this.ui.offcanvas.messagePanelOpen = true;
      },
      openMessengerBatch() {
        this.ui.offcanvas.messagePanelOpen = true;
      },
      batchRoleUpdate() {
        this.ui.offcanvas.batchRolePanelOpen = true;
      },
      getActiveGroup(groupID) {
        this.tableFilters.activeGroup = groupID;
      },
      returnGroupTag(group) {
        if (this.returnTableData.groups[group]) {
          return this.returnTableData.groups[group].title;
        }
      },
      returnGroupColour(group) {
        if (this.returnTableData.groups[group]) {
          return "background:" + this.returnTableData.groups[group].color;
        }
      },
    },
    template:
      '<div class="uip-margin-top-m uip-text-normal uip-flex uip-gap-m uip-flex-wrap">\
          <div v-if="ui.groupsOpen" class="uip-w-250 uip-flex-no-shrink mobile:uip-w-100p">\
            <div class="uip-margin-bottom-s uip-text-bold uip-background-muted uip-padding-xs uip-border-round uip-flex uip-flex-center uip-text-bold">{{data.translations.groups}}</div>\
            <user-groups :currentGroup="tableFilters.activeGroup" :updateuserdata="getUserData" :appdata="data" :updateactivegroup="getActiveGroup"></user-groups>\
          </div>\
          <div class="uip-flex-grow">\
            <div class="uip-margin-bottom-s uip-flex uip-flex-between uip-flex-wrap uip-row-gap-xs">\
              <div class="uip-flex uip-gap-xs uip-flex-wrap uip-row-gap-xs">\
                <tooltip :tooltiptext="data.translations.userGroups">\
                  <div @click="ui.groupsOpen = !ui.groupsOpen" class="uip-background-muted uip-padding-xs uip-border-round hover:uip-background-grey uip-flex uip-flex-center uip-cursor-pointer">\
                    <span class="material-icons-outlined uk-form-icon">group</span>\
                  </div>\
                </tooltip>\
                <div class="uip-background-muted uip-padding-xs uip-border-round hover:uip-background-grey uip-flex uip-flex-center uip-max-w-400">\
                  <span class="material-icons-outlined uk-form-icon uip-margin-right-xs">search</span>\
                  <input class="uip-blank-input uip-w-100p" :placeholder="data.translations.searchUsers" v-model="tableFilters.search">\
                </div>\
                <div class="">\
                  <role-select :selected="tableFilters.roles"\
                  :name="data.translations.filterByRole"\
                  :translations="data.translations"\
                  :single=\'false\'\
                  :placeholder="data.translations.searchRoles"\
                  :updateRoles="updateRoles"></role-select>\
                </div>\
                <div class="uip-position-relative">\
                  <!-- DATE FILTERS -->\
                  <dropdown type="icon" icon="calendar_today" pos="botton-left" buttonsize="normal" \
                  :tooltip="true" :tooltiptext="data.translations.dateFilters">\
                    <div class="uip-padding-s uip-flex uip-flex-column uip-row-gap-s uip-flex-start">\
                      <div class="uip-text-muted uip-text-bold">{{data.translations.dateCreated}}</div>\
                      <div class="uip-flex uip-gap-xs uip-background-muted uip-border-round uip-padding-xxs">\
                        <div class="uip-padding-xxs uip-border-round uip-cursor-pointer" @click="tableFilters.dateCreated.type = \'on\'" \
                        :class="{\'uip-background-default uip-text-bold\' : tableFilters.dateCreated.type == \'on\'}">\
                          {{data.translations.on}}\
                        </div>\
                        <div class="uip-padding-xxs uip-border-round uip-cursor-pointer" @click="tableFilters.dateCreated.type = \'after\'" \
                        :class="{\'uip-background-default uip-text-bold\' : tableFilters.dateCreated.type == \'after\'}">\
                          {{data.translations.after}}\
                        </div>\
                        <div class="uip-padding-xxs uip-border-round uip-cursor-pointer" @click="tableFilters.dateCreated.type = \'before\'" \
                        :class="{\'uip-background-default uip-text-bold\' : tableFilters.dateCreated.type == \'before\'}">\
                          {{data.translations.before}}\
                        </div>\
                      </div>\
                      <div>\
                        <input class="uip-input" type="date" v-model="tableFilters.dateCreated.date">\
                      </div>\
                    </div>\
                  </dropdown>\
                  <!-- DATE FILTERS NUMBER DISPLAY -->\
                  <span v-if="tableFilters.dateCreated.date != \'\'" class="uip-text-inverse uip-background-primary uip-border-round uip-text-s uip-w-18 uip-margin-left-xxs uip-text-center uip-position-absolute uip-right--8 uip-top--8">\
                    1\
                  </span>\
                </div>\
              </div>\
              <div class="uip-flex uip-gap-xs">\
                <button class="uip-button-secondary uip-flex uip-gap-xxs uip-flex-center" type="button" @click="ui.offcanvas.newUserOpen = true">\
                  <span>{{data.translations.newUser}}</span>\
                  <span class="material-icons-outlined">person_add</span>\
                </button>\
                <offcanvas :open="ui.offcanvas.newUserOpen" :closeOffcanvas="function() {ui.offcanvas.newUserOpen = false}">\
                  <new-user :groups="returnTableData.groups" :userID="ui.activeUser" :translations="data.translations" :refreshTable="getUserData" :closePanel="function() {ui.offcanvas.newUserOpen = false}"></new-user>\
                </offcanvas>\
                <dropdown type="icon" icon="more_horiz" pos="botton-left" buttonsize="normal" \
                :tooltip="true" :tooltiptext="data.translations.tableOptions" :width="250">\
                  <div class="uip-padding-s uip-flex uip-flex-column uip-row-gap-s uip-w-250">\
                    <div class="uip-flex uip-flex-between uip-flex-center">\
                      <div class="uip-flex">\
                        <span class="material-icons-outlined uip-margin-right-xxs">swap_vert</span>\
                        <span>{{data.translations.order}}</span>\
                      </div>\
                      <div>\
                        <select v-model="tableOptions.direction">\
                          <option value="ASC">{{data.translations.ascending}}</option>\
                          <option value="DESC">{{data.translations.descending}}</option>\
                        </select>\
                      </div>\
                    </div>\
                    <div class="uip-flex uip-flex-between uip-flex-center">\
                      <div class="uip-flex">\
                        <span class="material-icons-outlined uip-margin-right-xxs">sort</span>\
                        <span>{{data.translations.sortBy}}</span>\
                      </div>\
                      <div>\
                        <select v-model="tableOptions.sortBy">\
                          <template v-for="column in returnTableData.columns">\
                            <option :value="column.name">{{column.label}}</option>\
                          </template>\
                        </select>\
                      </div>\
                    </div>\
                    <div class="uip-flex uip-flex-between uip-flex-center">\
                      <div class="uip-flex">\
                        <span class="material-icons-outlined uip-margin-right-xxs">format_list_numbered</span>\
                        <span>{{data.translations.perPage}}</span>\
                      </div>\
                      <div>\
                        <input type="number" min="1" max="2000" class="uip-input" v-model="tableOptions.perPage">\
                      </div>\
                    </div>\
                    <div class="uip-border-bottom uip-margin-top-xs uip-margin-bottom-xs"></div>\
                    <div class="uip-text-muted uip-text-bold ">{{data.translations.fields}}</div>\
                    <div class="">\
                      <div v-for="column in returnTableData.columns" class="uip-flex uip-flex-between uip-flex-center uip-padding-xxs uip-border-round hover:uip-background-muted uip-cursor-pointer" @click="column.active = !column.active">\
                        <div class="">{{column.label}}</div>\
                        <input type="checkbox" v-model="column.active">\
                      </div>\
                    </div>\
                  </div>\
                </dropdown>\
              </div>\
            </div>\
	  	      <table class="uip-w-100p uip-border-collapse uip-margin-bottom-s">\
                <thead>\
                    <tr class="uip-border-bottom">\
                        <th class="uip-text-left uip-w-28 uip-padding-xs"><input type="checkbox" v-model="selectAll"></th>\
                        <template v-for="column in returnTableData.columns">\
                          <th class="uip-text-left uip-text-bold uip-text-muted uip-padding-xs" :class="{\'uip-hidden-small\' : !column.mobile}" v-if="column.active">\
                            {{column.label}}\
                          </th>\
                        </template>\
                        <th></th>\
                    </tr>\
                </thead>\
                <tbody>\
                    <template v-for="user in returnTableData.users">\
                      <tr class="hover:uip-background-muted uip-cursor-pointer" @dblclick="ui.offcanvas.userPanelOpen = true; ui.activeUser = user.user_id">\
                          <td class="uip-border-bottom uip-w-28 uip-padding-xs">\
                            <div class=" uip-flex uip-gap-xs uip-flex-center">\
                              <input class="uip-user-check" :data-id="user.user_id" type="checkbox" v-model="user.selected">\
                              <div class="uip-flex uip-w-28"><span class="material-icons-outlined uip-cursor-drag uip-user-drag" :data-id="user.user_id" draggable="true">drag_indicator</span></div>\
                            </div>\
                          </td>\
                          <template v-for="column in returnTableData.columns">\
                            <td class="uip-text-left uip-padding-xs uip-border-bottom" v-if="column.active" :class="{\'uip-hidden-small\' : !column.mobile}">\
                              <div v-if="column.name == \'username\'" class="uip-flex uip-flex-center uip-gap-xs">\
                                <img v-if="user.image" class="uip-w-28 uip-h-28 uip-border-circle" :src="user.image">\
                                <span class="uip-text-bold">{{user[column.name]}}</span>\
                              </div>\
                              <div v-else-if="column.name == \'roles\'" class="uip-flex uip-flex-wrap uip-gap-xs uip-row-gap-xxs">\
                                <div v-for="role in user.roles" class="uip-background-primary-wash uip-border-round uip-padding-left-xxs uip-padding-right-xxs uip-text-bold uip-text-s">\
                                  {{role}}\
                                </div>\
                              </div>\
                              <div v-else-if="column.name == \'uip_user_group\'" class="uip-flex uip-flex-wrap uip-gap-xxs uip-row-gap-xxxs">\
                                <template v-for="group in user.uip_user_group">\
                                  <div v-if="returnTableData.groups[group]" class="uip-border-round uip-padding-left-xxs uip-padding-right-xxs uip-text-bold uip-text-s uip-text-inverse" :style="returnGroupColour(group)">\
                                    {{returnGroupTag(group)}}\
                                  </div>\
                                </template>\
                              </div>\
                              <div v-else>{{user[column.name]}}</div>\
                            </td>\
                          </template>\
                          <td class="uip-border-bottom">\
                            <div class="uip-flex">\
                              <dropdown type="icon" icon="more_horiz" pos="bottom-right" buttonsize="small" \
                              :tooltip="true" :tooltiptext="data.translations.userOptions" :width="200">\
                                <div class="uip-padding-xs uip-flex uip-flex-column uip-row-gap-xxs uip-w-200">\
                                  <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="ui.offcanvas.userPanelOpen = true; ui.activeUser = user.user_id">\
                                    <div class="material-icons-outlined">person</div>\
                                    <div>{{data.translations.openProfile}}</div>\
                                  </div>\
                                  <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="sendPasswordReset(user)">\
                                    <div class="material-icons-outlined">lock</div>\
                                    <div>{{data.translations.sendPasswordReset}}</div>\
                                  </div>\
                                  <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="openMessenger(user)">\
                                    <div class="material-icons-outlined">mail</div>\
                                    <div>{{data.translations.sendMessage}}</div>\
                                  </div>\
                                  <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="deleteUser(user.user_id)">\
                                    <div class="material-icons-outlined">delete</div>\
                                    <div>{{data.translations.deleteUser}}</div>\
                                  </div>\
                                </div>\
                              </dropdown>\
                            </div>\
                          </td>\
                      </tr>\
                    </template>\
                </tbody>\
            </table>\
            <div class="uip-padding-top-xs uip-padding-bottom-xs uip-padding-right-xs uip-flex uip-flex-between">\
              <div class="">{{returnTableData.totalFound}} {{data.translations.results}}</div>\
              <div class="uip-flex uip-gap-xs">\
                <button v-if="tablePage > 1" class="uip-button-default" @click="changePage(\'previous\')">{{data.translations.previous}}</button>\
                <button class="uip-button-default" @click="changePage(\'next\')">{{data.translations.next}}</button>\
              </div>\
            </div>\
          </div>\
	    </div>\
      <div class="uip-position-fixed uip-right-0 uip-bottom-0 uip-padding-m uip-z-index-9 uip-text-normal" v-if="totalSelected > 0">\
        <div class="uip-position-relative">\
          <dropdown type="text" icon="more_horiz" :buttontext="totalSelected + \' \' + data.translations.usersSelected"\
          pos="bottom-right" buttonsize="medium" :tooltip="false" :width="200" buttonstyle="primary">\
            <div class="uip-padding-xs uip-w-200 uip-flex uip-flex-column uip-row-gap-xxs">\
              <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="openMessengerBatch()">\
                <div class="material-icons-outlined">mail</div>\
                <div>{{data.translations.sendMessage}}</div>\
              </div>\
              <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="batchRoleUpdate()">\
                <div class="material-icons-outlined">bookmarks</div>\
                <div>{{data.translations.assignRoles}}</div>\
              </div>\
              <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="sendMultiplePasswordReset()">\
                <div class="material-icons-outlined">lock</div>\
                <div>{{data.translations.sendPasswordReset}}</div>\
              </div>\
              <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="deleteMultiple()">\
                <div class="material-icons-outlined">delete</div>\
                <div>{{data.translations.deleteUsers}}</div>\
              </div>\
              <div class="uip-flex uip-gap-xs uip-border-round uip-cursor-pointer hover:uip-background-muted uip-padding-xxs" @click="deSelectAllUsers()">\
                <div class="material-icons-outlined">backspace</div>\
                <div>{{data.translations.clearSelection}}</div>\
              </div>\
            </div>\
          </dropdown>\
        </div>\
      </div>\
      <offcanvas :open="ui.offcanvas.userPanelOpen" :closeOffcanvas="function() {ui.offcanvas.userPanelOpen = false}">\
        <user-panel :groups="returnTableData.groups" :userID="ui.activeUser" :translations="data.translations" :refreshTable="getUserData" :sendmessage="openMessenger" :closePanel="function() {ui.offcanvas.userPanelOpen = false}"></user-panel>\
      </offcanvas>\
      <offcanvas :open="ui.offcanvas.batchRolePanelOpen" :closeOffcanvas="function() {ui.offcanvas.batchRolePanelOpen = false}">\
        <batch-role-update :refreshTable="getUserData" :batchRecipients="getTotalSelectedUsers" :translations="data.translations" :closePanel="function() {ui.offcanvas.batchRolePanelOpen = false}"></batch-role-update>\
      </offcanvas>\
      <offcanvas :open="ui.offcanvas.messagePanelOpen" :closeOffcanvas="function() {ui.offcanvas.messagePanelOpen = false}">\
        <user-message :recipient="ui.messageRecipient" :batchRecipients="getTotalSelectedUsers" :translations="data.translations" :closePanel="function() {ui.offcanvas.messagePanelOpen = false}"></user-message>\
      </offcanvas>',
  };
  return compData;
}
