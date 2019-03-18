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
                                <li class="segmentListItem" v-for="(segment, index) in segmentList.segments" :key="index" :class="{'selectedSegment': $store.state.selectedSegment===segment.id}">
                                    <div class="segmentListName" @click="$store.state.selectedSegment=segment.id">{{ segment.name }}</div>
                                    <div class="segmentActionIcon">
                                        <div class="saiWrapper" @click="openSegmentEdit(index)">
                                            <i class="material-icons">create</i>
                                        </div>
                                        <div class="saiWrapper" @click="deleteSegment(index)">
                                            <i class="material-icons">delete</i>
                                        </div>
                                        <div class="saiWrapper">
                                            <figure>
                                                <img src="/images/icons/common/sort.png"/>
                                            </figure>
                                        </div>
                                    </div>
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
        <div class="popFixedContainer popFixedCenter" v-if="editSegment>-1">
            <div class="userAttributePop filterAttribute">
                <div class="uaBodyCon">
                    <h5 class="uaTitle">Edit Segment</h5>
                    <div class="segmentTitleCon">
                        <label class="segmentTitleLabel">Segment Name:</label>
                        <input class="segmentTitleInput" type="text" placeholder="Segment name" v-model="segmentList.segments[editSegment].name"/>
                    </div>
                    <template v-if="segmentList.segments[editSegment].isAttrLoading">
                        Loading...
                    </template>
                    <template v-else>
                        <div class="attributeSelectorList alignAttribute">
                            <template v-for="(attribute, index) in segmentList.segments[editSegment].attributes">
                                <div class="attributeSelector" :key="index">
                                    <attribute-selector-component
                                        :isSegment="true"
                                        :attribute="attribute"
                                        :canCondition="(segmentList.segments[editSegment].attributes.length-1)>index"
                                        :segmentValue="[]"
                                        :segment="[]"
                                    ></attribute-selector-component>
                                    <button v-if="segmentList.segments[editSegment].attributes.length>1" class="deleteAttribute" @click="deleteFilter(index)">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </div>
                            </template>
                            <div @click="creatNewFilter()" class="addMoreFilterButton" v-if="!segmentList.segments[editSegment].isAttrCreating">
                                <i class="material-icons">add</i>
                            </div>
                            <template v-if="segmentList.segments[editSegment].isAttrCreating">
                                Creating...
                            </template>
                        </div>
                    </template>
                </div>
                <div class="uaFooterCon">
                    <button class="headerButtonTypeOne" @click="editSegment=-1">Cancel</button>
                    <button class="headerButtonTypeOne" @click="updateSegment()" :disabled="segmentList.segments[editSegment].isAttrLoading">Update</button>
                </div>
            </div>
        </div>
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
    private editSegment: number = -1;

    private segmentList: SegmentListModel = new SegmentListModel();

    @Watch('$store.state.projectInfo', { immediate: true })
    async initSegment() {
        if(undefined === this.$store.state.projectInfo.id) return;

        this.segmentList.setProjectId = this.$store.state.projectInfo.id;

        let loadSegment: any = await this.segmentList.loadSegment();

        if(!loadSegment['status']) {
            alert(loadSegment['mesg']);
        }
    }

    private async openSegmentEdit(index: number) {
        this.editSegment = index;
        if(!this.segmentList.segments[index].isAttrLoaded) {
            let loadAttributes = await this.segmentList.segments[index].loadAttributes();
            if(!loadAttributes.status) {
                alert(loadAttributes.mesg);
            }
        }
    }
    
    private async creatNewFilter() {
        let newFilter = await this.segmentList.segments[this.editSegment].createNewAttribute();
        if(!newFilter.status) {
            alert(newFilter.mesg);
        }
    }

    private async deleteFilter(index: number) {
        if(confirm('Are you sure you want to delete this filter condition?')) {
            let deleteFilter = await this.segmentList.segments[this.editSegment].deleteFilter(index);

            if(!deleteFilter.status) {
                alert(deleteFilter.mesg);
            }
        }
    }

    private async updateSegment() {
        let updateSegment = await this.segmentList.segments[this.editSegment].updateSegment();

        if(!updateSegment.status) {
            alert(updateSegment.mesg);
            return;
        }

        this.editSegment = -1;
    }

    private async deleteSegment(index: number) {
        if(confirm('Are you sure you want to delete this segment?')) {
            let delId = this.segmentList.segments[index].id;
            let deleteSegment = await this.segmentList.deleteSegment(index);

            if(!deleteSegment.status) {
                alert(deleteSegment.mesg);
            } else {
                if(this.$store.state.selectedSegment===delId) {
                    this.$store.state.selectedSegment = 0;
                }
            }
        }
    }

    beforeDestory() {
        this.segmentList.cancelLoading();
    }
}
</script>