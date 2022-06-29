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
          action: "",
        },
        tableOptions: {
          direction: "DESC",
          sortBy: "username",
          perPage: 20,
        },
        ui: {
          userPanelOpen: false,
          messagePanelOpen: false,
          activeUser: 0,
          messageRecipient: [],
          newUserOpen: false,
          offcanvas: {
            userPanelOpen: false,
            messagePanelOpen: false,
          },
        },
      };
    },
    mounted: function () {
      this.loading = false;
      this.getActivityData();
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
          this.getActivityData();
        },
        deep: true,
      },
      tableOptions: {
        handler(newValue, oldValue) {
          this.getActivityData();
        },
        deep: true,
      },
    },
    computed: {
      returnTableData() {
        return this.tableData;
      },
    },
    methods: {
      changePage(direction) {
        if (direction == "next") {
          this.tablePage += 1;
        }
        if (direction == "previous") {
          this.tablePage = this.tablePage - 1;
        }
        this.getActivityData();
      },
      updateRoles(roles) {
        this.tableFilters.roles = roles;
      },
      getActivityData() {
        let self = this;

        if (self.queryRunning) {
          return;
        }
        self.queryRunning = true;
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_get_activity_table_data",
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

            if (self.tablePage > data.tableData.totalPages) {
              self.tablePage = data.tableData.totalPages;
            }
          },
        });
      },
      returnActionClass(type) {
        if (type == "primary") {
          return "uip-background-primary-wash";
        }
        if (type == "warning") {
          return "uip-background-orange-wash";
        }
        if (type == "danger") {
          return "uip-background-red-wash";
        }

        return "uip-background-primary-wash";
      },
      openUser(id) {
        this.ui.activeUser = id;
        this.ui.offcanvas.userPanelOpen = true;
      },
      openMessenger(user) {
        this.ui.messageRecipient = user;
        this.ui.offcanvas.messagePanelOpen = true;
      },
    },

    template:
      '<div class="uip-margin-top-m uip-text-normal">\
          <div class="uip-margin-bottom-s uip-flex uip-flex-between">\
            <div class="uip-flex uip-gap-xs uip-flex-wrap uip-row-gap-xs">\
              <div class="uip-background-muted uip-padding-xs uip-border-round hover:uip-background-grey uip-flex uip-flex-center uip-max-w-400">\
                <span class="material-icons-outlined uk-form-icon uip-margin-right-xs">search</span>\
                <input class="uip-blank-input uip-w-100p" :placeholder="data.translations.searchHistory" v-model="tableFilters.search">\
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
              <div class="uip-flex">\
                <select class="" style="background-color: var(--uip-background-muted);border:none;"  v-model="tableFilters.action">\
                  <option value="">{{data.translations.allActions}}</option>\
                  <template v-for="action in returnTableData.actions">\
                    <option :value="action.name">{{action.label}}</option>\
                  </template>\
                </select>\
              </div>\
            </div>\
            <div class="uip-flex uip-gap-xs">\
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
                      <span class="material-icons-outlined uip-margin-right-xxs">format_list_numbered</span>\
                      <span>{{data.translations.perPage}}</span>\
                    </div>\
                    <div>\
                      <input type="number" min="1" max="2000" class="uip-input" v-model="tableOptions.perPage">\
                    </div>\
                  </div>\
                </div>\
              </dropdown>\
            </div>\
          </div>\
	  	    <table class="uip-w-100p uip-border-collapse uip-margin-bottom-s">\
              <thead>\
                  <tr class="uip-border-bottom">\
                      <template v-for="column in returnTableData.columns">\
                        <th class="uip-text-left uip-text-bold uip-text-muted uip-padding-xs" v-if="column.active" :class="{\'uip-hidden-small\' : !column.mobile}">\
                          {{column.label}}\
                        </th>\
                      </template>\
                  </tr>\
              </thead>\
              <tbody>\
                  <template v-for="action in returnTableData.activity">\
                    <tr class="hover:uip-background-muted">\
                        <template v-for="column in returnTableData.columns">\
                          <td class="uip-text-left uip-padding-xs uip-border-bottom" v-if="column.active" :class="{\'uip-hidden-small\' : !column.mobile}">\
                            <div v-if="column.name == \'user\'" class="uip-flex uip-flex-center uip-gap-xs uip-cursor-pointer" @click="openUser(action.user_id)">\
                              <img v-if="action.image" class="uip-w-28 uip-h-28 uip-border-circle" :src="action.image">\
                              <div class="uip-flex uip-flex-column uip-row-gap-xxs">\
                                <div class="uip-text-bold">{{action[column.name]}}</div>\
                                <div class="uip-flex uip-flex-wrap uip-gap-xs uip-row-gap-xxs">\
                                  <div v-for="role in action.roles" class="uip-background-primary-wash uip-border-round uip-padding-left-xxs uip-padding-right-xxs uip-text-bold uip-text-s">\
                                    {{role}}\
                                  </div>\
                                </div>\
                              </div>\
                            </div>\
                            <div v-else-if="column.name == \'action\'" class="uip-flex">\
                              <div :class="returnActionClass(action.type)" class="uip-border-round uip-padding-left-xxs uip-padding-right-xxs uip-text-bold uip-flex uip-text-s">\
                                {{action[column.name]}}\
                              </div>\
                            </div>\
                            <div v-else-if="column.name == \'description\'" class="">\
                              <div v-html="action[column.name]"></div>\
                              <div v-if="action.links" class="uip-flex uip-gap-xs">\
                                <template v-for="link in action.links">\
                                  <a :href="link.url" class="uip-link-muted uip-link-no-underline">{{link.name}}</a>\
                                </template>\
                              </div>\
                            </div>\
                            <div v-else>{{action[column.name]}}</div>\
                          </td>\
                        </template>\
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
          <offcanvas :open="ui.offcanvas.userPanelOpen" :closeOffcanvas="function() {ui.offcanvas.userPanelOpen = false}">\
            <user-panel :groups="returnTableData.groups" :userID="ui.activeUser" :translations="data.translations" :refreshTable="getActivityData" :sendmessage="openMessenger" :closePanel="function() {ui.offcanvas.userPanelOpen = false}"></user-panel>\
          </offcanvas>\
          <offcanvas :open="ui.offcanvas.messagePanelOpen" :closeOffcanvas="function() {ui.offcanvas.messagePanelOpen = false}">\
            <user-message :recipient="ui.messageRecipient" :batchRecipients="[]" :translations="data.translations" :closePanel="function() {ui.offcanvas.messagePanelOpen = false}"></user-message>\
          </offcanvas>\
	    </div>',
  };
  return compData;
}
