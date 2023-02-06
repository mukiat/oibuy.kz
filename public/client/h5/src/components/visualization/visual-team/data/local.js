import i18n from '@/locales'
export default {
    module: "visual-team",
    componentName: 'Бірлесу',
    suggest: "",
    setting: "0",
    isShow: true, //控制是否显示
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.default'),
        }],
        allValue:{
            number:10
        },
        list:[],
        isShowStyle:"0"
    }
}
