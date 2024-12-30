module.exports = {
    module: "search",
    setting: '1',
    globalbg: true,//注意这里的设置项要和data下allValue有bgColor
    isShow: true,
    fixed: true,
    data: {
        allValue: {
            searchValue: "",
            fontColor:"",
            bgColor: "#f34646",
            tenKey: "",
            img: "",
        },
        isSuspendSel: "0",
        isPositionSel: "0",
        isLogoSel: "1",
        isMessageSel: "0"
    }
}