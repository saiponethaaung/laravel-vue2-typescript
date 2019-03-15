<template>
    <div class="attributeSelectorRoot">
        <div class="attributeSelectorBox">
            <div class="attrSelector">
                <div class="optionSpinner">
                    <spinner-drop-down-component
                        :options="attributeOptions"
                        :selectedKey="attribute.option"
                        v-model="attribute.option"
                    ></spinner-drop-down-component>
                </div>
            </div>
            <div class="attrSelector" v-if="attribute.option!==4">
                <template v-if="attribute.option === 1">
                    <div class="optionSpinner">
                        <spinner-drop-down-component
                            :options="userAttribute"
                            :selectedKey="attribute.user"
                            v-model="attribute.user"
                        ></spinner-drop-down-component>
                    </div>
                </template>
                <template v-else-if="attribute.option === 2">
                    <input
                        placeholder="Attribute name"
                        v-model="attrName"
                        class="attrSelInput withSuggestion"
                        ref="input"
                        :class="{'hideBottomRadius': showSuggest, 'required': !showSuggest && canShowErr && attribute.name===''}"
                        @keyup="searchKeySuggestion"
                    >
                    <div
                        class="attrKeySuggestCon"
                        ref="suggestion"
                        v-if="attrName!=='' && showSuggest"
                    >
                        <ul>
                            <template v-if="keyLoading">
                                <li>Loading...</li>
                            </template>
                            <template v-else>
                                <template v-if="keySuggestion.length===0">
                                    <li>Invalid attribute!</li>
                                </template>
                                <template v-else>
                                    <template v-for="(k, index) in keySuggestion">
                                        <li
                                            :key="index"
                                            @click="attribute.name=k;attrName=k;keySuggestion=[];showSuggest=false;"
                                        >{{ k }}</li>
                                    </template>
                                </template>
                            </template>
                        </ul>
                    </div>
                </template>
                <template v-else-if="attribute.option === 3">
                    <div class="optionSpinner">
                        <spinner-drop-down-component
                            :options="systemAttribute"
                            :selectedKey="attribute.system"
                            v-model="attribute.system"
                        ></spinner-drop-down-component>
                    </div>
                </template>
            </div>
            <div class="attrSelectorOption" v-if="attribute.option === 4">
                <template>
                    <div class="optionSpinner">
                        <spinner-drop-down-component
                            :options="filterType"
                            :selectedKey="attribute.type"
                            v-model="attribute.type"
                        ></spinner-drop-down-component>
                    </div>
                </template>
            </div>
            <div class="attrSelector" v-if="attribute.option === 4">
                <template>
                    <div class="optionSpinner">
                        <spinner-drop-down-component
                            :options="segmentValue"
                            :selectedKey="segment.id"
                            v-model="segment.id"
                        ></spinner-drop-down-component>
                    </div>
                </template>
            </div>
            <div class="attrSelectorOption" v-if="attribute.option!==4">
                <template>
                    <div class="optionSpinner">
                        <spinner-drop-down-component
                            :options="filterType"
                            :selectedKey="attribute.type"
                            v-model="attribute.type"
                        ></spinner-drop-down-component>
                    </div>
                </template>
            </div>
            <div class="attrSelector" v-if="attribute.option!==4">
                <template v-if="attribute.option === 1">
                    <div class="optionSpinner">
                        <spinner-drop-down-component
                            :options="userAttributeValue"
                            :selectedKey="attribute.userValue"
                            v-model="attribute.userValue"
                        ></spinner-drop-down-component>
                    </div>
                </template>
                <template v-else-if="attribute.option === 2">
                    <input
                        placeholder="Attribute value"
                        v-model="attrValue"
                        ref="valueInput"
                        class="attrSelInput withSuggestion"
                        :class="{'hideBottomRadius': showValueSuggest, 'required': !showValueSuggest && canShowValueErr && attribute.value===''}"
                        :disabled="attribute.name==''"
                        @keyup="searchValueSuggestion"
                    >
                    <div
                        class="attrKeySuggestCon"
                        ref="valueSuggestion"
                        v-if="attrValue!=='' && showValueSuggest"
                    >
                        <ul>
                            <template v-if="valueLoading">
                                <li>Loading...</li>
                            </template>
                            <template v-else>
                                <template v-if="valueSuggestion.length===0">
                                    <li>Invalid value!</li>
                                </template>
                                <template v-else>
                                    <template v-for="(k, index) in valueSuggestion">
                                        <li
                                            :key="index"
                                            @click="attribute.value=k;attrValue=k;valueSuggestion=[];showValueSuggest=false;"
                                        >{{ k }}</li>
                                    </template>
                                </template>
                            </template>
                        </ul>
                    </div>
                </template>
                <template v-else-if="attribute.option === 3">
                    <div class="optionSpinner">
                        <spinner-drop-down-component
                            :options="systemAttributeValue"
                            :selectedKey="attribute.systemValue"
                            v-model="attribute.systemValue"
                        ></spinner-drop-down-component>
                    </div>
                </template>
            </div>
            <div v-if="canCondition" class="alignFilter">
                <button
                    class="filterType"
                    :class="{'selectedCondi': attribute.condi===1}"
                    @click="attribute.condi=1"
                >and</button>
                <button
                    class="filterType"
                    :class="{'selectedCondi': attribute.condi===2}"
                    @click="attribute.condi=2"
                >or</button>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import SpinnerDropDownComponent from "./SpinnerDropDownComponent.vue";
