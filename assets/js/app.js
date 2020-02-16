// any CSS you import will output into a single css file (app.css in this case)
import '../css/base.scss';

import Vue from "vue";

import store from "../js/Store/store.js";
import router from "./router";


const vue = new Vue({
    el: "#app",
    router,
    store,
});