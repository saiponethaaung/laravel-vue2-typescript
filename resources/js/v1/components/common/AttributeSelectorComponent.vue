<template>
    <div class="attributeSelectorRoot">
        <div class="attributeSelectorBox">
            <div class="attrSelector attrSelOption">
                <div class="optionSpinner">
                    <spinner-drop-down-component
                        :options="attributeOptions"
                        :selectedKey="attribute.option"
                        v-model="attribute.option"
                    ></spinner-drop-down-component>
                </div>
            </div>
            <div class="attrSelector attrSelName">
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
                    <input placeholder="Attribute name" v-model="attribute.name" class="attrSelInput"/>
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
            <div class="attrSelector attrSelOption">
                <div class="optionSpinner">
                    <spinner-drop-down-component
                        :options="filterType"
                        :selectedKey="attribute.type"
                        v-model="attribute.type"
                    ></spinner-drop-down-component>
                </div>
            </div>
            <div class="attrSelector attrSelValue">
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
                    <input placeholder="Attribute value" v-model="attribute.value" class="attrSelInput"/>
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
        </div>    
        <div v-if="canCondition" class="attributeSelectorCondi">
            <spinner-drop-down-component
                :options="condiOptions"
                :selectedKey="attribute.condi"
                v-model="attribute.condi"
            ></spinner-drop-down-component>
        </div>
    </div>    
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';
import SpinnerDropDownComponent from './SpinnerDropDownComponent.vue';
import AttributeFilterModel from '../../models/AttributeFilterModel';

@Component({
    components: {
        SpinnerDropDownComponent
    }
})
export default class AttributeSelectorComponent extends Vue {
    @Prop({
        default: false
    }) canCondition!: boolean;
    @Prop() attribute!: AttributeFilterModel;

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

        if(this.attribute.option === 2) {
            res = [
                ...res,
                ...[
                    {
                        key: 3,
                        value: 'start with'
                    },
                    {
                        key: 4,
                        value: 'greater than'
                    },
                    {
                        key: 5,
                        value: 'less than'
                    }
                ]
            ];
        }

        return res;
    }
}
</script>
