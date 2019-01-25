<template>
    <div>
        <div class="upperDisplay">
            <div class="outerDisplay">
                <div @click="showOption1=!showOption1">
                    <div class="btnSub">
                        <span>Choose Message Type</span>
                        <span class="iconSub">
                            <i class="material-icons">
                                <template v-if="showOption1">expand_less</template>
                                <template v-else>expand_more</template>
                            </i>
                        </span>
                    </div>
                    <div v-show="showOption1" class="dropDownList">
                        <ul>
                            <li>1</li>
                            <li>2</li>
                            <li>3</li>
                        </ul>
                    </div>
                </div>
                <div class="label">
                    <span>Non-promo message under the News, Productivity, and Personal Trackers categories described in the Messenger Platform's subscription messaging policy.</span>
                    <span class="link">subscription messaging policy.</span>
                </div>
            </div>

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

            <div class="textAlign">
                <span>You have 4 users based on your filters. </span>
            </div>
        </div>

        <div>
            <div>
                <builder-component-mock :value="[]" :section="0" class="fullWidth"></builder-component-mock>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import BuilderComponentMock from '../common/BuilderComponentMock.vue';
import AttributeFilterListModel from '../../models/AttributeFilterListModel';
import AttributeFilterModel from '../../models/AttributeFilterModel';

@Component({
    components: {
        BuilderComponentMock
    }
})
export default class BroadcastSendNowComponent extends Vue {

    private showOption1: boolean = false;

    private filterSegment: AttributeFilterListModel = new AttributeFilterListModel(false, this.$store.state.projectInfo.id, []);

    mounted() {
        this.addNewFitler();
    }

    private addNewFitler() {
        this.filterSegment.createNewAttributeFilter();
    }

}
</script>