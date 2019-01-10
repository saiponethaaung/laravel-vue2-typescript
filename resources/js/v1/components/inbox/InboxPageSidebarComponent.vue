<template>
    <div>
        <template v-if="undefined==$store.state.projectInfo.pageConnected">
            Loading...
        </template>
        <template v-else>
            <template v-if="$store.state.projectInfo.pageConnected">
                <div class="chatSbHeaderOption">
                    <div class="chatFilterList float-left">
                        <div class="inboxOptionTitle">Inbox:</div>
                        <div class="inboxOptionSelector">
                            <div class="inboxSelectedOption">
                                <span class="inboxSelectedOptionValue">{{ filters[selectedFilter].value }}</span>
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
                            <div v-if="showFilter">
                                <ul>
                                    <li v-for="(filter, index) in filters" :key="index" @click="selectedFilter=index;showFilter=false">
                                        {{ filter.value }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="chatFilterAction float-right">
                        <div>
                            <img :src="showUrgent ? '/images/icons/urgent_active.png' : '/images/icons/urgent.png' "/>
                        </div>
                        <div>
                            <i class="material-icons">star_border</i>
                        </div>
                    </div>
                </div>

                <div class="availableUserList">
                    <template v-for="(user, index) in $store.state.inboxList">
                        <div v-if="user.live_chat==filters[selectedFilter].state" :key="index" class="userBriefCon" :class="{'selected': $store.state.selectedInbox===index}">
                            <figure class="userBriefImageCon" @click="selectInbox(index)">
                                <img :src="user.profile_pic ? user.profile_pic : '/images/sample/logo.png'" class="userBriefImage"/>
                            </figure>
                            <div class="userBriefInfoCon">
                                <div class="userBriefContentCon" @click="selectInbox(index)">
                                    <div class="userBriefName">{{ `${user.first_name} ${user.last_name}` }}</div>
                                    <div class="userBriefLastMesg">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla quam velit, vulputate eu pharetra nec, mattis ac neque.</div>
                                </div>
                                <div class="userBriefActionCon">
                                    <div v-on:click="urgentStatus(index)">
                                        <img :src="user.urgent ? '/images/icons/urgent_active.png' : '/images/icons/urgent.png' "/>
                                    </div>
                                    <div>
                                        <i class="material-icons">star_border</i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-if="loadingInbox">Loading...</template>
                </div>
            </template>
        </template>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Watch } from 'vue-property-decorator';
import Axios,{ CancelTokenSource } from 'axios';

@Component
export default class InboxPageSidebarComponent extends Vue {
    private filters: any = [
        {
            key: 0,
            state: 1,
            value: 'Live'
        },
        {
            key: 1,
            state: 0,
            value: 'Bot'
        },
    ];

    private pageId: any = "";
    private selectedFilter: number = 0;
    private showFilter: boolean = false;
    private loadingInbox: boolean = false;
    private showUrgent: boolean = false;
    private loadInboxToken: CancelTokenSource = Axios.CancelToken.source();

    mounted() {
        this.$store.commit('updateSelectedInbox', {
            selected: -1
        });
        this.$store.commit('updateInboxList', {
            inbox: []
        });
    }

    private selectInbox(index: number) {
        this.$store.commit('updateSelectedInbox', {
            selected: index
        });
    }

    @Watch('$store.state.projectInfo', { immediate: true, deep: true })
    loadUserEvent() {

        if(undefined==this.$store.state.projectInfo.pageId || this.pageId===this.$store.state.projectInfo.pageId) return;

        if(this.pageId!==this.$store.state.projectInfo.pageId) {
            this.pageId = this.$store.state.projectInfo.pageId;
        }

        this.loadUserList();

    }
    
    async loadUserList() {
        this.loadingInbox = true;
        this.loadInboxToken.cancel();
        this.loadInboxToken = Axios.CancelToken.source();

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user`,
            cancelToken: this.loadInboxToken.token
        }).then((res: any) => {
            console.log("inbox res", res.data);
            var inboxList = res.data.data;
            this.$store.commit('updateInboxList', {
                inbox: inboxList
            });
        });

        this.loadingInbox = false;
    }

    async urgentStatus(index: number) {
        var status = !this.$store.state.inboxList[index].urgent;

        var data = new FormData();
        data.append('status', status.toString());

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${this.$store.state.inboxList[index].id}/urgent`,
            data: data,
            method: 'post'
        }).then((res) => {
            this.$store.commit('updateInboxUrgentStatus', {
                index: index,
                status: status
            });
        }).catch((err) => {

        });
    }
}
</script>
