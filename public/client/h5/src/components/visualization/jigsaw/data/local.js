import i18n from '@/locales'
export default {
    module: "jigsaw",
    componentName: i18n.t('lang.jigsaw'),
    suggest: "",
    setting: "0",
    isShow: true,
    data: {
        list: [],
        showStyle:[{
            key:"0",
            type:"radio",
            title:i18n.t('lang.jigsaw_mode_1')
        },{
            key:"1",
            type:"radio",
            title:i18n.t('lang.jigsaw_mode_2')
        }],
        showStyleList: [{
            key: "1",
            type: "checkbox",
            title: i18n.t('lang.style_padding')
        }, {
            key: "2",
            type: "checkbox",
            title: i18n.t('lang.style_border')
        }],
        showPosition:[{
            key:"0",
            type:"radio",
            title:i18n.t('lang.style_float_left')
        },{
            key:"1",
            type:"radio",
            title:i18n.t('lang.style_float_right')
        }],
        allValue: {
            showStyle2Size: "1:2:3",
            showStyle1Size:"",
            showStyle1Right:"",
        },
        styleSelList: [],
        isStyleSel:"0",
        isPositionSel:"0"
    }
}
