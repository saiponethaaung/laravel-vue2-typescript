<template>
    <div>
        <div class="chatSbHeaderOption">
            <div class="chatFilterList float-left">
                <div class="inboxOptionTitle">User:</div>
                <div class="inboxOptionSelector">
                    <div class="inboxSelectedOption">
                        <span class="inboxSelectedOptionValue">Accounts</span>
                        <span class="inboxFilterOptionIcon">
                            <i class="material-icons" @click="showFilter=!showFilter">
                                <template v-if="showFilter">arrow_drop_up</template>
                                <template v-else>arrow_drop_down</template>
                            </i>
                        </span>
                    </div>
                    <div class="inboxOptionsCon" v-if="showFilter">
                        <ul>
                            <li>
                                <router-link :to="{'name': 'project.users'}">Accounts</router-link>
                            </li>
                            <li>
                                <router-link :to="{'name': 'project.users.segments'}">Segments</router-link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <template
                v-if="hasCheck || ($store.state.prevUserFilter!=='' && $store.state.prevUserFilter!=JSON.stringify($store.state.userFilter))"
            >
                <template
                    v-if="$store.state.prevUserFilter!=JSON.stringify($store.state.userFilter)"
                >
                    <span @click="applyFilters()" class="filterButton">Apply Filters</span>
                </template>
                <template v-else>
                    <span @click="removeAllChecked()" class="filterButton">Remove Filters</span>
                </template>
            </template>
        </div>

        <ul class="avaFilterList">
            <li
                v-for="(type, index) in $store.state.userFilter"
                :key="index"
                class="avaFilterListType"
            >
                <h5 class="avaFilterListTypeHeading">{{ type.name }}</h5>
                <ul class="avaFilterList">
                    <li
                        v-for="(attribute, aindex) in type.child"
                        :key="aindex"
                        class="avaFilterListItem"
                        :class="{'hasChecked': checkHasChecked(index, aindex)}"
                    >
                        <h5 class="aflHeading" @click="attribute.open=!attribute.open">
                            <i
                                class="material-icons"
                            >{{ attribute.open ? 'expand_less' : 'expand_more'}}</i>
                            <span>{{ attribute.name }}</span>
                        </h5>
                        <ul class="avaFilterOptionList" v-show="attribute.open">
                            <li
                                v-for="(value, vindex) in attribute.value"
                                :key="vindex"
                                class="avafolChild"
                                @click="checkFilter(index, aindex, vindex)"
                                :class="{'selected': value.checked}"
                            >
                                <i
                                    class="material-icons"
                                >{{ value.checked ? 'check_box' : 'check_box_outline_blank' }}</i>
                                <span>{{ value.value }}</span>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import Axios from "axios";

@Component
export default class UserListSidebarComponent extends Vue {
    private showFilter: boolean = false;

    mounted() {
        this.loadUserFilter();
    }

    get hasCheck(): boolean {
        let status = false;

        for (let i of this.$store.state.userFilter) {
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

        return status;
    }

    removeAllChecked() {
        let status = false;

        for (let i in this.$store.state.userFilter) {
            for (let i2 in this.$store.state.userFilter[i].child) {
                for (let i3 in this.$store.state.userFilter[i].child[i2]
                    .value) {
                    this.$store.state.userFilter[i].child[i2].value[
                        i3
                    ].checked = false;
                }
            }
        }

        this.$store.state.prevUserFilter = "";
    }

    applyFilters() {
        this.$store.state.prevUserFilter = JSON.stringify(
            this.$store.state.userFilter
        );
    }

    async loadUserFilter() {
        if (undefined === this.$store.state.projectInfo.id) return;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/users/attributes`,
            method: "get"
        })
            .then(res => {
                let data: any = [];
                for (let i of res.data.data) {
                    let parsed: any = i;
                    for (let i2 in parsed.child) {
                        parsed.child[i2].open = false;
                        for (let i3 in parsed.child[i2].value) {
                            parsed.child[i2].value[i3].checked = false;
                        }
                    }
                    data.push(parsed);
                }
                console.log("res", data);
                this.$store.state.userFilter = data;
            })
            .catch(err => {});
    }

    private checkHasChecked(index: number, index2: number) {
        let status = false;

        for (let i of this.$store.state.userFilter[index].child[index2].value) {
            if (!i.checked) continue;
            status = true;
            break;
        }

        return status;
    }

    private checkFilter(tindex: number, aindex: number, index: any) {
        if (
            this.$store.state.userFilter[tindex].single ||
            this.$store.state.userFilter[tindex].child[aindex].type == 1
        ) {
            if (
                !this.$store.state.userFilter[tindex].child[aindex].value[index]
                    .checked
            ) {
                for (let i in this.$store.state.userFilter[tindex].child[aindex]
                    .value) {
                    if (i === index) continue;
                    this.$store.state.userFilter[tindex].child[aindex].value[
                        i
                    ].checked = false;
                }
            }
        }

        this.$store.state.userFilter[tindex].child[aindex].value[
            index
        ].checked = !this.$store.state.userFilter[tindex].child[aindex].value[
            index
        ].checked;
    }
}
</script>