import AttributeFilterModel from "../../models/AttributeFilterModel";
import BroadcastAttributeFilterListModel from "../../models/BroadcastAttributeFilterListModel";
import Axios, { CancelTokenSource } from "axios";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

@Component({
    components: {
        SpinnerDropDownComponent
    }
})
export default class AttributeSelectorComponent extends Vue {
    @Prop({
        default: false
    })
    canCondition!: boolean;
    @Prop() attribute!: any;

    @Prop({
        type: Boolean,
        default: false
    })
    isSegment!: boolean;

    @Prop({
        type: Array,
        default: []
    })
    segmentValue!: Array<any>;
    private attrName: string = "";
    private keyTimeout: any = null;
    private keyLoading: boolean = false;
    private canShowErr: boolean = false;
    private showSuggest: boolean = false;
    private keySuggestion: any[] = [];
    private keyCancelToken: CancelTokenSource = Axios.CancelToken.source();

    private attrValue: string = "";
    private valueTimeout: any = null;
    private valueLoading: boolean = false;
    private canShowValueErr: boolean = false;
    private showValueSuggest: boolean = false;
    private valueSuggestion: any[] = [];
    private valueCancelToken: CancelTokenSource = Axios.CancelToken.source();

    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    @Prop() segment!: any;

    @Watch("attribute.option")
    @Watch("attribute.type")
    @Watch("attribute.name")
    @Watch("attribute.value")
    @Watch("attribute.condi")
    @Watch("attribute.system")
    @Watch("attribute.systemValue")
    @Watch("attribute.user")
    @Watch("attribute.userValue")
    @Watch("segment.id")
    private updateFilter() {
        if (this.isSegment) return;
        this.attribute.updateFilter();
    }

    private showOption: boolean = false;

    private condiOptions: any = [
        {
            key: 1,
            value: "and"
        },
        {
            key: 2,
            value: "or"
        }
    ];

    private attributeOptions: any = [
        {
            key: 1,
            value: "User Attribute"
        },
        {
            key: 2,
            value: "Attribute Name"
        },
        {
            key: 3,
            value: "System Attribute"
        }
    ];

    private systemAttribute: any = [
        {
            key: 1,
            value: "Signed up"
        },
        {
            key: 2,
            value: "Last Seen"
        },
        {
            key: 3,
            value: "Last Engaged"
        }
    ];

    private userAttribute: any = [
        {
            key: 1,
            value: "Gender"
        }
    ];

    private systemAttributeValue: any = [
        {
            key: 1,
            value: "24 hrs ago"
        },
        {
            key: 2,
            value: "1 week ago"
        },
        {
            key: 3,
            value: "1 month ago"
        },
        {
            key: 4,
            value: "3 months ago"
        }
    ];

    private userAttributeValue: any = [
        {
            key: 1,
            value: "Male"
        },
        {
            key: 2,
            value: "Female"
        }
    ];

    get filterType(): Array<any> {
        let res = [
            {
                key: 1,
                value: "is not"
            },
            {
                key: 2,
                value: "is"
            }
        ];

        // if(this.attribute.option === 2) {
        //     res = [
        //         ...res,
        //         ...[
        //             {
        //                 key: 3,
        //                 value: 'start with'
        //             },
        //             {
        //                 key: 4,
        //                 value: 'greater than'
        //             },
        //             {
        //                 key: 5,
        //                 value: 'less than'
        //             }
        //         ]
        //     ];
        // }

        return res;
    }

