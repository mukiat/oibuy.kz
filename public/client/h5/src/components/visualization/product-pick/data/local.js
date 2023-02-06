import i18n from '@/locales'
export default {
    module: "product-pick",
    componentName: '推荐商品',
    suggest: "",
    setting: "0",
    isShow: true, //控制是否显示
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.default')
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
        showTab:[{
            key: "0",
            type: "checkbox",
            title: 'tab1',
            text:'',
            desc:''
        }, {
            key: "1",
            type: "checkbox",
            title: 'tab2',
            text:'',
            desc:''
        }, {
            key: "2",
            type: "checkbox",
            title: 'tab3',
            text:'',
            desc:''
        }, {
            key: "3",
            type: "checkbox",
            title: 'tab4',
            text:'',
            desc:''
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
