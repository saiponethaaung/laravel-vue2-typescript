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

        <div class="contentRoot">Schedule your message</div>
        <div class="broadcastContentList schedule">
            <ul>
                <template v-for="(schedule, index) in this.scheduleList">
                    <li :key="index">
                        <router-link :to="{name: 'project.broadcast.schedule', params: { scheduleid: schedule.id }}">
                            {{ scheduleName(index) }}
                        </router-link>
                    </li>
                </template>
            </ul>
        </div>
        <template v-if="creatingSchedule">
            <div class="btnClick">
                creating...
            </div>
        </template>
        <template v-else>
            <div class="btnClick" @click="createSchedule()">
                <i class="material-icons">add</i>Add a schedule
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component
export default class BroadcastSidebarComponent extends Vue {

    private scheduleList: Array<any> = [];
    private creatingSchedule: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    mounted() {
        this.loadSchedule();
        this.loadMessageTags();
    }

    private scheduleName(index: number) {
        let name = '';
        let time = this.scheduleList[index].time.toString();
        let hour = time.slice(0, 2);
        let min = time.slice(2, 4);
        let timeOfDay = "am";

        if(hour>12) {
            hour = hour-12;
            timeOfDay = "pm";
        }

        time = `${hour}:${min} ${timeOfDay}`;

        switch(this.scheduleList[index].interval_type) {
            case(1):
                name = `${this.$store.state.months[this.scheduleList[index].month]} ${this.scheduleList[index].day} ${this.scheduleList[index].year} ${time}`;
                break;

            case(2):
                name = `Daily at ${time}`;
                break;

            case(3):
                name = `Weekend at ${time}`;
                break;

            case(4):
                name = `Every month ${this.scheduleList[index].day}  at ${time}`;
                break;

            case(5):
                name = `Workdays at ${time}`;
                break;

            case(6):
                name = `Every year ${this.$store.state.months[this.scheduleList[index].month]} ${this.scheduleList[index].day} at ${time}`;
                break;

            case(7):
                name = `Custom at ${time}`;
                break;
        }

        return name;
    }

    private async loadSchedule() {
        if(undefined===this.$store.state.projectInfo.id) return;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast/schedule`,
            method: 'get'
        }).then(res => {
            this.scheduleList = res.data.data;
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load schedule list!');
                alert(mesg);
            }
        })
    }
    
    private async createSchedule() {
        this.creatingSchedule = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast?section=schedule`,
            method: 'post'
        }).then(res => {
            this.scheduleList.push(res.data.data);
            this.$router.push({name: 'project.broadcast.schedule', params: { scheduleid: res.data.data.id }})
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new schedule!');
               alert(mesg);
            }
        });

        this.creatingSchedule = false;
    }

    private async loadMessageTags() {
        if(undefined===this.$store.state.projectInfo.id) return;
        
        this.$store.state.loadingMessageTags = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/message-tags`,
            method: 'get'
        }).then(res => {
            this.$store.state.messageTags = res.data.data;
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load message tags!');
                alert(mesg);
            }
        });

        this.$store.state.loadingMessageTags = false;
    }
}
</script>