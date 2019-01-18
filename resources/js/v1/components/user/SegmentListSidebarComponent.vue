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
                <ul class="avaFilterOptionList" v-show="showSegments">
                    <template v-if="$store.state.segments.length>0">
                        <li v-for="(value, vindex) in $store.state.segments" :key="vindex" class="avafolChild" :class="{'selected': value.checked}">
                            <i class="material-icons">{{ value.checked ? 'check_box' : 'check_box_outline_blank' }}</i>
                            <span>{{ value.value }}</span>
                        </li>
                    </template>
                    <template v-else>
                        <li>There is no segements!</li>
                    </template>
                </ul>
            </li>
        </ul>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';

@Component
export default class SegmentListSidebarComponent extends Vue {
    private showFilter: boolean = false;
    private showSegments: boolean = true;
    // private filters: any = 
}
</script>