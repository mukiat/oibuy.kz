import i18n from '@/locales'
export default {
    module: "count-down",
    componentName: "Seckill",
    suggest: "",
    setting: "0",
    isShow: true,
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.default'),
        }, {
            key: "1",
            type: "radio",
            title: "Сурет",
        }, {
            key: "2",
            type: "radio",
            title: "Жаңа",
        }],
        allValue:{
            number:10
        },
        list:[],
        isShowStyle:"0"
    }
}
