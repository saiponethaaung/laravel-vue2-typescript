<template>
    <div class="processWarning">
        <span class="noticIcon">
            <i class="material-icons">warning</i>
        </span>
        <p>{{ mesg }}</p>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from "vue-property-decorator";

@Component
export default class WarningComponent extends Vue {
    @Prop({
        type: String
    })
    mesg!: string;
}
</script>

