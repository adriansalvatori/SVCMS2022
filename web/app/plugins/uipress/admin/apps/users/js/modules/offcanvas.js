export function moduleData() {
  return {
    props: {
      open: Boolean,
      closeOffcanvas: Function,
    },
    data: function () {
      return {};
    },
    methods: {},
    template:
      '<div v-if="open" class="uip-position-fixed uip-w-100p uip-h-viewport uip-hidden uip-text-normal" \
          style="background:rgba(0,0,0,0.3);z-index:99999;top:0;left:0;right:0;max-height:100vh" \
          :class="{\'uip-nothidden\' : open}">\
            <!-- MODAL GRID -->\
            <div class="uip-flex uip-w-100p uip-overflow-auto">\
              <div class="uip-flex-grow" @click="closeOffcanvas()" ></div>\
              <div class="uip-w-600 uip-background-default uip-padding-m uip-h-viewport uip-overflow-auto uip-border-box" style="max-height: 100vh;">\
                <div class="uip-position-relative">\
                  <!-- OFFCANVAS TITLE -->\
                  <div class="uip-position-absolute uip-right-0 uip-top-0 ">\
                     <span @click="closeOffcanvas"\
                      class="material-icons-outlined uip-background-muted uip-padding-xxs uip-border-round hover:uip-background-grey uip-cursor-pointer">\
                         close\
                      </span>\
                  </div>\
                  <slot></slot>\
                </div>\
              </div>\
            </div>\
          </div>',
  };
}
