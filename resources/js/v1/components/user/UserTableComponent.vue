<template>
    <div>
        <table class="userListTable">
            <thead>
                <tr>
                    <th class="utlCheckColumn">
                        <div class="ultWrapper ultChecker" :class="{'ultChecked': allChecked}">
                            <i class="material-icons" @click="toggleAll()">{{ allChecked ? 'check_box' : 'check_box_outline_blank' }}</i>
                        </div>
                    </th>
                    <th>
                        <div class="ultWrapper">
                            Name
                        </div>
                    </th>
                    <th class="utlGenderColumn">
                        <div class="ultWrapper">
                            Gender
                        </div>
                    </th>
                    <th>
                        <div class="ultWrapper">
                            Age
                        </div>
                    </th>
                    <th class="ultDateColumn">
                        <div class="ultWrapper">
                            Last Engaged
                        </div>
                    </th>
                    <th class="ultDateColumn">
                        <div class="ultWrapper">
                            Last Seen
                        </div>
                    </th>
                    <th class="ultDateColumn">
                        <div class="ultWrapper">
                            Signed up
                        </div>
                    </th>
                    <th class="utlSessinColumn">
                        <div class="ultWrapper">
                            Session
                        </div>
                    </th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                <template v-if="userLoading">
                    <tr>
                        <td colspan="9">Loading...</td>
                    </tr>
                </template>
                <template v-else>
                    <tr v-for="(user, index) in userList" :key="index">
                        <td class="utlCheckColumn">
                            <div class="ultWrapper ultChecker" :class="{'ultChecked': user.checked}">
                                <i class="material-icons" @click="user.checked=!user.checked">{{ user.checked ? 'check_box' : 'check_box_outline_blank' }}</i>
                            </div>
                        </td>
                        <td>
                            <div class="ultWrapper">
                                {{ user.name }}
                            </div>
                        </td>
                        <td class="utlGenderColumn">
                            <div class="ultWrapper">
                                {{ user.gender }}
                            </div>
                        </td>
                        <td>
                            <div class="ultWrapper">
                                {{ user.age>0 ? user.age : "-" }}
                            </div>
                        </td>
                        <td class="ultDateColumn">
                            <div class="ultWrapper">
                                {{ user.lastEngaged }}
                            </div>
                        </td>
                        <td class="ultDateColumn">
                            <div class="ultWrapper">
                                {{ user.lastSeen }}
                            </div>
                        </td>
                        <td class="ultDateColumn">
                            <div class="ultWrapper">
                                {{ user.signup }}
                            </div>
                        </td>
                        <td class="utlSessinColumn">
                            <div class="ultWrapper">
                                Session
                            </div>
                        </td>
                        <td class="utlIconColumn">
                            <div class="ultWrapper iconCenter">
                                <i class="material-icons ultEditIcon" @click="openAttributePop(index)">create</i>
                            </div>
                        </td>
                        <td class="utlIconColumn">
                            <div class="ultWrapper iconCenter">
                                <img src="/images/icons/messenger.png" @click="enableLiveChat(user)" class="messengerIcon"/>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div class="popFixedContainer popFixedCenter" v-if="editIndex>-1">
            <div class="userAttributePop">
                <div class="uaBodyCon">
                    <h5 class="uaTitle">{{ userList[editIndex].name }}</h5>
                    <div class="uaTable">
                        <table class="attributeTable">
                            <thead>
                                <tr>
                                    <th class="attrTitle">custom attribues</th>
                                    <th class="attrValue">value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-if="userList[editIndex].isAttrLoad">
                                    <template v-if="userList[editIndex].attributes.length>0">
                                        <tr v-for="(attribute, index) in userList[editIndex].attributes" :key="index" class="attrRow">
                                            <!-- <td class="attrTitle">{{ attribute.name }}</td> -->
                                            <td class="attrTitle">
                                                <input type="text" :class="{'newAttribute': attribute.name===''}" v-model="attribute.name" v-on:blur="updateAttributeName(editIndex, index)"/>
                                            </td>
                                            <td class="attrValue">
                                                <input type="text" :class="{'newAttribute': attribute.value===''}" v-model="attribute.value"  v-on:blur="updateAttributeValue(editIndex, index)"/>
                                                <button @click="deleteAttribute(editIndex, index)" class="attrDel">
                                                    <i class="material-icons">delete</i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="2">There is no attribute!</td>
                                        </tr>
                                    </template>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td colspan="2">Loading...</td>
                                    </tr>
                                </template>
                                <tr v-if="userList[editIndex].creating>0">
                                    <td colspan="2">Creating...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="uaTable">
                        <table class="attributeTable">
                            <thead>
                                <tr>
                                    <th class="attrTitle">system attribues</th>
                                    <th class="attrValue">value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="attrTitle">Gender</td>
                                    <td class="attrValue">{{ userList[editIndex].gender }}</td>
                                </tr>
                                <tr>
                                    <td class="attrTitle">Last Engaged</td>
                                    <td class="attrValue">{{ userList[editIndex].lastEngaged }}</td>
                                </tr>
                                <tr>
                                    <td class="attrTitle">Last Seen</td>
                                    <td class="attrValue">{{ userList[editIndex].lastSeen }}</td>
                                </tr>
                                <tr>
                                    <td class="attrTitle">Signed up</td>
                                    <td class="attrValue">{{ userList[editIndex].signup }}</td>
                                </tr>
                                <tr>
                                    <td class="attrTitle">Session</td>
                                    <td class="attrValue">1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="uaFooterCon">
                    <button class="headerButtonTypeOne" @click="createNewAttribute(editIndex)">+ Add</button>
                    <button class="headerButtonTypeOne" @click="editIndex=-1">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import UserListModel from '../../models/users/UserListModel';

