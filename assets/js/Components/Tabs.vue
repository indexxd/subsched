<template>
    <div class="tabs">
        <slot></slot>
        <div class="tab__filler"></div>
    </div>
</template>

<style>
.tabs {
  position: relative;   
  margin: 25px 0;
  display:flex;
}
</style>