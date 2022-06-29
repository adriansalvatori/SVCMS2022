export function moduleData() {
  return {
    props: {
      appdata: Object,
      folderUpdate: Function,
      activeFolder: [String, Number],
      folder: Object,
      setOpenFolders: Function,
      openFolders: Array,
      refreshFolders: Function,
      updateuserdata: Function,
    },
    data: function () {
      return {
        loading: true,
        dragCounter: 0,
      };
    },
    mounted: function () {},
    methods: {
      isFolderOpen(folderid) {
        if (this.openFolders.includes(folderid)) {
          return true;
        } else {
          return false;
        }
      },
      startFolderDrag(evt, item) {
        evt.dataTransfer.dropEffect = "move";
        evt.dataTransfer.effectAllowed = "move";
        evt.dataTransfer.setData("itemID", item.id);
        evt.dataTransfer.setData("type", "folder");
        jQuery(".uip-remove-folder").addClass("uip-nothidden");
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
      dragEnd(evt, folder) {
        jQuery(".uip-folder-can-drop").removeClass("uip-background-primary-wash");
        jQuery(".uip-remove-folder").removeClass("uip-nothidden");
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
      dropInfolder(evt, folder) {
        this.dragCounter = 0;
        let itemID = evt.dataTransfer.getData("itemID");
        let dropItemType = evt.dataTransfer.getData("type");

        if (dropItemType == "folder") {
          this.moveFolder(itemID, folder.id);
        }
        if (dropItemType == "content") {
          this.moveContentToFolder(itemID, JSON.parse(folder.id));
        }

        jQuery(".uip-folder-can-drop").removeClass("uip-background-primary-wash");
        jQuery(".uip-remove-folder").removeClass("uip-nothidden");
      },
      moveFolder(folderiD, destinationId) {
        let self = this;
        let data = {
          action: "uip_move_user_group",
          security: uip_user_app_ajax.security,
          folderiD: folderiD,
          destinationId: destinationId,
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
      moveContentToFolder(contentID, destinationId) {
        let allIDs = JSON.parse(contentID);
        let self = this;
        let data = {
          action: "uip_move_users_to_group",
          security: uip_user_app_ajax.security,
          contentID: allIDs,
          destinationId: destinationId,
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
              self.updateuserdata();
            }
          },
        });
      },
    },
    template:
      '<div>\
        <div :class="{\'uip-background-muted uip-text-bold uip-text-emphasis\' : activeFolder == folder.id}"\
        class="uip-border-round uip-flex uip-padding-xxs uip-text-m hover:uip-background-muted uip-margin-bottom-xxs uip-folder-can-drop"\
        @dragstart="startFolderDrag($event,folder)"\
        @dragend="dragEnd($event,folder)"\
        @drop="dropInfolder($event, folder)" \
        @dragenter="addDropClass($event, folder)"\
        @dragleave="removeDropClass($event, folder)"\
        @dragover.prevent\
        @dragenter.prevent draggable="true" :folder-id="folder.id">\
          <span class="material-icons-outlined uip-margin-right-xxs" :style="{\'color\': folder.color}">{{folder.icon}}</span>\
          <span class="uip-flex-grow uip-cursor-pointer" @click="folderUpdate(folder.id, folder)" >{{folder.title}}</span>\
          <span class="uip-border-round uip-background-primary-wash uip-padding-left-xxs uip-padding-right-xxs">{{folder.count}}</span>\
          <span class="uip-w-28 uip-text-right">\
            <span v-if="folder.subs && !isFolderOpen(folder.id)"\
            class="material-icons-outlined  uip-cursor-pointer" @click="setOpenFolders(folder.id)">chevron_right</span>\
            <span v-if="folder.subs && isFolderOpen(folder.id)"\
            class="material-icons-outlined  uip-cursor-pointer" @click="setOpenFolders(folder.id)">expand_more</span>\
          </span>\
        </div>\
        <!-- IF SUB -->\
        <div class="uip-margin-left-s" v-if="folder.subs && openFolders.includes(folder.id)">\
          <template v-for="sub in folder.subs">\
            <group-template :updateuserdata="updateuserdata" :appdata="appdata" :refreshFolders="refreshFolders" :openFolders="openFolders" :setOpenFolders="setOpenFolders" :folder="sub"\
            :activeFolder="activeFolder" :folderUpdate="folderUpdate"></group-template>\
          </template>\
        </div>\
      </div>',
  };
}
