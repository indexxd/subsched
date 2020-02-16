<template>
	<div>
	<div id="ok"></div>
	<div id="background">
		<div id="output-tt-container">
			<p id="output-date">{{ formattedDate }}</p>
			<table v-if="!loaded">
				<tr>
					<th></th>
					<th v-for="hour in 9" :key="'-' + hour">{{ hour }}</th>
				</tr>
				<tr v-for="item in 22" :key="item">
					<td class="row-classname">&nbsp;</td>
					<td v-for="hour in 9" :key="hour">
						<div class="output-cell">&nbsp;</div>
					</td>
				</tr>
			</table>
			<!--  -->
			<table v-if="loaded">
				<tr>
					<th></th>
					<th v-for="hour in 9" :key="hour">{{ hour }}</th>
				</tr>
				<tr v-for="(hours, grade) in timetable" :key="grade">
					<td class="row-classname">{{ grade }}</td>
					<td v-for="(value, hour) in hours" :key="hour">
						<div class="output-cell"
							:data-hour="hour + 1"
							:data-grade="grade"
							:contenteditable="isAuthenticated"
							@focus="setCellDefault"
							@blur="updateCell"
							@keydown="keyDown"
							@keyup="keyUp"
							>{{ value }}</div>
					</td>
				</tr>
			</table>
			<p id="output-absent">
				Nepřítomni:
				{{ teachersSeparated }}
			</p>
		</div>
	</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';
import api from "../api"
import moment from 'moment';

export default {
	data() {
		return {
			keysDown: {13: false, 16: false},
			isAuthenticated: false,
			timetable: {},
			absences: [],
			date: "",
			loaded: false,
			cellDefault: "",
			selectedHour: null,
			selectedGrade: null,
		}
	},
	methods: {
		keyDown(e) {
			if (e.keyCode in this.keysDown) {
				this.keysDown[e.keyCode] = true;
				
				if (this.keysDown[13] && !this.keysDown[16]) {
					document.querySelector(".output-cell:focus").blur();
		        } 
			}
		},
		keyUp(e) {
			if (e.keyCode in this.keysDown) {
				this.keysDown[e.keyCode] = false;
			}
		},
		async updateCell(e) {
			if (this.cellDefault === e.target.innerText) return;
			if (!this.isAuthenticated) return;

			if (confirm("Určitě?")) {
				const payload = {
					date: this.date,
					hour: e.target.dataset.hour,
					grade_name: e.target.dataset.grade,
					value: e.target.innerText,
				}

				const r = await api.post("/reschedule/custom", payload);
				if (!r) {
					alert("Chyba ://");
					e.target.innerText = this.cellDefault;
				}

			}
			else {
				e.target.innerText = this.cellDefault;
			}
		},
		setCellDefault(e) {
			this.cellDefault = e.target.innerText;
		},
		async updateAbsences() {
			await this.$store.dispatch("fetchAbsent");
			this.absences = this.$store.getters.getAbsent;
		},
		async init() {
			const r = await api.get("/output?date=" + this.$route.query.date);

			if (this.$route.query.date !== r.date) {
				this.$router.push({ path: '', query: { date: r.date } })
			}
			this.date = r.date;
			this.$store.dispatch("setDate", r.date);
			this.timetable = r.timetable;
			
			this.isAuthenticated = await this.$store.dispatch("auth/isAuthenticated");
			await this.updateAbsences();
			this.loaded = true;
		}
	},
	computed: {
		formattedDate() {
			return this.date.split("-").join(". ");
		},
		teachersSeparated() {
			let string = "";

			for (let i = 0; i < this.absences.length; i++) {
				string += this.absences[i].teacher.shortname;

				if (i < this.absences.length - 1) {
					string += ", ";
				}
			}

			return string
		}
	},
	beforeRouteEnter(from, to, next) {
		next(vm => vm.init());
	}
}
</script>

<style lang="scss" scoped>
#ok {
	position: fixed;
	background: #525659;
	height: 100%;
	width: 100%;
	z-index: -1;
}

#background {
	width: 100%;
	height: 100%;
	padding-top: 20px;
	padding-bottom: 10px;
}

#output-tt-container {		
	* {
		font-family: initial;	
		box-sizing: border-box;
	}
	
	#output-date {
		margin: 0 auto;
		margin-top: 50px;
		width: 600px;
	}
		
	.output-cell {
		margin: 0 auto;
		max-width: 75px;
		text-align: center;
		white-space: pre-wrap;
	}
	
	th {
		color: black;
		font-weight: bold;
		border: 1px solid black;
		text-align: center;
		padding: 0px 25px;
	}
	
	td {
		border: 1px solid black;
	}

	td, th {

	}
	
	table {
		width: 600px;
		margin:0 auto;
		border-collapse: collapse;
	}
	
	.row-classname {
		font-weight: bold;
		font-size: 24px;
		padding: 4px 10px;
	}
	
	#output-absent {
		width: 600px;
		margin: 0 auto;
		margin-bottom: 50px;
	}
	
	background: white;
	max-width: 800px;
	margin: 0 auto;
	overflow : auto;
	box-shadow: 0px 0px 8px 0px black;
}
</style>