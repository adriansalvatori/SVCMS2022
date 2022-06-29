export function moduleName() {
  return "todo-list";
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
        toDoList: [],
        newItem: {
          description: "",
        },
      };
    },
    mounted: function () {
      this.getQuote();
    },
    watch: {
      overviewData: {
        handler(newValue, oldValue) {
          this.getQuote();
        },
        deep: true,
      },
      toDoList: {
        handler(newValue, oldValue) {
          this.cardOptions.toDoList = this.toDoList;
        },
        deep: true,
      },
      toDoList: function (newValue, oldValue) {
        if (newValue.toDoList != oldValue.toDoList) {
          this.$emit("card-change", newValue);
        }
      },
    },
    computed: {
      toDoListFormatted() {
        let allitems = this.toDoList;
        let tempItems = [];

        for (var i = 0; i < allitems.length; i++) {
          if (allitems[i].status == true || allitems[i].status == "true") {
            allitems[i].status = true;
          } else {
            allitems[i].status = false;
          }
          tempItems.push(allitems[i]);
        }

        if (tempItems.length > 0) {
          this.toDoList = tempItems;
          return this.toDoList;
        } else {
          return [];
        }
      },
    },
    methods: {
      returnList(toDoList) {
        this.cardOptions.toDoList = toDoList;
        this.$emit("card-change", this.cardOptions);
      },
      addToDo() {
        if (this.newItem.description != "") {
          this.toDoList.unshift({
            description: this.newItem.description,
            status: false,
            date: moment().format("MMMM Do YYYY, h:mm a"),
          });
          this.newItem.description = "";
          this.returnList(this.toDoList);
          this.$root.saveDash(false);
        }
      },
      removeToDo(index) {
        this.toDoList.splice(index, 1);
        this.returnList(this.toDoList);
        this.$root.saveDash(false);
      },
      toggleToDoStatus(index) {
        if (this.toDoList[index].status == true || this.toDoList[index].status == "true") {
          this.toDoList[index].status = false;
        } else {
          this.toDoList[index].status = true;
        }
        this.returnList(this.toDoList);
        this.$root.saveDash(false);
      },
      getQuote() {
        let self = this;

        //CHECK IF WE ARE STILL LOADING

        if (this.cardOptions.toDoList && Array.isArray(this.cardOptions.toDoList)) {
          self.toDoList = this.cardOptions.toDoList;
        }
      },
    },
    template:
      '<div v-if="!overviewData.ui.editingMode" class="uip-padding-s">\
        <div class="uip-margin-bottom-m uip-flex uip-flex-row uip-gap-m">\
          <div class="uip-flex-grow">\
            <textarea rows="1" v-model="newItem.description" \
            class="uip-w-100p" :placeholder="overviewData.translations.newToDo"></textarea>\
          </div>\
          <div><button class="uip-button-default " @click="addToDo()">{{overviewData.translations.add}}</button></div>\
        </div>\
        <div class="uip-overflow-hidden">\
          <TransitionGroup name="uip-remove-list" tag="div">\
            <div v-for="(item, index) in toDoListFormatted" :key="item.description">\
                <div class="uip-flex uip-flex-row uip-margin-bottom-xs uip-gap-s uip-padding-xxs uip-border-round hover:uip-background-muted uip-flex-center">\
                  <div class="uip-w-28 uip-flex-no-shrink">\
                    <input type="checkbox" v-model="item.status">\
                  </div>\
                  <div class="uip-flex uip-flex-column uip-flex-grow uip-flex-start uip-cursor-pointer" @click="toggleToDoStatus(index)">\
                    <div class="uip-text-bold">{{item.description}}</div>\
                    <div class="uip-text-muted uip-flex uip-gap-xxs">\
                      <span class="material-icons-outlined" style="font-size: 1em;">calendar_today</span>\
                      <span>{{item.date}}</span>\
                    </div>\
                  </div>\
                  <div class="">\
                    <div class="uip-background-muted uip-border-round hover:uip-background-grey uip-cursor-pointer material-icons-outlined uip-padding-xxs" @click="removeToDo(index)">\
                      delete\
                    </div>\
                  </div>\
                </div>\
            </div>\
          </TransitionGroup>\
        </div>\
		 </div>',
  };
  return compData;
}
