
<div class="preview" if="preview">
	<div class="gallery_wrap"><a href="
@if($pictures['0']['img_url'])
{{ $pictures['0']['img_url'] }}
@else
{{ $goods['goods_img'] }}
@endif
" class="MagicZoomPlus" id="Zoomer" rel="hint-text: ; selectors-effect: false; selectors-class: img-hover; selectors-change: mouseover; zoom-distance: 10;zoom-width: 400; zoom-height: 474;"><img src="
@if($pictures['0']['img_url'])
{{ $pictures['0']['img_url'] }}
@else
{{ $goods['goods_img'] }}
@endif
" id="J_prodImg" alt="{{ $goods['goods_name'] }}"></a></div>
	<div class="spec-list">
		<a href="javascript:void(0);" class="spec-prev"><i class="iconfont icon-left"></i></a>
		<div class="spec-items">
			<ul>
            	
@if(!$pictures['0']['img_url'] && $goods['goods_img'])

                <li><a href="{{ $goods['goods_img'] }}" rel="zoom-id: Zoomer" rev="{{ $goods['goods_img'] }}"><img src="{{ $goods['goods_img'] }}" alt="{{ $goods['goods_name'] }}" width="58" height="58"/></a></li>
                
@endif

            	
@if($pictures)
 
                
@foreach($pictures as $picture)

                <li>
					<a href="
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
>
						<img src="
@if($picture['thumb_url'])
{{ $picture['thumb_url'] }}
@else
{{ $picture['img_url'] }}
@endif
" alt="{{ $goods['goods_name'] }}" width="58" height="58" />
					</a>
				</li>
                
@endforeach
 
              	
@endif

			</ul>
		</div>
		<a href="javascript:void(0);" class="spec-next"><i class="iconfont icon-right"></i></a>
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

