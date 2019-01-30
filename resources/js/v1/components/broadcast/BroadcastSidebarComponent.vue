<template>
    <div class="bcs">
        <div class="chatSbHeaderOption broadcastHeading">
            <div class="chatFilterList float-left">
                <div class="inboxOptionTitle">Broadcast</div>
            </div>
        </div>

        <div class="contentRoot">
            <div class="broadcastHeading">
                <i class="material-icons iconAlign">send</i>
                <span>Send your message now</span>
            </div>
        </div>
        <div class="btnClick">
            <router-link :to="{'name': 'project.broadcast.sendnow'}">
                Click here to send your message now
            </router-link>
        </div>

        <div class="contentRoot">
            <div class="broadcastHeading">
                <figure>
                    <img src="/images/icons/trigger.png"/>
                </figure>
                <span>Trigger your message</span>
            </div>
        </div>
        <div class="broadcastContentList schedule">
            <ul class="broadcastScheduleListRoot">
                <template v-for="(trigger, index) in this.triggerList">
                    <li :key="index" class="broadcastScheduleList">
                        <router-link
                            :to="{name: 'project.broadcast.trigger', params: { triggerid: trigger.id }}"
                            :class="{'activeBroadcast': $route.params.triggerid==trigger.id}"
                        >
                            {{ trigger.duration }}
                            <template v-if="trigger.duration_type===1">{{ trigger.duration>1 ? 'minutes' : 'minute' }}</template>
                            <template v-if="trigger.duration_type===2">{{ trigger.duration>1 ? 'hours' : 'hour' }}</template>
                            <template v-if="trigger.duration_type===3">{{ trigger.duration>1 ? 'days' : 'day' }}</template>
                            &nbsp;after&nbsp;
                            <template v-if="trigger.trigger_type===1">first interaction</template>
                            <template v-if="trigger.trigger_type===2">last interaction</template>
                            <template v-if="trigger.trigger_type===3">attribute set</template>
                        </router-link>
                    </li>
                </template>
            </ul>
        </div>
        <template v-if="creatingTrigger">
            <div class="btnClick">
                creating...
            </div>
        </template>
        <template v-else>
            <div class="btnClick" v-if="triggerLoading">
                Loading...
            </div>
            <div class="btnClick" @click="createTrigger()" v-else>
                <i class="material-icons">add</i>Add a trigger
            </div>
        </template>

        <div class="contentRoot">
            <div class="broadcastHeading">
                <figure>
                    <img src="/images/icons/schedule.png"/>
                </figure>
                <span>Schedule your message</span>
            </div>
        </div>
        <div class="broadcastContentList schedule">
            <ul class="broadcastScheduleListRoot">
                <template v-for="(schedule, index) in this.scheduleList">
                    <li :key="index" class="broadcastScheduleList">
                        <router-link
                            :to="{name: 'project.broadcast.schedule', params: { scheduleid: schedule.id }}"
                            v-html="scheduleName(index)"
                            :class="{'activeBroadcast': $route.params.scheduleid==schedule.id}"
                        ></router-link>
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
            <div class="btnClick" v-if="scheduleLoading">
                Loading...
            </div>
            <div class="btnClick" @click="createSchedule()" v-else>
                <i class="material-icons">add</i>Add a schedule
            </div>
        </template>
        <div>
            <div contenteditable="true"></div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Watch } from 'vue-property-decorator';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component
export default class BroadcastSidebarComponent extends Vue {

    private scheduleLoading: boolean = true;
    private triggerLoading: boolean = true;
    private scheduleList: Array<any> = [];
    private triggerList: Array<any> = [];
    private creatingSchedule: boolean = false;
    private creatingTrigger: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    mounted() {
        this.loadSchedule();
        this.loadTrigger();
        this.loadMessageTags();
    }
    
