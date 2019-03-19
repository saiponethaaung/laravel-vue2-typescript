<template>
    <div class="userListRoot">
        <div class="userListControlCon">
            <div class="userListFilterCon">
                <h5 class="userFilterHeading">
                    <span class="headingTitle">Member List</span>
                    <template v-if="userFilters.length>0">
                        <template v-for="(value, index) in userFilters">
                            <div :key="index" class="listFilterCon segmentSection">
                                <span class="filterKey">{{ value.key }}:</span>
                                <span class="filterValue">{{ value.value }}</span>
                            </div>
                        </template>
                    </template>
                </h5>
            </div>
            <div class="userListOptionCon">
                <button class="uloButton" @click="createSegment=true">
                    <i class="material-icons">group_add</i>
                    <span>Create Segment</span>
                </button>
                <button class="uloButton" @click="exportCSV" v-if="userList.length>0">
                    <figure>
                        <img src="/images/icons/user/export.png">
                    </figure>
                    <span>export</span>
                </button>
            </div>
        </div>

        <template v-if="$store.state.selectedSegment>0">
            <user-table-component :userList="userList" :userLoading="userLoading"></user-table-component>
        </template>
        <template v-else>
            <div class="noContent">
                <i class="material-icons">supervisor_account</i>
                <span clss="noContentInfo">Select a segment to view reachable users</span>
            </div>
        </template>

        <div class="popFixedContainer popFixedCenter" v-if="createSegment">
            <div class="userAttributePop filterAttribute">
                <div class="uaBodyCon">
                    <h5 class="uaTitle">Create new segment</h5>
                    <div class="segmentTitleCon">
                        <label class="segmentTitleLabel">Segment Name:</label>
                        <input
                            class="segmentTitleInput"
                            type="text"
                            placeholder="Segment name"
                            v-model="filterSegment.name"
                        >
                    </div>
                    <div class="attributeSelectorList alignAttribute">
                        <template v-for="(attribute, index) in filterSegment.attributes">
                            <div class="attributeSelector" :key="index">
                                <attribute-selector-component
                                    :isSegment="true"
                                    :attribute="attribute"
                                    :canCondition="(filterSegment.attributes.length-1)>index"
                                    :segmentValue="[]"
                                    :segment="[]"
                                ></attribute-selector-component>
                                <button
                                    v-if="filterSegment.attributes.length>1"
                                    class="deleteAttribute"
                                    @click="filterSegment.attributes.splice(index, 1);"
                                >
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </template>
                        <div @click="addNewFitler()" class="addMoreFilterButton">
                            <i class="material-icons">add</i>
                        </div>
                    </div>
                </div>
                <div class="uaFooterCon">
                    <button class="headerButtonTypeOne" @click="createSegment=false">Cancel</button>
                    <button class="headerButtonTypeOne" @click="createNewSegment()">Create</button>
                </div>
                <template v-if="errorSegment!==''">
                    <error-component :mesg="errorSegment" @closeError="errorSegment=''"></error-component>
                </template>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Watch } from "vue-property-decorator";
import AttributeFilterListModel from "../../models/AttributeFilterListModel";
import UserListModel from "../../models/users/UserListModel";
import UserTableComponent from "./UserTableComponent.vue";
import Axios, { CancelTokenSource } from "axios";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

@Component({
    components: {
        UserTableComponent
    }
})
export default class UserSegmentListComponent extends Vue {
    private userLoading: boolean = false;
    private userFilters: Array<any> = [];
    private loadUserToken: CancelTokenSource = Axios.CancelToken.source();
    private userList: Array<UserListModel> = [];
    private createSegment: boolean = false;
    private errorSegment: string = "";
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private filterSegment: AttributeFilterListModel = new AttributeFilterListModel(
        false,
        this.$store.state.projectInfo.id,
        []
    );

    mounted() {
        this.addNewFitler();
    }

    private addNewFitler() {
        this.filterSegment.createNewAttributeFilter();
    }

    @Watch("$store.state.selectedSegment")
    private async loadUser() {
        this.userList = [];
        this.userFilters = [];
        if (this.$store.state.selectedSegment === 0) {
            return;
        }

        this.loadUserToken.cancel();
        this.loadUserToken = Axios.CancelToken.source();

        this.userLoading = true;

        await Axios({
            url: `/api/v1/project/${
                this.$store.state.projectInfo.id
            }/users/segments/${this.$store.state.selectedSegment}/users`,
            method: "get",
            cancelToken: this.loadUserToken.token
        })
            .then(res => {
                this.userFilters = res.data.data.filters;
                for (let i of res.data.data.user) {
                    this.userList.push(
                        new UserListModel(i, this.$store.state.projectInfo.id)
                    );
                }
                this.userLoading = false;
            })
            .catch(err => {
                if (err.response) {
                    let mesg = this.ajaxHandler.globalHandler(
                        err,
                        "Failed to load user!"
                    );
                    this.errorSegment = mesg;
                }
            });

        console.log("loading user by segment");
    }

    private async createNewSegment() {
        let createSegment = await this.filterSegment.createSegment();

        if (!createSegment["status"]) {
            this.errorSegment = createSegment["mesg"];
            return;
        }

        this.createSegment = false;
    }

    private exportCSV() {
        const rows = [
            [
                "Name",
                "Gender",
                "Age",
                "Last Engaged",
                "Last Seen",
                "Signed up",
                "Session"
            ]
        ];

        for (let i of this.userList) {
            rows.push(i.csvFormat);
        }

        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            let row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "userdata.csv");
        document.body.appendChild(link);

        link.click();
    }
}
</script>
