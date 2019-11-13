<template>
    <div class="userListRoot">
        <div class="userListControlCon">
            <div class="userListFilterCon">
                <template v-if="hasCheck">
                    <h5 class="userFilterHeading">
                        <span class="headingTitle">Filters Applied</span>
                        <template v-for="(type, tindex) in JSON.parse($store.state.prevUserFilter)">
                            <template v-for="(attribute, aindex) in type.child">
                                <template v-for="(value, index) in attribute.value">
                                    <div
                                        :key="`${tindex}-${aindex}-${index}`"
                                        v-if="value.checked"
                                        class="listFilterCon"
                                    >
                                        <span class="filterKey">{{ attribute.name }}:</span>
                                        <span class="filterValue">{{ value.value }}</span>
                                        <span
                                            class="filterRemoveCheck"
                                            @click="
                                                $store.state.userFilter[tindex].child[aindex].value[index].checked=false;
                                                $store.state.prevUserFilter=JSON.stringify($store.state.userFilter);
                                            "
                                        >
                                            <i class="material-icons">clear</i>
                                        </span>
                                    </div>
                                </template>
                            </template>
                        </template>
                    </h5>
                </template>
                <template v-else>
                    <h5 class="headingOne">Showing all users</h5>
                </template>
            </div>
            <div class="userListOptionCon">
                <button class="uloButton" v-if="hasCheck" @click="assignSegment=true">
                    <i class="material-icons">group_add</i>
                    <span>Save to segment</span>
                </button>
                <button class="uloButton" @click="exportCSV" v-if="userList.length>0">
                    <figure>
                        <img src="/images/icons/user/export.png" />
                    </figure>
                    <span>export</span>
                </button>
            </div>
        </div>

        <user-table-component :userList="userList" :userLoading="userLoading"></user-table-component>

        <div class="popFixedContainer popFixedCenter" v-if="assignSegment">
            <div class="userAttributePop saveToSegmentPop">
                <div class="uaBodyCon">
                    <h5 class="uaTitle">Save filter as a Segment</h5>
                    <div class="segmentTitleCon">
                        <label class="segmentTitleLabel">Segment Name:</label>
                        <input
                            class="segmentTitleInput"
                            type="text"
                            placeholder="Segment name"
                            v-model="segmentName"
                        />
                    </div>
                </div>
                <div class="uaFooterCon">
                    <button class="headerButtonTypeOne" @click="assignSegment=false">Cancel</button>
                    <button
                        class="headerButtonTypeOne"
                        @click="createSegment()"
                        :disabled="segmentList.loading"
                    >Create</button>
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
import UserListModel from "../../models/users/UserListModel";
import Axios from "axios";
import UserTableComponent from "./UserTableComponent.vue";
import SegmentListModel from "../../models/SegmentListModel";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

@Component({
    components: {
        UserTableComponent
    }
})
export default class UserListComponent extends Vue {
    private userList: Array<UserListModel> = [];
    private userLoading: boolean = false;
    private assignSegment: boolean = false;
    private segmentList: SegmentListModel = new SegmentListModel();
    private segmentName: string = "";
    private errorSegment: string = "";
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    @Watch("$store.state.projectInfo", { immediate: true })
    async initSegment() {
        if (undefined === this.$store.state.projectInfo.id) return;

        this.segmentList.setProjectId = this.$store.state.projectInfo.id;

        let loadSegment: any = await this.segmentList.loadSegment();

        if (!loadSegment["status"]) {
            this.errorSegment = loadSegment["mesg"];
        }
    }

    get generateExportLink(): boolean {
        let status = false;
        if (this.userList.length > 0) {
            for (let i of this.userList) {
                if (i.checked) {
                    status = true;
                    break;
                }
            }
        }
        return status;
    }

    get hasCheck(): boolean {
        let status = false;

        if (this.$store.state.prevUserFilter !== "") {
            for (let i of JSON.parse(this.$store.state.prevUserFilter)) {
                for (let i2 of i.child) {
                    for (let i3 of i2.value) {
                        if (!i3.checked) continue;
                        status = true;
                        break;
                    }
                    if (status) break;
                }
                if (status) break;
            }
        }

        return status;
    }

    @Watch("$store.state.prevUserFilter", { immediate: true })
    private async loadUser() {
        let filter = "";

        if (this.$store.state.userFilter.length > 0) {
            for (let i2 of this.$store.state.userFilter[0].child) {
                filter += `custom[${i2.key}][type]=${i2.type}&`;
                filter += `user[${i2.key}][key]=${i2.key}&`;
                for (let i3 of i2.value) {
                    if (!i3.checked) continue;
                    filter += `user[${i2.key}][value][]=${i3.value}&`;
                }
            }
            for (let i2 of this.$store.state.userFilter[1].child) {
                filter += `custom[${i2.key}][type]=${i2.type}&`;
                filter += `custom[${i2.key}][key]=${i2.key}&`;
                for (let i3 of i2.value) {
                    if (!i3.checked) continue;
                    filter += `custom[${i2.key}][value][]=${i3.value}&`;
                }
            }
            for (let i2 of this.$store.state.userFilter[2].child) {
                filter += `custom[${i2.key}][type]=${i2.type}&`;
                filter += `system[${i2.key}][key]=${i2.key}&`;
                for (let i3 of i2.value) {
                    if (!i3.checked) continue;
                    filter += `system[${i2.key}][value]=${i3.value}&`;
                    break;
                }
            }
        }

        this.userLoading = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/users?${filter}`,
            method: "get"
        })
            .then((res: any) => {
                this.userList = [];
                for (let i of res.data.data) {
                    this.userList.push(
                        new UserListModel(i, this.$store.state.projectInfo.id)
                    );
                }
            })
            .catch((err: any) => {});

        this.userLoading = false;
    }

    async createSegment() {
        if (this.segmentName === "") {
            this.errorSegment = "Segment name is required!";
            return;
        }

        let data = new FormData();
        data.append("name", this.segmentName);

        if (this.$store.state.prevUserFilte !== "") {
            let filters = JSON.parse(this.$store.state.prevUserFilter);

            if (filters.length > 0) {
                for (let i2 of filters[0].child) {
                    data.append(`filters[${i2.key}][key]`, i2.key);
                    for (let i3 in i2.value) {
                        if (!i2.value[i3].checked) continue;
                        data.append(
                            `filters[${i2.key}][value][${i3}]`,
                            i2.value[i3].value
                        );
                    }
                }

                for (let i2 of filters[1].child) {
                    data.append(`filters[${i2.key}][key]`, i2.key);
                    for (let i3 in i2.value) {
                        if (!i2.value[i3].checked) continue;
                        data.append(
                            `filters[${i2.key}][value][${i3}]`,
                            i2.value[i3].value
                        );
                    }
                }

                for (let i2 of filters[2].child) {
                    data.append(`filters[${i2.key}][key]`, i2.key);
                    for (let i3 in i2.value) {
                        if (!i2.value[i3].checked) continue;
                        data.append(
                            `filters[${i2.key}][value][${i3}]`,
                            i2.value[i3].value
                        );
                    }
                }
            }
        }

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/users/segments/user-filter`,
            data: data,
            method: "post"
        })
            .then(res => {
                this.assignSegment = false;
                this.segmentName = "";
            })
            .catch(err => {
                if (err.response) {
                    let mesg = this.ajaxHandler.globalHandler(
                        err,
                        "Failed to create segment!"
                    );
                    this.errorSegment = mesg;
                }
            });
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
