<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chat
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg flex"
                    style="min-height: 400px; max-height: 400px"
                >
                    <!-- list users -->
                    <div
                        class="w-3/12 bg-gray-200 bg-opacity-25 border-r border-gray-200 overflow-y-auto"
                    >
                        <ul>
                            <li
                                v-for="user in users"
                                :key="user.id"
                                @click="loadMessages(user.id)"
                                class="p-6 text-lg text-gray-600 leading-7 font-semibold border-b border-gray-200 hover:bg-opacity-50 hover:bg-gray-200 cursor-pointer"
                                :class="(userActive && userActive.id == user.id) ? 'bg-gray-200 bg-opacity-50' : ''"
                            >
                                <p>
                                    {{ user.name }}
                                    <span
                                        v-if="user.notification"
                                        class="ml-2 w-2 h-2 bg-blue-500 rounded-full inline-block"
                                    ></span>
                                </p>
                            </li>
                        </ul>
                    </div>

                    <!-- box message -->
                    <div class="w-9/12 flex flex-col justify-between">
                        <!-- message -->
                        <div class="w-full p-6 flex flex-col overflow-y-auto">
                            <div
                                v-for="message in messages"
                                :key="message.id"
                                class="w-full mb-3 message"
                                :class="(message.from == $page.props.auth.user.id) ? 'text-right' : 'text-left'"
                            >
                                <p
                                    class="inline-block p-2 rounded-md"
                                    :class="(message.from == $page.props.auth.user.id) ? 'message-from-me' : 'message-to-me'"
                                    style="max-width: 75%"
                                >
                                    {{ message.content }}
                                </p>
                                <span class="block mt-1 text-xs text-gray-500">{{ formatDate(message.created_at) }}</span>
                            </div>
                        </div>

                        <!-- form -->
                        <div
                            v-if="userActive"
                            class="w-full bg-gray-200 bg-opacity-25 p-6 border-t border-gray-200 flex"
                        >
                            <form class="w-full" v-on:submit.prevent="sendMessage">
                                <div
                                    class="flex rounded-md overflow-hidden border border-gray-300"
                                >
                                    <input
                                        type="text"
                                        class="flex-1 px-4 py-2 text-sm focus:outline-none border-0"
                                        v-model="message"
                                    />
                                    <button
                                        type="submit"
                                        class="bg-indigo-500 text-white px-4 py-2 outline-indigo-500"
                                    >
                                        Enviar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import moment from 'moment';

export default {
    components: {
        AppLayout,
    },
    data() {
        return {
            users: [],
            messages: [],
            userActive: null,
            message: '',
        };
    },
    mounted() {
        // console.log(this.user);

        axios.get(route('users.index')).then((response) => {
            this.users = response.data.users;
        });

        Echo.private(`user.${this.user.id}`).listen('.SendMessage', async (e) => {
            const message = e.message

            if (this.userActive && this.userActive.id == message.from) {
                await this.messages.push(e.message);
                this.scrollToBottom();
            } else {
                const userIndex = this.users.findIndex(user => user.id === message.from);

                if (userIndex >= 0) {
                    // user.notification = true;
                    // Vue.set(user, 'notification', true);
                    this.users[userIndex].notification = true;
                }
            }
        });
    },
    computed : {
        user() {
            return this.$store.state.user;
        },
    },
    methods: {
        scrollToBottom() {
            if (this.messages.length) {
                document.querySelector('.message:last-child').scrollIntoView();
            }
        },

        async loadMessages(userId) {
            if (this.userActive && this.userActive.id == userId) return;

            await axios.get(route('users.show', userId)).then(response => {
                this.userActive = response.data.user;
            });

            await axios.get(route('messages.index', userId)).then(response => {
                this.messages = response.data.messages;
            });

            const userIndex = this.users.findIndex(user => user.id === userId);
            if (userIndex >= 0) this.users[userIndex].notification = false;

            this.scrollToBottom();
        },

        formatDate(value) {
            if (value) return moment(value).format('DD/MM/YYYY HH:mm');

            return value;
        },

        async sendMessage() {
            if (!this.message) return;

            // console.log(this.$page.props.auth.user);
            // console.log(this.user);

            await axios.post(route('messages.store', this.userActive.id), {
                message: this.message
            }).then(response => {
                this.messages.push(response.data.message);
                this.message = '';
            });

            this.scrollToBottom();
        },
    },
};
</script>

<style scoped>
    .message-from-me {
        @apply bg-indigo-300 bg-opacity-25;
    }

    .message-to-me {
        @apply bg-gray-300 bg-opacity-25;
    }
</style>
