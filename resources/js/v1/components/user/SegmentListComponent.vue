<template>
    <div class="userListRoot">
        <div class="userListControlCon">
            <div class="userListFilterCon">
                <h5 class="userFilterHeading">
                    <span class="headingTitle">Member List</span>
                    <!-- <template v-for="(type, tindex) in JSON.parse($store.state.prevUserFilter)">
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
                    </template> -->
                </h5>
            </div>
            <div class="userListOptionCon">
                <button class="uloButton" @click="createSegment=true">
                    <i class="material-icons">group_add</i>
                    <span>Create Segment</span>
                </button>
                <button class="uloButton">
                    <i class="material-icons">exit_to_app</i>
                    <span>export</span>
                </button>
            </div>
            <template v-if="$store.state.selectedSegment>-1">
                <div>Segment selected</div>
            </template>
            <template v-else>
                <div>Select a segment</div>
            </template>
        </div>

        <div class="popFixedContainer popFixedCenter" v-if="createSegment">
            <div class="userAttributePop filterAttribute">
                <div class="uaBodyCon">
                    <h5 class="uaTitle">Create new segment</h5>
                    <input type="text" v-model="filterSegment.name"/>
                    <div class="attributeSelectorList">
                        <template v-for="(attribute, index) in filterSegment.attributes">
                            <div class="attributeSelector" :key="index">
                                <attribute-selector-component
                                    :attribute="attribute"
                                    :canCondition="(filterSegment.attributes.length-1)>index"
                                ></attribute-selector-component>
                                <button v-if="filterSegment.attributes.length>1" class="deleteAttribute" @click="filterSegment.attributes.splice(index, 1);">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </template>
                        <div @click="addNewFitler()" class="addMoreFilterButton">
                            <i class="material-icons">add</i>
                        </div>
                    </div>
                </div>
                <div class="uaFooterCon">
                    <button class="headerButtonTypeOne" @click="createSegment=false">Cancel</button>
                    <button class="headerButtonTypeOne" @click="createNewSegment()">Create</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import AttributeSelectorComponent from '../common/AttributeSelectorComponent.vue';
import AttributeFilterListModel from '../../models/AttributeFilterListModel';

@Component({
    components: {
        AttributeSelectorComponent
    }
})
export default class UserSegmentListComponent extends Vue {
    private createSegment: boolean = false;
    private filterSegment: AttributeFilterListModel = new AttributeFilterListModel(false, this.$store.state.projectInfo.id, []);
    
    mounted() {
        this.addNewFitler();
    }

    private addNewFitler() {
        this.filterSegment.createNewAttributeFilter();
    }

    private async createNewSegment() {
        let createSegment = await this.filterSegment.createSegment();

        if(!createSegment['status']) {
            alert(createSegment['mesg']);
            return;
        }

        this.createSegment = false;
    }
}
</script>
