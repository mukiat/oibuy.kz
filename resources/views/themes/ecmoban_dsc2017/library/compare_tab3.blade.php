
<style type="text/css">
#tbl_duibi_float{}

#tbl_duibi_float{
position: fixed;
bottom: 0px;
left: 50%;
z-index: 1;
width: 602px;
height: 52px;
background-position: 0 0;
}

#tbl_duibi_float, #tbl_duibi_float a.setup, #compare_highlight, #compare_equal, #locate, #static {
background-image: url({{ skin('images/duibi_tool.png') }});
background-repeat: no-repeat;
}
#tbl_duibi_float #compare_highlight {
padding-left: 24px;
background-position: 0 -53px;
}
#tbl_duibi_float #compare_equal {
padding-left: 24px;
background-position: -124px -53px;
}
#tbl_duibi_float a.setup:hover {
text-decoration: none;
}
#tbl_duibi_float .con {
display: block;
width: 340px;
height: 34px;
padding: 13px 0px 0px 130px;
background-position: -53px 0;
line-height: 34px; margin:0;
}
#tbl_duibi_float a.setup {
float: left;
width: 92px;
height: 25px;
padding-left: 21px;
margin-right: 10px;
background-position: -224px -53px;
line-height: 24px;
text-align: center;
color: #fff;
}
#locate {
background-position: 0 -79px;
}

#static {
background-position: -89px -79px;
}
#locate, #static {
float: right;
right: 30px;
top: 13px;
cursor: pointer;
width: 80px;
height: 25px;
}
#tool .con, #locate, #static {
position: relative;
}
#tool a.setup b {
float: right;
display: none;
width: 12px;
height: 34px;
}




/*比较结果页*/
#pcomprare {
	margin: 0 0 0 0;
	border:0;
	border-top:1px solid #ddd;
	overflow:hidden;
}


#pcomprare .tab {
width:1198px;
position: relative;
z-index: 1;
border: 1px solid #ddd;
border-bottom: none;
margin-bottom: 0;
background: #f7f7f7;
clear:both;
float:left;
}
#pcomprare .tab li {
float: left;
display: block;
width: 132px;
height: 31px;
margin-left: 10px;
text-align: left;
font: normal 15px/31px \5fae\8f6f\96c5\9ed1;
color: #333;
}
.tb-1 {
width: 988px;
margin-top: -1px;
border-collapse: collapse;
border: 1px solid #ddd;
border-top: none;
table-layout: fixed;
}
#pcomprare .tab  .f_l input {width: 13px;height: 13px;margin: 12px 4px 0 14px;padding: 0;display: inline;float: left;}
#pcomprare .tab  .f_l span{ color:#333}


