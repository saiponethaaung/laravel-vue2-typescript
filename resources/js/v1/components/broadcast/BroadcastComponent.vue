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
                        <span>Sent Date</span>
                    </div>
                    <div class="bchColumn bcType">
                        <span>Type</span>
                    </div>
                    <div class="bchColumn bcContent">
                        <span>Content</span>
                    </div>
                    <div class="bchColumn bcCounts bcBlock">
                        <span>Blocked</span>
                        <i class="material-icons">arrow_drop_down</i>
                    </div>
                    <div class="bchColumn bcCounts bcSent">
                        <span>Sent</span>
                        <i class="material-icons">arrow_drop_down</i>
                    </div>
                    <div class="bchColumn bcCounts bcRead">
                        <span>Read</span>
                        <i class="material-icons">arrow_drop_down</i>
                    </div>
                    <div class="bchColumn bcCounts bcClick">
                        <span>Clicked</span>
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
                            <span>Sent Date</span>
                        </div>
                        <div class="bchColumn bcType">
                            <span>Type</span>
                        </div>
                        <div class="bchColumn bcContent">
                            <span>Content</span>
                        </div>
                        <div class="bchColumn bcCounts bcBlock">
                            <span>Blocked</span>
                            <i class="material-icons">arrow_drop_down</i>
                        </div>
                        <div class="bchColumn bcCounts bcSent">
                            <span>Sent</span>
                            <i class="material-icons">arrow_drop_down</i>
                        </div>
                        <div class="bchColumn bcCounts bcRead">
                            <span>Read</span>
                            <i class="material-icons">arrow_drop_down</i>
                        </div>
                        <div class="bchColumn bcCounts bcClick">
                            <span>Clicked</span>
                            <i class="material-icons">arrow_drop_down</i>
                        </div>
                        <!-- <div>
                            <i class="material-icons">
                                error_outline
                            </i>
                        </div> -->
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