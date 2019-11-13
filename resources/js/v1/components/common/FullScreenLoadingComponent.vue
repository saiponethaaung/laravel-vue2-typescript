<template>
    <div>
        <loading-component></loading-component>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";

@Component
export default class FullScreenLoadingComponent extends Vue {}
</script>
