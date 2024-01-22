import axios from 'axios';
import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';

const store =  createStore({
    state: {
        user: {}
    },

    mutations: {
        setUserState(state, value) {
            state.user = value;
        },
    },

    actions: {
        userStateAction: ({ commit }) =>{
            axios.get('/api/user/me').then(response => {
                const user = response.data.user;
                commit('setUserState', user);
            });
        },
    },

    plugins: [createPersistedState()],
});

export default store;
