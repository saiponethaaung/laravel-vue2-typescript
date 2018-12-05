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

export interface BotContent {
    id: number
    type: number,
    section: number,
    block: number
}

export interface textContent {
    content: string,
    button: Array<buttonContent>
}

export interface typingContent {
    duration: number
}

export interface quickReplyContent {
    id: number,
    title: string,
    attribute: {
        id: number,
        title: string,
        valie: string
    },
    content_id: number,
    block: Array<number>
}

export interface imageContent {

}

export interface galleryContent {
    id: number,
    image: string,
    title: string,
    sub: string,
    url: string,
    content_id: number,
    button: Array<buttonContent>
}

export interface listContent {
    id: number,
    image: string,
    title: string,
    sub: string,
    url: string,
    content_id: number,
    button: buttonContent
}

export interface userInputContent {

}

export interface buttonContent {
    type: number,
    content: string,
    block: Array<number>,
    url: string,
    phone: {
        countryCode: number,
        number: number
    }
}