<template>
    <div class="btnComponentTypeOne" ref="textBtn">
        <div class="buttonPopContent">
            <div class="buttonPopHeading">
                <p class="buttonPopInfo">If subscriber clicks</p>
                <div class="actionInfo">
                    <input type="text" v-model="button.id"/>
                </div>
            </div>
            <div class="buttonOptions">
                <div class="buttionActions">
                    <ul>
                        <li @click="button.type=0">Blocks</li>
                        <li @click="button.type=1">URL</li>
                        <li @click="button.type=2">Phone call</li>
                    </ul>
                    <div>
                        <div v-if="button.type===0">
                            block
                            <input type="text" v-model="blockKeyword"/>
                        </div>
                        <div v-if="button.type===1">
                            url
                            <input type="text" v-model="button.url"/>
                        </div>
                        <div v-if="button.type===2">
                            phone
                            <input type="text" v-model="button.phone"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue, Emit } from 'vue-property-decorator';
import { buttonContent } from '../../../configuration/interface';
import AjaxErrorHandler from '../../../utils/AjaxErrorHandler';

@Component
export default class ButtonComponent extends Vue {
    
    @Prop() button!: buttonContent;

    private blockKeyword: string = "";
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();


    @Emit('closeContent')
    closeContent(status: boolean){};

    documentClick(e: any){
        let el: any = this.$refs.textBtn;

        let target = e.target;
        if (( el !== target) && !el.contains(target)) {
          this.closeContent(true);
          return null;
        }

        this.closeContent(false);
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
