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
                    <div class="addIcon" @click="createBot = true">
                        <i class="material-icons">add</i>
                    </div>
                    <div class="btnProject" @click="createBot = true">
                        Create Bot
                    </div>
                </div>
                <div class="createProject">
                    <div class="popFixedContainer popFixedCenter" v-if="createBot">
                        <div class="userAttributePop filterAttribute">
                            <div class="createReply">
                               <div class="createProjectNav">
                                    <span class="createTitle">Create a new project</span>
                                </div>
                                <div class="projectInputName">
                                    <input class="inputName" placeholder="Enter project name" v-model="projectName" />
                                </div>
                                <div class="alignBtn">
                                    <button class="createBtn" @click="createProject()">Create</button>
                                    <button class="createBtn" @click="createBot = false">Cancel</button>
                                </div>
                            </div>
                        </div>
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
import store from '../configuration/store';

@Component
export default class ProjectListComponent extends Vue {
    @Prop({
        type: Boolean,
        default: false
    }) loading!: boolean;

    private createBot: boolean = false;
    private projectName: string = "";
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    async createProject() {
        if(this.projectName=="") {
            alert('Project name is required!');
            return;
        }

        let res = {
            status: true,
            mesg: 'Success'
        };
        
        let data = new FormData();
        data.append('name', this.projectName);


        await Axios({
            url: `/api/v1/project`,
            method: 'post',
            data: data
        }).then(res => {
            this.$store.state.projectList.push(res.data.data);
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.ajaxHandler.globalHandler(err, 'Failed to create a reply!');
            }
        });

        this.createBot = false;
        
    }

}
</script>

