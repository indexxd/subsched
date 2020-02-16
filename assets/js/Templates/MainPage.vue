<template>
    <div class="main-page">
        <side-panel></side-panel>
        <bulk-edit-modal></bulk-edit-modal>
        <options-modal></options-modal>
        <settings-modal></settings-modal>
        <time-table></time-table>
    </div>
</template>

<script>
import TimeTable from "../Components/TimeTable.vue"
import SettingsModal from "../Components/SettingsModal.vue"
import BulkEditModal from "../Components/BulkEditModal.vue"
import OptionsModal from "../Components/OptionsModal.vue"
import SidePanel from "../Components/SidePanel";

export default {
    components: {
        TimeTable,
        SettingsModal,
        BulkEditModal,
        OptionsModal,
        SidePanel,
    }
}
</script>