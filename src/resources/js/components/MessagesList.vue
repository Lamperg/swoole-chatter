<template>
    <div class="card w-100 shadow-sm">
        <div class="card-body messages">
            <div
                v-if="messages.length"
                class="alert message"
                v-bind:class="[message.username === user ? 'alert-primary' : 'alert-secondary']"
                v-for="message in messages"
            >
                <div class="alert-heading message__username">{{ message.username }}:</div>
                <p class="mb-0">{{ message.text }}</p>
                <hr class="mt-2 mb-2" />
                <div class="mb-0 text-secondary text-right message__date">{{ message.date }}</div>
            </div>

            <div v-else class="h-100 text-secondary d-flex align-items-center justify-content-center">
                There are no any messages yet
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["messages", "user"]
};
</script>

<style type="scss">
.messages {
    overflow-y: auto;
    height: 80vh;

    .message {
        min-width: 300px;
    }

    .message__date {
        font-size: 12px;
    }

    .message__username {
        font-weight: bold;
    }
}
</style>
