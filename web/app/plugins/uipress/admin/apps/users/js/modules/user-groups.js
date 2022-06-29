jQuery(document).on("dragstart", ".uip-user-drag", function (ev) {
  let allIDS = [];
  let thefiles = 1 + " " + uipTranslations.user;

  if (jQuery(".uip-user-check:checkbox:checked").length > 0) {
    jQuery(".uip-user-check:checkbox:checked").each(function (index) {
      let tempid = jQuery(this).attr("data-id");
      allIDS.push(tempid);
    });

    ev.originalEvent.dataTransfer.setData("itemID", JSON.stringify(allIDS));

    thefiles = allIDS.length + " " + uipTranslations.users;
  } else {
    let theid = jQuery(ev.currentTarget).attr("data-id");
    ev.originalEvent.dataTransfer.setData("itemID", JSON.stringify([theid]));
  }

  ev.originalEvent.dataTransfer.dropEffect = "move";
  ev.originalEvent.dataTransfer.effectAllowed = "move";
  ev.originalEvent.dataTransfer.setData("type", "content");

  ///SET DRAG HANDLE

  let elem = document.createElement("div");
  elem.id = "uip-content-drag";
  elem.innerHTML = thefiles;
  elem.style.position = "absolute";
  elem.style.top = "-1000px";
  document.body.appendChild(elem);
  ev.originalEvent.dataTransfer.setDragImage(elem, 0, 0);

  jQuery(".uip-remove-folder").addClass("uip-nothidden");
});

