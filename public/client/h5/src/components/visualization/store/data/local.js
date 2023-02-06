import i18n from '@/locales'
export default {
    module: "store",
    componentName: i18n.t('lang.shop_street'),
    suggest: "",
    setting: "0",
    isShow: true, //控制是否显示
    data: {
        list: [],
        showStyle: [{
            key: "0",
            type: "radio",
            title: '默认',
        }, {
            key: "1",
            type: "radio",
            title: '新版',
        }],
        allValue: {
            number: 10,
            spikeDesc:'',
        },
        isStyleSel:'0'
    }
}