    @Watch('$store.state.deleteTrigger')
    private removeTrigger() {
        for(let i in this.triggerList) {
            if(this.triggerList[i].id===this.$store.state.deleteTrigger) {
                this.triggerList.splice(parseInt(i), 1);
                break;
            }
        }
    }
    
    @Watch('$store.state.updateTrigger', { deep: true })
    private updateTrigger() {
        if(this.$store.state.updateTrigger==null) return;
        for(let i in this.triggerList) {
            if(this.triggerList[i].id===this.$store.state.updateTrigger.id) {
                this.triggerList[i].duration = this.$store.state.updateTrigger.duration;
                this.triggerList[i].duration_type = this.$store.state.updateTrigger.duration_type;
                this.triggerList[i].trigger_type = this.$store.state.updateTrigger.trigger_type;
                break;
            }
        }
    }

    @Watch('$store.state.deleteSchedule')
    private removeSchedule() {
        for(let i in this.scheduleList) {
            if(this.scheduleList[i].id===this.$store.state.deleteSchedule) {
                this.scheduleList.splice(parseInt(i), 1);
                break;
            }
        }
    }

    @Watch('$store.state.updateSchedule', { deep: true })
    private updateSchedule() {
        if(this.$store.state.updateSchedule==null) return;
        for(let i in this.scheduleList) {
            if(this.scheduleList[i].id===this.$store.state.updateSchedule.id) {
                this.scheduleList[i].day = this.$store.state.updateSchedule.day;
                this.scheduleList[i].month = this.$store.state.updateSchedule.month;
                this.scheduleList[i].year = this.$store.state.updateSchedule.year;
                this.scheduleList[i].time = this.$store.state.updateSchedule.time;
                this.scheduleList[i].interval_type = this.$store.state.updateSchedule.interval_type;
                break;
            }
        }
    }

    private scheduleName(index: number) {
        let name = '';
        let time = this.scheduleList[index].time.toString();
        let hour = time.slice(0, 2);
        let min = time.slice(2, 4);
        let timeOfDay = "am";

        if(parseInt(hour)>12) {
            hour = hour-12;
            timeOfDay = "pm";
        }

        time = `${hour}:${min} ${timeOfDay}`;

        switch(this.scheduleList[index].interval_type) {
            case(1):
                name = `${this.$store.state.months[parseInt(this.scheduleList[index].month)]} ${this.scheduleList[index].day} ${this.scheduleList[index].year} <span class='float-right'>${time}</span>`;
                break;

            case(2):
                name = `Daily <span class='float-right'>${time}</span>`;
                break;

            case(3):
                name = `Weekend <span class='float-right'>${time}</span>`;
                break;

            case(4):
                name = `Every month <span class='float-right'>${this.scheduleList[index].day} • ${time}</span>`;
                break;

            case(5):
                name = `Workdays <span class='float-right'>${time}</span>`;
                break;

            case(6):
                name = `Every year <span class='float-right'>${this.$store.state.months[parseInt(this.scheduleList[index].month)]} ${this.scheduleList[index].day} • ${time}</span>`;
                break;

            case(7):
                name = `Custom <span class='float-right'>${time}</span>`;
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
            this.scheduleLoading = false;
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load schedule list!');
                alert(mesg);
                this.scheduleLoading = false;
            }
        })
    }

    private async loadTrigger() {
        if(undefined===this.$store.state.projectInfo.id) return;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast/trigger`,
            method: 'get'
        }).then(res => {
            this.triggerList = res.data.data;
            this.triggerLoading = false;
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load trigger list!');
                alert(mesg);
                this.triggerLoading = false;
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
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new schedule!');
               alert(mesg);
            }
        });

        this.creatingSchedule = false;
    }
    
    private async createTrigger() {
        this.creatingTrigger = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast?section=trigger`,
            method: 'post'
        }).then(res => {
            this.triggerList.push(res.data.data);
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new schedule!');
               alert(mesg);
            }
        });

        this.creatingTrigger = false;
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