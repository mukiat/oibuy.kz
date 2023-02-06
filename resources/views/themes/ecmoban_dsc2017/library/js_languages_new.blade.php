<link rel="stylesheet" type="text/css" href="{{ skin('css/base.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/style.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/preview.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/iconfont.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/purebox.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/quickLinks.css') }}" />
@if(config('shop.lang') == 'en')
<link rel="stylesheet" type="text/css" href="{{ skin('css/lang_en.css') }}" />
@endif

<script src="{{ asset('js/jquery-1.12.4.min.js') }}"></script>
<script src="{{ asset('js/jquery.json.js') }}"></script>
<script src="{{ asset('js/transport_jquery.js') }}"></script>

<script type="text/javascript">
var json_languages = {!! $json_languages !!};

//加载效果
var load_cart_info = '<img src="{{ skin('images/load/loadGoods.gif') }}" class="load" />';
var load_icon = '<img src="{{ skin('images/load/load.gif') }}" width="200" height="200" />';

// 判断当前语言，给body添加class
var cfg_lang = '{{ $cfg_lang }}';
$(document).ready(function(){
	if(cfg_lang == 'en'){
		$('body').addClass("lang_en");
	}
})
</script>
