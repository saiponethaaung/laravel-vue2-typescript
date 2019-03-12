<template>
    <div class="inheritHFW broadcastRoot">
        <template v-if="loading && undefined!==schedule">
            Loading...
        </template>
        <template v-else>
            <div class="broadcastFilterCon">
                <div class="outerDisplay" v-if="$store.state.messageTags.length>0">
                    <div @click="showTags=!showTags">
                        <div class="btnSub">
                            <span>{{ $store.state.messageTags[selectedTag].name }}</span>
                            <span class="iconSub">
                                <i class="material-icons">
                                    <template v-if="showTags">expand_less</template>
                                    <template v-else>expand_more</template>
                                </i>
                            </span>
                        </div>
                        <div v-show="showTags" class="dropDownList">
                            <ul>
                                <template v-for="(tag, index) in $store.state.messageTags">
                                    <li :key="index" @click="schedule.tag=tag.id">{{ tag.name }}</li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <div class="label" v-html="$store.state.messageTags[selectedTag].mesg">
                        <!-- <span>Non-promo message under the News, Productivity, and Personal Trackers categories described in the Messenger Platform's subscription messaging policy.</span>
                        <span class="link">subscription messaging policy.</span> -->
                    </div>
                </div>

                <div class="attributeSelectorList">
                    <template v-for="(attribute, index) in filterList.attributes">
                        <div class="attributeSelector" :key="index">
                            <attribute-selector-component
                                :isSegment="false"
                                :attribute="attribute"
                                :canCondition="(filterList.attributes.length-1)>index"
                                :segmentValue="filterList.segments"
                                :segment="attribute.segment"
                            ></attribute-selector-component>
                            
                            <button v-if="filterList.attributes.length>1" class="deleteAttribute" @click="filterList.deleteFilter(index);">
                                <i class="material-icons">delete</i>
                            </button>
                            <div v-if="(filterList.attributes.length-1)==index" @click="addNewFitler()" class="addMoreFilterButton">
                                <i class="material-icons">add</i>Add More
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="reachableUser">You have <b>4</b> users based on your filters.</div>

                <div class="broadcastCondition">
                    <h5 class="bccHeading float-left">
                        Schedule:
                    </h5>
                    <div class="bccCalender float-left">
                        <span>{{ showDate }}</span>
                        <i class="material-icons">date_range</i>
                        <div class="calendarPlugin">
                            <v-date-picker
                                v-model="schedule.date"
                                :min-date="new Date()"
                            ></v-date-picker>
                        </div>
                    </div>
                    <div class="bccTime float-left">
                        <div class="timeOptionCon">
                            <time-input-component
                                :value="schedule.time"
                                v-model="schedule.time"
                            ></time-input-component>
                        </div>
                        <dropdown-keybase-component
                            :options="periodOption"
                            :selectedKey="schedule.period"
                            v-model="schedule.period"
                        ></dropdown-keybase-component>
                    </div>
                    <div class="bccRepeat float-left">
                        <dropdown-keybase-component
                            :labelText="'Repeat: '"
                            :options="repeatOption"
                            :selectedKey="schedule.repeat"
                            v-model="schedule.repeat"
                        ></dropdown-keybase-component>
                    </div>
                </div>
                <template v-if="schedule.repeat===7">
                    <div class="dayPicker">
                        <ul>
                            <template v-for="(day, index) in schedule.days">
                                <li
                                    :class="{ 'selectedDay': day.check }"
                                    :key="index"
                                    @click="day.check=!day.check">{{ day.name }}</li>
                            </template>
                        </ul>
                    </div>
                </template>
                <div class="btnAction broadcastActionBtn">
                    <a href="javascript:void(0);" @click="deleteBroadcast()">
                        <figure>
                            <img src="/images/icons/broadcast/delete.png"/>
                        </figure>
                    </a>
                    <a href="javascript:void(0);" @click="schedule.updateStatus()" :to="{name: 'project.broadcast'}">
                        <figure class="btnSend statusBtn" :class="{'deactiveStatus': !schedule.status}">
                            <img :src="'/images/icons/broadcast/'+(schedule.status ? 'broadcast_status_enable': 'broadcast_status')+'.png'"/>
                        </figure>   
                    </a>
                </div>
            </div>
            <div v-if="!loadingContent">
                <builder-component
                    :isBroadcast="true"
                    :value="contents"
                    :section="schedule.section"></builder-component>
            </div>
            <div v-else>
                Loading...
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Watch} from 'vue-property-decorator';
import BuilderComponentMock from '../common/BuilderComponentMock.vue';
import ScheduleModel from '../../models/broadcast/ScheduleModel';
import BroadcastAttributeFilterListModel from '../../models/BroadcastAttributeFilterListModel';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component({
    components: {
        BuilderComponentMock
    }
})
export default class BroadcastScheduleComponent extends Vue{
    private loading: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private loadingToken: CancelTokenSource = Axios.CancelToken.source();
    private loadingContentToken: CancelTokenSource = Axios.CancelToken.source();
    private loadingContent: boolean = true;
    private contents: any = [];
    private filterList: BroadcastAttributeFilterListModel = new BroadcastAttributeFilterListModel(this.$store.state.projectInfo.id);

