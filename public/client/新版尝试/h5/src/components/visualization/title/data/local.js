import i18n from '@/locales'
export default {
    module: "title",
    componentName: i18n.t('lang.headline'),
    suggest: "",
    setting: "0",
    isShow: true, //控制是否显示
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.tradition'),
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.wechat'),
        }
        ,{
            key: "2",
            type: "radio",
            title: i18n.t('lang.simplification'),
        }
        ],
        allValue: {
            
        },
        list:[
        ],
        showPosition: [{
            title: i18n.t('lang.the_left'),
            type: "radio",
            key: "0"
        }, {
            title: i18n.t('lang.the_center'),
            type: "radio",
            key: "1"
        }, {
            title: i18n.t('lang.the_right'),
            type: "radio",
            key: "2"
        }],
        isStyleSel: "0",
        isPositionSel: "0"
    },
}
