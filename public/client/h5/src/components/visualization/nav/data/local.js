import i18n from '@/locales'
export default {
    module: "nav",
    componentName: i18n.t('lang.nav_image'),
    suggest: "",
    setting: "0",
    isShow: true, //控制是否显示
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.style_mode_1')
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.style_mode_2')
        }, {
            key: "2",
            type: "radio",
            title: i18n.t('lang.style_mode_3')
        }],
        showStyleList: [{
            key: "0",
            type: "checkbox",
            title: i18n.t('lang.style_overall_margin')
        }, {
            key: "1",
            type: "checkbox",
            title: i18n.t('lang.style_padding')
        }, {
            key: "2",
            type: "checkbox",
            title: i18n.t('lang.style_border')
        }],
        showNumber: [{
            key: "2",
            type: "radio",
            title: i18n.t('lang.two_columns')
        }, {
            key: "3",
            type: "radio",
            title: i18n.t('lang.three_columns')
        }, {
            key: "4",
            type: "radio",
            title: i18n.t('lang.four_columns')
        }, {
            key: "5",
            type: "radio",
            title: i18n.t('lang.five_columns')
        }],
        showDesc: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.show'),
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.hide')
        }],
        showIcon: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.show'),
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.hide'),
        }],
        list: [],
        styleSelList: [],
        isNumberSel: "5",
        isStyleSel: "0",
        isIconSel: "0",
        isDescSel:"0"
    }
}
