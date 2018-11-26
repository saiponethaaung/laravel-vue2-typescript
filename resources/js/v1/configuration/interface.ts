import ChatBlockSectionModel from "../models/ChatBlockSectionModel";

export interface ChatBlock {
    id: number,
    title: string,
    lock: boolean,
    sections: Array<ChatBlockSectionModel>
}

export interface ChatBlockSection {
    id: number,
    title: string
}