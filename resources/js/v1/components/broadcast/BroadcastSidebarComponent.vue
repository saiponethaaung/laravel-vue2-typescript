<template>
    <div>
        <div class="chatSbHeaderOption">
            <div class="chatFilterList float-left">
                <div class="inboxOptionTitle">Broadcast</div>
            </div>
        </div>

        <div class="contentRoot"><i class="material-icons iconAlign">send</i>Send your message now</div>
        <div class="btnClick">
            <router-link :to="{'name': 'project.broadcast.sendnow'}">
                Click here to send your message now
            </router-link>
        </div>

        <div class="contentRoot">Trigger your message</div>
        <div class="btnClick">
            <router-link :to="{'name': 'project.broadcast.trigger'}">
                <i class="material-icons">add</i>Add a trigger
            </router-link>
        </div>

        <div class="contentRoot"></i>Schedule your message</div>
        <div class="chatBlockContentList">
            <div class="btnClick">Happy New Year</div>
            <div class="btnClick">Time</div>
        </div>
        <div class="btnClick"><i class="material-icons">add</i>Add a trigger</div>

        
    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';

@Component
export default class BroadcastSidebarComponent extends Vue {

    mounted() {

    }
}
</script>