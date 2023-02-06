import i18n from '@/locales'
export default {
    module: "slide",
    componentName: i18n.t('lang.slide'),
    suggest: "",
    setting: "0",
    isShow: true, //控制是否显示
    data: {
        showStyle: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.slide_show_1'),
            picSizeKey: ["0"]
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.slide_show_2'),
            picSizeKey: ["0", "1"]
        }, 
        {
            key: "2",
            type: "radio",
            title: '新版样式',
            picSizeKey: ["0"],
        }],
        picSize: [{
            key: "0",
            type: "radio",
            title: i18n.t('lang.max_image')
        }, {
            key: "1",
            type: "radio",
            title: i18n.t('lang.min_image'),
        }, {
            key: "3",
            type: "radio",
            title: i18n.t('lang.min_image'),
        }],
        pagination:[{
            key:"0",
            type:"radio",
            title:'居左'
        },{
            key:"1",
            type:"radio",
            title:'居中'
        },{
            key:"2",
            type:"radio",
            title:'居右'
        }],
        separateStyle:[{
            key:"0",
            type:"radio",
            title:'默认'
        },{
            key:"1",
            type:"radio",
            title:'新版'
        }],
        allValue:{
            number:1
        },
        list: [],        
        isStyleSel: "0",
        isSizeSel: "0",
        isPaginationSel: "0",
        isSeparateSel: "0"
    }
}
