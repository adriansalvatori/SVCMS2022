var uipMediaUploader;
export function moduleData() {
  return {
    props: {
      userID: Number,
      translations: Object,
      refreshTable: Function,
      closePanel: Function,
      groups: Object,
    },
    data: function () {
      return {
        user: {
          editData: {
            username: "",
            first_name: "",
            last_name: "",
            roles: [],
            user_email: "",
            userNotes: "",
            passWord: "",
            uip_profile_image: "",
            uip_user_group: [],
          },
        },
      };
    },
    mounted: function () {},
    computed: {},
    methods: {
      updateUser() {
        let self = this;
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_add_new_user",
            security: uip_user_app_ajax.security,
            user: self.user.editData,
          },
          success: function (response) {
            let data = JSON.parse(response);

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error, { pos: "bottom-left", status: "danger" });
              return;
            }
            uipNotification(data.message, { pos: "bottom-left", status: "danger" });
            self.closePanel();
            self.refreshTable();
          },
        });
      },
      returnRoles(roles) {
        this.user.editData.roles = roles;
      },
      returnGroups(groups) {
        this.user.editData.uip_user_group = groups;
      },
      chooseImage() {
        self = this;
        uipMediaUploader = wp.media.frames.file_frame = wp.media({
          title: self.translations.chooseImage,
          button: {
            text: self.translations.chooseImage,
          },
          multiple: false,
        });
        uipMediaUploader.on("select", function () {
          var attachment = uipMediaUploader.state().get("selection").first().toJSON();
          self.user.editData.uip_profile_image = attachment.url;
        });
        uipMediaUploader.open();
      },
    },
    template:
      '<div class="" >\
        <!-- EDITING USER -->\
        <div class="" >\
          <div class="uip-text-bold uip-text-xl uip-margin-bottom-m">{{translations.newUser}}</div>\
          <div class="uip-flex uip-flex-column uip-row-gap-s">\
            <div class="uip-w-50p">\
              <div class="uip-margin-bottom-xs">{{translations.profileImage}}</div>\
              <div v-if="!user.editData.uip_profile_image" class="uip-flex uip-flex-center uip-flex-middle uip-background-default uip-border uip-padding-s uip-border-circle uip-w-50 uip-h-50 uip-margin-bottom-xs uip-cursor-pointer" @click="chooseImage()">\
                <span class="uip-text-muted uip-text-center">{{translations.chooseImage}}</span>\
              </div>\
              <img v-if="user.editData.uip_profile_image" class="uip-h-50 uip-max-h-50 uip-w-50 uip-border-circle uip-border uip-margin-bottom-xs uip-cursor-pointer" :src="user.editData.uip_profile_image"  @click="chooseImage()">\
              <div class="uip-flex">\
                <input class="uip-flex-grow uip-margin-right-xs uip-standard-input" type="text" placeholder="URL..." v-model="user.editData.uip_profile_image">\
                <span class="uip-background-muted material-icons-outlined uip-padding-xxs uip-border-round hover:uip-background-grey uip-cursor-pointer uip-text-normal"\
                @click="user.editData.uip_profile_image = \'\'">delete</span>\
              </div>\
            </div>\
            <div>\
              <div class="uip-margin-bottom-xs">{{translations.username}}</div>\
              <input type="text" class="uip-w-100p" v-model="user.editData.username">\
            </div>\
            <div>\
              <div class="uip-margin-bottom-xs">{{translations.firstName}}</div>\
              <input type="text" class="uip-w-100p" v-model="user.editData.first_name">\
            </div>\
            <div>\
              <div class="uip-margin-bottom-xs">{{translations.lastName}}</div>\
              <input type="text" class="uip-w-100p"  v-model="user.editData.last_name">\
            </div>\
            <div>\
              <div class="uip-margin-bottom-xs">{{translations.email}}</div>\
              <input type="text" class="uip-w-100p"  v-model="user.editData.user_email">\
            </div>\
            <div>\
              <div class="uip-margin-bottom-xs">{{translations.password}}</div>\
              <input type="password" class="uip-w-100p"  v-model="user.editData.password">\
            </div>\
            <div>\
              <div class="uip-margin-bottom-xs">{{translations.roles}}</div>\
              <role-select :selected="user.editData.roles"\
              :name="translations.assignRoles"\
              :translations="translations"\
              :single=\'false\'\
              :placeholder="translations.searchRoles"\
              :updateRoles="returnRoles"></role-select>\
            </div>\
            <div>\
              <div class="uip-margin-bottom-xs">{{translations.groups}}</div>\
              <group-select :groups="groups" :selected="user.editData.uip_user_group"\
              :name="translations.assignGroups"\
              :single=\'false\'\
              :placeholder="translations.searchGroups"\
              :updategroups="returnGroups"></group-select>\
            </div>\
            <div>\
              <div class="uip-margin-bottom-xs">{{translations.userNotes}}</div>\
              <textarea type="text" class="uip-w-100p" rows="10" v-model="user.editData.notes"></textarea>\
            </div>\
            <div class="uip-flex uip-flex-between uip-margin-top-m">\
              <button class="uip-button-default" @click="closePanel()">{{translations.cancel}}</button>\
              <button class="uip-button-primary" @click="updateUser()">{{translations.saveUser}}</button>\
            </div>\
          </div>\
        </div>\
      </div>',
  };
}
