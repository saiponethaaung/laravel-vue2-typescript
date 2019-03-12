<template>
    <li :key="index" class="userInputCon">
        <div class="userInputForm">
            <div class="userInputQuestion">
                <input
                    type="text"
                    v-model="ui.question"
                    v-on:blur="canShowError=true;ui.saveContent()"
                    placeholder="What do you want to ask"
                >
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
            <div class="userInputAttribute">
                <input
                    type="text"
                    v-model="ui.attribute"
                    v-on:blur="canShowError=true;ui.saveContent()"
                    placeholder="Required"
                    :class="{'required': canShowError && ui.attribute===''}"
                >
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

@Component
export default class UserInputItemComponent extends Vue {
    @Prop({
        type: UserInputItemModel
    })
    ui!: UserInputItemModel;
    @Prop() index: any;

    private validation: Array<string> = ["None", "Phone", "Email", "Number"];
    private canShowError: boolean = false;

    @Emit("closeOtherSection")
    closeOtherSection(index: any) {}

    @Emit("delItem")
    delItem(index: any) {}
}
</script>

