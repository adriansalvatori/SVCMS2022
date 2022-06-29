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
      };
    },
    mounted: function () {
      this.loading = false;

      let searchParams = new URLSearchParams(window.location.search);
      if (searchParams.has("section")) {
        let param = searchParams.get("section");
        this.modData.currentPage = param;
      }
    },
    watch: {
      modData: {
        handler(newValue, oldValue) {
          this.dataChange(newValue);
        },
        deep: true,
      },
    },
    computed: {},
    methods: {
      returnPageTitle() {
        let self = this;
        let pageTitle = "";
        for (var page in self.modData.pages) {
          let opt = self.modData.pages[page];
          if (self.modData.currentPage == opt.name) {
            pageTitle = opt.label;
          }
        }
        return pageTitle;
      },
      changePage(page) {
        let self = this;

        self.modData.currentPage = page;

        var searchParams = new URLSearchParams(window.location.search);
        searchParams.set("section", page);
        var newRelativePathQuery = window.location.pathname + "?" + searchParams.toString();

        history.pushState(null, "", newRelativePathQuery);
      },
    },
    template:
      '<div>\
	  	<div class="uip-flex uip-margin-bottom-m">\
	  		<div class="uip-text-bold uip-text-xxl uip-text-emphasis uip-flex-grow">{{returnPageTitle()}}</div>\
	  	</div>\
	  	<div class="uip-flex uip-gap-xs">\
	  		<template v-for="page in modData.pages">\
	  			<div @click="changePage(page.name)"\
				  class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer uip-padding-xs uip-text-muted uip-text-bold uip-no-wrap" :class="{\'uip-background-dark uip-text-inverse hover:uip-background-secondary\' : page.name == modData.currentPage}">{{page.label}}</div>\
	  		</template>\
	  	</div>\
	  </div>',
  };
  return compData;
}
