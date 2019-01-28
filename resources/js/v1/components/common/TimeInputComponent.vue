<template>
    <input v-model="content" @keypress.prevent="validateContent" ref="timeInput" placeholder="HH:mm"/>
</template>

<script lang="ts">
import { Component, Vue, Emit, Watch, Prop} from 'vue-property-decorator';

@Component
export default class TimeInputComponent extends Vue {

    @Prop({
        default: '00:00'
    }) value!: string;

    private content: string = '00:00';
    
    private allowKey: any = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9'
    ];

    @Watch('value', { immediate: true })
    private updateProp() {
        this.content = this.value;
    }

    private validateContent(e: any) {
        console.log('validate time input', e);
        if(this.allowKey.indexOf(e.key)>-1 && e.target.selectionStart<5) {
            
            // set dummy value if value is empty
            if(this.content==='') {
                this.content = '00:00';
            }

            // get cursor position
            let position = e.target.selectionStart===2 ? e.target.selectionStart+1 : e.target.selectionStart;
            
            // get first part
            let output = this.content.slice(0, position===0 ? position+1: position);
            
            // replace first part if positin is zero
            if(position===0) {
                output = e.key;
            } else {
            // append value
                output += e.key;
            }
            
            // join with the second part with 1 offset
            output = [output, this.content.slice(position+1)].join('');
            // validate hour is max 12 and min is max 59
            let calculate = output.split(":");
            calculate[0] = parseInt(calculate[0])>12 ? '12' : calculate[0];
            calculate[1] = parseInt(calculate[1])>59 ? '59' : calculate[1];
            output = calculate.join(":");

            this.content = output;
            
            // reposition a cursor
            setTimeout(() => {
                let el: any = this.$refs.timeInput;
                el.focus();
                position = position<5 ? position===1 ? position +2 : position+1 : position;
                el.setSelectionRange(position, position+1);
            }, 10);
            
            this.updateValue(this.content);
            return true;
        }
    }

    @Emit('input')
    private updateValue(content: string) {
        return content;
    }
}
</script>

