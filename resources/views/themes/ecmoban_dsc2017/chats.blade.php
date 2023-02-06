<!doctype html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="Keywords" content="" />
    <meta name="Description" content="" />
    <title>{{ $lang['online_service'] }}</title>
</head>
<body>


<script type="text/javascript" src="{{ asset('js/jquery-1.12.4.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.json.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/transport_jquery.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
<!--[if < IE 9]>
<script src="https://g.alicdn.com/aliww/ww/json/json.js" charset="utf-8"></script>
<![endif]-->

<script src="https://g.alicdn.com/aliww/??h5.openim.sdk/1.0.6/scripts/wsdk.js,h5.openim.kit/0.3.3/scripts/kit.js" charset="utf-8"></script>

<script type="text/javascript">
    var step=0
    var timer=0;
    function flash_title()
    {
        step++
        if (step==3) {step=1}
        if (step==1) {document.title='{{ $lang['message_see'] }}'}
        if (step==2) {document.title='{{ $lang['message_not'] }}'}
        timer= setTimeout("flash_title()",500);
    }
    window.onload = function(){
            WKIT.init({
            container: document.getElementById('J_demo'),
            width: 700,
            height: 500,
            uid: '{{ $user_id }}',
            appkey: '{{ $kf_appkey }}',
            credential: '{{ $user_id }}',
            touid: '{{ $kf_touid }}',
			customData: {
				item: {
					id: '{{ $goods_id }}'
				}
			},
            sendMsgToCustomService: true,
            logo: '{{ $kf_logo }}',//客服小头像
            toAvatar: '{{ $kf_logo }}',//客服大头像
            avatar: '{{ $user_logo }}',
            pluginUrl: '{{ $IM_menu }}',
            welcomeMsg: '{{ $kf_welcome_msg }}',
            sendBtn: true,
            onMsgReceived:function(){
                flash_title()
            }
        });
        $(document).click(function(){
            $('title').html('{{ $lang['online_service'] }}');
            clearTimeout(timer)
        })
    }

	 setTimeout(function(){
		$('.wkit-powered-by a').html(' Powered by {{ $dwt_shop_name }}').attr({'href':'javascript:;','target':''});
	},200)
</script>
</body>
</html>
