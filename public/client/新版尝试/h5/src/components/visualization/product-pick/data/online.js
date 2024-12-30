module.exports = {
    module: "product-pick",
    setting: '1',
    isShow: true, //控制是否显示
    data: {
        allValue:{
            number: 10,
            scrollNumber:3,
            categorySOption:'',
            brandSelect:'',
            selectGoodsId:[]
        },
        isStyleSel: "0",        
        isSizeSel: "0",
        tagSelList: ["0","1"],
        isModuleSel: "0",
        tabSelList:["0","1","2","3"],
        tabInputList:[{
            key:'0',
            text:'',
            desc:''
        },
        {
            key:'1',
            text:'',
            desc:''
        },
        {
            key:'2',
            text:'',
            desc:''
        },
        {
            key:'3',
            text:'',
            desc:''
        }]
    }
}