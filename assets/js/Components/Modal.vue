<template>
    <transition name="fade">
    <div v-if="open" @click.self="closeModal" class="modal-container">
        <div class="modal">
            <div class="modal__header">
                <div style="width:60px"></div>
                <span class="modal__header-title">{{ title }}</span>
                <img @click="closeModal" class="modal__header-btn" src="/build/images/cancel.png" alt="close">
            </div>
            <div class="modal__body">
                <slot></slot>
            </div>
        </div>
    </div>
    </transition>
</template>

<script>
import { mapGetters, mapActions } from "vuex";

export default {
    data() {
        return {
            open: false
        }
    },
    props: {
        title: String,
        type: String,
    },
    computed: mapGetters(["isModalOpen", "getModalType"]),
    watch: {
        isModalOpen(value) {
            if (this.getModalType === this.type) {
                this.open = value;
                this.$emit(value ? "opened" : "closed");
                this.setPageBlur(value);
            }
        }
    },
    methods: {
        ...mapActions(["closeModal"]),
        setPageBlur(value) {
            Array.from(document.querySelectorAll(".main-page > *:not(.modal-container)")).map(el => el.style.filter = value ? "blur(3px)" : "")
        },
    }
}
</script>


<style lang="scss">
.modal-container {
    height: 100vh;
    width:  100vw;
    position: fixed;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100;
}
.modal {
    height: 400px;
    width: 700px;
    background-color: white;
    box-shadow: 0px 0px 13px 0px rgba(0, 0, 0, 0.75);
    color: var(--text-color);

    &__header {
        background-color: #333333;
        height: 50px;
        display: flex;
        justify-content: space-between;
        align-items: center;

        &-btn {
            width: 25px;
            height: 25px;
            margin-right: 10px;
            cursor: pointer;
        }
        
        &-title {
            color: #efefef;
            font-size: 25px;
            font-weight: 200;
        }
    }



    &__body {
        padding: 20px;
        height: calc(100% - 50px);
    }
}
.fade-enter-active {
  animation: fade-in .1s;
}
.fade-leave-active {
  animation: fade-in .1s reverse;
}
@keyframes fade-in {
  0% {
    opacity: 0;
  }
  50% {
    opacity: 0.5;
  }
  100% {
    opacity: 1;
  }
}
</style>