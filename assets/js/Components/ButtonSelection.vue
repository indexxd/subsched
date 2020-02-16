<template>
    <div class="selection">
        <button @click="open" class="btn btn--neutral">{{ selectedValue }}</button>
        <div class="selection-box" v-if="isOpen" @click.self="isOpen = false">
            <tabs>
                <tab v-for="(list, index) in values" 
                    group="2"
                    variant=2
                    :key="list.name" 
                    :title="list.name" 
                    :index="index + 1"
                    :checked="index === 0"
                    >
                    <div @click.self="isOpen = false" class="selection-box__button-container">
                        <button v-for="value in list.items" 
                            :key="value" 
                            class="btn selection-box__btn"
                            @click="selected(value)"
                            >{{ value }}
                        </button>                    
                    </div>
                </tab>
            </tabs>
        </div>
    </div>
</template>

<script>
import Tabs from "./Tabs";
import Tab from "./Tab";
import api from "../api";

export default {
    data() {
        return {
            selectedValue: String,
            isOpen: false,
            values: null,
        }
    },
    props: {
        defaultValue: String,
        getData: Function,
        mayOpen: {
            type: Function,
            default: () => true
        },
        alwaysReload: {
            type: Boolean,
            default: false,
        }
    },
    created() {
        this.selectedValue = this.defaultValue;
    },
    methods: {
        selected(value) {
            this.$emit("selected-event", value)
            this.selectedValue = value;
            this.isOpen = false;
        },
        async open() {
            if (!this.mayOpen()) return false;

            if (this.values === null || this.alwaysReload) {
                const r = await this.getData();
                this.values = r;
            }
            
            this.isOpen = true;
        }
    },
    components: {
        Tabs,
        Tab,
    },
}
</script>



<style lang="scss">
@import "../../css/scrollbar-dark.scss";
.selection-box__button-container {
    height: 300px;
    overflow: auto;
}
.selection-box {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    background: rgba(33, 33, 33, 0.8);
    width: 800px;
    height: 500px;
    padding: 60px;

    & &__btn {
        background-color: #808080;
        border: 1px solid #808080;
        margin: 10px;   

        &:hover {
            background-color: #808080;
            border: 1px solid white;
        }
    }    
}
@include scrollbar-dark(".selection-box");

</style>