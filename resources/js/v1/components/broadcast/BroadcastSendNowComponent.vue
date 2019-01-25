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
            <!-- <div  ref="spinnerDropDown" @click="showOption2=!showOption2">Segment</div>
            <ul class="sddList" v-if="showOption && options.length>1">
                <li v-for="(attributeOption, index) in attributeOptions" :key="index" @click="selectNewOption(attributeOption.key)">{{ attributeOption.value }}</li>
            </ul> -->
            <!-- <template>
                <div  ref="spinnerDropDown" @click="showOption2=!showOption2">Value</div>
                <ul class="sddList">
                    <li v-for="(attributeOption, index) in attributeOptions" :key="index">{{ attributeOption.value }}</li>
                </ul>
            </template> -->
            <div>
                <div class="btnOption" @click="showOption3=!showOption3">
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

        <div class="outerDisplay">
            <div @click="showOption5=!showOption5">
                <div class="btnSub">
                    <span>Attributes</span>
                    <span class="iconSub">
                        <i class="material-icons">
                            <template v-if="showOption5">expand_less</template>
                            <template v-else>expand_more</template>
                        </i>
                    </span>
                </div>
                <div v-show="showOption5" class="dropDownList">
                    <ul>
                        <li>1</li>
                        <li>2</li>
                        <li>3</li>
                    </ul>
                </div>
            </div>
            <div @click="showOption6=!showOption6">
                <div class="btnSub">
                    <span>{{ attributeOptions[selected].value }}</span>
                    <span class="iconSub">
                        <i class="material-icons">
                            <template v-if="showOption6">expand_less</template>
                            <template v-else>expand_more</template>
                        </i>
                    </span>
                </div>
                <div v-show="showOption6" class="dropDownList">
                    <ul>
                        <li v-for="(attributeOption, index) in attributeOptions" :key="index">{{attributeOption.value}}</li>
                    </ul>
                </div>
            </div>
            <div>
                <div class="btnOption" @click="showOption7=!showOption7">
                    <span>is</span>
                    <span class="iconSub">
                        <i class="material-icons">
                            <template v-if="showOption7">expand_less</template>
                            <template v-else>expand_more</template>
                        </i>
                    </span>
                </div>
                <div v-show="showOption7" class="dropDownList">
                    <ul>
                        <li v-for="(type, index) in filterType" :key="index">{{type.value}}</li>
                    </ul>
                </div>
            </div>
            <div>
                <div class="btnSub" @click="showOption8=!showOption8">
                    <span>Select value</span>
                    <span class="iconSub">
                        <i class="material-icons">
                            <template v-if="showOption8">expand_less</template>
                            <template v-else>expand_more</template>
                        </i>
                    </span>
                </div>
                <div v-show="showOption8" class="dropDownList">
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
import { Component, Prop, Emit, Watch } from 'vue-property-decorator';
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
    private showOption2: boolean = false;
    private showOption3: boolean = false;
    private showOption4: boolean = false;
    private showOption5: boolean = false;
    private showOption6: boolean = false;
    private showOption7: boolean = false;
    private showOption8: boolean = false;
    private createSegment: boolean = false;

    private filterSegment: AttributeFilterListModel = new AttributeFilterListModel(false, this.$store.state.projectInfo.id, []);

    @Prop({
            default: false
        }) canCondition!: boolean;
    @Prop() attribute!: AttributeFilterModel;

    @Prop() options!: Array<any>;
    @Prop({default: -1}) selectedKey!: number;

    get selected(): number {
        if(this.selectedKey === -1) return 0;

        let index: any = 0;

        for(let i in this.options) {
            if(this.options[i].key!==this.selectedKey) continue;
            index = i;
            break;
        }

        return index;
    }

    private condiOptions: any = [
        {
            key: 1,
            value: 'and'
        },
        {
            key: 2,
            value: 'or'
        }
    ];

    private attributeOptions: any = [
        {
            key: 1,
            value: 'User Attribute'
        },
        {
            key: 2,
            value: 'Attribute',
        },
        {
            key: 3,
            value: 'System Attribute'
        }
    ];

    private systemAttribute: any = [
        {
            key: 1,
            value: "Signed up",
        },
        {
            key: 2,
            value: "Last Seen",
        },
        {
            key: 3,
            value: "Last Engaged",
        }
    ];

    private userAttribute: any = [
        {
            key: 1,
            value: "Gender",
        },
    ];

    private systemAttributeValue: any = [
        {
            key: 1,
            value: "24 hrs ago",
        },
        {
            key: 2,
            value: "1 week ago",
        },
        {
            key: 3,
            value: "1 month ago",
        },
        {
            key: 4,
            value: "3 months ago",
        },
    ];

    private userAttributeValue: any = [
        {
            key: 1,
            value: "Male",
        },
        {
            key: 2,
            value: "Female",
        }
    ];

    get filterType() : Array<any> {
        let res = [
            {
                key: 1,
                value: 'is not'
            },
            {
                key: 2,
                value: 'is'
            }
        ];

        return res;
    }

    @Emit('input')
    selectNewOption(key: number) {
        return key;
    }

    documentClick(e: any){
        let el: any = this.$refs.spinnerDropDown;
        let target = e.target;
        if (( el !== target) && !el.contains(target)) {
            this.showOption2 = false;
        }
    }

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