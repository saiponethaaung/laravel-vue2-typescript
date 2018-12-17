import Axios, { CancelTokenSource } from "axios";

export default class AjaxErrorHandler {

    private searchToken: CancelTokenSource = Axios.CancelToken.source();

    globalHandler (err: any, mesg: string) {
        if(undefined===mesg) {
            return "Operation failed!";
        }

        if(err.response && err.response.status) {
            if(err.response.status===422) {
                return this.handle422(err, mesg);
            }

            if(err.response.status===404) {
                return this.handle404(err, mesg);
            }
        }

        return mesg;
    }

    handle422 (err: any, mesg: string) {
        if(err.response.data && err.response.data.mesg) {
            return err.response.data.mesg;
        }
        return mesg;
    }

    handle404 (err: any, mesg: string) {
        if(err.response.data && err.response.data.mesg) {
            return err.response.data.mesg;
        }
        return mesg;
    }

    async searchSections(keyword: string) {

        if(undefined===keyword) {
            keyword = "";
        }

        this.searchToken.cancel();
        this.searchToken = Axios.CancelToken.source();

        let response = {
            status: true,
            code: 200,
            type: 'general',
            mesg: 'Sucess',
            data: []
        };

        await Axios({
            url: `/api/v1/chat-bot/blocks/search?keyword=${keyword}`,
            method: 'get',
            cancelToken: this.searchToken.token
        }).then((res: any) => {
            response.data = res.data.data;
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to serach blocks!');
                response.status = false;
                response.code = err.response.status;
                response.mesg = mesg;
            } else {
                response.type = 'cancel';
            }
        });

        return response;
    }
}