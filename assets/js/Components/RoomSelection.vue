<template>
     <div>
        <button-selection 
            :defaultValue="getSelectedLesson.room.name"
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
    methods: {
        ...mapActions("lesson_options", ["setSelectedRoomSub"]),
        selected(value) {
            this.selectedValue = value;
            this.setSelectedRoomSub(value);
        },
        async fetchData() {
            const result = await api.get("/rooms/available", {
                date: this.getDate, 
                hour: this.getSelectedLesson.hour, 
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
        }
    },
    computed: {
        ...mapGetters(["getRooms", "getDate"]),
        ...mapGetters("lesson", ["getSelectedLesson"]),
    },
    beforeDestroy() {
        this.setSelectedRoomSub(null);
    },
    created() {
        this.setSelectedRoomSub(this.getSelectedLesson.room.name);
    }

}
</script>

<style>

</style>