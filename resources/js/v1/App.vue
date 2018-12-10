<template>
    <div class="">
        <template v-if="$store.state.isLogin">
            <default-layout></default-layout>
        </template>
        <template v-else>
            <login></login>
        </template>
    </div>
</template>


<script lang="ts">
    import Vue from 'vue';
    import { Component, Watch } from 'vue-property-decorator';
    import DefaultLayout from './layouts/DefaultLayout.vue';
    import Login from './non-member/Login.vue';

    @Component({
        components: {
            DefaultLayout,
            Login
        }
    })
    export default class App extends Vue {
        
        @Watch('$route.name')
        validateRouter() {
            console.log("Route", this.$route.name);
        }

        mounted() {
            console.log(this.$store.state.isLogin);
        }
    }
</script>