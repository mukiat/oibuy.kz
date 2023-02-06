import i18n from '@/locales'
export default {
    module: "product",
    componentName: i18n.t('lang.product_list'),
    suggest: "",
    setting: "0",
    isShow: true, //控制是否显示
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.default'),
            tags: ["0", "1", "2"]
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.roll'),
            tags: ["2"]
        }],
        showSize: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.max_image'),
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.standard'),
        }, {
            key: "2",
            type: "radio",
            title: i18n.t('lang.min_image'),
        }],
        showTag: [{
            key: "0",
            type: "checkbox",
            title: i18n.t('lang.stock')
        }, {
            key: "1",
            type: "checkbox",
            title: i18n.t('lang.sales_volume')
        }, {
            key: "2",
            type: "checkbox",
            title: i18n.t('lang.headline')
        }],
        showModule: [{
            key: "3",
            type: "radio",
            title: i18n.t('lang.all')
        }, {
            key: "0",
            type: "radio",
            title: i18n.t('lang.base')
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.new')
        }, {
            key: "2",
            type: "radio",
            title: i18n.t('lang.hot')
        }],
        allValue: {
            number: 10,
            scrollNumber: 3,
            categorySOption:'',
            brandSelect:'',
            selectGoodsId:[]
        },
        isStyleSel: "0",
        isSizeSel: "0",
        tagSelList: [],
        isModuleSel: "0"
    }
}
