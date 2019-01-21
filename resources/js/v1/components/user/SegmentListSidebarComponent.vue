<template>
    <div>
        <div class="chatSbHeaderOption">
            <div class="chatFilterList float-left">
                <div class="inboxOptionTitle">User:</div>
                <div class="inboxOptionSelector">
                    <div class="inboxSelectedOption">
                        <span class="inboxSelectedOptionValue">Segments</span>
                        <span class="inboxFilterOptionIcon">
                            <i class="material-icons" @click="showFilter=!showFilter">
                                <template v-if="showFilter">
                                    arrow_drop_up
                                </template>
                                <template v-else>
                                    arrow_drop_down
                                </template>
                            </i>
                        </span>
                    </div>
                    <div class="inboxOptionsCon" v-if="showFilter">
                        <ul>
                            <li>
                                <router-link :to="{'name': 'project.users'}">
                                    Accounts
                                </router-link>
                            </li>
                            <li>
                                <router-link :to="{'name': 'project.users.segments'}">
                                    Segments
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <ul class="avaFilterList">
            <li class="avaFilterListItem hasChecked">
                <h5 class="aflHeading" @click="showSegments=!showSegments">
                    <i class="material-icons">{{ showSegments ? 'expand_less' : 'expand_more'}}</i>
                    <span>Custom Segments</span>
                </h5>
                <ul class="segmentList" v-show="showSegments">
                    <template v-if="undefined !== segmentList">
                        <template v-if="segmentList.loading">
                            <li>Loading...</li>
                        </template>
                        <template v-else>
                            <template v-if="segmentList.segments.length>0">
                                <li class="segmentListItem" v-for="(segment, vindex) in segmentList.segments" :key="vindex" :class="{'selectedSegment': $store.state.selectedSegment===segment.id}">
                                    <div class="segmentListName" @click="$store.state.selectedSegment=segment.id">{{ segment.name }}</div>
                                    <!-- <div calss="segmentActionIcon">
                                        <div>Edit</div>
                                        <div>Remove</div>
                                        <div>Sort</div>
                                    </div> -->
                                </li>
                            </template>
                            <template v-else>
                                <li>There is no segments!</li>
                            </template>
                        </template>
                    </template>
                </ul>
            </li>
        </ul>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Watch } from 'vue-property-decorator';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';
import SegmentListModel from '../../models/SegmentListModel';

@Component
export default class SegmentListSidebarComponent extends Vue {
    private showFilter: boolean = false;
    private showSegments: boolean = true;

    private segmentList: SegmentListModel = new SegmentListModel();
    // private filters: any = 

    @Watch('$store.state.projectInfo', { immediate: true })
    async initSegment() {
        if(undefined === this.$store.state.projectInfo.id) return;

        this.segmentList.setProjectId = this.$store.state.projectInfo.id;

        let loadSegment: any = await this.segmentList.loadSegment();

        if(!loadSegment['status']) {
            alert(loadSegment['mesg']);
        }
    }

    beforeDestory() {
        this.segmentList.cancelLoading();
    }
}
</script>