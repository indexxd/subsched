export default {
    namespaced: true,
    
    state: {
        selectedTeacher: null,
        selectedRoomSub: null,
        selectedRoomMove: null,
        selectedHour: null,
        selectedDate: null,
    },
    mutations: {
        SET_SELECTED_TEACHER: (state, teacher) => state.selectedTeacher = teacher,
        SET_SELECTED_ROOM_SUB: (state, room) => state.selectedRoomSub = room,
        SET_SELECTED_ROOM_MOVE: (state, room) => state.selectedRoomMove = room,
        SET_SELECTED_HOUR: (state, hour) => state.selectedHour = hour,
        SET_SELECTED_DATE: (state, date) => state.selectedDate = date,
    },
    actions: {
        setSelectedTeacher: (context, teacher) => context.commit("SET_SELECTED_TEACHER", teacher),
        setSelectedRoomSub: (context, room) => context.commit("SET_SELECTED_ROOM_SUB", room),
        setSelectedRoomMove: (context, room) => context.commit("SET_SELECTED_ROOM_MOVE", room),
        setSelectedHour: (context, hour) => context.commit("SET_SELECTED_HOUR", hour),
        setSelectedDate: (context, date) => context.commit("SET_SELECTED_DATE", date),
    },
    getters: {
        getSelectedTeacher: (state) => state.selectedTeacher, 
        getSelectedRoomSub: (state) => state.selectedRoomSub, 
        getSelectedRoomMove: (state) => state.selectedRoomMove, 
        getSelectedHour: (state) => state.selectedHour, 
        getSelectedDate: (state) => state.selectedDate, 
    }
}