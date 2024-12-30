module.exports = {
    module: "slide",
    setting: '1',
    globalbg: true, //注意这里的设置项要和data下allValue有bgColor
    isShow: true, //控制是否显示
    data: {
        list: [],
        allValue:{
            number:1,
            bgColor:'#f34646'
        },
        isStyleSel: '1',
        isSizeSel: '0',
        isPaginationSel: "1",
        isSeparateSel: "0"
    }
}