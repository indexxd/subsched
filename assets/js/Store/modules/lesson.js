import api from "../../api";

export default {
    namespaced: true,
    
    state: {
        selectedLesson: {},
        edittedLessons: {},
    },
    mutations: {
        SET_SELECTED_LESSON: (state, lesson) => state.selectedLesson = lesson,
        SET_EDITTED_LESSONS: (state, lessons) => state.edittedLessons = lessons,
        ADD_EDITTED_LESSON: (state, lesson) => state.edittedLessons.push(lesson),
    },
    actions: {
        setSelectedLesson: (context, lesson) => context.commit("SET_SELECTED_LESSON", lesson),
        addEdittedLesson: (context, lesson) => context.commit("ADD_EDITTED_LESSON", lesson),
        fetchEdittedLessons: async (context) => {
            const lessons = await api.get("/lessons/" + context.rootState.date + "/editted");
            context.commit("SET_EDITTED_LESSONS", lessons.map(item => parseInt(item.lesson_id)));
        },
    },
    getters: {
        getSelectedLesson: (state) => state.selectedLesson, 
        getEdittedLessons: (state) => state.edittedLessons, 
    }
}