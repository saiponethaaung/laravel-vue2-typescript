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
}

export interface galleryContent {

}

export interface textContent {
    content: string,
    button: Array<buttonContent>
}

export interface typingContent {
    duration: number
}

export interface quickReplyContent {

}

export interface imageContent {

}

export interface galleryContent {
    image: string,
    heading: string,
    desc: string,
    url: string,
    button: Array<buttonContent>
}

export interface listContent {

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