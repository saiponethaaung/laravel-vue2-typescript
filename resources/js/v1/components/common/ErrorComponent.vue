<template>
    <popup-component>
        <div class="ecMesg" v-html="mesg"></div>
        <div class="ecBtnCon">
            <button class="ecBtn" type="button" @click="closeError()">Close</button>
        </div>
    </popup-component>
</template>

<script lang="ts">
import { Component, Vue, Prop, Emit } from "vue-property-decorator";

@Component
export default class ErrorComponent extends Vue {
    @Prop({
        type: String
    })
    mesg!: string;

    @Emit("closeError")
    closeError() {}
}
</script>

