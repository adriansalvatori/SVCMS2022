export function moduleName() {
  return "system-info";
}

export function moduleData() {
  return {
    props: {
      cardData: Object,
      overviewData: Object,
    },
    data: function () {
      return {
        cardOptions: this.cardData,
        recentPosts: [],
        loading: true,
      };
    },
    mounted: function () {
      this.loading = false;
    },
    computed: {
      getPostsOnce() {
        this.getPosts();
      },
      formattedPosts() {
        this.getPostsOnce;
        return this.recentPosts;
      },
    },
    methods: {
      getPosts() {
        let self = this;
        if (self.overviewData.globalDataObject.data.system_info.posts) {
          self.recentPosts = self.overviewData.globalDataObject.data.system_info.posts;
        }
      },
    },
    template:
      '<div class="uip-padding-s">\
  	  	<loading-placeholder v-if="overviewData.globalDataObject.loading == true"></loading-placeholder>\
  		  <div v-if="overviewData.globalDataObject.loading == false" >\
      			<div class="uip-flex uip-flex-center uip-margin-bottom-xs" v-for="post in formattedPosts">\
      			  <div class="uip-flex-grow">\
      				{{post.name}}\
      			  </div>\
      			  <div class="">\
      				  <div class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold">{{post.version}}</div>\
      			  </div>\
      			</div>\
  		  </div>\
		 </div>',
  };
  return compData;
}

export default function () {
  console.log("Loaded");
}
