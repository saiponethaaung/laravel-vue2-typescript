<template>
    <!-- <div>
        <div>Project list</div>
        <template v-if="loading">
            Loading...
        </template>
        <template v-else>
            <div v-for="(project, index) in $store.state.projectList" :key="index">
                <router-link :to="{name: 'project.home', params: { projectid: project.id }}">
                    {{ project.name }}
                </router-link>
            </div>
        </template>
    </div> -->
    <div>
        <div class="navList">
            <figure>
                <img src="images/sample/logo.png" class="navIcon"/>
            </figure>
            <div class="navUser">
                <span class="userIcon"></span>
                <span>TESTING USER</span>
            </div>
        </div>
        <div class="bodyList">
            <div class="titleHeight">                
                <div class="titleList">Dashboard</div>
            </div>
            <div class="outerCardList">
                <div class="cardList addBgColor">
                    <div class="addIcon">
                        <i class="material-icons">add</i>
                    </div>
                    <div class="btnProject">
                        Create Bot
                    </div>
                </div>
                <div v-for="(project, index) in $store.state.projectList" :key="index">
                    <div class="cardList">
                        <div class="addIcon"></div>
                        <div class="btnProject">
                            <template v-if="loading">
                                Loading...
                            </template>
                            <template v-else>
                                <router-link class="projectName" :to="{name: 'project.home', params: { projectid: project.id }}">
                                    {{ project.name }}
                                </router-link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Prop, Vue} from 'vue-property-decorator';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../utils/AjaxErrorHandler';

@Component
export default class ProjectListComponent extends Vue {
    @Prop({
        type: Boolean,
        default: false
    }) loading!: boolean;
}
</script>

