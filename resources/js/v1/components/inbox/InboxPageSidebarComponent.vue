<template>
    <div>
        <template v-if="undefined==$store.state.projectInfo.pageConnected">
            Loading...
        </template>
        <template v-else>
            <template v-if="$store.state.projectInfo.pageConnected">
                <div class="">
                    <div class="chatFilterList">
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

                    <div class="chatFilterAction">
                        <div>
                            <i class="material-icons">label_important</i>
                        </div>
                        <div>
                            <i class="material-icons">star_border</i>
                        </div>
                    </div>
                </div>

                <div class="">
                    
                </div>
            </template>
        </template>
    </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';

@Component
export default class InboxPageSidebarComponent extends Vue {

    private filters: any = [
        {
            key: 0,
            value: 'Live'
        },
        {
            key: 1,
            value: 'Bot'
        },
    ];

    private selectedFilter: number = 0;
    private showFilter: boolean = false;
}
</script>
