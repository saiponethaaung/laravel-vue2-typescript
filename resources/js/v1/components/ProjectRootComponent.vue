<template>
    <div class="inheritHFW">
        <template v-if="$store.state.validatingProject">
            <div class="floatingLoading">
                Loading...
            </div>
        </template>
        <template v-else>
            <router-view></router-view>
        </template>
    </div>
</template>

<script lang="ts">
import {Component, Watch, Vue} from 'vue-property-decorator';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../utils/AjaxErrorHandler';

@Component
export default class ProjectRootComponent extends Vue {

    private validateToken: CancelTokenSource = Axios.CancelToken.source();
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    
    @Watch('$route.params.projectid')
    async validateProject() {
        this.$store.commit('setProjectStatus', {
            status: true,
        });
        
        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}`,
            method: 'get'
        }).then((res: any) => {
            this.$store.commit('setProjectInfo', { project: res.data.data });
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to validate project!');
                alert(mesg);
            }
        });
            
        this.$store.commit('setProjectStatus', {
            status: false,
        });
    }

    created() {
        this.validateProject();
    }
}
</script>