import i18n from '@/locales'
export default {
    module: "search",
    componentName: i18n.t('lang.search'),
    suggest: "",
    setting: "0",
    isShow: true,
    fixed: true,
    data: {
        positionSel: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.show')
        },
        {
            key: "1",
            type: "radio",
            title: i18n.t('lang.hide')
        }],
        logoSel: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.show')
        },
        {
            key: "1",
            type: "radio",
            title: i18n.t('lang.hide')
        }],
        messageSel: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.show')
        },
        {
            key: "1",
            type: "radio",
            title: i18n.t('lang.hide')
        }],
        suspendSel: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.yes')
        },
        {
            key: "1",
            type: "radio",
            title: i18n.t('lang.no')
        }],
        allValue: {
            img: ""
        }
    }
}
