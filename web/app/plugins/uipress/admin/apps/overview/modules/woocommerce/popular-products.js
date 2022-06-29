export function moduleName() {
  return "popular-products";
}

export function moduleData() {
  return {
    props: {
      cardData: Object,
      overviewData: Object,
    },
    data: function () {
      return {
        recentPosts: [],
        currentPage: 1,
        maxPage: 1,
        totalFound: 0,
        loading: true,
        nonfound: "",
        woocommerce: true,
        colors: ["rgba(12, 92, 239, 1)", "rgb(104, 152, 241)", "rgb(173, 197, 242)"],
        chartData: [],
        cardOptions: this.cardData,
        sub: true,
        analytics: false,
        error: false,
        errorMsg: "",
      };
    },
    mounted: function () {
      this.loading = false;
    },
    computed: {
      getTheDates() {
        return this.overviewData.dateRange;
      },
      getPostsOnce() {
        this.getPosts();
      },
      formattedPosts() {
        this.getPostsOnce;

        if (!this.recentPosts) {
          return [];
        } else {
          return this.recentPosts;
        }
      },
    },
    methods: {
      getPosts() {
        let self = this;
        self.loading = true;

        jQuery.ajax({
          url: uipress_overview_ajax.ajax_url,
          type: "post",
          data: {
            action: "uipress_get_popular_products",
            security: uipress_overview_ajax.security,
            dates: self.getTheDates,
            currentPage: self.currentPage,
          },
          success: function (response) {
            var responseData = JSON.parse(response);

            if (responseData.error) {
              self.loading = false;
              self.woocommerce = false;
              return;
            }

            self.recentPosts = responseData.posts;
            self.loading = false;
            self.nonfound = responseData.nocontent;
            self.totalFound = responseData.totalFound;
            self.maxPage = responseData.maxPages;
          },
        });
      },
    },
    template:
      '<div class="uip-padding-s uip-position-relative" >\
          <div v-if="!woocommerce" class="uip-background-red-wash uip-padding-s uip-border-round">\
              {{overviewData.translations.woocommerce}}\
          </div>\
          <template v-else>\
            <loading-placeholder v-if="loading == true"></loading-placeholder>\
            <template v-else>\
    	  	    <p v-if="totalFound == 0" class="uip-text-muted">{{nonfound}}</p>\
    		      <div v-if="loading == false && formattedPosts.length > 0">\
                <div class="uip-margin-bottom-s uip-flex">\
                    <div class="uip-text-bold uip-flex-grow">{{overviewData.translations.product}}</div>\
                    <div class="uip-text-bold uip-text-right uip-w-80">{{overviewData.translations.sold}}</div>\
                    <div class="uip-text-bold uip-text-right uip-w-80">{{overviewData.translations.value}}</div>\
                </div>\
      			     <div class="uip-flex uip-flex-center uip-margin-bottom-xs" v-for="post in formattedPosts">\
                    <div class="uip-flex uip-flex-grow uip-flex-center">\
                        <img v-if="post.img" :src="post.img" class="uip-margin-right-xs uip-w-28 uip-h-28 uip-border-round">\
                        <span v-if="!post.img" class="material-icons-outlined uip-margin-right-xs">local_offer</span>\
          				      <a :href="post.link" class="uip-link-default uip-no-underline uip-text-bold">{{post.title}}</a>\
                    </div>\
                    <div class="uip-margin-left-xs uip-w-80 uip-text-right uip-text-bold">\
                        {{post.salesCount}}\
                    </div>\
                    <div class="uip-flex uip-flex-right uip-margin-left-xs uip-w-80">\
                        <span class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold uip-flex">{{post.totalValue}}</span>\
                    </div>\
        			    </div>\
    		      </div>\
              <div class="uip-flex uip-margin-top-s" v-if="maxPage > 1">\
                <button @click="currentPage -= 1" :disabled="currentPage == 1"\
                class="uip-button-default material-icons-outlined uip-margin-right-xxs">chevron_left</button>\
                <button @click="currentPage += 1" :disabled="currentPage == maxPage"\
                class="uip-button-default material-icons-outlined">chevron_right</button>\
              </div>\
            </template>\
          </template>\
		 </div>',
  };
  return compData;
}

export default function () {
  console.log("Loaded");
}
