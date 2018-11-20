<template>
    <div class="">
        <template v-if="$store.state.isLogin">
            <default-layout>
                <router-view></router-view>
            </default-layout>
        </template>
        <template v-else>
            No welcome
            <router-view></router-view>
        </template>
    </div>
</template>


<script lang="ts">
    import Vue from 'vue';
    import { Component, Watch } from 'vue-property-decorator';
    import DefaultLayout from './layouts/DefaultLayout.vue';

    @Component({
        components: {
            DefaultLayout
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
