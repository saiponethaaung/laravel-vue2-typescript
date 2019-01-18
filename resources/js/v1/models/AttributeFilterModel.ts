import { attributeFilter } from "../configuration/interface";

export default class AttributeFilterModel {
    constructor(
        private attributeData: attributeFilter
    ) {

    }

    get id() : number {
        return this.attributeData.id;
    }

    get option() : number{
        return this.attributeData.option;
    }

    set option(option: number) {
        this.attributeData.option = option;
    }

    get type() : number {
        return this.attributeData.type;
    }
    
    set type(type: number) {
        this.attributeData.type = type;
    }

    get name() : string {
        return this.attributeData.name;
    }

    set name(name: string) {
        this.attributeData.name = name;
    }

    get value() : string {
        return this.attributeData.value;
    }

    set value(value) {
        this.attributeData.value = value;
    }

    get condi() : number {
        return this.attributeData.condi;
    }

    set condi(condi: number) {
        this.attributeData.condi = condi;
    }

    get system() : number {
        return this.attributeData.systemAttribute;
    }

    set system(system: number) {
        this.attributeData.systemAttribute = system;
    }

    get systemValue() : number {
        return this.attributeData.systemAttributeValue;
    }

    set systemValue(value: number) {
        this.attributeData.systemAttributeValue = value;
    }
}