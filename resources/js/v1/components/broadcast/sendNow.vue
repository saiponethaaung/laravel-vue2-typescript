<template>
    <div>
        <div class="outerDisplay">
            <div class="btnSub">
                <span>Segment</span>
                <span class="iconSub"><i class="material-icons">expand_more</i></span>
            </div>
            <div class="btnSub">
                <span>is</span>
                <span class="iconSub"><i class="material-icons">expand_more</i></span>
            </div>
            <div class="btnSub">
                <span>Select segment</span>
                <span class="iconSub"><i class="material-icons">expand_more</i></span>
            </div>
            <div class="btnClick">
                <i class="material-icons">add</i>Add More
            </div>
        </div>

        <div>
            <div class="textArea">
                <div class="textBox">
                    
                </div>
            </div>
        </div>

        <div>
            <div>
                <builder-component :value="[]" :section="0" class="fullWidth"></builder-component>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Watch } from 'vue-property-decorator';

@Component
export default class sendNow extends Vue {

}
</script>