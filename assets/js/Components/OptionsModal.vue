<template>
    <modal title="Nahrazení hodiny" type="options" @opened="check = false">
        <div style="padding:20px">
            <tabs>
                <tab title="Suplování" group="1" index=1 variant=1 checked>
                    <div class="inside">
                        <teacher-selection></teacher-selection>
                        <room-selection></room-selection>
                        <button @click="submitSub" class="btn btn--safe">Uložit</button>
                    </div>
                </tab>
                <tab title="Přesunutí" group="1" index=2 variant=1>
                    <div class="options-move__container">
                        <label class="options-move__label">Datum:</label>     
                        <date-picker 
                            v-model="date" 
                            valueType="format"
                            :disabled-date="date => date.getDay() === 0 || date.getDay() === 6"
                            format="DD-MM-YYYY"
                            >
                            <template #input>
                                <input type="text" autocomplete="off" name="date" :value="date" class="options-move__datepicker input">
                            </template>
                        </date-picker>
                        <label class="options-move__label">Hodina:</label>
                        <div class="options-move__radios">
                            <pretty-radio v-for="hour in 9" :key="hour" name="radio" :value="hour" color="success-o" v-model="radio">{{ hour }}</pretty-radio>
                        </div>
						<div class="options-move__bottom">
							<future-room-selection 
								defaultValue="Vyberte učebnu"
							></future-room-selection>
							<button @click="submitMove" class="options-move__submit btn btn--safe">Zadat</button>
						</div>
                    </div>
                </tab>
                <tab title="Zrušení" group="1" index=3 variant=1>
					<div class="options-cancel__container">
						<label for="">Spojit hodiny:</label>
						<pretty-check v-model="check" :disabled="checkDisabled"></pretty-check>
						<br>
						<button @click="submitCancel" class="btn btn--safe">Zadat</button>
					</div>
				</tab>
            </tabs>
        </div>
    </modal>
</template>

<script>
import Modal from "./Modal";
import Tabs from "./Tabs";
import Tab from "./Tab";
import TeacherSelection from "./TeacherSelection";
import ButtonSelection from "./ButtonSelection";
import RoomSelection from "./RoomSelection";
import FutureRoomSelection from "./FutureRoomSelection";
import api from "../api";
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
import 'vue2-datepicker/locale/cs';
import PrettyRadio from "pretty-checkbox-vue/radio";
import PrettyCheck from "pretty-checkbox-vue/check";
import 'pretty-checkbox/src/pretty-checkbox.scss'
import { mapGetters, mapActions } from "vuex";

export default {
    data() {
        return {
            date: null,
			radio: null,
			check: false,
			checkDisabled: true,
        }	
    },
    components: { 
		DatePicker, Modal, Tabs, Tab, ButtonSelection, RoomSelection, PrettyRadio, TeacherSelection, FutureRoomSelection,
		PrettyCheck
    },
    computed: {
		...mapGetters("lesson_options", [
			"getSelectedTeacher", "getSelectedRoomSub", "getSelectedRoomMove", "getSelectedHour", "getSelectedDate"
		]),
        ...mapGetters("lesson", ["getSelectedLesson"]),
		...mapGetters(["getDate", "getRooms", "getTeachers", "isModalOpen"]),
	},
	watch: {
		date(val) {
			this.setSelectedDate(val);
		},
		radio(val) {
			this.setSelectedHour(val);
		},
		isModalOpen(val) {
			if (!val) {
				this.date = null;
				this.radio = null;
			}

			this.checkDisabled = this.getSelectedLesson.group === "celá";
		}
	}
    ,methods: {
        ...mapActions(["closeModal"]),
		...mapActions("lesson_options", ["setSelectedDate", "setSelectedHour"]),
		...mapActions("lesson", ["addEdittedLesson", "fetchEdittedLessons"]),
        async submitSub() {
            const teacher = this.getSelectedTeacher;
            const teachers = this.getTeachers;
            const room = this.getSelectedRoomSub;
            const rooms = this.getRooms;
            const lesson = this.getSelectedLesson;

            if (teacher === null || room === null) {
				alert("Vyberte učitele");
				return false;
			}

			const payload = {
				date: this.getDate,
				hour: lesson.hour,
				grade: lesson.grade.id,
				room: Object.keys(rooms).filter(key => rooms[key].name === room)[0],
				teacher: Object.keys(teachers).filter(key => teachers[key].fullname === teacher)[0],
				lesson: lesson.id,
			};

			const r = await api.post("/reschedule/sub", payload);

			if (!r) {
				alert("Jejda");
				return;
			}

			this.addEdittedLesson(this.getSelectedLesson.id);
			this.closeModal();
			
		},
		async submitMove() {
			const lesson = this.getSelectedLesson;
            const rooms = this.getRooms;
			const room = this.getSelectedRoomMove;

			if (room === null) {
				alert("Vyberte učebnu");
				return false;
			}

			const payload = {
				originalDate: this.getDate,
				date: this.getSelectedDate,
				hour: this.getSelectedHour,
				grade: lesson.grade.id,
				room: Object.keys(rooms).filter(key => rooms[key].name === room)[0],
				lesson: lesson.id,
			}
			
			const r = await api.post("/reschedule/move", payload);

			if (!r) {
				alert("Jejda");
				return;
			}

			this.fetchEdittedLessons();
			this.setSelectedDate(null);
			this.setSelectedHour(null);
			this.closeModal();

		},
		async submitCancel() {
			const lesson = this.getSelectedLesson;

			const payload = {
				date: this.getDate,
				hour: lesson.hour,
				grade: lesson.grade.id,
				lesson: lesson.id,
				merge: this.check,
			}

			const r = await api.post("/reschedule/cancel", payload);

			if (!r) {
				alert("Jejda");
				return;
			}

			this.fetchEdittedLessons();
			this.closeModal();
		}
	},
}
</script>

<style lang="scss">
.options-move {
	&__container {
		display: grid;
		grid-template-areas:
			"label1 datepicker"
			"label2 radios"
			"bottom bottom";
		grid-row-gap: 10px;
		align-items: center;
		justify-items: flex-start;
		grid-template-columns: 142px 1fr;
	}
	&__label {
		grid-area: label2;
	
		&:first-of-type {
			grid-area: label1;
		}
	}
	&__radios {
		grid-area: radios;
	}
	&__datepicker {
		grid-area: datepicker;
	}
	&__bottom {
		grid-area: bottom;
		display: flex;
		> * {
			margin-right: 5px;
		}
	}
}
.options-cancel__container {
	> * {
		margin-right: 7px;
		margin-bottom: 13px;
	}
}
.inside {
    display: flex;
    > * {
        margin-right: 5px;
    }
}
</style>