import Vue from "vue";

Vue.component("chatbox", require("./components/Chatbox.vue").default);
Vue.component("online-users", require("./components/OnlineUsers.vue").default);
Vue.component("messages-list", require("./components/MessagesList.vue").default);

const app = new Vue({ el: "#app" });
