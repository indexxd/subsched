import axios from "axios";

export default {
    baseUrl: window.location.origin + "/api",
    axios: axios,
    
    async get(path, params = {}) {
        const res = await axios.get(this.baseUrl + path, {
            params
        });

        if (res.status === 200) {
            return res.data;
        }

        return null;
    },

    async post(path, data = {}) {
        try {
            const res = await axios.post(this.baseUrl + path, data);
            if (res.status === 200 || res.status === 201) {
                return true;
            }

        }
        catch {
            return false;
        }
        
        return false;
    },

    async patch(path, data = {}) {
        const res = await axios.patch(this.baseUrl + path, data);

        if (res.status === 200 || res.status === 201) {
            return true;
        }

        return false;
    },

    async delete(path) {
        const res = await axios.delete(this.baseUrl + path);

        if (res.status === 200) {
            return true;
        }

        return false;
    }
}