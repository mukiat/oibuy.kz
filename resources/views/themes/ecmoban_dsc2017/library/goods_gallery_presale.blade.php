
<div class="preview">
    <div class="gallery_wrap">
        <a href="
@if($pictures['0']['img_url'])
{{ $pictures['0']['img_url'] }}
@else
{{ $goods['goods_img'] }}
@endif
" class="MagicZoomPlus" id="Zoomer" rel="hint-text: ; selectors-effect: false; selectors-class: img-hover; zoom-distance: 10; selectors-change: mouseover; zoom-width: 400; zoom-height: 474;"><img src="
@if($pictures['0']['img_url'])
{{ $pictures['0']['img_url'] }}
@else
{{ $goods['goods_img'] }}
@endif
" id="J_prodImg" alt="{{ $goods['goods_name'] }}" width="405" height="405" /></a>
    </div>
    <div class="spec-list">
        <a href="javascript:void(0);" class="spec-prev"></a>
        <a href="javascript:void(0);" class="spec-next"></a>
        <div class="spec-items">
            <ul>

@if(!$pictures['0']['img_url'] && $goods['goods_img'])

                <li><a href="{{ $goods['goods_img'] }}" rel="zoom-id: Zoomer" rev="{{ $goods['goods_img'] }}"><img src="{{ $goods['goods_img'] }}" alt="{{ $goods['goods_name'] }}" width="60" height="60"/></a></li>

@endif


@if($pictures)


@foreach($pictures as $picture)

                <li><a href="
@if($picture['img_url'])
{{ $picture['img_url'] }}
@else
{{ $picture['thumb_url'] }}
@endif
" rel="zoom-id: Zoomer" rev="
@if($picture['img_url'])
{{ $picture['img_url'] }}
@else
{{ $picture['thumb_url'] }}
@endif
"
@if($loop->first)
class="img-hover"
@endif
><img src="
@if($picture['thumb_url'])
{{ $picture['thumb_url'] }}
@else
{{ $picture['img_url'] }}
@endif
" alt="{{ $goods['goods_name'] }}" width="60" height="60" /></a></li>

@endforeach


@endif

            </ul>
        </div>
    </div>
    <div class="short-share">

@if($cfg['show_goodssn'])
<div class="short-share-r bar_code hide">{{ $lang['bar_code'] }}<em id="bar_code"></em></div>
@endif

    	<div id="compare">
          <a href="javascript:;" class="btn-compare" id="compareLink" style=" margin-left:10px;">
       	  	<input id="{{ $goods['goods_id'] }}" type="checkbox" onClick="Compare.add(this, {{ $goods['goods_id'] }},'{{ $goods['goods_name'] }}','{{ $goods['goods_type'] }}', '{{ $goods['goods_thumb'] }}', '{{ $goods['shop_price'] }}', '{{ $goods['market_price'] }}')" style=" vertical-align:middle; margin-left:8px;"/>&nbsp;&nbsp;<label for="{{ $goods['goods_id'] }}" style=" line-height:25px; height:25px;">{{ $lang['compare'] }}</label>
          </a>
        </div>
        <div class="collecting"><a href="javascript:void(0);" class="collection choose-btn-coll"  data-dialog="goods_collect_dialog" data-divid="goods_collect" data-url="user_collect.php?act=collect" data-goodsid="{{ $goods['goods_id'] }}"><b></b><em>{{ $lang['collect'] }} (<i id="collect_count"></i>)</em></a></div>
<!--
@if($is_http == 2)

        <div class="bdsharebuttonbox">
        	<a href="#" class="bds_more" data-cmd="more" style="background:url(themes/ecmoban_dsc2017/images/fx.png) 0px 10px no-repeat !important;color: #666;line-height: 25px;height: 25px;margin: 4px 15px 4px 10px;padding-left: 20px; padding-top:5px;display: block; float:right;">{{ $lang['share_flow'] }}</a>
        </div>

@else

        	<div id="bdshare" class="bdshare_t bds_tools get-codes-bdshare" style="float:right;"><a class="bds_more" href="#none" style="background:url({{ skin('images/fx.png') }}) 0px 10px no-repeat !important;color: #666;line-height: 25px;height: 25px;margin: 4px 15px 4px 10px;padding-left: 20px; padding-top:5px;display: block; float:right;">{{ $lang['share_flow'] }}</a></div>

@endif
 -->
    </div>
</div>

<script type="text/javascript" id="bdshare_js" data="type=tools&amp;uid=692785" ></script>
<script type="text/javascript" id="bdshell_js"></script>

@if($is_http == 2)

<script type="text/javascript">
	document.getElementById("bdshell_js").src = "{{ $url }}storage/static/api/js/share.js?v=89860593.js?cdnversion=" + new Date().getHours();
</script>

@else

<script type="text/javascript">
	document.getElementById("bdshell_js").src = "http://bdimg.share.baidu.com/static/js/shell_v2.js?cdnversion=" + new Date().getHours();
</script>

@endif


<script type="text/javascript">
$(function(){
	get_collection();
});

function get_collection(){
	Ajax.call('ajax_dialog.php', 'act=goods_collection&goods_id=' + {{ $goods_id ?? 0 }}, goodsCollectionResponse, 'GET', 'JSON');
}

function goodsCollectionResponse(res){
	$("#collect_count").html(res.collect_count);

	if(res.is_collect > 0){
		$(".collection").addClass('selected');
		$("#collection_iconfont").addClass("icon-collection-alt");
		$("#collection_iconfont").removeClass('icon-collection');
	}else{
		$(".collection").removeClass('selected');
		$("#collection_iconfont").addClass("icon-collection");
		$("#collection_iconfont").removeClass('icon-collection-alt');
	}
}
</script>
