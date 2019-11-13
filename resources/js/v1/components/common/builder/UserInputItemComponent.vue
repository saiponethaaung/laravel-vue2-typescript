<template>
    <li :key="index" class="userInputCon">
        <div class="userInputForm">
            <div class="userInputQuestion">
                <input
                    type="text"
                    v-model="ui.question"
                    v-on:blur="canShowError=true;ui.saveContent()"
                    placeholder="What do you want to ask"
                />
            </div>
            <div class="userInputValidation">
                <div class="userInputValidationCon">
                    <div
                        class="userInputValidationValue"
                        @click="ui.showVal=!ui.showVal;closeOtherSection(index)"
                    >
                        <span>{{ validation[ui.validation] }}</span>
                        <i class="material-icons">{{ ui.showVal ? 'expand_less' : 'expand_more' }}</i>
                    </div>
                    <ul class="userInputValidationOption" v-if="ui.showVal">
                        <li
                            v-for="(v, vindex) in validation"
                            :key="vindex"
                            @click="ui.validation=vindex;ui.showVal=false;ui.saveContent();"
                        >{{ v }}</li>
                    </ul>
                </div>
            </div>
            <div class="userInputAttribute" ref="attrTitleSuggest">
                <input
                    type="text"
                    v-model="ui.attribute"
                    v-on:blur="canShowError=true;ui.saveContent()"
                    placeholder="Required"
                    :class="{'required': canShowError && ui.attribute===''}"
                    @keyup="searchKeySuggestion($event)"
                />
                <template v-if="keySuggestion.length>0">
                    <div class="attrKeySuggestCon" ref="suggestion">
                        <ul>
                            <template v-for="(key, index) in keySuggestion">
                                <li
                                    :key="index"
                                    @click="ui.attribute=key;keySuggestion=[];ui.saveContent();"
                                >{{ key }}</li>
                            </template>
                        </ul>
                    </div>
                </template>
                <template v-else-if="keyLoading">
                    <div class="attrKeySuggestCon" ref="suggestion">
                        <ul>
                            <li>Loading...</li>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
        <div class="delIcon" @click="delItem(index)">
            <i class="material-icons">delete</i>
        </div>
        <template v-if="ui.errorMesg!==''">
            <error-component :mesg="ui.errorMesg" @closeError="ui.errorMesg=''"></error-component>
        </template>
    </li>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from "vue-property-decorator";
import UserInputItemModel from "../../../models/bots/UserInputItemModel";
import Axios, { CancelTokenSource } from "axios";
import AjaxErrorHandler from "../../../utils/AjaxErrorHandler";

@Component
export default class UserInputItemComponent extends Vue {
    @Prop({
        type: UserInputItemModel
    })
    ui!: UserInputItemModel;
    @Prop() index: any;

    private validation: Array<string> = ["Other", "Phone", "Email", "Number"];
    private canShowError: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    private keyTimeout: any = null;
    private keyLoading: boolean = false;
    private showSuggest: boolean = false;
    private keySuggestion: any[] = [];
    private keyCancelToken: CancelTokenSource = Axios.CancelToken.source();

    mounted() {
        console.log("rerender");
    }

    @Emit("closeOtherSection")
    closeOtherSection(index: any) {}

    @Emit("delItem")
    delItem(index: any) {}

    documentClick(e: any) {
        let el: any = this.$refs.attrTitleSuggest;

        let target = e.target;

        if (el !== target && !el.contains(target)) {
            this.keySuggestion = [];
            this.keyLoading = false;
            this.keyCancelToken.cancel();
            return null;
        }
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

        this.keyCancelToken.cancel();
        this.keyLoading = false;
        this.showSuggest = true;
        clearTimeout(this.keyTimeout);

        if (this.ui.attribute == "") return;

        this.keyLoading = true;
        this.keyTimeout = setTimeout(async () => {
            this.keyCancelToken = Axios.CancelToken.source();

            this.keySuggestion = [];

            let data = new FormData();
            data.append("keyword", this.ui.attribute);

            await Axios({
                url: `/api/v1/project/${this.$store.state.projectInfo.id}/attributes/serach/attribute`,
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

    created() {
        document.addEventListener("click", this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener("click", this.documentClick);
    }
}
</script>

