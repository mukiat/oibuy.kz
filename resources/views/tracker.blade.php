<!DOCTYPE html>
<html lang="zh-Hans">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{{ trans('user.logistics_tracking') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/tracker/css/mbase_v6.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/tracker/css/query_v6.css') }}"/>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_2021055_apux87hx13h.css"/>
    <style>
        .container{ height: 100vh; }
        .company ul {padding: 0.3rem;}
        .company ul li {line-height: 2rem;color: #5a5a5a;}
        .data-img {width: 4rem;height: 4rem;display: inline-block}
        .data-img img {width: 4rem;height: auto;}
        .kd-content:last-child { margin-bottom: 0; }
        .more { text-align: center; font-size: 14px; color: #999; padding: 10px 0; }
        .result-list { overflow: hidden; transition: all 0.3s; }
        .result-list li.other { display: none;}

        .header{ background:#fff; height:60px; border-radius: 10px; box-shadow: 2px 0px 8px 2px rgba(0,0,0,.1); display: flex; flex-direction: row; align-items: center; justify-content: flex-start;margin: 10px; position: fixed; top: 0; left:0; right: 0; z-index: 11;}
        .header .left{ margin: 0 10px; }
        .header .content{ flex: 1; display: flex; flex-direction: row; align-items: center; }
        .header .content .data-img{ width: 40px; height: 40px; }
        .header .content .data-img img{ height: 100%; width: 100%; }
        .header .content .text{ margin-left: 10px; }
        .header .right{ display: flex; flex-direction: row; align-items: center; }
        .header .right .kefu{ display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .header .right .kefu span{ font-size: 10px; color: #999; }
        .header .right .icon-gengduo1{ margin: 0 15px; }

        .footer{ position: fixed; bottom: 0; left: 0; right: 0; max-height: 60%; z-index: 9}
        .footer .warpper{ position: relative; height: 100%; }
        .footer .info{ background: #fff; border-radius: 10px; padding: 10px; margin-bottom: 10px;}
        .footer .info .item{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; font-size: 14px; line-height: 2; color: #666; }
        .footer .info .item .copy{ margin-left: 20px; cursor: pointer;}
        .footer .list{ background: #fff; border-radius: 10px 10px 0 0; margin: 0 5px;}

        .footer .list .result-list,
        .footer .list .result-list li{ border: 0; }
        .footer .list .result-list li:last-child .col3:before{ top: 0; bottom: 50%;}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.6/dist/clipboard.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/better-scroll@2.1.1/dist/better-scroll.min.js"></script>
</head>
<body>
<div class="container" id="main">
    <div class="main">
        <iframe name="ifm" id="ifm" class="ifm" scrolling="yes" width="100%" height="100%" marginwidth="0" marginheight="0" frameborder="0"></iframe>

        <div class="kd-content" v-for="(trackerItem,trackerIndex) in trackerList" :key="trackerIndex">
            <div class="header">
                <div class="left" @click="onBack"><i class="iconfont icon-find-fanhui"></i></div>
                <div class="content">
                    <div class="data-img" v-for="(itemImg, listImg) in trackerItem.img" :key="listImg">
                        <img :src="itemImg.goods_img" class="img" v-if="itemImg.goods_img">
                    </div>
                    <div class="text">@{{ state }}</div>
                </div>
            </div>

            <div class="footer" id="footer">
                <div class="warpper">
                    <div class="info">
                        <div class="item" v-if="trackerItem.shipping_name">
                            <div class="label">国内承运人：</div>
                            <div class="value">@{{ trackerItem.shipping_name }}</div>
                        </div>
                        <div class="item">
                            <div class="label">运单号：</div>
                            <div class="value">@{{ trackerItem.invoice_no }}<a href="javascript:;" :data-clipboard-text=" trackerItem.invoice_no" class="copy">复制</a></div>
                        </div>
                    </div>
                    <div class="list">
                        <ul id="result" class="result-list sortup" v-if="list[trackerIndex] && list[trackerIndex].length > 0">
                            <li v-for="(item, index) in list[trackerIndex]"
                                :class="{ last: index == 0}" :key="index">
                                <div class="col1">
                                    <dl>
                                        <dt>@{{ item.time }}</dt>
                                    </dl>
                                </div>
                                <div class="col2"><span></span></div>
                                <div class="col3">@{{ item.context }}</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ asset('js/jquery-1.9.1.min.js') }}"></script>
    <script src="{{ asset('js/vue.min.js') }}"></script>
    <script>

        new Vue({
            el: '#main',
            data: {
                trackerList:[],
                error: [],
                list: [],
                number: [],
                status: [],
                index:'',
                state:''
            },
            created: function () {
                this.loader()
            },
            mounted: function (){
                this.$nextTick(() => { });
            },
            methods: {
                loader: function () {
                    var that = this;

                    @if(!empty($delivery_sn))

                    var url = '/api/order/tracker_order?delivery_sn={{ $delivery_sn }}';
                    $.get(url, function (res) {
                        if (res.status === 'success') {
                            that.trackerList = res.data;
                            that.mapUrl(that.trackerList[0].shipping_code,that. trackerList[0].invoice_no);
                            that.seeMore(that.trackerList[0].shipping_code,that.trackerList[0].invoice_no,that.trackerList[0].order_id,0);
                        }
                    }, 'JSON');

                    @else

                        var shipping_code = '{{ $type ?? '' }}';
                        var invoice_no = '{{ $post_id ?? '' }}';
                        var order_id = '{{ $order_id ?? 0 }}';

                        that.trackerList = new Array({shipping_code:shipping_code,invoice_no:invoice_no,order_id:order_id});

                        that.seeMore(that.trackerList[0].shipping_code,that.trackerList[0].invoice_no,that.trackerList[0].order_id,0);

                    @endif
                },
                mapUrl(shipping_code, invoice_no){
                    var that = this;
                    var url = '/api/order/map_track?postid='+ invoice_no +'&type=' + shipping_code +'&from={{ $from }}&to={{ $to }}&mobile={{ $mobile }}';
                    $.get(url, function (res) {
                        if (res.status === 'success') {
                            document.getElementById("ifm").src = res.data;
                            $("#ifm").show();

                             setTimeout(()=>{
                                let footer = document.getElementById("footer");
                                let bs = BetterScroll.createBScroll(footer, {
                                    pullUpLoad: true
                                });
                            },200);
                        }else{
                            $("#ifm").hide();

                            setTimeout(()=>{
                                let footer = document.getElementById("footer");
                                let bs = BetterScroll.createBScroll(footer, {
                                    pullUpLoad: true,
                                    click: true
                                });

                                $(".footer").css({"top":80,"min-height":"100%"});
                            },200);
                        }
                    }, 'JSON');
                },
                seeMore: function (shipping_code,invoice_no,order_id,index) {
                    var that = this;
                    var url = '/api/order/tracker';

                    if(index == 0){
                        that.index = 0
                    }

                    $.post(url,{type:shipping_code,postid:invoice_no,order_id:order_id},function (res) {
                        var error = res.status === 'success' ? false : true;

                        that.error.splice(index,1,error);

                        if (res.status === 'success') {
                            that.list.splice(index,1,res.data.traces)
                            that.number.splice(index,1,res.data.traces.length);
                            that.status.splice(index,1,false);

                            that.state = res.data.state;

                            that.list = that.list.reverse();
                        }
                    }, 'JSON');
                },
                upMore: function(index){
                    var that = this;
                    var length = that.list[index].length;
                    if(that.status[index] == false){
                        that.number.splice(index,1,3);
                    }else{
                        that.number.splice(index,1,length);
                    }

                    that.status[index] = !that.status[index]
                },
                onBack(){
                    window.history.back(-1);
                },
            },
            filters: {
                dater: function (value) {
                    var date = new Date();
                    date.setTime(value * 1000);
                    return date.toLocaleDateString();
                },
                timer: function (value) {
                    var date = new Date();
                    date.setTime(value * 1000);
                    return date.toLocaleTimeString().substr(2);
                }
            },
            watch:{
                trackerList(){
                    let that = this;
                    that.trackerList.forEach((v,i)=>{
                        that.list.push([]);
                        that.number.push([]);
                        that.status.push([]);
                        that.error.push(false);
                    })
                },
                list(){
                    this.upMore(this.index);
                }
            }
        });

        $(function () {
            $("#ifm").load(function () {
                var h = document.body.clientHeight;
                var w = document.body.clientWidth;
                var ifm = document.getElementById("ifm");

                ifm.height = h + "px";
                ifm.width = w + "px";
            });

            var clipboard = new ClipboardJS('.copy');
            clipboard.on('success', function(e) {
                alert("复制成功");
                e.clearSelection();
            });
            clipboard.on('error', function(e) {
                alert("复制失败");
            });
        })
    </script>
</div>
</body>
</html>
