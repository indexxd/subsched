<template>
    <div>
        <button-selection 
            :defaultValue="'Vyberte učitele'" 
            :getData="fetchData"
            @selected-event="selected"
        ></button-selection>
    </div>
</template>

<script>
import ButtonSelection from "./ButtonSelection";
import api from "../api";
import { mapGetters, mapActions } from "vuex";

export default {
    data() {
        return {
            selectedValue: "",
        }
    },
    components: {
        ButtonSelection,
    },
    computed: {
        ...mapGetters("lesson", ["getSelectedLesson"]),
        ...mapGetters(["getDate", "getTeachers"]),
    },
    methods: {
        ...mapActions("lesson_options", ["setSelectedTeacher"]),
        selected(value) {
            this.selectedValue = value;
            this.setSelectedTeacher(value);
        },
        async fetchData() {
            const lesson = this.getSelectedLesson;

            const result = await api.get("/teachers/available", {
                date: this.getDate, 
                hour: lesson.hour, 
                subject: lesson.subject.id
            });
            
            return [
                {
                    name: "doupporučený",
                    items: result.recommended.map(item => this.getTeachers[item.id].fullname),
                },
                {
                    name: "dosputní",
                    items: result.available.map(item => this.getTeachers[item.id].fullname),
                },
                {
                    name: "ostatní",
                    items: result.unavailable.map(item => this.getTeachers[item.id].fullname),
                }
            ];
        }
    },
    beforeDestroy() {
        this.setSelectedTeacher(null);
    }
}
</script>

<style>

</style>