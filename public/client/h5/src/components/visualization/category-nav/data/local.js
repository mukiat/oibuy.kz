import i18n from '@/locales'
export default {
    module: "category-nav",
    componentName: i18n.t('lang.category-nav'),
    suggest: "",
    setting: "1",
    isShow: true,
    fixed: true,
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.yes'),
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.no'),
        }],
        allValue: {
            number: 10,
            img: "",
            announContent: i18n.t('lang.content'),
            optionCascaderVal:""
        },
    }
}
