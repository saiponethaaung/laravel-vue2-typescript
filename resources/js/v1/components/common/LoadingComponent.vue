<template>
    <svg stlyle="width: inherit; height: inherit;max-width: 100%;max-height: 100%;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-eclipse" style="background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%;">
        <path stroke="none" d="M10 50A40 40 0 0 0 90 50A40 41 0 0 1 10 50" fill="#c238eb">
            <animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 50.5;360 50 50.5" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform>
        </path>
    </svg>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';

@Component
export default class LoadingComponent extends Vue {
    
}
</script>
