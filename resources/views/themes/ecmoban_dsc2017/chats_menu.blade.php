<!DOCTYPE html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="Keywords" content="" />
    <meta name="Description" content="" />
    <title></title>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_1441957734_410811.css"/>
    <style>
        * {
            margin: 0;
            padding: 0
        }

        html, body {
            overflow: hidden;
        }

        .plugin-wrap {
            width: 140px;
            margin: 0 auto;
        }

        .plugin-wrap a {
            display: block;
            width: 50px;
            height: 80px;
            float: left;
            color: #333;
            text-decoration: none;
            font-size: 12px;
            text-align: center;
            margin: 10px;
        }

        .plugin-wrap a span {
            display: block;
            width: 50px;
            height: 50px;
            background: #f9f3f3;
            border-radius: 100%;
            margin-bottom: 5px;
        }

        .plugin-wrap a span .iconfont {
            font-size: 26px;
            line-height: 50px;
            color: #ff6d71;
        }

        .tab {
            width: 100%;
            height: 30px;
            background: #f6f6f6;
            line-height: 30px;
            text-align: center;
            color: #dc2a2e;
            font-size: 12px;
        }

    </style>
@include('frontend::library/js_languages_new')
</head>
<body>
<script>
    with (document)with (body)with (insertBefore(createElement("script"), firstChild))setAttribute("exparams", "category=&userid=&aplus&yunid=&&asid=AQAAAAB4jkZXJ0lvcAAAAACOQcih7BZ3aQ==", id = "tb-beacon-aplus", src = (location > "https" ? "//g" : "//g") + ".alicdn.com/alilog/mlog/aplus_v2.js")
</script>
<div class="tab"><span>{{ $lang['fast_tool'] }}</span></div>
<div class="plugin-wrap">

    <a href="user_collect.php?act=collection_list" target="_blank">
<span>
<i class="iconfont icon-tuihuo"></i>
</span>
        {{ $lang['my_collect'] }}
    </a>
    <a href="user_order.php?act=order_list" target="_blank">
<span>
<i class="iconfont icon-chaxun"></i>
</span>
        {{ $lang['my_order'] }}
    </a>
    @if ($cfg['use_bonus'] == 1)
    <a href="user_activity.php?act=bonus" target="_blank">
<span>
<i class="iconfont icon-dingdan"></i>
</span>
        {{ $lang['my_bonus'] }}
    </a>
    @endif
    <a href="article.php?id=21" target="_blank">
<span>
<i class="iconfont icon-importedlayerscopy6fill1fill2"></i>
</span>
        {{ $lang['help_centre'] }}
    </a>
</div>
<script type="text/javascript">(function (e) {
    if (!e["_med"])e["_med"] = {};
    var t = e["_med"];
    t.cookie = function (e, t, a) {
        if (t !== undefined) {
            a = a || {};
            if (typeof a.expires === "number") {
                var o = a.expires, l = a.expires = new Date;
                l.setTime(+l + o * 864e5)
            }
            return document.cookie = [e, "=", String(t), a.expires ? "; expires=" + a.expires.toUTCString() : "", a.path ? "; path=" + a.path : "", a.domain ? "; domain=" + a.domain : "", a.secure ? "; secure" : ""].join("")
        }
        var r = e ? undefined : {};
        var i = document.cookie ? document.cookie.split("; ") : [];
        for (var n = 0, s = i.length; n < s; n++) {
            var b = i[n].split("=");
            var d = b.shift();
            var p = b.join("=");
            if (e && e === d) {
                r = p;
                break
            }
            if (!e && p !== undefined) {
                r[d] = p
            }
        }
        return r
    };
    var a = document;
    var o = e.devicePixelRatio || 1, l = a.documentElement.clientWidth, r = a.documentElement.clientHeight, i, n, s, b = /initial-scale=([\d\.]+?),/i, d, p;
    if (a.querySelector) {
        p = a.querySelector('meta[name="viewport"]');
        if (p) {
            d = b.exec(p.content + ",");
            if (d) {
                s = parseFloat(d[1], 10)
            }
        }
    }
    if (s) {
        l = l * s;
        r = r * s
    }
    if (screen) {
        if (Math.abs(screen.width - l * o) < .2 * screen.width) {
            l = screen.width / o;
            r = screen.height / o;
            i = screen.width;
            n = screen.height
        } else {
            l = screen.width;
            r = screen.height;
            i = screen.width * o;
            n = screen.height * o
        }
    } else {
        i = l * o;
        n = r * o
    }
    var m = "createTouch" in a && "ontouchstart" in e ? 1 : 0;
    var c = ["dw:" + l, "dh:" + r, "pw:" + i, "ph:" + n, "ist:" + m].join("&");
    t.cookie("_med", c, {expires: 3650})
})(window);
</script>
</body>
</html>
