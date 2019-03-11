<template>
    <div class="componentTypeOne">
        <div class="userInputRootContainer">
            <div class="userInputInnerContainer">
                <div class="userInputHeading">
                    <div class="uihIcon">
                        <i class="material-icons">textsms</i>
                    </div>
                    <span class="uihText">User Input</span>
                </div>
                <p
                    class="userInputDesc"
                >Ask bot users questions and save their responses to user attributes. You can then utilize user attributes in broadcasting user filters.</p>
                <div>
                    <ul class="userInputList" ref="dropdownMenu">
                        <li class="userInputHeading">
                            <div class="userInputForm">
                                <div class="userInputQuestion">Message to user *</div>
                                <div class="userInputValidation">Validation</div>
                                <div class="userInputAttribute">Save answer to attribute *</div>
                            </div>
                        </li>
                        <template v-for="(ui, index) in content.item">
                            <user-input-item-component
                                :index="index"
                                :ui="ui"
                                :key="index"
                                @closeOtherSection="closeOtherSection"
                                @delItem="delItem"
                            ></user-input-item-component>
                        </template>
                    </ul>
                    <div>
                        <template v-if="content.isCreating">Creating...</template>
                        <template v-else>
                            <button class="addMoreRule" @click="createNewUserInput()">
                                <i class="material-icons">add</i>
                                <span>Add Fields</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from "vue-property-decorator";
import UserInputContentModel from "../../../models/bots/UserInputContentModel";
import UserInputItemComponent from "./UserInputItemComponent.vue";

@Component({
    components: {
        UserInputItemComponent
    }
})
export default class UserInputComponent extends Vue {
    @Prop({
        type: UserInputContentModel
    })
    content!: UserInputContentModel;

    async createNewUserInput() {
        this.content.createUserInpt();
    }

    documentClick(e: any) {
        let el: any = this.$refs.dropdownMenu;
        let target = e.target;
        if (el !== target && !el.contains(target)) {
            this.closeOtherSection(-1);
        }
    }

    delItem(index: any) {
        this.content.delItem(index);
    }

    closeOtherSection(index: any) {
        for (let i in this.content.item) {
            if (i == index) continue;
            this.content.item[i].showVal = false;
        }
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
