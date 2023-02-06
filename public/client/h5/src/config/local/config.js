const localUrl = "http://10.10.10.14:3000/"
const pageInfo = {
    headerMenu: {
        pageListTitle: {
            icon: "page",
            isActive: true,
            isShow:true
        },
        globaSettings: {
            text: "Жалпы баптау",
            isActive: false,
            isShow:false
        },
        menuComponent: [{
            text: "Макет",
            isActive: false,
            isShow: true,
            type:"all",
            subComponent: [{
                icon: "line",
                text: "Сызық",
                module: "line",
                type:"all",
                isShow: true,
            }, {
                icon: "blank",
                text: "Бос орын",
                module: "blank",
                type:"all",
                isShow: true,
            }, {
                icon: "magic-cube",
                text: "Кубик",
                module: "magic-cube",
                type:"all",
                isShow: false,
            }]
        }, {
            text: "Текст",
            isActive: false,
            isShow: true,
            type:"all",
            subComponent: [{
                text: "Тақырып",
                icon: "title",
                module: "title",
                type:"all",
                isShow: true,
            }, {
                text: "Өткел",
                icon: "passage",
                module: "passage",
                type:"all",
                isShow: false,
            }, {
                text: "Рик-мәтін",
                icon: "rick-text",
                module: "rick-text",
                type:"all",
                isShow: false,
            }, {
                text: "Хабар",
                icon: "announcement",
                module: "announcement",
                type:"all",
                isShow: true,
            }, {
                text: "Мәтін нав.",
                icon: "text-nav",
                module: "textNav",
                type:"all",
                isShow: false,
            }]
        }, {
            text: "Сурет",
            isActive: false,
            isShow: true,
            type:"all",
            subComponent: [{
                icon: "img-nav",
                text: "Сурет нав.",
                module: "nav",
                type:"all",
                isShow: true,
            }, {
                icon: "slide",
                text: "Слайд",
                module: "slide",
                type:"all",
                isShow: true,
            }, {
                icon: "jigsaw",
                text: "Джигсо",
                module: "jigsaw",
                type:"all",
                isShow: true,
            }, {
                icon: "live",
                text: "LIVE",
                module: "live",
                type:"platform",
                isShow: true,
            }]
        }, {
            text: "Функция",
            isActive: false,
            isShow: true,
            type:"all",
            subComponent: [{
                icon: "count-down",
                text: "Seckill",
                module: "count-down",
                type:"platform",
                isShow: true,
            }, {
                icon: "coupon",
                text: "Купон",
                module: "coupon",
                type:"all",
                isShow: false,
            }, {
                icon: "product-list",
                text: "Тауарлар",
                module: "product",
                type:"all",
                isShow: true,
            }, {
                icon: "search",
                text: "Іздеу",
                module: "search",
                type:"platform",
                isShow: true,
            }, {
                icon: "button",
                text: "Кнопка",
                module: "button",
                type:"all",
                isShow: false,
            },{
                icon: "store",
                text: "Дүкен",
                module: "store",
                type:"platform",
                isShow: true,
            },{
                icon:"tab-down",
                text:"Навигация",
                module:"tab-down",
                type:"platform",
                isShow:true
            }]
        },
        {
            text: "Жаңа бөлшек",
            isActive: false,
            isShow: true,
            type:"platform-home",
            subComponent: [{
                icon: "product-list",
                text: "Түрлер",
                module: "category-nav",
                type:"platform",
                isShow: true,
            },
            {
                icon: "product-list",
                text: "Бірлесу",
                module: "visual-team",
                type:"platform",
                isShow: true,
            },
            {
                icon: "product-list",
                text: "Сурет",
                module: "visual-adv",
                type:"platform",
                isShow: true,
            },
            {
                icon: "product-list",
                text: "Ұсыныс",
                module: "product-pick",
                type:"platform",
                isShow: true
            }]
        }],
    },
    pageListTool: {
        system: {
            text: "Әдепкі"
        },
        custom: {
            text: "Еркін"
        },
        search: {
            icon: "search",
            text: "Іздеу"
        }
    },
    editArea: {
        text: "Өңдеу",
        function: {
            clear: {
                text: "Тазалау",
                icon: "clear"
            },
            save: {
                text: "Сақтау",
                icon: "save",
            },
            restore: {
                text: "Бастапқы",
                icon: "restore"
            }
        },
        compontentTool: {
            spread: "arrow",
            sort: "sort-arrow",
        }
    },
    previewArea: {
        text: "Қарау",
        function: {
            release: {
                text: "Жолдау",
                icon: "release"
            }
        }
    },
    otherArea: {
        QRCodeText: "QR-мен көру",
        function: {
            import: "Импорт",
            export: "Экспорт"
        },
        prompt: {
            headline: "Ескерту",
            text: "QR-код арқылы телефондағы нәтижесін көре аласыз.",
            http: {
                text: "Сілтеме аты",
                link: "http://www.oibuy.kz"
            }
        }
    },
    defalutBg:"#f34646"
} 

export default{
    localUrl,
    pageInfo
}