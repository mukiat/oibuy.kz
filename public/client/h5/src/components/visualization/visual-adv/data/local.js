import i18n from '@/locales'
export default {
    module: "visual-adv",
    componentName: '图片广告',
    suggest: "",
    setting: "0",
    isShow: true, //控制是否显示
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.slide_show_1'),
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.slide_show_2'),
        }],
        allValue:{
            titleImg:'',
            number:10,
            selectGoodsId:[],
            bgColor: "",
            url:"",
            appPage:"",
            appletPage:""
        },
        isShowStyle:"0"
    }
}