    private periodOption: any = [
        {
            key: 1,
            value: "am"
        },
        {
            key: 2,
            value: "pm"
        },
    ];

    private repeatOption: any = [
        {
            key: 1,
            value: 'None'
        },
        {
            key: 2,
            value: 'Daily'
        },
        {
            key: 3,
            value: 'Weekend'
        },
        {
            key: 4,
            value: 'Every Month'
        },
        {
            key: 5,
            value: 'Workdays'
        },
        {
            key: 6,
            value: 'Yearly'
        },
        {
            key: 7,
            value: 'Custom'
        },
    ];

    private showTags: boolean = false;

    private schedule: ScheduleModel = new ScheduleModel();

    get showDate() {
        if(undefined === this.schedule) return '';

        let date = '';
        let month = this.schedule.date.getMonth()+1;
        return `${this.$store.state.months[month]} ${this.schedule.date.getDate()}, ${this.schedule.date.getFullYear()}`;
    }

    get selectedTag() {
        if(undefined === this.schedule) return 0;

        let index: any = 0;

        for(let i=0; this.$store.state.messageTags.length>i; i++ ) {
            if(this.$store.state.messageTags[i].id===this.schedule.tag) {
                index = i;
                break;
            }
        }

        return index;
    }

    mounted() {
        this.loadSchedule();
    }

    private addNewFitler() {
        this.filterList.createNewAttribute();
    }
    
    @Watch('$route.params.scheduleid')
    async loadSchedule() {
        this.loadingToken.cancel();
        this.loadingToken = Axios.CancelToken.source();

        this.loading = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast/schedule/${this.$route.params.scheduleid}`,
            method: 'get',
            cancelToken: this.loadingToken.token
        }).then(res => {
            this.schedule.init(res.data.data);
            this.filterList = new BroadcastAttributeFilterListModel(this.$store.state.projectInfo.id);
            this.filterList.id = this.schedule.id;
            this.filterList.loadAttributes();
            this.loadScheduleContent();
            this.loading = false;
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load schedule info!');
                alert(mesg);
                this.loading = false;
            }
        });
    }

    async loadScheduleContent() {
        this.loadingContentToken.cancel();
        this.loadingContentToken = Axios.CancelToken.source();

        this.loadingContent = true;
        this.contents = [];

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast/${this.schedule.id}/section/${this.schedule.section.id}/content`,
            cancelToken: this.loadingContentToken.token
        }).then((res: any) => {
            this.contents = res.data.content;
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load content!');
                alert(mesg);
            } else {
                this.loadingContentToken.cancel();
            }
        });

        this.loadingContent = false;
    }

    @Watch('schedule.date')
    @Watch('schedule.time')
    @Watch('schedule.period')
    @Watch('schedule.repeat')
    @Watch('schedule.days', {deep: true})
    private async updateSchedule() {
        if(this.loadingContent) return;
        await this.schedule.updateSchedule();
        console.log('time',  this.schedule.timeServerFormat);
        this.$store.state.updateSchedule = {
            id: this.schedule.id,
            day: this.schedule.dateDate,
            month: this.schedule.dateMonth,
            year: this.schedule.dateYear,
            time: this.schedule.timeServerFormat.toString(),
            interval_type: this.schedule.repeat
        };
    }

    @Watch('schedule.tag')
    private async updateTag() {
        if(this.loadingContent) return;
        await this.schedule.updateTag();
    }

    private async deleteBroadcast() {
        if(confirm("Are you sure you want to delete this broadcast?")) {
            let del = await this.schedule.deleteBroadcast();
            if(!del.status) {
                alert(del.mesg);
            } else {
                this.$store.state.deleteSchedule = this.schedule.id;
                this.$router.push({name: 'project.broadcast'});
            }
        }
    }
}
</script>