@Component
export default class UserTableComponent extends Vue{
    @Prop({default: []}) userList!: Array<UserListModel>;
    @Prop({default: false}) userLoading!: boolean;

    private editIndex: number = -1;

    get allChecked() : boolean{
        let status = false;
        if(this.userList.length>0) {
            let checked = 0;
            for(let i of this.userList) {
                if(i.checked) checked++;
            }

            status = this.userList.length===checked;
        }
        return status;
    }

    private toggleAll() {
        let status = !this.allChecked;

        for(let i in this.userList) {
            this.userList[i].checked = status;
        }
    }
    
    private async openAttributePop(index: number) {
        this.editIndex = index;

        // Load user attribute if it's not yet loaded
        if(!this.userList[index].isAttrLoad) {
            let loadAttribute = await this.userList[index].loadAttribute();
            // alert an error if the process failed
            if(!loadAttribute.status) {
                alert(loadAttribute.mesg);
            }
        }
    }

    private async updateAttributeName(user: number, attribute: number) {
        let update = await this.userList[user].attributes[attribute].updateAttributeName();
        
        if(!update['status']) {
            alert(update['mesg']);
        }
    }

    private async updateAttributeValue(user: number, attribute: number) {
        let update = await this.userList[user].attributes[attribute].updateAttributeValue();
        
        if(!update['status']) {
            alert(update['mesg']);
        }
    }

    private async createNewAttribute(user: number) {
        let create = await this.userList[user].createAttribute();

        if(!create['status']) {
            alert(create['mesg']);
        }
    }

    private async deleteAttribute(user: number, attribute: number) {
        if(confirm("Are you sure you want to delete this attribute?")) {
            let create = await this.userList[user].deleteAttribute(attribute);

            if(!create['status']) {
                alert(create['mesg']);
            }
        }
    }

    private async enableLiveChat(user: UserListModel) {
        let liveChat = await user.enabledLiveChat();

        if(!liveChat['status']) {
            alert(liveChat['mesg']);
            return;
        }

        this.$router.push({name: 'project.inbox'});
    }
}
</script>
