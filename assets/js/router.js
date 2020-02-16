import Vue from "vue"
import Router from "vue-router"
import store from "./Store/store"

Vue.use(Router);

import output from "./Templates/Output"
import mainp from "./Templates/MainPage"
import login from "./Templates/Login"

const isAuthenticated = async () => await store.dispatch("auth/isAuthenticated"); 

const denyOnAuthState = async (path, next, state) => {
    state = await isAuthenticated() ? !state : state;
    
    if (!state) {
        // deny
        next(path);
    } 
    else {
        // allow
        next();
    }
}

const denyAuthenticated = (to, from, next) => denyOnAuthState("/", next, true);
const denyAnonymous = (to, from, next) => denyOnAuthState("/login", next, false);


const routes = [
    { name: "login", path: "/login", component: login, 
        beforeEnter: denyAuthenticated
    },
    { name: "default", path: "/", component: mainp,
        beforeEnter: denyAnonymous
    },
    { name: "output",  path: "/output", component: output },
];

export default new Router({
    routes,
    mode: "history"
});
