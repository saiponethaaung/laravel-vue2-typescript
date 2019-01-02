<template>
    <div>
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

