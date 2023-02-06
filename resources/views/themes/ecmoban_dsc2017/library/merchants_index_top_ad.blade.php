
@if($ad_child)


@foreach($ad_child as $ad)

<div class="sett-banner" style="background:url({{ $ad['ad_code'] }}) center center no-repeat;">
    <div class="banner-auto">
        <div class="s-b-tit">
            <h3>{{ $lang['sett_title'] }}</h3>
            <div class="s-b-line"></div>
        </div>
        <div class="s-b-btn">
            <a href="javascript:void(0);" data-url="{{ $url_merchants_steps }}" class="im-sett" ectype="url_merchants_steps">{{ $lang['settled_down'] }}</a>
            <a href="javascript:void(0);" data-url="{{ $url_merchants_steps_site }}" class="view-prog" ectype="url_merchants_steps">{{ $lang['settled_down_schedule'] }}</a>
        </div>
    </div>
</div>

@endforeach

<script type="text/javascript">
$("*[ectype='url_merchants_steps']").on("click",function(){
	var url = $(this).data("url");
	var user_id = "{{ $user_id }}"
	if(user_id > 0){
		location.href = url;
	}else{
		var back_url = location.href.split('#')[0];  // current url remove hash
		$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
	}
});
</script>

@endif
