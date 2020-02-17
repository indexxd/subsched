<template>
    <div class="side-panel">
        <div class="side-panel__info">
            <div>{{ today }}</div>
            <div style="font-size: 18px">{{ week }}</div>
            <div style="font-size: 24px">{{ day }}</div>
        </div>
        <div class="side-panel__items">
        <div class="side-panel__item" @click="previousDay()">
            <img class="side-panel__icon" title="Předchozí den" src="/build/images/left.png" alt="Přidat učitele">
        </div>
        <div class="side-panel__item" @click="nextDay()">
            <img class="side-panel__icon" title="Další den" src="/build/images/right.png" alt="Přidat učitele">
        </div>
        <div class="side-panel__item">
            <img class="side-panel__icon" @click="toggleItem('addAbsence')" title="Přidat absenci" src="/build/images/add-user.png">
            <new-absence-popup v-if="items.addAbsence" @submit="absenceAdded()"></new-absence-popup>
        </div>
        <div class="side-panel__item">
            <img class="side-panel__icon" @click="toggleItem('editAbsence')" title="Upravit absence" src="/build/images/user.png" alt="Přidat učitele">
            <absence-edit @close="closeAbsenceEdit" v-if="items.editAbsence"></absence-edit>
        </div>
        <div class="side-panel__item" @click="toggleItem('changeDate')">
            <img class="side-panel__icon" title="Změnit datum" src="/build/images/calendar.png" alt="Přidat učitele">
            <div class="side-panel__datepicker" v-if="items.changeDate">
                <date-picker
                    @change="redirectToDate(date)"
                    v-model="date"
                    valueType="format"
                    :disabled-date="date => date.getDay() === 0 || date.getDay() === 6"
                    format="DD-MM-YYYY"
                    inline
                    >
                </date-picker>
            </div>
        </div>
        <div class="side-panel__item" @click="bulkEdit()" >
            <img class="side-panel__icon" title="Hromadné zadání" src="/build/images/edit.png" alt="Přidat učitele">
        </div>
        <div class="side-panel__item" @click="$router.push({ name: 'output', query: { 'date': getDate }})">
            <img class="side-panel__icon" title="Suplovací rozvrh" src="/build/images/output.png" alt="Přidat učitele">
        </div>
        <div class="side-panel__item" title="Nastavení" @click="openSettings()">
            <img class="side-panel__icon" src="/build/images/settings.png" alt="Přidat učitele">
        </div>
        <div class="side-panel__item" title="Odhlásit se" @click="logout">
            <img class="side-panel__icon" src="/build/images/logout.png" alt="Přidat učitele">
        </div>
        </div>
        <div class="side-panel__info">

        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import DatePicker from "vue2-datepicker";
import api from "../api";
import "vue2-datepicker/index.css";
import 'vue2-datepicker/locale/cs';
import moment from "moment";
import NewAbsencePopup from "./NewAbsencePopup";
import AbsenceEdit from "./AbsenceEdit";

export default {
    components: {
        DatePicker, NewAbsencePopup, AbsenceEdit
    }
    ,data() {
        return {
            daysNamed: ["NE", "PO", "ÚT", "ST", "ČT", "PÁ", "SO"],
            weeksNamed: ["Sudý", "Lichý"],
            items: {
                editAbsence: false,
                addAbsence: false,
                changeDate: false,
            },
            day: "",
            week: "",
            today: "",
            date: "",
        }
    }
    ,computed: {
        ...mapGetters(["getDate"]),
    },
    watch: {
        getDate(value) {
            const date = moment(value, "DD-MM-YYYY");
    
            this.today = date.format("DD. MM.");
            this.week = this.weeksNamed[date.week() % 2 === 0 ? 0 : 1];
            this.day = this.daysNamed[date.weekday()];
        }
    }
    ,methods: {
        ...mapActions(["setDate", "openModal"]),
        absenceAdded() {
            this.$store.dispatch("fetchAbsent");
            this.toggleItem('addAbsence');
        }
        ,bulkEdit() {
            this.openModal("bulkedit")
        }
        ,openSettings() {
            this.openModal("settings");
        }
        ,closeAbsenceEdit() {
            this.items.editAbsence = false;
        }
        ,toggleItem(name) {
            const items = Object.keys(this.items);
            const originalState = this.items[name];

            items.forEach(item => this.items[item] = false);
            
            this.items[name] = !originalState;
        }
        ,previousDay() {
            let currentDay = moment(this.getDate, "DD-MM-YYYY");
            let weekday = currentDay.weekday();
            let prevDay = currentDay.subtract(1, "d");

            if (weekday === 1) {
                prevDay = currentDay.subtract(2, "d");
            }

            this.redirectToDate(prevDay.format("DD-MM-YYYY"));
        }
        ,nextDay() {
            let currentDay = moment(this.getDate, "DD-MM-YYYY");
            let weekday = currentDay.weekday();
            let nextDay = currentDay.add(1, "d");

            if (weekday === 5) {
                nextDay = currentDay.add(2, "d");
            }

            this.redirectToDate(nextDay.format("DD-MM-YYYY"));
        }
        ,redirectToDate(date) {
            this.setDate(date);
            this.$router.push({ path: '', query: { date: date } });
        },
        async logout() {
            await api.post("/logout");
            this.$router.push("login");
        }
    }
}
</script>

<style lang="scss">
@import "../../css/base.scss";

.side-panel {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 55px;
    background: #333333;
    display: grid;
    grid-template-rows: 100px 1fr 100px;
    z-index: 10;
    box-shadow: 0px 0px 3px 0px grey;

    &__datepicker {
        position: relative;
        left: 55px;
        top: -55px;

        .mx-datepicker {
            box-shadow: 0px 0px 10px 0px black;
        }
    }

    &__add-absence {
        position: relative;
        left: 55px;
        top: -55px;
        width: 250px;
        padding: 10px;
        padding-bottom: 0px;
        display: flex;
        background-color: #333;
        flex-direction: column;
        > * {
            margin-bottom: 10px;
        }
     
        #autosuggest__input {
            @extend .input;
            @extend .input--dark;
            width: 100%;
        }
     
        .mx {
            &-datepicker-range {
                width: 230px;
            }
    
            &-icon-calendar, &-icon-clear {
                svg {
                    fill: #e0e0e0;
                }
            }
    
            &-input, .input {
                width: 230px;
            }
            
            &-input {
                @extend .input--dark;
                @extend .input;
            }
        }
    }

    &__icon {
        height: 100%;
        padding: 11px;
        &:hover {
            background-color: #505050;
            cursor: pointer;
        }
    }

    &__info {
        color: white;
        > * {
            margin-left: 3px;
            margin-top: 1px;
        }
    }

    &__items {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    &__item {
        height: 55px;

    }
}

</style>