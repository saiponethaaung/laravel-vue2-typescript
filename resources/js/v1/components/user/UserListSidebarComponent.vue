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
            <li v-for="(type, index) in $store.state.userFilter" :key="index" class="avaFilterListType">
                <h5 class="avaFilterListTypeHeading">{{ type.name }}</h5>
                <ul class="avaFilterList">
                    <li v-for="(attribute, aindex) in type.child" :key="aindex" class="avaFilterListItem">
                        <h5 class="aflHeading">
                            <i class="material-icons" @click="attribute.open=!attribute.open">{{ attribute.open ? 'expand_less' : 'expand_more'}}</i>
                            {{ attribute.name }}
                        </h5>
                        <ul class="avaFilterOptionList" v-show="attribute.open">
                            <li v-for="(value, vindex) in attribute.value" :key="vindex" class="avafolChild">
                                <i class="material-icons" @click="value.checked=!value.checked">{{ value.checked ? 'check_box' : 'check_box_outline_blank' }}</i>
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
import { Component, Vue } from 'vue-property-decorator';

@Component
export default class UserListSidebarComponent extends Vue {
    private showFilter: boolean = false;
    // private filters: any = 
}
</script>