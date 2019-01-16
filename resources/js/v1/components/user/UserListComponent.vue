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
                                    <div :key="`${tindex}-${aindex}-${index}`" v-if="value.checked" class="listFilterCon">
                                        <span class="filterKey">{{ attribute.name }}:</span>
                                        <span class="filterValue">{{ value.value }}</span>
                                        <span class="filterRemoveCheck"
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
                <button class="uloButton">
                    <i class="material-icons">group_add</i>
                    <span>Save to segment</span>
                </button>
                <button class="uloButton">
                    <i class="material-icons">exit_to_app</i>
                    <span>export</span>
                </button>
            </div>
        </div>
        <table class="userListTable">
            <thead>
                <tr>
                    <th>
                        <i class="material-icons">check_box_outline_blank</i>
                    </th>
                    <th>
                        Name
                    </th>
                    <th>
                        Gender
                    </th>
                    <th>
                        Age
                    </th>
                    <th>
                        Last Engaged
                    </th>
                    <th>
                        Last Seen
                    </th>
                    <th>
                        Signed up
                    </th>
                    <th>
                        Session
                    </th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                <template v-if="userLoading">
                    <tr>
                        <td colspan="9">Loading...</td>
                    </tr>
                </template>
                <template v-else>
                    <tr v-for="(user, index) in userList" :key="index">
                        <td>
                            <i class="material-icons" @click="user.checked=!user.checked">{{ user.checked ? 'check_box' : 'check_box_outline_blank' }}</i>
                        </td>
                        <td>
                            {{ user.name }}
                        </td>
                        <td>
                            {{ user.gender }}
                        </td>
                        <td>
                            {{ user.age }}
                        </td>
                        <td>
                            {{ user.lastEngaged }}
                        </td>
                        <td>
                            {{ user.lastSeen }}
                        </td>
                        <td>
                            {{ user.signup }}
                        </td>
                        <td>
                            Session
                        </td>
                        <td>
                            <i class="material-icons">create</i>
                        </td>
                        <td>
                            <a :href="'https://m.me/'+user.fbid" target="_blank">
                                <img src="/images/icons/messenger.png" class="messengerIcon"/>
                            </a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Watch } from 'vue-property-decorator';
import UserListModel from '../../models/users/UserListModel';
import Axios from 'axios';

@Component
export default class UserListComponent extends Vue {
    private userLoading: boolean = false;
    private userList: Array<UserListModel> = [];

    mounted() {
    }

    get hasCheck() : boolean {
        let status = false;

        if(this.$store.state.prevUserFilter!=='') {
            for(let i of JSON.parse(this.$store.state.prevUserFilter)) {
                for(let i2 of i.child) {
                    for(let i3 of i2.value) {
                        if(!i3.checked) continue;
                        status = true;
                        break;
                    }
                    if(status) break;
                }
                if(status) break;
            }
        }

        return status;
    }

    @Watch('$store.state.prevUserFilter', { immediate: true })
    private async loadUser() {
        let filter = '';
        
        if(this.$store.state.userFilter.length>0) {
            for(let i2 of this.$store.state.userFilter[0].child) {
                filter += `user[${i2.key}][key]=${i2.key}&`;
                for(let i3 of i2.value) {
                    if(!i3.checked) continue;
                    filter += `user[${i2.key}][value][]=${i3.value}&`;
                }
            }
            for(let i2 of this.$store.state.userFilter[1].child) {
                filter += `custom[${i2.key}][key]=${i2.key}&`;
                for(let i3 of i2.value) {
                    if(!i3.checked) continue;
                    filter += `custom[${i2.key}][value][]=${i3.value}&`;
                }
            }
            for(let i2 of this.$store.state.userFilter[2].child) {
                filter += `system[${i2.key}][key]=${i2.key}&`;
                for(let i3 of i2.value) {
                    if(!i3.checked) continue;
                    filter += `system[${i2.key}][value]=${i3.value}&`;
                    break;
                }
            }
        } 

        this.userLoading = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/users?${filter}`,
            method: 'get'
        }).then((res: any) => {
            this.userList = [];
            for(let i of res.data.data) {
                this.userList.push(new UserListModel(i));
            }
        }).catch((err: any) => {

        });

        this.userLoading = false;
    }
}
</script>