import Vue from "vue";
import Vuex from "vuex";
import global from "./modules/global";
import lesson from "./modules/lesson";
import auth from "./modules/auth";
import lesson_options from "./modules/lesson_options";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        lesson, lesson_options, auth
    },
    ...global
});