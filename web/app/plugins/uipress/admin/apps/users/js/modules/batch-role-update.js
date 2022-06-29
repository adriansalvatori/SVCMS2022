export function moduleData() {
  return {
    props: {
      recipient: Object,
      translations: Object,
      closePanel: Function,
      batchRecipients: Array,
      refreshTable: Function,
    },
    data: function () {
      return {
        allRecipients: this.batchRecipients,
        showAllRecipients: false,
        settings: {
          roles: [],
          replaceExisting: false,
        },
      };
    },
    mounted: function () {},
    computed: {
      rerturnRecipients() {
        return this.allRecipients;
      },
    },
    methods: {
      removeRecipient(index) {
        this.allRecipients.splice(index, 1);
      },
      returnRoles(roles) {
        this.settings.roles = roles;
      },
      updateUsers() {
        let self = this;

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_batch_update_roles",
            security: uip_user_app_ajax.security,
            allRecipients: self.rerturnRecipients,
            settings: self.settings,
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
            self.closePanel();
          },
        });
      },
    },
    template:
      '<div class="">\
        <div class="uip-text-bold uip-text-xl uip-margin-bottom-m">{{translations.updateRoles}}</div>\
        <div class="uip-flex uip-flex-column uip-row-gap-s">\
          <div class="uip-flex uip-flex-column uip-flex-start">\
            <div class="uip-text-muted uip-margin-bottom-xs">{{translations.Users}}</div>\
            <div class="uip-flex uip-flex-wrap uip-gap-xxs uip-row-gap-xxs uip-margin-bottom-xs">\
              <div class="uip-background-muted uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-gap-xxs uip-cursor-pointer uip-flex uip-flex-center uip-gap-xs" @click="showAllRecipients = !showAllRecipients">\
                {{rerturnRecipients.length + \' \' + translations.users}}\
                <span v-if="!showAllRecipients" class="material-icons-outlined uip-cursor-icon">chevron_left</span>\
                <span v-if="showAllRecipients" class="material-icons-outlined uip-cursor-icon" >expand_more</span>\
              </div>\
            </div>\
            <div class="uip-flex uip-flex-wrap uip-gap-xxs uip-row-gap-xxs uip-max-h-280 uip-overflow-auto">\
              <template v-if="showAllRecipients" v-for="(item, index) in rerturnRecipients">\
                <div class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-gap-xxs">\
                  {{item.user_email}}\
                  <span class="material-icons-outlined uip-cursor-icon" @click="removeRecipient(index)">cancel</span>\
                </div>\
              </template>\
            </div>\
          </div>\
          <div class="">\
            <div class="uip-text-muted uip-margin-bottom-xs">{{translations.roles}}</div>\
            <role-select :selected="settings.roles"\
            :name="translations.assignRoles"\
            :translations="translations"\
            :single=\'false\'\
            :placeholder="translations.searchRoles"\
            :updateRoles="returnRoles"></role-select>\
          </div>\
          <div class="uip-flex uip-flex-row uip-gap-s uip-flex-center">\
            <div class="uip-text-muted ">{{translations.replaceExistingRoles}}</div>\
            <input type="checkbox" class="uip-input uip-w-100p" v-model="settings.replaceExisting">\
          </div>\
          <div class="uip-margin-top-s">\
            <button class="uip-button-primary uip-flex uip-gap-xxs uip-flex-center" @click="updateUsers()">\
              <span>{{translations.updateRoles}}</span>\
              <span class="material-icons-outlined">bookmarks</span>\
            </button>\
          </div>\
        </div>\
      </div>',
  };
}