/*tb-1*/
.tb-1{width:100%;margin-top:-1px;border-collapse:collapse;border:1px solid #ddd;border-top:none;table-layout:fixed;}
.tb-1 th,.tb-1 td{padding:8px 10px;border:1px solid #ddd;line-height:20px;word-wrap:break-word;}
.tb-1 th{width:84px;padding-left:30px;text-align:left;font-weight:normal;color:#999; font-size:12px;}
.tb-1 td{overflow:hidden;width:195px;text-align:center;vertical-align:top;}
.tb-1 thead tr{background:#F7F7F7 url(i/20130425B.png) no-repeat 10px 50%;}
.tb-1 thead tr.active{background:#F7F7F7 url(i/20130425C.png) no-repeat 10px 50%;}
.tb-1 thead th{font-size:14px;color:#E4393C;}
.tb-1 thead td{background:#F7F7F7 none;}
.tb-1 tbody tr.differ{background:#EBFBE2;}
.tb-1 tbody tr.hover{background:#F7F7F7;}
.tb-1 tbody .no-contrast{padding:70px 0px 5px;text-align:center;font-family:"微软雅黑";font-size:25px;color:#ccc;}
.tb-1 tbody .add-contrast{text-align:center;}
.tb-1 tbody .p-price td{font-family:verdana;font-size:14px;font-weight:bold;color:#E4393C;}
.tb-1 tbody .brand td{color:#005EA7;}
.tb-1 .inner{width:250px;background:#fff;line-height:22px;}
.tb-1 .inner caption{background:#F7F7F7;font-weight:bold;text-align:center;color:#333;}
.tb-1 .inner th,.tb-1 .inner td{padding:0px;border:none;text-align:left;}
.tb-1 .inner th{width:80px;padding:0px;font-weight:normal;color:#999;}
.tb-1 .inner td{width:170px;color:#333;}
.tb-1 a:link,.tb-1 a:visited{color:#005EA7;}
.tb-1 a:hover{color:#c00;}
.tb-1 span,.tb-1 span a:link,.tb-1 span a:visited{color:#E4393C;}
.nobor tbody th,.nobor tbody td{ border-width:0px 1px 0px 0px;}

.tabcon{ clear:both;}

</style>








<div id="pcomprare">
    <div class="tabcon">
		<table class="tb-1">
			<tbody>
				<tr style="background: #EEEEEE;">
					<th style=" background: #EEEEEE; font-size:14px;">{{ $lang['Basic_info_comp'] }}</th>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr class="" style="background-color: rgb(255, 255, 255);">
					<th>{{ $lang['Model'] }}</th>

@foreach($goods_list as $goods)

					<td>
						<a href="{{ $goods['url'] }}" style="display:block;"><img width="100" src="{{ $goods['goods_thumb'] }}" alt="{{ $goods['goods_name'] }}"></a>
						<div><a href="{{ $goods['url'] }}" style="overflow:hidden;">{{ $goods['goods_name'] }}</a></div>
					</td>

@endforeach


@if($goods_count)


@foreach($goods_count as $count)

					<td>
						<div style="font-size:14px; margin-top:46px;">{{ $lang['contrast_item'] }}</div>
					</td>

@endforeach


@endif

				</tr>
				<tr class="p-price">
					<th>{{ $lang['gb_ladder_price'] }}</th>

@foreach($goods_list as $goods)

					<td>

@if($goods['promote_price'] != '')

                    	<strong>{{ $goods['promote_price'] }}</strong>

@else

                        <strong>{{ $goods['shop_price'] }}</strong>

@endif

                    </td>

@endforeach

				</tr>

				<tr class="p-price">
					<th style="font-size:14px; background:#EEEEEE;"><img onClick="collapseExpand(this,'one');" src="storage/images/btn_unfold.gif" style=" padding:3px 6px 0 0; cursor:pointer;" />{{ $lang['goods'] }}{{ $lang['compare'] }}</th>
					<td style="background:#EEEEEE;"></td>
					<td style="background:#EEEEEE;"></td>
					<td style="background:#EEEEEE;"></td>
					<td style="background:#EEEEEE;"></td>
				</tr>
			</tbody>
		</table>

		<table  class="tb-1 tb_compare ">
			<thead  style="cursor: pointer;" title="{{ $lang['compare_click_see'] }}"></thead>
			<tbody  id="tb_compare_one">

@if($brand_count != 1)

				<tr style="
@if($brand_attr_hidden)
display: none;
@endif
">
					<th style="
@if($brand_attr_highlight)
background:#EBFBE2;
@endif
">{{ $lang['brand'] }}</th>

@foreach($goods_list as $goods)

					<td  style="
@if($brand_attr_highlight)
background:#EBFBE2;
@endif
">{{ $goods['brand_name'] }}</td>

@endforeach


@if($goods_count)


@foreach($goods_count as $count)

					<td></td>

@endforeach


@endif

				</tr>

@endif


@if($weight_count != 1)

				<tr style="
@if($weight_attr_hidden)
display: none;
@endif
">
					<th style="
@if($weight_attr_highlight)
background:#EBFBE2;
@endif
">{{ $lang['goods_weight'] }}</th>

@foreach($goods_list as $goods)

					<td  style="
@if($weight_attr_highlight)
background:#EBFBE2;
@endif
">{{ $goods['goods_weight'] }}</td>

@endforeach


@if($goods_count)


@foreach($goods_count as $count)

					<td ></td>

@endforeach


@endif

				</tr>

@endif


@foreach($attribute as $key => $val)

				<tr>
					<th style="
@if($val['attr_highlight'])
background:#EBFBE2;
@endif
">{{ $val['attr_name'] }}</th>

@foreach($basic_arr as $basic)

					<td style="
@if($val['attr_highlight'])
background:#EBFBE2;
@endif
">&nbsp;&nbsp;

@foreach($basic['spe'] as $k => $property)


@if($k == $key)

						{{ $property['values'] }}

@endif


@endforeach

@foreach($basic['pro'][''] as $k => $pro)

@if($k == $key)

						{{ $pro['value'] }}

@endif


@endforeach

					</td>

@endforeach


@if($goods_count)


@foreach($goods_count as $count)

					<td></td>

@endforeach


@endif

				</tr>

@endforeach

			</tbody>
		</table>

		<div id="tbl_duibi_float" style="position: fixed; margin: 10px 0px 0px -323px;">

			<div id="locate" title="{{ $lang['compare_one'] }}" style="display:block;" onclick="isFiexd(this);"></div>
			<div id="static" title="{{ $lang['compare_two'] }}" onclick="isFiexd(this);" style="display: none;"></div>
			<div class="con">
				<a href="#" id="compare_highlight" onclick="highlightParam('@if($is_highlight == 1){!! $ids !!}@endif', @if($is_compare){{ $is_compare }}@else 0 @endif );" class="setup" style="width:99px;"><b></b>
@if($is_highlight == 1)
{{ $lang['compare_two'] }}
@else
{{ $lang['compare_three'] }}
@endif
</a>
				<a href="#" id="compare_equal" class="setup" style="width:75px;"
                   onclick="hideSameParam('@if($is_compare == 1){!! $ids !!}@endif',@if($is_highlight){{ $is_highlight }}@else 0 @endif );"><b></b>
@if($is_compare == 1)
{{ $lang['compare_four'] }}
@else
{{ $lang['compare_five'] }}
@endif
</a>
			</div>
		</div>
    </div>
 </div>

<script type="text/javascript">
function collapseExpand(obj,type)
{
	var tbl = document.getElementById('tb_compare_' + type);
	if(tbl.style.display == '')
	{
		tbl.style.display = 'none';
		obj.innerHTML = '<img src="storage/images/btn_fold.gif" style=" padding:3px 6px 0 0">';
	}
	else
	{
		tbl.style.display = '';
		obj.innerHTML = '<img src="storage/images/btn_unfold.gif">';
	}
}

function hideSameParam(ids, int)
{
	var highlight = "";
	if(int)
	{
		highlight = "&highlight=1";
	}
	if(ids == '')
	{
		window.location.href = window.location.href + '&compare=1';
	}
	else
	{
		window.location.href = "category_compare.php?" + ids + highlight;
	}
}

function highlightParam(ids, int)
{
	var compare = "";
	if(int)
	{
		compare = "&compare=1";
	}
	if(ids == '')
	{
		window.location.href = window.location.href + '&highlight=1';
	}
	else
	{
		window.location.href = "category_compare.php?" + ids + compare;
	}
}

function isFiexd(obj)
{
	var tbl_duibi_float = document.getElementById('tbl_duibi_float');
	if(tbl_duibi_float.style.position == 'fixed')
	{
		tbl_duibi_float.style.position = "static";
		document.getElementById('locate').style.display = 'none';
		document.getElementById('static').style.display = 'block';
		document.getElementById('static').style.width = '80px';
		tbl_duibi_float.style.margin = '10px auto 0px';
	}
	else
	{
		tbl_duibi_float.style.position = "fixed";
		document.getElementById('locate').style.display = 'block';
		document.getElementById('static').style.display = 'none';
		document.getElementById('static').style.width = '71px';
		tbl_duibi_float.style.margin = '10px 0px 0px -323px';
	}
}

function remove(id, url)
{
  if (document.getCookie("compareItems") != null)
  {
    var obj = document.getCookie("compareItems").parseJSON();
    delete obj[id];
    var date = new Date();
    date.setTime(date.getTime() + 99999999);
    document.setCookie("compareItems", obj.toJSONString());
  }
}
</script>
