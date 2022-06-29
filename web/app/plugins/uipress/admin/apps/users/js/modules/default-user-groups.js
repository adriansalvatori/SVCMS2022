export function moduleData() {
  return {
    props: {
      translations: Object,
      masterPrefs: Object,
      defaults: Object,
      preferences: Object,
      folderUpdate: Function,
      activeFolder: [String, Number],
      mediaCount: Number,
      noFolderCount: Number,
    },
    data: function () {
      return {
        loading: true,
      };
    },
    mounted: function () {},
    methods: {},
    template:
      '<div class="uip-margin-bottom-s">\
      <div @click="folderUpdate(\'all\')" :class="{\'uip-background-muted uip-text-bold uip-text-emphasis\' : activeFolder == \'all\'}"\
      class="uip-border-round uip-flex uip-padding-xxs uip-text-m hover:uip-background-muted uip-cursor-pointer uip-margin-bottom-xxs">\
        <span class="material-icons-outlined uip-margin-right-xxs">folder</span>\
        <span class="uip-flex-grow">{{translations.allContent}}</span>\
        <span class="uip-border-round uip-background-primary-wash uip-padding-left-xxs uip-padding-right-xxs">{{mediaCount}}</span>\
      </div>\
      <div @click="folderUpdate(\'nofolder\')"  :class="{\'uip-background-muted uip-text-bold uip-text-emphasis\' : activeFolder == \'nofolder\'}"\
      class="uip-border-round uip-flex uip-padding-xxs uip-text-m hover:uip-background-muted uip-cursor-pointer ">\
        <span class="material-icons-outlined uip-margin-right-xxs">folder</span>\
        <span class="uip-flex-grow">{{translations.noFolder}}</span>\
        <span class="uip-border-round uip-background-primary-wash uip-padding-left-xxs uip-padding-right-xxs">{{noFolderCount}}</span>\
      </div>\
    </div>',
  };
}
