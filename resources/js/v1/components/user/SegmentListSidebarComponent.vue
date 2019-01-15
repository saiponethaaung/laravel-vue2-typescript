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
    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';

@Component
export default class SegmentListSidebarComponent extends Vue {
    private showFilter: boolean = false;
    // private filters: any = 
}
</script>