<template>
    <div>
        <div>Project list</div>
        <template v-if="loading">
            Loading...
        </template>
        <template v-else>
            <div v-for="(project, index) in projects" :key="index">
                <router-link :to="{name: 'project.home', params: { projectid: project.id }}">
                    {{ project.name }}
                </router-link>
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Vue} from 'vue-property-decorator';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../utils/AjaxErrorHandler';

@Component
export default class ProjectListComponent extends Vue {
    
    private loading: boolean = true;
    private loadToken: CancelTokenSource = Axios.CancelToken.source();
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler(); 
    private projects: Array<any> = [];

    async mounted() {
        await this.loadProject();
    }

    async loadProject() {
        this.loading = true;
        this.loadToken.cancel();
        this.loadToken = Axios.CancelToken.source();

        await Axios({
            url: `/api/v1/project/list`,
            method: 'get'
        }).then((res: any) => {
            this.projects = res.data.data;
        }).catch((err:any) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load project list!');
                alert(mesg);
            }
        });

        this.loading = false;
    }

    beofreDestory() {
        this.loadToken.cancel();
    }

}
</script>

