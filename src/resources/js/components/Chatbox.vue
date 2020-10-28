<template>
    <div class="app__content">
        <div id="header" class="container-fluid bg-dark mb-3">
            <h3 class="text-white">Swoole Chatter</h3>

            <div v-if="username" class="text-white float-right">{{ username }}</div>
        </div>
        <div class="container-lg">
            <div class="row">
                <div class="position-absolute users-wrapper">
                    <online-users :users="users" :user="username"></online-users>
                </div>

                <messages-list :messages="messages" :user="username"></messages-list>

                <div class="card w-100 mt-3 mb-3 shadow-sm">
                    <div class="card-body">
                        <form @submit.prevent>
                            <div class="form-group">
                                <textarea
                                    id="textbox"
                                    class="form-control rounded-0 w-100"
                                    v-model="message"
                                    @keydown.enter.prevent="sendMessage()"
                                ></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" @click="sendMessage()">Send message</button>
                        </form>
                    </div>
                </div>

                <div class="alert alert-flash" :class="`alert-${flashMessage.level}`" role="alert" v-show="flashMessage.show">
                    {{ flashMessage.message }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            users: [],
            messages: [],
            message: "",
            username: null,
            websocket: null,
            authenticated: false,
            flashMessage: {
                show: false,
                message: "",
                level: "success"
            }
        };
    },

    mounted() {
        this.authenticate();
        this.initWebsocketConnection();
    },

    methods: {
        initWebsocketConnection() {
            if (this.websocket) {
                this.websocket.close();
            }

            let queryParams = new URLSearchParams();
            queryParams.append("username", this.username);
            const API_URL = `ws://${window.location.hostname}:9000/?${queryParams.toString()}`;

            this.websocket = new WebSocket(API_URL);
            this.websocket.keepalive = true;

            this.websocket.onmessage = (event) => this.parseResponse(JSON.parse(event.data));
            this.websocket.onerror = (error) => this.flash(error.message, "danger");
            this.websocket.onopen = (event) => console.log("Connection has been opened!");
        },

        authenticate() {
            this.username = "";
            this.authenticated = false;

            while (!this.username) {
                this.username = prompt("Enter you name", "New user");
                if (this.username) {
                    this.username = this.username.trim();
                } else {
                    alert("invalid username");
                }
            }
        },

        parseResponse(data) {
            switch (data.type) {
                case "error":
                    this.parseErrorResponse(data.body);
                    break;
                case "users":
                    this.parseUsersResponse(data.body);
                    break;
                case "messages":
                    this.parseMessagesResponse(data.body);
                    break;
                case "login":
                    this.authenticated = true;
                    this.flash(`You are successfully logged in as : ${this.username}`);
                    break;
            }
        },

        parseMessagesResponse(data) {
            if (!data) {
                return false;
            }

            let newMessages = [];
            data.forEach(function (message) {
                newMessages.push(message);
            });
            this.messages = this.messages.concat(newMessages);
            this.scrollMessagesToEnd();
        },

        parseUsersResponse(data) {
            if (!data) {
                return false;
            }
            let onlineUsers = [];
            data.forEach(function (user) {
                onlineUsers.push(user);
            });

            this.users = onlineUsers;
        },

        parseErrorResponse(data) {
            if (!data.message || !data.error_type) {
                return false;
            }

            this.flash(data.message, "danger");

            if (data.error_type === "login_error") {
                alert(data.message);
                this.authenticate();
                this.initWebsocketConnection();
            }
        },

        sendMessage() {
            this.websocket.send(
                JSON.stringify({
                    username: this.username,
                    message: this.message
                })
            );
            this.message = "";
            this.scrollMessagesToEnd();
        },

        flash(message, level = "success") {
            this.flashMessage.show = true;
            this.flashMessage.level = level;
            this.flashMessage.message = message;
            this.hideFlash();
        },

        hideFlash() {
            setTimeout(() => {
                this.flashMessage.show = false;
            }, 3000);
        },

        scrollMessagesToEnd: function () {
            // next tick to process an updated DOM
            this.$nextTick(() => {
                const container = this.$el.querySelector(".messages");
                container.scrollTop = container.scrollHeight;
            });
        }
    }
};
</script>

<style type="text/css">
.alert-flash {
    position: fixed;
    right: 25px;
    bottom: 25px;
}

.users-wrapper {
    right: 0;
}
</style>
