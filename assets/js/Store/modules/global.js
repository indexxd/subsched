import api from "../../api";

export default {
    namespaced: true,
    state: {
        modalOpen: false,
        modalType: "",
        date: "",
        teachers: [],
        rooms: [],
        grades: [],
        absent: [],
    },
    mutations: {
        SET_MODAL_OPEN: (state, value) => state.modalOpen = value,
        SET_MODAL_TYPE: (state, type) => state.modalType = type,
        SET_DATE: (state, date) => state.date = date,
        SET_TEACHERS: (state, teachers) => state.teachers = teachers,
        SET_ROOMS: (state, rooms) => state.rooms = rooms,
        SET_GRADES: (state, grades) => state.grades = grades,
        SET_ABSENT: (state, absent) => state.absent = absent,
    },
    actions: {
        openModal: (context, type) => {
            context.commit("SET_MODAL_OPEN", true);
            context.commit("SET_MODAL_TYPE", type);
        },
        closeModal: (context) => context.commit("SET_MODAL_OPEN", false),
        setDate: (context, date) => context.commit("SET_DATE", date),
        loadTeachers: async (context) => {
            let teachers = await api.get("/teachers");
            let teachersIndexed = {};

            teachers.map(teacher => {
                teachersIndexed[teacher.id] = teacher;
                teachersIndexed[teacher.id].fullname = teacher.firstname + " " + teacher.lastname;
            });

            context.commit("SET_TEACHERS", teachersIndexed);
        },
        loadRooms: async (context) => {
            let rooms = await api.get("/rooms");
            let roomsIndexed = {};

            rooms.map(room => {
                roomsIndexed[room.id] = room;
            });

            context.commit("SET_ROOMS", roomsIndexed);
        },
        fetchAbsent: async (context) => {
            const date = context.state.date;
            const r = await api.get("/absence/" + date);

            const included = [];

            const a = r.filter(item => {
                if (included.includes(item.teacher.id)) {
                    return false;
                }
                else {
                    included.push(item.teacher.id);
                    return true
                }
            });
            
            context.commit("SET_ABSENT", a);
        },
        setGrades: (context, grades) => context.commit("SET_GRADES", grades),
    },
    getters: {
        isModalOpen: (state) => state.modalOpen,
        getModalType: (state) => state.modalType,
        getDate: (state) => state.date,
        getTeachers: (state) => state.teachers,
        getRooms: (state) => state.rooms,
        getGrades: (state) => state.grades,
        getAbsent: state => state.absent,
    }
}