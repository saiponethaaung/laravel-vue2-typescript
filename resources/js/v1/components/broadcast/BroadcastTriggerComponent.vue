<template>
    <div class="inheritHFW broadcastRoot">
        <template v-if="loading && undefined!==trigger">
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
                                    <li :key="index" @click="trigger.tag=tag.id">{{ tag.name }}</li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <div class="label" v-html="$store.state.messageTags[selectedTag].mesg"></div>
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
                        Trigger:
                    </h5>
                   
                    <div class="bccTime bccDuration float-left">
                        <div class="timeOptionCon">
                            <input type="number" v-model="trigger.duration" min="1"/>
                        </div>
                        <dropdown-keybase-component
                            :options="durationOption"
                            :selectedKey="trigger.durationType"
                            v-model="trigger.durationType"
                        ></dropdown-keybase-component>
                    </div>
                    <div class="bccRepeat float-left">
                        <dropdown-keybase-component
                            :options="triggerOption"
                            :selectedKey="trigger.triggerType"
                            v-model="trigger.triggerType"
                        ></dropdown-keybase-component>
                    </div>
                    <template v-if="trigger.durationType===3">
                        <div class="float-left">
                           &nbsp;&nbsp;send at&nbsp;&nbsp;
                        </div>
                        <div class="bccTime float-left">
                            <div class="timeOptionCon">
                                <time-input-component
                                    :value="trigger.time"
                                    v-model="trigger.time"
                                ></time-input-component>
                            </div>
                            <dropdown-keybase-component
                                :options="periodOption"
                                :selectedKey="trigger.period"
                                v-model="trigger.period"
                            ></dropdown-keybase-component>
                        </div>
                    </template>
                </div>

                <div class="triggerAttribute" v-if="trigger.triggerType===3">
                    <input type="text" v-model="trigger.attr" placeholder="Attribute Name"/>
                    <dropdown-keybase-component
                        :options="[
                            {
                                key: 1,
                                value: 'is'
                            },
                            {
                                key: 2,
                                value: 'is not'
                            }
                        ]"
                        :selectedKey="trigger.condi"
                        v-model="trigger.condi"
                    ></dropdown-keybase-component>
                    <input type="text" v-model="trigger.value" placeholder="Attribute Value"/>
                </div>

                <div class="btnAction broadcastActionBtn">
                    <a href="javascript:void(0);" @click="deleteBroadcast()">
                        <figure>
                            <img src="/images/icons/delete.png"/>
                        </figure>
                    </a>
                    <a href="javascript:void(0);" @click="trigger.updateStatus()" :to="{name: 'project.broadcast'}">
                        <figure class="btnSend statusBtn" :class="{'deactiveStatus': !trigger.status}">
                            <img :src="'/images/icons/'+(trigger.status ? 'broadcast_status_enable': 'broadcast_status')+'.png'"/>
                        </figure>   
                    </a>
                </div>
            </div>
            <div v-if="!loadingContent">
                <builder-component
                    :isBroadcast="true"
                    :value="contents"
                    :section="trigger.section"></builder-component>
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Watch} from 'vue-property-decorator';
import BroadcastAttributeFilterListModel from '../../models/BroadcastAttributeFilterListModel';
import TriggerModel from '../../models/broadcast/TriggerModel';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component
export default class BroadcastTriggerComponent extends Vue{
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

    private durationOption: any = [
        {
            key: 1,
            value: 'Minute'
        },
        {
            key: 2,
            value: 'Hour'
        },
        {
            key: 3,
            value: 'Day'
        }
    ];

    private triggerOption: any = [
        {
            key: 1,
            value: 'After first interaction'
        },
        {
            key: 2,
            value: 'After last interaction'
        },
        {
            key: 3,
            value: 'After attribute is set'
        }
    ];

    private showTags: boolean = false;

    private trigger: TriggerModel = new TriggerModel();

    get selectedTag() {
        if(undefined === this.trigger) return 0;

        let index: any = 0;

        for(let i=0; this.$store.state.messageTags.length>i; i++ ) {
            if(this.$store.state.messageTags[i].id===this.trigger.tag) {
                index = i;
                break;
            }
        }

        return index;
    }

    mounted() {
        this.loadTrigger();
    }

    private addNewFitler() {
        this.filterList.createNewAttribute();
    }
    
    @Watch('$route.params.triggerid')
    async loadTrigger() {
        this.loadingToken.cancel();
        this.loadingToken = Axios.CancelToken.source();

        this.loading = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast/trigger/${this.$route.params.triggerid}`,
            method: 'get',
            cancelToken: this.loadingToken.token
        }).then(res => {
            this.trigger.init(res.data.data);
            this.filterList.id = this.trigger.id;
            this.filterList.loadAttributes();
            this.loadBroadcastContent();
            this.loading = false;
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load trigger info!');
                alert(mesg);
                this.loading = false;
            }
        });
    }

    async loadBroadcastContent() {
        this.loadingContentToken.cancel();
        this.loadingContentToken = Axios.CancelToken.source();

        this.loadingContent = true;
        this.contents = [];

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast/${this.trigger.id}/section/${this.trigger.section.id}/content`,
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

    @Watch('trigger.duration')
    @Watch('trigger.time')
    @Watch('trigger.period')
    @Watch('trigger.durationType')
    @Watch('trigger.triggerType')
    @Watch('trigger.attr')
    @Watch('trigger.value')
    @Watch('trigger.condi')
    private async updateTrigger() {
        if(this.loadingContent) return;
        await this.trigger.updateTrigger();
        this.$store.state.updateTrigger = {
            id: this.trigger.id,
            duration: this.trigger.duration,
            duration_type: this.trigger.durationType,
            trigger_type: this.trigger.triggerType
        };
    }

    @Watch('trigger.tag')
    private async updateTag() {
        if(this.loadingContent) return;
        await this.trigger.updateTag();
    }

    private async deleteBroadcast() {
        if(confirm("Are you sure you want to delete this broadcast?")) {
            let del = await this.trigger.deleteBroadcast();
            if(!del.status) {
                alert(del.mesg);
            } else {
                this.$store.state.deleteTrigger = this.trigger.id;
                this.$router.push({name: 'project.broadcast'});
            }
        }
    }
}
</script>
