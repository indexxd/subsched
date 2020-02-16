<template>
    <!-- <div @click="clicked" :ref="lesson.id" class="lesson"> -->
    <div @click="clicked" :ref="lesson.id" class="lesson" :class="['lesson--size-' + size, { 'lesson--absent': absent}]">
        <div class="lesson__symbol" v-if="editted">✓</div>
        <div class="lesson__subject">{{ lesson.subject.short }}</div>
        <div class="lesson__teacher">{{ lesson.teacher.shortname }}</div>
        <div class="lesson__room">{{ lesson.room.name }}</div>
        <div class="lesson__group">{{ lesson.group }}</div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";

export default {
    data() {
        return {
            editted: false,
            absent: false,
        }
    }
    ,props: {
        size: Number,
        lesson: {}
    }
    ,computed: {
        ...mapGetters("lesson", ["getEdittedLessons"]),
        ...mapGetters(["getAbsent"])
    }
    ,watch: {
        getEdittedLessons(array) {
            if (array.includes(this.lesson.id)) {
                this.editted = true;
            }
        },
        getAbsent(array) {
            array.forEach(item => {
                if (item.teacher.id === this.lesson.teacher.id) {
                    this.absent = true;
                }
            })
        }
    }
    ,methods: {
        ...mapActions(["openModal"]),
        ...mapActions("lesson", ["setSelectedLesson"]),
        clicked() {
            this.openModal("options");
            this.setSelectedLesson(this.lesson);
        }
    },
}
</script>

<style lang="scss">

.lesson {
    display: grid;
    justify-items: center;

    &:hover, &:hover &__symbol {
        cursor: pointer;
        color: white;
        background-color:limegreen;
    }

    &--absent {
        background-color: pink;

        &:hover, &:hover &__symbol {
            color: white;
            background-color: red;
        }
    }

    &--size-1 {
        height: 110px;
        grid-template-rows: 1fr 1fr 2fr;
        grid-template-areas:
            "symbol symbol group group"
            "subject subject subject subject "
            "teacher teacher room room"
        ;
    }

    &--size-2 {
        height: calc(110px / 2); 
        grid-template-rows: 1fr 1fr;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        grid-template-areas:
            "symbol symbol subject subject group group"
            "teacher teacher  teacher room room room"
        ;
        padding: 2px;
        align-items: center;
    }

    &--size-3 {
        height: calc(110px / 3);
        grid-template-rows: minmax(0, 1fr) minmax(0, 1fr);;
        grid-template-areas:
            "symbol  .  group"
            "subject teacher room"
        ;
        .lesson__room, .lesson__teacher {
            font-size: 12px;
        }
        .lesson__subject {
            margin-top: -4px;
        }
    }

    &__symbol {
        font-weight: 800;        
    }

    &__group {
        grid-area: group;
        justify-self: right;
        padding: 5px;
        font-size: 12px;
    }

    &__subject {
        grid-area: subject;
    }
    
    &__room {
        grid-area: room;
    }
    
    &__teacher {
        grid-area: teacher;
    }

    &:not(:last-child) {
        border-bottom: 1px solid var(--border-color);
    }
}

</style>