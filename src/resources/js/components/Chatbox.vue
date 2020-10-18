<template>
    <div class="container-lg">
        <div class="row">
            <div class="position-absolute users-wrapper">
                <online-users :users="users"></online-users>
            </div>

            <messages-list :messages="messages"></messages-list>

            <div class="card w-100 mt-3 mb-3 shadow-sm">
                <div class="card-body">
                    <form>
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

    created() {
        this.authenticate();
        this.initWebsocketConnection();
    },

    methods: {
        initWebsocketConnection() {
            let queryParams = new URLSearchParams();
            queryParams.append("username", this.username);
            const API_URL = `ws://swoole-chatter.loc:9000/?${queryParams.toString()}`;

            this.websocket = new WebSocket(API_URL);
            this.websocket.keepalive = true;

            this.websocket.onmessage = (event) => this.parseResponse(JSON.parse(event.data));
            this.websocket.onerror = (error) => this.flash(error.message, "danger");
        },

        authenticate() {
            while (!this.username || !this.authenticated) {
                this.username = prompt("Enter you name", "New user");
                this.username = this.username.trim();
            }

            this.flash(`You are successfully logged in as : ${this.username}`);
        },

        parseResponse(data) {
            switch (data.type) {
                case "login":
                    this.authenticated = true;
                    break;
                case "messages":
                    this.parseMessages(data.body);
                    break;
                case "error":
                    this.flash(data.body.message, "danger");
                    break;
                case "users":
                    this.parseUsersResponse(data.body);
                    break;
            }
        },

        parseMessages(data) {
            if (!data) {
                return false;
            }

            let newMessages = [];
            data.forEach(function (message) {
                newMessages.push(message);
            });
            this.messages = this.messages.concat(newMessages);
        },

        sendMessage() {
            this.websocket.send(
                JSON.stringify({
                    username: this.username,
                    message: this.message
                })
            );
            this.message = "";
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

        flash(message, level = "success") {
            this.flashMessage.show = true;
            this.flashMessage.level = level;
            this.flashMessage.message = message;
            this.hideFlash();
        },

        hideFlash() {
            setTimeout(() => {
                this.flashMessage.show = false;
            }, 2000);
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
</style>
