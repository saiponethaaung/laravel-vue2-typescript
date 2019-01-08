<template>
    <div class="inheritHFW">
        <template v-if="undefined==$store.state.projectInfo.pageConnected">
            Loading...
        </template>
        <template v-else>
            <template v-if="$store.state.projectInfo.pageConnected">
                Inbox Page
            </template>
            <template v-else>
                <div class="noContent">
                    <i class="material-icons">assignment</i>
                    <span clss="noContentInfo">Connect a facebook page to access an inbox function.</span>
                </div>
            </template>
        </template>
    </div>   
</template>

<script lang="ts">
import { Component, Watch, Vue } from 'vue-property-decorator';

@Component
export default class InboxPageComponent extends Vue {
    
}
</script>
