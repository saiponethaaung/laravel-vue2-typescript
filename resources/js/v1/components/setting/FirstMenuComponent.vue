<template>
    <div style="position: relative">
        <div>
            <div @click="showOption=true">{{ menu.content.title ? menu.content.title : "Enter menu name" }}</div>
            <template v-if="menu.content.type===2">
                <div>
                    Edit Submenu >
                </div>
            </template>
        </div>
        <first-menu-option
            :menu="menu"
            v-if="showOption"
            v-on:closeContent="(status) => {
                showOption = status;
            }"
        ></first-menu-option>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import PersistentMenu from '../../models/PersistentMenu';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';
import { blockSuggestion } from '../../configuration/interface';
import Axios,{ CancelTokenSource } from 'axios';
import FirstMenuOption from './FirstMenuOption.vue';

@Component({
    components: {
        FirstMenuOption
    }
})
export default class FirstMenuComponent extends Vue {
    @Prop() menu!: PersistentMenu;
    private showOption: boolean = false;
}
</script>
