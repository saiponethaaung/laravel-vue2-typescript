<template>
    <div id="loginContent">
        <section id="sideMenu">
            Sidebar
        </section>
        <div id="mainContent">
            <section id="headerContent">
                <div class="sidebar headerSidebar">
                    Account Control
                </div>
                <div class="headerConent">
                    Action
                </div>
            </section>
            <section id="innnerContent">
                <div class="sidebar bodySidebar">
                    Body sidebar
                </div>
                <div class="bodyContent">
                    Body Content
                </div>
            </section>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component } from 'vue-property-decorator';

@Component
export default class DefaultLayout extends Vue {

}
</script>
