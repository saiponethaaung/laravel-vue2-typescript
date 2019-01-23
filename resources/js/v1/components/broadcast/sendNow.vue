<template>
    <div>
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
        <div class="outerDisplay">
            <div @click="showOption2=!showOption2">
                <div class="btnSub">
                    <span>Segment</span>
                    <span class="iconSub">
                        <i class="material-icons">
                            <template v-if="showOption2">expand_less</template>
                            <template v-else>expand_more</template>
                        </i>
                    </span>
                </div>
                <div v-show="showOption2" class="dropDownList">
                    <ul>
                        <li>1</li>
                        <li>2</li>
                        <li>3</li>
                    </ul>
                </div>
            </div>
            <div>
                <div class="btnSub" @click="showOption3=!showOption3">
                    <span>is</span>
                    <span class="iconSub">
                        <i class="material-icons">
                            <template v-if="showOption3">expand_less</template>
                            <template v-else>expand_more</template>
                        </i>
                    </span>
                </div>
                <div v-show="showOption3" class="dropDownList">
                    <ul>
                        <li>1</li>
                        <li>2</li>
                        <li>3</li>
                    </ul>
                </div>
            </div>
            <div>
                <div class="btnSub" @click="showOption4=!showOption4">
                    <span>Select segment</span>
                    <span class="iconSub">
                        <i class="material-icons">
                            <template v-if="showOption4">expand_less</template>
                            <template v-else>expand_more</template>
                        </i>
                    </span>
                </div>
                <div v-show="showOption4" class="dropDownList">
                    <ul>
                        <li>1</li>
                        <li>2</li>
                        <li>3</li>
                    </ul>
                </div>
            </div>
            <div  class="addBtn">
                <i class="material-icons iconAlign">add</i>Add More
            </div>
        </div>
        
        <div class="textAlign">
            <span>You have 4 users based on your filters. </span>
        </div>

        <div>
            <div>
                <builder-component-mock :value="[]" :section="0" class="fullWidth"></builder-component-mock>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Watch } from 'vue-property-decorator';
import BuilderComponentMock from '../common/BuilderComponentMock.vue';

@Component({
    components: {
        BuilderComponentMock
    }
})
export default class sendNow extends Vue {

    private showOption1: boolean = false;
    private showOption2: boolean = false;
    private showOption3: boolean = false;
    private showOption4: boolean = false;

}
</script>