<template>
    <transition name="fade">
    <div class="absence-container" @click.self="$emit('close')">
        <div class="absence-table" @click.self="$emit('close')">
            <table class="table">
                <tr class="tr">
                    <th class="th">Jméno</th>
                    <th class="th">Od</th>
                    <th class="th">Do</th>
                    <th class="th"></th>
                </tr>
                <tr class="tr" v-for="absence in absences" :key="absence.id">
                    <td class="td">{{ absence.teacher.firstname }} {{ absence.teacher.lastname }}</td>
                    <td class="td">
                        <date-picker v-model="absence.from" 
                            @confirm="update(absence)" confirm-text="Potvrdit" 
                            confirm 
                            :clearable=false value-type="DD. MM. YYYY">
                            <template #icon-calendar>
                                <span></span>
                            </template>
                            <template #input>
                                <label class="absence-datepicker__label">{{ absence.from }}</label>
                            </template>
                        </date-picker>
                    </td>
                    <td class="td">
                         <date-picker v-model="absence.to" 
                            @confirm="update(absence)" confirm-text="Potvrdit" 
                            confirm 
                            :clearable=false value-type="DD. MM. YYYY">
                            <template #icon-calendar>
                                <span></span>
                            </template>
                            <template #input>
                                <label class="absence-datepicker__label">{{ absence.to }}</label>
                            </template>
                        </date-picker>
                    </td>
                    <td class="td"><img class="absence__icon" @click="remove(absence)" src="/build/images/delete.png" alt=""></td>
                </tr>
            </table>    
        </div>
    </div>
    </transition>
</template>

<script>
import api from "../api"
import moment from "moment"
import DatePicker from "vue2-datepicker"

export default {
    components: {
        DatePicker
    },
    data() {
        return {
            absences: [],
        }
    },
    methods: {
        async fetchAbsences() {
            const r = await api.get("/absence");

            r.forEach(item => {
                item.from = this.format(item.from);
                item.to = this.format(item.to);
            });
        
            this.absences = r;
        },
        format: date => moment(date).format("DD. MM. YYYY"),
        update(absence) {
            const payload = {
                from: moment(absence.from, "DD. MM. YYYY").format("DD-MM-YYYY"),
                to: moment(absence.to, "DD. MM. YYYY").format("DD-MM-YYYY"),
                teacher: absence.teacher.id,
            };

            const r = api.patch("/absence/" + absence.id, payload);

            if (!r) {
                alert("Chyba ryba");
            }
        },
        async remove(absence) {
            if (!confirm("Opravdu?")) return;
            
            const r = await api.delete("/absence/" + absence.id);

            if (!r) {
                alert("Bro ://");
                return;
            }
            
            this.$store.dispatch("fetchAbsent");
            this.absences = this.absences.filter(item => item.id !== absence.id);
        },
    },
    created() {
        this.fetchAbsences();
    }
}
</script>

<style lang="scss" scoped>
@import "../../css/base.scss";
@import "../../css/scrollbar-dark.scss";

.absence-container {
    position: fixed;
    width: calc(100vw - (100vw - 100%));
    height: 100vh;
    top: 0;
    right: 0;
    background-color: #0000007a;
    display:flex;
    justify-content: center;
}

.absence-datepicker__label {
    padding: 15px;
    &:hover {
        font-weight: 800;
        cursor: pointer;
    }
}

$fg: #5d5d5d;

.td {
    color: $fg;
    font-size: 18px;
    text-align: center;
}
.th {
    @extend .td;
    background-color: #333;
    color: white;
    padding: 15px 55px;
    font-weight: 400;
}
.tr {
    &:nth-child(even) {
        background-color: #ececec;
    }
}
.table {
    background-color: white;
    width: 1055px;
    border-collapse: collapse;
    box-shadow: 0px 0px 8px 0px black;
    margin: 0px 10px;
}

.absence__icon {
    height: 60px;
    width: auto;
    filter: invert(0.95);
    margin-bottom: -7px;
    padding: 15px;

    &:hover {
        cursor: pointer;
    }
}

.absence-table {
    overflow-y: auto;
}

@include scrollbar-dark(".absence-container")

</style>