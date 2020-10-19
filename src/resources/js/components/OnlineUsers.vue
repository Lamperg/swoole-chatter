<template>
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Online Users: {{ users.length }}</h5>
            <hr />
            <div class="card-text">
                <ul class="list-group list-group-flush">
                    <li
                        class="list-group-item"
                        v-bind:class="[user.username === user ? 'text-primary' : 'text-dark']"
                        v-for="user in users"
                    >
                        {{ user.username }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["users", "user"]
};
</script>
