<template>
    <div class="inheritHFW broadcastRoot">
        <template v-if="loading">Loading...</template>
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
                                    <li :key="index" @click="broadcast.tag=tag.id">{{ tag.name }}</li>
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

                            <button
                                v-if="filterList.attributes.length>1"
                                class="deleteAttribute"
                                @click="filterList.deleteFilter(index);"
                            >
                                <i class="material-icons">delete</i>
                            </button>
                            <div
                                v-if="(filterList.attributes.length-1)==index"
                                @click="addNewFitler()"
                                class="addMoreFilterButton"
                            >
                                <i class="material-icons">add</i>Add More
                            </div>
                        </div>
                    </template>
                </div>

                <div class="reachableUser">
                    You have
                    <b>4</b> users based on your filters.
                </div>

                <div class="btnAction broadcastActionBtn">
                    <a href="javascript:void(0);" @click="deleteBroadcast()">
                        <figure>
                            <img src="/images/icons/broadcast/delete.png" />
                        </figure>
                    </a>
                    <a href="javascript:void(0);" @click="broadcastSend()">
                        <figure class="btnSend">
                            <img src="/images/icons/broadcast/send.png" />
                        </figure>
                    </a>
                </div>
            </div>

            <div v-if="!loadingContent">
                <builder-component
                    :isBroadcast="true"
                    :value="contents"
                    :section="broadcast.section"
                ></builder-component>
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Emit, Vue, Watch } from "vue-property-decorator";
import BuilderComponentMock from "../common/BuilderComponentMock.vue";
import BroadcastAttributeFilterListModel from "../../models/BroadcastAttributeFilterListModel";
import AttributeFilterModel from "../../models/AttributeFilterModel";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import Axios, { CancelTokenSource } from "axios";
import BroadcastModel from "../../models/broadcast/BroadcastModel";

@Component({
    components: {
        BuilderComponentMock
    }
})
export default class BroadcastSendNowComponent extends Vue {
    private showTags: boolean = false;
    private broadcast: BroadcastModel = new BroadcastModel();
    private loading: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private loadingToken: CancelTokenSource = Axios.CancelToken.source();
    private loadingContentToken: CancelTokenSource = Axios.CancelToken.source();
    private loadingContent: boolean = true;
    private contents: any = [];
    private filterList: BroadcastAttributeFilterListModel = new BroadcastAttributeFilterListModel(
        this.$store.state.projectInfo.id
    );

    @Emit("input")
    selectNewOption(key: number) {
        return key;
    }

    mounted() {
        this.loadSendNowContent();
    }

    get selectedTag() {
        if (undefined === this.broadcast) return 0;

        let index: any = 0;

        for (let i = 0; this.$store.state.messageTags.length > i; i++) {
            if (this.$store.state.messageTags[i].id === this.broadcast.tag) {
                index = i;
                break;
            }
        }

        return index;
    }

    private async loadSendNowContent() {
        this.loadingToken.cancel();
        this.loadingToken = Axios.CancelToken.source();

        this.loading = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast/sendnow`,
            method: "get",
            cancelToken: this.loadingToken.token
        })
            .then(res => {
                this.broadcast.broadcastInit(res.data.data);
                this.filterList = new BroadcastAttributeFilterListModel(
                    this.$store.state.projectInfo.id
                );
                this.filterList.id = this.broadcast.id;
                this.filterList.loadAttributes();
                this.loadBroadcastContent();
                this.loading = false;
            })
            .catch(err => {
                if (err.response) {
                    let mesg = this.ajaxHandler.globalHandler(
                        err,
                        "Failed to load broadcast info!"
                    );
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
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/broadcast/${this.broadcast.id}/section/${this.broadcast.section.id}/content`,
            cancelToken: this.loadingContentToken.token
        })
            .then((res: any) => {
                this.contents = res.data.content;
            })
            .catch((err: any) => {
                if (err.response) {
                    let mesg = this.ajaxHandler.globalHandler(
                        err,
                        "Failed to load content!"
                    );
                    alert(mesg);
                } else {
                    this.loadingContentToken.cancel();
                }
            });

        this.loadingContent = false;
    }

    private addNewFitler() {
        this.filterList.createNewAttribute();
    }

    @Watch("broadcast.tag")
    private async updateTag() {
        if (this.loadingContent) return;
        await this.broadcast.updateTag();
    }

    private async deleteBroadcast() {
        if (confirm("Are you sure you want to delete this broadcast?")) {
            let del = await this.broadcast.deleteBroadcast();
            if (!del.status) {
                alert(del.mesg);
            } else {
                this.$store.state.deleteTrigger = this.broadcast.id;
                this.$router.push({ name: "project.broadcast" });
            }
        }
    }

    private async broadcastSend() {
        if (confirm("Are you sure you want to publish this broadcast?")) {
            let publish = await this.broadcast.broadcastSend();
            if (!publish.status) {
                alert(publish.mesg);
            } else {
                this.$store.state.deleteTrigger = this.broadcast.id;
                this.$router.push({ name: "project.broadcast" });
            }
        }
    }
}
</script>
