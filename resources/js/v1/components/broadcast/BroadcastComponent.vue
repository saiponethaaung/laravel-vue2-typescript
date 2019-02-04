<template>
    <div class="userListRoot">
        <div class="userListControlCon">
            <div class="userListFilterCon">
                <h5 class="headingOne">Showing all users</h5>
            </div>
        </div>
        <div>
            <ul class="broadcastHistoryList">
                <li class="broadcastHistoryHeading">
                    <div class="bchColumn bcDate">
                        <b>Sent Date</b>
                    </div>
                    <div class="bchColumn bcType">
                        <b>Type</b>
                    </div>
                    <div class="bchColumn bcContent">
                        <b>Content</b>
                    </div>
                    <div class="bchColumn bcCounts bcBlock">
                        <b>Blocked</b>
                        <i class="material-icons">arrow_drop_down</i>
                    </div>
                    <div class="bchColumn bcCounts bcSent">
                        <b>Sent</b>
                        <i class="material-icons">arrow_drop_down</i>
                    </div>
                    <div class="bchColumn bcCounts bcRead">
                        <b>Read</b>
                        <i class="material-icons">arrow_drop_down</i>
                    </div>
                    <div class="bchColumn bcCounts bcClick">
                        <b>Clicked</b>
                        <i class="material-icons">arrow_drop_down</i>
                    </div>
                    <!-- <div>
                        <i class="material-icons">
                            error_outline
                        </i>
                    </div> -->
                </li>
                <template v-for="(broadcast, index) in 3">
                    <li class="broadcastHistory" :key="index">
                        <div class="bchColumn bcDate">
                            <div class="bchIcon">
                                <template v-if="broadcast===1">
                                    <i class="material-icons">send</i>
                                </template>
                                <template v-if="broadcast===2">
                                    <figure>
                                        <img src="/images/icons/broadcast/trigger.png"/>
                                    </figure>
                                </template>
                                <template v-if="broadcast===3">
                                    <figure>
                                        <img src="/images/icons/broadcast/schedule.png"/>
                                    </figure>
                                </template>
                            </div>
                            <div class="bchTimestamp">
                                <span class="bchDate">Dec 20, 2018</span>
                                <span class="bchTime">10:00 AM</span>
                            </div>
                        </div>
                        <div class="bchColumn bcType">
                            <span>Subscription</span>
                        </div>
                        <div class="bchColumn bcContent">
                            <span>Thank you for...</span>
                        </div>
                        <div class="bchColumn bcCounts bcBlock">
                            <div class="bchPercentage">
                                <b>2%</b>
                                <span>2/100</span>
                            </div>
                        </div>
                        <div class="bchColumn bcCounts bcSent">
                            <div class="bchPercentage">
                                <b>98%</b>
                                <span>98/100</span>
                            </div>
                        </div>
                        <div class="bchColumn bcCounts bcRead">
                            <div class="bchPercentage">
                                <b>98%</b>
                                <span>98/100</span>
                            </div>
                        </div>
                        <div class="bchColumn bcCounts bcClick">
                            <div class="bchPercentage">
                                <b>98%</b>
                                <span>98/100</span>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';

@Component
export default class BroadcastComponent extends Vue {
    mounted() {

    }
}
</script>