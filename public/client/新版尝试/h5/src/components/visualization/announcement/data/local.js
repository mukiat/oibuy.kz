import i18n from '@/locales'
export default {
    module: "announcement",
    componentName: i18n.t('lang.announcement'),
    suggest: "",
    setting: "1",
    isShow: true, //控制是否显示
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.news_model'),
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.notice_patterns'),
        }],
        showDate: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.show')
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.hide')
        }],
        showNewStyle:[{
            key: "0",
            type: "radio",
            title: 'Әдепкі'
        }, {
            key: "1",
            type: "radio",
            title: 'Жаңа'
        }],
        allValue: {
            number: 10,
            img: "",
            announContent: i18n.t('lang.content'),
            optionCascaderVal:""
        },
        img: "",
        isStyleSel: "0",
        isDateSel: "0",
        isNewStyleSel: "0"
    }
}
