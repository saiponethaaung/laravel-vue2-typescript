<template>
    <div class="persistentMenuComponent">
        <div class="persistentRootCon">
            <div @click="showOption=true" class="persistentMenuAction">
                <span class="persistentMenuName">{{ menu.content.title ? menu.content.title : "Enter menu name" }}</span>
                <template v-if="menu.content.type!==2">
                    <template v-if="menu.content.type==0">
                        <span class="pmnSubContent">{{ menu.content.blocks.length>0 ? menu.content.blocks[0].title : '-' }}</span>
                    </template>
                    <template v-if="menu.content.type==1">
                        <span class="pmnSubContent">{{ menu.content.url }}</span>
                    </template>
                </template>
            </div>
            <template v-if="menu.content.type===2">
                <div class="pmnSubContent" @click="selectedSecond(index)">
                    <span>Edit Submenu</span>
                    <i class="material-icons">chevron_right</i>
                </div>
            </template>
        </div>
        <second-menu-option
            :menu="menu"
            v-if="showOption"
            v-on:closeContent="(status) => {
                showOption = status;
            }"
        ></second-menu-option>
        <div class="delIcon" @click="deleteMenu()">
            <i class="material-icons">delete</i>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import PersistentSecondMenu from '../../models/PersistentSecondMenu';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';
import { blockSuggestion } from '../../configuration/interface';
import Axios,{ CancelTokenSource } from 'axios';
import SecondMenuOption from './SecondMenuOption.vue';

@Component({
    components: {
        SecondMenuOption
    }
})
export default class SecondMenuComponent extends Vue {
    @Prop() menu!: PersistentSecondMenu;
    @Prop() index!: any;
    private showOption: boolean = false;

    @Emit("selected")
    private selectedSecond(index: any){}

    @Emit('deleteSecond')
    deleteMenu() {
        return this.index;
    }
}
</script>
