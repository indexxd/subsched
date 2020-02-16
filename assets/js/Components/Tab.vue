<template>
    <div class="tab">
       <input :class="'tab__radio tab__radio' + variant"
            type="radio"
            :id="'tab-' + group + index" 
            :name="'tab-group-' + group"
            :checked="checked"
        >
       <label :class="'tab__label tab__label' + variant"
            :for="'tab-' + group + index"
            >{{ title }}
        </label>
       <div :class="'tab__content tab__content' + variant">
           <slot></slot>
       </div> 
   </div>
</template>

<script>
import "../../css/tabs/style1.scss";
import "../../css/tabs/style2.scss";

export default {
    props: {
        title: String,
        index: [String, Number],
        group: [String, Number],
		checked: Boolean,
		variant: {
			default: "xxx",
		},
	}
}
</script>

<style lang="scss">

</style>
