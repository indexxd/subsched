import api from "../../api"

export default {
    namespaced: true,
    actions: {
        isAuthenticated: async () => (await api.get("/auth")).authenticated
    }
}