import { createStore } from 'vuex';

const store = createStore({
  state() {
    return {
      // Define your state properties here
      rooms: [],
      user: JSON.parse(localStorage.getItem('user')) || null
    };
  },
  mutations: {
    // Define mutations for updating state
    setRooms(state, rooms) {
      state.rooms = rooms;
    },
    setUser(state, user) {
      state.user = user;
      if (user) {
        localStorage.setItem('user', JSON.stringify(user));
      } else {
        localStorage.removeItem('user');
      }
    }
  },
  actions: {
    // Define actions to commit mutations
    fetchRooms({ commit }) {
      // Fetch data (you can use axios or another method here)
      const rooms = ['Room 1', 'Room 2', 'Room 3']; // Example data
      commit('setRooms', rooms);
    },
  },
  getters: {
    // Define getters if needed
    getRooms(state) {
      return state.rooms;
    },
  },
});
export default store;

