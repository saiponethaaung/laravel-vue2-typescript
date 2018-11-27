<template>
    <div class="popupContainer">
        <div :class="myClassName">
            <slot></slot>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';

@Component
export default class PopupComponent extends Vue{
    @Prop({
        default: 1,
        type: Number
    }) type !: number;

    get myClassName() : string{
        let className = '';

        switch(this.type) {
            default: 
                className = 'defPopCon';
                break;
        }

        return className;
    }
}
</script>
