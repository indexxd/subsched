<template>
     <div>
        <button-selection
            defaultValue="Vyberte učebnu"
            :mayOpen="mayOpen"
            :getData="fetchData"
            always-reload
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
    methods: {
        ...mapActions("lesson_options", ["setSelectedRoomMove", "setSelectedDate", "setSelectedHour"]),
        selected(value) {
            this.selectedValue = value;
            this.setSelectedRoomMove(value);
        },
        async fetchData() {
            const result = await api.get("/rooms/available", {
                date: this.getSelectedDate, 
                hour: this.getSelectedHour, 
            });

            return [
                {
                    name: "Volné učebny",
                    items: result.available.map(item => item.name),
                },
                {
                    name: "Obsazené",
                    items: result.unavailable.map(item => item.name),
                },
            ];
        },
        mayOpen() {
            if (this.$store.getters["lesson_options/getSelectedDate"] === null 
                || this.$store.getters["lesson_options/getSelectedHour"] === null) {
                alert("Vyplňte prosím datum a hodinu");
                return false;
            }
            
            return true;
        }
    },
    computed: {
        ...mapGetters(["getRooms", "getDate"]),
        ...mapGetters("lesson", ["getSelectedLesson"]),
        ...mapGetters("lesson_options", ["getSelectedRoomMove", "getSelectedHour", "getSelectedDate"]),
    },
    beforeDestroy() {
        this.setSelectedRoomMove(null);
    },
}
</script>

<style>

</style>