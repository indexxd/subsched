<template>
    <modal title="Nastavení" type="settings">
        <div class="layout">
            <div class="form-group">
                <label for="old-pass">Aktuální heslo</label>
                <input v-model="oldPass" class="input" type="password" id="old-pass">
                <label for="new-pass">Nové heslo</label>
                <input v-model="newPass" class="input" type="password" id="new-pass">
                <button @click="submit" class="btn btn--safe">Změnit heslo</button>
            </div>
            <button @click="updateDb" class="btn btn--bad">Aktualizovat databázi</button>
        </div>
    </modal>
</template>

<script>
import Modal from "./Modal.vue";
import api from "../api.js";

export default {
    data() {
        return {
            oldPass: "",
            newPass: "",
        }
    },
    components: {
        Modal
    },
    methods: {
        async submit() {
            const r = await api.post("/change-password", {
                oldPassword: this.oldPass,
                newPassword: this.newPass,
            });

            alert(r ? "Heslo bylo úspěšně změneno" : "Nesprávné heslo");
        },
        async updateDb() {
            const r = await api.post("/update", {
                oldPassword: this.oldPass,
                newPassword: this.newPass,
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.layout {
    display:grid;
    grid-template-rows: 1fr;
    grid-template-columns: 1fr 1fr;
    height: 100%;
}
.form-group {
    grid-column: 1;
    display: flex;
    flex-direction: column;
    margin: 15px;
    width: 50%;
    align-items: baseline;

    > * {
        margin-top: 10px;
    }
    .btn {
        margin-top: 15px;
    }
}
.btn--bad {
    align-self: end;
    justify-self: flex-end;
    margin: 30px 20px;
}
</style>