    async searchKeySuggestion(e: any) {
        console.log(e);
        if (
            e.keyCode == 37 ||
            e.keyCode == 38 ||
            e.keyCode == 39 ||
            e.keyCode == 40 ||
            e.keyCode == 17 ||
            e.keyCode == 16 ||
            e.keyCode == 18 ||
            (e.ctrlKey && e.keyCode == 65)
        ) {
            return;
        }

        this.canShowErr = true;
        this.keyCancelToken.cancel();
        this.keyLoading = false;
        this.showSuggest = true;
        this.attribute.name = "";
        this.attrValue = "";
        this.attribute.value = "";
        this.canShowValueErr = false;
        clearTimeout(this.keyTimeout);

        if (this.attrName == "") return;

        this.keyLoading = true;
        this.keyTimeout = setTimeout(async () => {
            this.keyCancelToken = Axios.CancelToken.source();

            this.keySuggestion = [];

            let data = new FormData();
            data.append("keyword", this.attrName);

            await Axios({
                url: `/api/v1/project/${
                    this.$store.state.projectInfo.id
                }/attributes/serach/attribute`,
                data: data,
                method: "post",
                cancelToken: this.keyCancelToken.token
            })
                .then(res => {
                    this.keySuggestion = res.data.data;
                })
                .catch(err => {
                    if (err.response) {
                        this.$store.state.errorMesg.push(
                            this.ajaxHandler.globalHandler(
                                err,
                                "Failed to load attribute name suggestion!"
                            )
                        );
                    }
                });

            this.keyLoading = false;
        }, 1000);
    }

    async searchValueSuggestion(e: any) {
        if (
            e.keyCode == 37 ||
            e.keyCode == 38 ||
            e.keyCode == 39 ||
            e.keyCode == 40 ||
            e.keyCode == 17 ||
            e.keyCode == 16 ||
            e.keyCode == 18 ||
            (e.ctrlKey && e.keyCode == 65)
        ) {
            return;
        }

        this.canShowValueErr = true;
        this.valueCancelToken.cancel();
        this.showValueSuggest = true;
        this.attribute.value = "";
        await clearTimeout(this.valueTimeout);

        if (this.attrValue == "") return;

        this.valueTimeout = await setTimeout(async () => {
            this.valueLoading = true;
            this.valueCancelToken = Axios.CancelToken.source();

            this.valueSuggestion = [];

            let data = new FormData();
            data.append("attr", this.attrName);
            data.append("keyword", this.attrValue);

            await Axios({
                url: `/api/v1/project/${
                    this.$store.state.projectInfo.id
                }/attributes/serach/value`,
                data: data,
                method: "post",
                cancelToken: this.valueCancelToken.token
            })
                .then(res => {
                    this.valueSuggestion = res.data.data;
                })
                .catch(err => {
                    if (err.response) {
                        this.$store.state.errorMesg.push(
                            this.ajaxHandler.globalHandler(
                                err,
                                "Failed to load attribute value suggestion!"
                            )
                        );
                    }
                });

            this.valueLoading = false;
        }, 1000);
    }

    documentClick(e: any) {
        let el1: any = this.$refs.input;
        let el2: any = this.$refs.suggestion;
        let target = e.target;
        if (
            el1 !== target &&
            !el1.contains(target) &&
            (typeof el2 == "undefined" ||
                (typeof el2 != "undefined" &&
                    el2 !== target &&
                    !el2.contains(target)))
        ) {
            this.showSuggest = false;
        }

        let el3: any = this.$refs.valueInput;
        let el4: any = this.$refs.valueSuggestion;
        if (
            el3 !== target &&
            !el3.contains(target) &&
            (typeof el4 == "undefined" ||
                (typeof el4 !== "undefined" &&
                    el4 !== target &&
                    !el4.contains(target)))
        ) {
            this.showValueSuggest = false;
        }
    }

    mounted() {
        if (
            !this.isSegment &&
            undefined !== this.segmentValue &&
            this.segmentValue.length > 0
        ) {
            this.attributeOptions.push({
                key: 4,
                value: "Segment"
            });
        }

        this.attrName = this.attribute.name;
        this.attrValue = this.attribute.value;
    }

    created() {
        document.addEventListener("click", this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener("click", this.documentClick);
    }
}
</script>
