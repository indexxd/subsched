<template>
    <div class="timetable-container justify">
         <table class="timetable" v-if="!loaded">
            <tr class="timetable__header-row">
                <th class="timetable__header-cell"></th>
                <th class="timetable__header-cell"
                    v-for="hour in 9" 
                    :key="hour"
                    >{{ hour }}
                </th>
            </tr>
            
            <tr class="timetable__row"
                v-for="item in 22"
                :key="item"
                >
                <td class="timetable__cell timetable__cell--grade"></td>
                <td v-for="hour in 9" 
                    :key="hour"
                    class="timetable__cell"
                    >
                </td>
            </tr>
        </table>
        <table :data-date="date" v-if="loaded" class="timetable">
            <tr class="timetable__header-row">
                <th class="timetable__header-cell"></th>
                <th class="timetable__header-cell"
                    v-for="hour in 9" 
                    :key="hour"
                    >{{ hour }}
                </th>
            </tr>
            
            <tr class="timetable__row"
                v-for="(item, grade) in timetable" 
                :key="grade"
                >
                <td class="timetable__cell timetable__cell--grade">{{ grade }}</td>
                <td v-for="hour in 9" 
                    :key="hour"
                    class="timetable__cell"
                    >
                    <lesson v-for="lesson in item[hour]" 
                            :size="item[hour].length" 
                            :key="lesson.id"
                            :lesson="lesson"
                            >cell
                    </lesson>
                </td>
            </tr>
        </table>
    </div>
</template>
    
<style lang="scss">
:root {
    --cell-height: 110px;
    --text-color: gray;
    --border-color: rgb(182, 182, 182);
    --primary-bck: #eaf8e6;
}

.timetable {
    border-collapse: collapse;
    font-size: 16px;
    color: var(--text-color);

    &__header-cell {
        background-color: #333;
        color: white;
        padding: 15px 55px;
        font-weight: 200;
    }

    &__header-row {
        height: 60px;
        border: 1px solid #333;
    }

    &__row {
        height: var(--cell-height);

        &:nth-of-type(odd) {
            background-color: var(--primary-bck);
        }
    } 
    
    &__cell {
        width: 105px;
        border: 1px solid var(--border-color);
    }

    &__cell--grade {
        text-align: center;
        font-weight: 500;
    }
}
</style>

<script>
import api from "../api.js";
import lesson from "../Components/Lesson.vue";
import { mapActions } from "vuex";

export default {
    data() {
        return {
            date: "",
            timetable: {},
            loaded: false,
        }
    },
    created() {
        this.init();

        this.$store.dispatch("loadTeachers");
        this.$store.dispatch("loadRooms");
    },
    watch: {
        $route(to) {
            if (to.path === "/" && to.query.date !== undefined) {
                this.init();
            }
        }
    }
    ,methods: {
        ...mapActions(["setDate", "fetchAbsent"]),
        ...mapActions("lesson", ["fetchEdittedLessons"])
        ,async init() {
            await this.getTimetable();
            await this.parseGrades();
            this.loaded = true;
            await this.fetchEdittedLessons();
            await this.fetchAbsent();
        }
        ,parseGrades() {
            const tt = this.timetable;
            const keys = Object.keys(tt);
            const grades = {};

            keys.forEach(key => {
                const firstHour = Object.keys(tt[key])[0];
                const id = tt[key][firstHour][0].grade.id;
                grades[id] = {};
                grades[id].name = key;
                grades[id].id = id;
            });

            this.$store.dispatch("setGrades", grades);
        }
        ,async getTimetable() {
            const r = await api.get("/timetable?date=" + this.$route.query.date);
            this.timetable = r.timetable;

            if (this.$route.query.date !== r.date) {
                this.$router.push({ path: '', query: { date: r.date } })
            }
            
            this.date = r.date;
            this.setDate(r.date);
        },
    },
    components: {
        lesson
    }
}
</script>