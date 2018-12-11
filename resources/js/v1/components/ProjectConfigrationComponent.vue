<template>
    <div>
        <template v-if="$store.state.projectInfo.isOwner">
            <template v-if="$store.state.user.facebook_connected">
                Connect a facebook page
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

@Component
export default class ProjectConfigrationComponent extends Vue {
    
}
</script>
