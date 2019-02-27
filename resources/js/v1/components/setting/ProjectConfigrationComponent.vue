<template>
    <div class="inheritHFW ovAuto">
        <template v-if="$store.state.projectInfo.isOwner">
            <template v-if="$store.state.user.facebook_connected">
                Connect a facebook page
                <template v-if="pages.length>0">
                    <ul class="pageList">
                        <li v-for="(p, index) in pages" :key="index" class="page">
                            <div class="pageRoot">
                                <figure class="pageImage">
                                    <img :src="p.image"/>
                                </figure>
                                <div class="pageInfo">
                                    <p>{{ p.name }}</p>
                                    <template v-if="p.currentProject">
                                        <button class="float-right" @click="disconnectPage(index)">Disconnect</button>
                                    </template>
                                    <template v-else-if="p.connected">
                                        <span class="float-right">Connected</span>
                                    </template>
                                    <template v-else-if="currentPage==-1">
                                        <button class="float-right" @click="connectPage(index)">Connect</button>
                                    </template>
                                </div>
                            </div>
                        </li>
                    </ul>
                </template>
                <template v-else>
                    <template v-if="loadingPages">
                        Loading pages...
                    </template>
                    <template v-else>
                        You don't have any page.
                    </template>
                </template>
            </template>
            <template v-else>
                Connect a facebook account
            </template>
        </template>
        <div>
            setting
        </div>
    </div>    
</template>

<script lang="ts">
import {Component, Vue} from 'vue-property-decorator';
import Axios,{ CancelTokenSource } from 'axios';
import ProjectPage from '../../models/ProjectPage';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component
export default class ProjectConfigrationComponent extends Vue {
    
    private pages: Array<ProjectPage> = [];
    private loadingPages: boolean = false;
    private loadPageToken: CancelTokenSource = Axios.CancelToken.source();
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    async mounted() {
        if(this.$store.state.projectInfo.isOwner) {
            if(this.$store.state.user.facebook_connected) {
                await this.loadPages();
            }
        } else {
            this.$router.push({name: 'project.configuration.persistent-menu'});
        }
    }

    get currentPage() {
        let index: any = -1;
        for(let i in this.pages) {
            if(this.pages[i].currentProject) {
                index = i;
                break;
            }
        }
        return index;
    }
    
    private async loadPages() {
        this.pages = [];
        this.loadingPages = true;

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/pages`,
            method: 'get'
        }).then((res: any) => {
            for(let p of res.data.data) {
                this.pages.push(new ProjectPage(p));
            }
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load pages!');
                alert(mesg);
            }
        });

        this.loadingPages = false;
    }

    private async connectPage(index: number) {
        let data = new FormData();
        data.append('id', this.pages[index].id.toString());
        data.append('access_token', this.pages[index].token);

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/pages/link`,
            data: data,
            method: 'post'
        }).then((res) => {
            this.pages[index].connected = true;
            this.pages[index].currentProject = true;
            this.$store.commit('setProjectInfo', { project: res.data.data});
        }).catch((err) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to connect a page!');
                alert(mesg);
            }
        });
    }

    private async disconnectPage(index: number) {
        let data = new FormData();
        data.append('page_id', this.pages[index].id.toString());
        data.append('_method', 'delete');

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/pages/link`,
            data: data,
            method: 'post'
        }).then((res) => {
            this.pages[index].connected = false;
            this.pages[index].currentProject = false;
            this.$store.commit('setProjectInfo', { project: res.data.data});
        }).catch((err) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to disconnect a page!');
                alert(mesg);
            }
        });
    }

}
</script>
