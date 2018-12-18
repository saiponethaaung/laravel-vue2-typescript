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
                <p class="userInputDesc">Ask bot users questions and save their responses to user attributes. You can then utilize user attributes in broadcasting user filters.</p>
                <div>
                    <ul class="userInputList" ref="dropdownMenu">
                        <li class="userInputHeading">
                            <div class="userInputForm">
                                <div class="userInputQuestion">Message to user *</div>
                                <div class="userInputValidation">Validation</div>
                                <div class="userInputAttribute">Save answer to attribute *</div>
                            </div>
                        </li>
                        <li v-for="(ui, index) in content.item" :key="index" class="userInputCon">
                            <div class="userInputForm">
                                <div class="userInputQuestion">
                                    <input type="text" v-model="ui.question" v-on:blur="ui.saveContent()"/>
                                </div>
                                <div class="userInputValidation">
                                    <div class="userInputValidationCon">
                                        <div class="userInputValidationValue" @click="ui.showVal=!ui.showVal;closeOtherSection(index)">{{ validation[ui.validation] }}</div>
                                        <ul class="userInputValidationOption" v-if="ui.showVal">
                                            <li v-for="(v, vindex) in validation" :key="vindex" @click="ui.validation=vindex;ui.showVal=false;ui.saveContent();">{{ v }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="userInputAttribute">
                                    <input type="text" v-model="ui.attribute" v-on:blur="ui.saveContent()"/>
                                </div>
                            </div>
                            <div class="delIcon" @click="content.delItem(index)">
                                <i class="material-icons">delete</i>
                            </div>
                        </li>
                    </ul>
                    <div>
                        <template v-if="content.isCreating">
                            Creating...
                        </template>
                        <template v-else>
                            <button @click="createNewUserInput()">+ Add Fields</button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import UserInputContentModel from '../../../models/bots/UserInputContentModel';

@Component
export default class UserInputComponent extends Vue {
    @Prop({
        type: UserInputContentModel,
    }) content!: UserInputContentModel;

    private validation: Array<string> = [
        'None',
        'Phone',
        'Email',
        'Number' 
    ];

    async createNewUserInput() {
        this.content.createUserInpt();
    }

    documentClick(e: any){
        let el: any = this.$refs.dropdownMenu;
        let target = e.target;
        if (( el !== target) && !el.contains(target)) {
          this.closeOtherSection(-1);
        }
    }

    closeOtherSection(index: any) {
        for(let i in this.content.item) {
            if(i==index) continue;
            this.content.item[i].showVal = false;
        }
    }

    created() {
      document.addEventListener('click', this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener('click', this.documentClick);
    }
}
</script>
