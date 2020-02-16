<template>
    <div class="login">
        <form class="login-form">
            <input type="text" value="admin" v-model="username" disabled class="input input--disabled">
            <input type="password" v-model="password" placeholder="heslo" class="input">
            <button class="btn" @click.prevent="login">Přihlásit</button>  
        </form>
    </div>
</template>

<script>
import api from "../api"
import store from "../Store/store"

export default {
    data() {
        return {
            username: "admin",
            password: "",
        }
    },
    methods: {
        async login() {
            const payload = new FormData();
            payload.set("username", this.username);
            payload.set("password", this.password);

            const url = api.baseUrl + "/login";

            const r = await api.axios({
                method: 'post',
                url: url,
                data: payload,
                headers: { 'Content-Type': 'multipart/form-data' }                
            });

            const authenticated = r.data.authenticated;

            if (!authenticated) {
                alert("Špatné heslo");
                return;
            }

            this.$router.push("/");
        }
    },
}
</script>

<style lang="scss" scoped>
.login {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;    
}
.login-form {
    display: flex;
    flex-direction: column;
    box-shadow: 0px 0px 18px 4px #ccc;
    padding: 20px;
    padding-bottom: 0px;
    width: 300px;

    > * {
        margin-bottom: 15px;
    }
}
</style>