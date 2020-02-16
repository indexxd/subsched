<template>
    <modal title="Hromadné zadání" type="bulkedit">
        <div class="bulkedit-content">
            <h3>Třídy</h3>
            <div class="pretty-label-left">
            <pretty-check 
                color="primary" 
                @change="toggleAll($event)" 
                v-model="checkAll"
                >Všechny třídy
            </pretty-check>
            </div>
            <div class="bulkedit-grades">
                <div class="bulkedit-grade" 
                    v-for="grade in grades" 
                    :key="grade.id">
                    <pretty-check 
                        color="success" 
                        :key="grade.id" 
                        :value="grade.name" 
                        v-model="checkedGrades"
                        >{{ grade.name }}
                    </pretty-check>    
                </div>
            </div>
            <h3>Hodiny</h3>
            <pretty-check 
                v-for="hour in 9" 
                :key="hour" 
                name="whaves" 
                :value="hour" 
                color="success-o" 
                v-model="checkedHours"
                >{{ hour }}
            </pretty-check><br>
            <label for="rrkr" class="label-text">Text: </label>
            <input type="text" v-model="inputValue" id="rrkr" class="input">
            <button @click="submit()" class="btn btn--safe">Zadat</button>
        </div>
    </modal>
</template>

<script>
import Modal from "./Modal.vue";
import api from "../api.js";
import PrettyCheck from "pretty-checkbox-vue/check";
import { mapGetters, mapActions } from 'vuex';

export default {
    data() {
        return {
            checkAll: false,
            checkedGrades: [],
            grades: [],
            checkedHours: [],
            inputValue: "",
        }
    }
    ,watch: {
        checkedGrades(value) {
            if (this.checkedGrades.length === Object.keys(this.grades).length) {
                this.checkAll = true;
            }
            else this.checkAll = false;
        }
        ,isModalOpen(value) {
            if (value && this.getModalType === "bulkedit") {
                this.grades = this.getGrades;
            }
        }
    }
    ,computed: {
        ...mapGetters(["getGrades", "getDate", "isModalOpen", "getModalType"])
    }
    ,components: {
        Modal, PrettyCheck
    }
    ,methods: {
        ...mapActions(["closeModal"]),
		...mapActions("lesson", ["fetchEdittedLessons"])
        ,async submit() {
            const date = this.getDate;
            const grades = [];
            Object.keys(this.grades).forEach(id => {
                if (this.checkedGrades.includes(this.grades[id].name)) {
                    grades.push(id);
                }
            });
            const hours = this.checkedHours;
            const value = this.inputValue;

            if (hours.length === 0 || grades.length === 0) {
                alert("Vyberte alespoň jednu hodinu a třídu");
                return;
            }

            const payload = {
                date, grades, hours, value
            }

            const r = await api.post("/reschedule/bulkedit", payload);

            if (!r) {
                alert("jejda");
                return;
            }

            this.fetchEdittedLessons();
            this.checkAll = false;
            this.checkedGrades = [];
            this.checkedHours = [];
            this.inputValue = "";           
            this.closeModal();
        }
        ,toggleAll($event) {
            if ($event) {
                this.checkedGrades = Object.keys(this.grades).map(key => this.grades[key].name);
            }
            
            else {
                this.checkedGrades = [];
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.bulkedit-grades {
    display: flex;
    flex-wrap: wrap;
    > * {
        flex: 1 1 0px;
        max-width: 50px;
        margin: 8px;
    }
}

.input {
    margin-left: 10px;
    margin-right: 5px;
}

.bulkedit-content {
    padding-left: 17px;
    h3 {
        font-weight: 300;
        font-size: 20px;
    }
    > * {
        margin-bottom: 10px;
    }
}

.pretty-label-left {
    .pretty .state label {
        padding-right: 1.5rem;
        text-indent: inherit;
    }
    .pretty .state label:after,
    .pretty .state label:before {
        left: auto;
        right: 0;
    }
}
</style>