export function moduleData() {
  return {
    props: {
      recipient: Object,
      translations: Object,
      closePanel: Function,
      batchRecipients: Array,
    },
    data: function () {
      return {
        quillEditor: "",
        allRecipients: this.batchRecipients,
        showAllRecipients: false,
        message: {
          recipient: this.recipient,
          subject: "",
          message: "",
          replyTo: "",
        },
      };
    },
    mounted: function () {
      this.startEditor();
    },
    computed: {
      rerturnRecipients() {
        return this.allRecipients;
      },
    },
    methods: {
      startEditor() {
        let self = this;
        let container = self.$refs.uipeditor;

        self.quillEditor = new Quill(container, {
          theme: "snow",
        });
      },
      removeRecipient(index) {
        this.allRecipients.splice(index, 1);
      },
      sendMessage() {
        let self = this;
        self.message.message = self.quillEditor.root.innerHTML;

        jQuery.ajax({
          url: uip_user_app_ajax.ajax_url,
          type: "post",
          data: {
            action: "uip_send_message",
            security: uip_user_app_ajax.security,
            message: self.message,
            allRecipients: self.rerturnRecipients,
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
          },
        });
      },
    },
    template:
      '<div class="">\
        <div class="uip-text-bold uip-text-xl uip-margin-bottom-m">{{translations.sendMessage}}</div>\
        <div class="uip-flex uip-flex-column uip-row-gap-s">\
          <div class="uip-flex uip-flex-column uip-flex-start">\
            <div class="uip-text-muted uip-margin-bottom-xs">{{translations.recipient}}</div>\
            <div v-if="rerturnRecipients.length == 0" class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold uip-text-">\
              {{recipient.user_email}}\
            </div>\
            <div v-else class="uip-flex uip-flex-wrap uip-gap-xxs uip-row-gap-xxs uip-margin-bottom-xs">\
              <div class="uip-background-muted uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-gap-xxs uip-cursor-pointer uip-flex uip-flex-center uip-gap-xs" @click="showAllRecipients = !showAllRecipients">\
                {{rerturnRecipients.length + \' \' + translations.recipients}}\
                <span v-if="!showAllRecipients" class="material-icons-outlined uip-cursor-icon">chevron_left</span>\
                <span v-if="showAllRecipients" class="material-icons-outlined uip-cursor-icon">expand_more</span>\
              </div>\
            </div>\
            <div v-if="showAllRecipients" class="uip-flex uip-flex-wrap uip-gap-xxs uip-row-gap-xxs uip-max-h-280 uip-overflow-auto">\
              <template v-if="showAllRecipients" v-for="(item, index) in rerturnRecipients">\
                <div class="uip-background-primary-wash uip-border-round uip-padding-xxs uip-text-bold uip-flex uip-gap-xxs">\
                  {{item.user_email}}\
                  <span class="material-icons-outlined uip-cursor-icon" @click="removeRecipient(index)">cancel</span>\
                </div>\
              </template>\
            </div>\
          </div>\
          <div class="">\
            <div class="uip-text-muted uip-margin-bottom-xs">{{translations.replyTo}}</div>\
            <input type="email" class="uip-input uip-w-100p" v-model="message.replyTo">\
          </div>\
          <div class="">\
            <div class="uip-text-muted uip-margin-bottom-xs">{{translations.subject}}</div>\
            <input type="email" class="uip-input uip-w-100p" v-model="message.subject">\
          </div>\
          <div class="">\
            <div class="uip-text-muted uip-margin-bottom-xs">{{translations.message}}</div>\
            <div ref="uipeditor"></div>\
          </div>\
          <div class="uip-margin-top-s">\
            <button class="uip-button-primary uip-flex uip-gap-xxs uip-flex-center" @click="sendMessage()">\
              <span>{{translations.sendMessage}}</span>\
              <span class="material-icons-outlined">send</span>\
            </button>\
          </div>\
        </div>\
      </div>',
  };
}
