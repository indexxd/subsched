<template>
    <transition name="fade">

	<div class="side-panel__add-absence">
		<auto-suggest
			:suggestions="[{data:filteredTeachers}]"
			:render-suggestion="suggestion => suggestion.item.fullname"
			:get-suggestion-value="suggestion => suggestion.item.fullname"
			:input-props="{id:'autosuggest__input', placeholder:'Vyberte učitele'}"
			@input="filterTeachers"
			@selected="option => selectedTeacher = option.item"
		></auto-suggest>
		<date-picker
			v-model="absenceDate"
			valueType="format"
			:disabled-date="date => date.getDay() === 0 || date.getDay() === 6"
			format="DD. MM. YYYY"
			range
			range-separator=" - "
			>
		</date-picker>
		<button class="btn btn--safe" @click="submitAbsence">
			Přidat absenci
		</button>
	</div>
    </transition>
</template>

<script>
import { mapGetters } from 'vuex'
import { VueAutosuggest as AutoSuggest } from "vue-autosuggest"
import DatePicker from "vue2-datepicker"
import "vue2-datepicker/index.css";
import 'vue2-datepicker/locale/cs';
import api from "../api"


export default {
	components: {
		AutoSuggest, DatePicker
	}
	,data() {
		return {
			selectedTeacher: "",
			absenceDate: "",
            teachers: [],
            filteredTeachers: [],

		}
	}
	,computed: {
        ...mapGetters(["getTeachers"]),
	}
    ,methods: {
		async submitAbsence(value) {
            const from = this.absenceDate[0].split(". ").join("-");
            const to = this.absenceDate[1].split(". ").join("-");
            const teacher = this.selectedTeacher.id;

			if (from === null || to === null || teacher == "" || teacher === undefined) {
                alert("Zadejte všechno");
                return;
            }
            
            const payload = {
                from, to, teacher
            }

            const r = await api.post("/absence", payload);

            if (!r) {
				alert("Chyba");
				return;
			} 
			
			this.$emit("submit");

		}
		,filterTeachers(input) {
            this.filteredTeachers = this.teachers.filter(item => item.fullname.toLowerCase().includes(input.toLowerCase()));
		}
	}
	,created() {
		this.teachers = Object.keys(this.getTeachers).map(key => this.getTeachers[key]);
	}
}
</script>

<style lang="scss">
@import "../../css/scrollbar-dark.scss";

.side-panel__add-absence {
	box-shadow: 4px 0px 10px 0px black;
}

#autosuggest__input {
outline: none;
position: relative;
display: block;
font-size: 20px;
border: 1px solid #616161;
padding: 10px;
width: 100%;
&::placeholder {
	color: #cfcfcf;
}
}

#autosuggest__input.autosuggest__input-open {
border-bottom-left-radius: 0;
border-bottom-right-radius: 0;
}

.autosuggest__results-container {
position: relative;
width: 100%;
}

.autosuggest__results {
font-weight: 300;
margin: 0;
position: absolute;
z-index: 10000001;
width: 100%;
border: 1px solid #e0e0e0;
border-bottom-left-radius: 4px;
border-bottom-right-radius: 4px;
padding: 0px;
overflow-x: hidden;      
max-height: 200px;
background-color: #333;
color: #e0e0e0;
border-top: none;
}

.autosuggest__results ul {
list-style: none;
padding-left: 0;
margin: 0;
}

.autosuggest__results .autosuggest__results-item {
cursor: pointer;
padding: 8px;
}

#autosuggest ul:nth-child(1) > .autosuggest__results_title {
border-top: none;
}

.autosuggest__results .autosuggest__results_title {
color: gray;
font-size: 11px;
margin-left: 0;
padding: 15px 13px 5px;
border-top: 1px solid lightgray;
}

.autosuggest__results .autosuggest__results_item:active,
.autosuggest__results .autosuggest__results_item:hover,
.autosuggest__results .autosuggest__results_item:focus,
.autosuggest__results .autosuggest__results_item.autosuggest__results_item-highlighted {
background-color: #ddd;
}

@include scrollbar-dark("#autosuggest ");

</style>