export function moduleData() {
  return {
    emits: ["group-change"],
    props: {
      appdata: Object,
      updateactivegroup: Function,
      currentGroup: [Number, String],
      updateuserdata: Function,
    },
    data() {
      return {
        loading: true,
        screenWidth: window.innerWidth,
        mediaCount: 0,
        noFolderCount: 0,
        folders: [],
        activeGroup: this.currentGroup,
        activeFolderObject: [],
        openFolders: [],
        ui: {
          createNew: {
            open: false,
            name: "",
            color: "#0c5cef",
            icon: "group_work",
          },
          edit: {
            open: false,
            active: this.activeFolderObject,
          },
        },
      };
    },
    watch: {},
    created: function () {
      window.addEventListener("resize", this.getScreenWidth);
    },
    computed: {
      formattedFolders() {
        return this.folders;
      },
    },
    mounted: function () {
      this.getGroups();
    },
    methods: {
      openCreateFolder() {
        this.ui.createNew.open = true;
      },
      openEditFolder() {
        this.ui.edit.open = true;
      },
      deleteFolder() {
        let self = this;

        let data = {
          action: "uip_delete_user_group",
          security: uip_user_app_ajax.security,
          activeFolder: self.activeGroup,
        };
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: data,
          success: function (response) {
            data = JSON.parse(response);
            self.loading = false;

            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error);
            } else {
              ///SOMETHING WENT RIGHT
              self.setActiveGroup("all", {});
              uipNotification(data.message);
              self.getGroups();
            }
          },
        });
      },

      createFolder() {
        let self = this;
        let data = {
          action: "uip_create_user_group",
          security: uip_user_app_ajax.security,
          folderInfo: self.ui.createNew,
          parent: self.activeGroup,
        };
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: data,
          success: function (response) {
            data = JSON.parse(response);
            self.loading = false;
            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error);
            } else {
              ///SOMETHING WENT RIGHT
              uipNotification(data.message);
              self.getGroups();
            }
          },
        });
      },
      addDropClass(evt, folder) {
        evt.preventDefault();
        let target = evt.target;
        this.dragCounter++;
        if (jQuery(target).hasClass("uip-folder-can-drop")) {
          jQuery(target).addClass("uip-background-primary-wash");
        } else {
          jQuery(target).closest(".uip-folder-can-drop").addClass("uip-background-primary-wash");
        }
      },
      removeDropClass(evt, folder) {
        evt.preventDefault();
        let target = evt.target;
        this.dragCounter--;

        if (this.dragCounter != 0) {
          return;
        }
        if (jQuery(target).hasClass("uip-folder-can-drop")) {
          jQuery(target).removeClass("uip-background-primary-wash");
        } else {
          jQuery(target).closest(".uip-folder-can-drop").removeClass("uip-background-primary-wash");
        }
      },
      removeFromFolder(evt) {
        this.dragCounter = 0;
        let itemID = evt.dataTransfer.getData("itemID");
        let dropItemType = evt.dataTransfer.getData("type");

        if (dropItemType == "folder") {
          itemID = [itemID];
        } else {
          itemID = JSON.parse(itemID);
        }
        this.removeTheFolder(itemID, dropItemType);

        jQuery(".uip-folder-can-drop").removeClass("uip-background-primary-wash");
        jQuery(".uip-remove-folder").removeClass("uip-nothidden");
      },
      removeTheFolder(items, itemtype) {
        let self = this;
        data = {
          action: "uip_remove_from_folder",
          security: uip_user_app_ajax.security,
          items: items,
          itemtype: itemtype,
        };
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: data,
          success: function (response) {
            data = JSON.parse(response);
            self.loading = false;
            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error);
            } else {
              ///SOMETHING WENT RIGHT
              uipNotification(data.message);
              self.refreshFolders();
            }
          },
        });
      },
      updateFolder() {
        let self = this;
        let data = {
          action: "uip_update_user_group",
          security: uip_user_app_ajax.security,
          folderInfo: self.ui.edit.active,
        };
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: data,
          success: function (response) {
            data = JSON.parse(response);
            self.loading = false;
            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error);
            } else {
              ///SOMETHING WENT RIGHT
              uipNotification(data.message);
              self.getGroups();
            }
          },
        });
      },
      removeFromFolder(evt) {
        this.dragCounter = 0;
        let itemID = evt.dataTransfer.getData("itemID");
        let dropItemType = evt.dataTransfer.getData("type");

        if (dropItemType == "folder") {
          itemID = [itemID];
        } else {
          itemID = JSON.parse(itemID);
        }
        this.removeTheFolder(itemID, dropItemType);

        jQuery(".uip-folder-can-drop").removeClass("uip-background-primary-wash");
        jQuery(".uip-remove-folder").removeClass("uip-nothidden");
      },
      removeTheFolder(items, itemtype) {
        let self = this;
        let data = {
          action: "uip_remove_from_group",
          security: uip_user_app_ajax.security,
          items: items,
          itemtype: itemtype,
        };
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: data,
          success: function (response) {
            data = JSON.parse(response);
            self.loading = false;
            if (data.error) {
              ///SOMETHING WENT WRONG
              uipNotification(data.error);
            } else {
              ///SOMETHING WENT RIGHT
              uipNotification(data.message);
              self.getGroups();
            }
          },
        });
      },
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
      setActiveGroup(groupID, groupObj) {
        this.activeGroup = groupID;
        this.updateactivegroup(groupID);

        if (groupObj) {
          this.ui.edit.active = groupObj;
        }
      },
      setOpenFolders(folderID) {
        if (this.openFolders.includes(folderID)) {
          index = this.openFolders.indexOf(folderID);
          this.openFolders.splice(index, 1);
        } else {
          this.openFolders.push(folderID);
        }

        if (!this.openFolders) {
          this.openFolders = [];
        }
      },
      getGroups() {
        let self = this;

        let data = {
          action: "uip_get_user_groups",
          security: uip_user_app_ajax.security,
        };
        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: data,
          success: function (response) {
            data = JSON.parse(response);
            self.loading = false;
            if (data.error) {
              ///SOMETHING WENT WRONG
            } else {
              ///SOMETHING WENT RIGHT
              self.folders = data.folders;
              self.mediaCount = data.mediaCount;
              self.noFolderCount = data.noFolderCount;
            }
          },
        });
      },
      getNewIcon(icon) {
        this.ui.createNew.icon = icon;
      },
      getActiveIcon(icon) {
        this.ui.edit.active.icon = icon;
      },
    },
    template:
      '<div class="uip-padding-top-xs">\
         <!-- DEFAULT GROUPS -->\
         <div class="uip-margin-bottom-s">\
           <div @click="setActiveGroup(\'all\')" :class="{\'uip-background-muted uip-text-bold uip-text-emphasis\' : activeGroup == \'all\'}"\
           class="uip-border-round uip-flex uip-padding-xxs uip-text-m hover:uip-background-muted uip-cursor-pointer uip-margin-bottom-xxs">\
             <span class="material-icons-outlined uip-margin-right-xxs">group</span>\
             <span class="uip-flex-grow">{{appdata.translations.allUsers}}</span>\
             <span class="uip-border-round uip-background-primary-wash uip-padding-left-xxs uip-padding-right-xxs">{{mediaCount}}</span>\
           </div>\
           <div @click="setActiveGroup(\'nofolder\')"  :class="{\'uip-background-muted uip-text-bold uip-text-emphasis\' : activeGroup == \'nofolder\'}"\
           class="uip-border-round uip-flex uip-padding-xxs uip-text-m hover:uip-background-muted uip-cursor-pointer ">\
             <span class="material-icons-outlined uip-margin-right-xxs">group_off</span>\
             <span class="uip-flex-grow">{{appdata.translations.noGroup}}</span>\
             <span class="uip-border-round uip-background-primary-wash uip-padding-left-xxs uip-padding-right-xxs">{{noFolderCount}}</span>\
           </div>\
         </div>\
         <!-- USER GROUPS -->\
         <div class="uip-margin-bottom-xs uip-padding-xxs uip-flex">\
             <div class="uip-text-muted">{{appdata.translations.groups}}</div>\
             <div class="uip-flex-grow uip-margin-left-xs uip-flex">\
               <div @click="openCreateFolder()"\
               class="uip-background-muted uip-border-round material-icons-outlined hover:uip-background-grey uip-cursor-pointer">add</div>\
             </div>\
             <div @click="openEditFolder()" v-if="!isNaN(activeGroup)"\
             class="uip-background-muted uip-border-round material-icons-outlined uip-margin-left-xs hover:uip-background-grey uip-cursor-pointer">edit</div>\
             <div @click="deleteFolder()" v-if="!isNaN(activeGroup)"\
             class="uip-background-red-wash uip-border-round material-icons-outlined uip-margin-left-xs hover:uip-background-grey uip-cursor-pointer">delete_outline</div>\
         </div>\
         <p class="uip-padding-xxs uip-text-muted" v-if="formattedFolders.length < 1">{{appdata.translations.noGroupCreated}}</p>\
         <div class="uip-overflow-auto uip-max-h-400">\
             <template v-for="folder in formattedFolders">\
               <group-template :updateuserdata="updateuserdata" :appdata="appdata" :refreshFolders="getGroups" :openFolders="openFolders" :setOpenFolders="setOpenFolders" :folder="folder" \
               :activeFolder="activeGroup" :folderUpdate="setActiveGroup"></group-template>\
             </template>\
         </div>\
         <!-- REMOVE FROM FOLDER -->\
         <div @drop="removeFromFolder($event, folder)" \
         @dragenter="addDropClass($event, folder)"\
         @dragleave="removeDropClass($event, folder)"\
         @dragover.prevent\
         @dragenter.prevent\
         class="uip-background-muted uip-border-round uip-flex uip-padding-xs uip-margin-top-s uip-text-m uip-folder-can-drop uip-remove-folder uip-hidden">\
           {{appdata.translations.removeFromGroup}}\
         </div>\
         <!--NEW GROUP OFFCANVAS -->\
         <offcanvas :open="ui.createNew.open" :closeOffcanvas="function() {ui.createNew.open = false}">\
            <div class="uip-flex uip-margin-bottom-m">\
              <div class="uip-text-xl uip-text-bold uip-flex-grow">{{appdata.translations.newGroup}}</div>\
            </div>\
            <div class="uip-margin-bottom-s">\
              <div class="uip-text-muted uip-margin-bottom-xs">{{appdata.translations.name}}:</div>\
              <input class="uip-w-100p uip-standard-input" type="text" :placeholder="appdata.translations.groupName" style="padding: 5px 8px;"\
              v-model="ui.createNew.name">\
            </div>\
            <div class="uip-margin-bottom-s">\
              <div class="uip-text-muted uip-margin-bottom-xs">{{appdata.translations.color}}:</div>\
              <div class="uip-margin-bottom-xm uip-padding-xxs uip-border uip-border-round uip-w-200" style="padding: 5px 8px;">\
                <div class="uip-flex uip-flex-center">\
                  <span class="uip-margin-right-xs uip-text-muted uip-margin-right-s">\
                      <label class="uip-border-circle uip-h-18 uip-w-18 uip-border uip-display-block" v-bind:style="{\'background-color\' : ui.createNew.color}">\
                        <input\
                        type="color"\
                        v-model="ui.createNew.color" style="visibility: hidden;">\
                      </label>\
                  </span> \
                  <input v-model="ui.createNew.color" type="search" placeholder="#HEX" class="uip-blank-input uip-margin-right-s " style="min-width:0;">\
                  <span class="uip-text-muted">\
                      <span class="material-icons-outlined uip-text-muted">color_lens</span>\
                  </span>\
                </div>\
              </div>\
            </div>\
            <div class="uip-margin-bottom-m">\
              <div class="uip-text-muted uip-margin-bottom-xs">{{appdata.translations.groupIcon}}:</div>\
              <icon-select :currentIcon="ui.createNew.icon" :updateIcon="getNewIcon"></icon-select>\
            </div>\
            <div class="">\
              <button @click="createFolder()" class="uip-button-primary uip-padding-xs" type="button">{{appdata.translations.createGroup}}</button>\
            </div>\
         </offcanvas>\
         <!--END NEW GROUP OFFCANVAS -->\
         <!--EDIT GROUP OFFCANVAS -->\
          <offcanvas :open="ui.edit.open" :closeOffcanvas="function() {ui.edit.open = false}">\
              <div class="uip-flex uip-margin-bottom-m">\
                <div class="uip-text-xl uip-text-bold uip-flex-grow">{{appdata.translations.editGroup}}</div>\
              </div>\
              <div class="uip-margin-bottom-s">\
                <div class="uip-text-muted uip-margin-bottom-xs">{{appdata.translations.name}}:</div>\
                <input class="uip-w-100p uip-standard-input" type="text" :placeholder="appdata.translations.groupName" style="padding: 5px 8px;"\
                v-model="ui.edit.active.title">\
              </div>\
              <div class="uip-margin-bottom-s">\
                <div class="uip-text-muted uip-margin-bottom-xs">{{appdata.translations.color}}:</div>\
                <div class="uip-margin-bottom-xm uip-padding-xxs uip-border uip-border-round uip-w-200" style="padding: 5px 8px;">\
                  <div class="uip-flex uip-flex-center">\
                    <span class="uip-margin-right-xs uip-text-muted uip-margin-right-s">\
                        <label class="uip-border-circle uip-h-18 uip-w-18 uip-border uip-display-block" v-bind:style="{\'background-color\' : ui.edit.active.color}">\
                          <input\
                          type="color"\
                          v-model="ui.edit.active.color" style="visibility: hidden;">\
                        </label>\
                    </span> \
                    <input v-model="ui.edit.active.color" type="search" placeholder="#HEX" class="uip-blank-input uip-margin-right-s " style="min-width:0;">\
                    <span class="uip-text-muted">\
                        <span class="material-icons-outlined uip-text-muted">color_lens</span>\
                    </span>\
                  </div>\
                </div>\
              </div>\
              <div class="uip-margin-bottom-m">\
                <div class="uip-text-muted uip-margin-bottom-xs">{{appdata.translations.groupIcon}}:</div>\
                <icon-select :currentIcon="ui.edit.active.icon" :updateIcon="getActiveIcon"></icon-select>\
              </div>\
              <div class="">\
                <button @click="updateFolder()" class="uip-button-primary uip-padding-xs" type="button">{{appdata.translations.updateGroup}}</button>\
              </div>\
          </offcanvas>\
           <!--END NEW GROUP OFFCANVAS -->\
      </div>',
  };
}
