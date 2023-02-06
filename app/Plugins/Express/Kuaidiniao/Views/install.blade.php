
<style>

</style>
<div class="warpper">
	<div class="title"><a href="{{ route('admin.express.index') }}" class="s-back">{{ lang('common.back') }}</a> {{ $cfg['name'] }}</div>
	<div class="content">

		<div class="explanation" id="explanation">
			<div class="ex_tit">
				<i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4>
			</div>
			<ul>

				@foreach(lang('admin/express.express_edit_tips') as $v)
					<li>{!! $v !!}</li>
				@endforeach

			</ul>
		</div>

		<div class="flexilist">
			<div class="main-info">
				<form action="{{ route('admin.express.edit', ['code' => $cfg['code']]) }}" method="post" role="form" class="form-horizontal" >
					<div class="switch_info">

						<div class="item_title">
							<div class="vertical"></div>
							<div class="f15">{{ lang('admin/express.base_title') }}</div>
						</div>

						<div class="item">
							<div class="label-t">{{ lang('admin/express.express_name') }}：</div>
							<div class="label_value"><input type="text" name="data[name]" class="text" value="{{ $cfg['name'] }}" readonly /></div>
						</div>

						@includeIf('admin/express.express_configure', ['express_configure' => $cfg['express_configure'] ?? ''])

						<div class="item">
							<div class="label-t">{{ lang('admin/express.sort_order') }}：</div>
							<div class="label_value"><input type="number" min="0" name="data[sort]" class="text w100" value="{{ $cfg['sort'] ?? '50' }}" /></div>
						</div>

						<div class="item">
							<div class="label-t">{{ __('admin/express.is_use')  }}：</div>
							<div class="label_value">
								<div class="checkbox_items">
									<div class="checkbox_item">
										<input type="radio" name="data[default]" class="ui-radio evnet_show" id="value_117_0" value="1"
											   @if((isset($cfg['default']) && $cfg['default'] == 1) || !isset($cfg['default']))
											   checked
												@endif
										>
										<label for="value_117_0" class="ui-radio-label @if((isset($cfg['default']) && $cfg['default'] == 1) || !isset($cfg['default'])) active @endif ">{{ __('admin/common.yes') }}</label>
									</div>
									<div class="checkbox_item">
										<input type="radio" name="data[default]" class="ui-radio evnet_show" id="value_117_1" value="0"
											   @if(isset($cfg['default']) && $cfg['default'] == 0)
											   checked
												@endif
										>
										<label for="value_117_1" class="ui-radio-label @if(isset($cfg['default']) && $cfg['default'] == 0) active @endif ">{{ __('admin/common.no') }}</label>
									</div>
								</div>
							</div>
						</div>

					    <div class="item">
					        <div class="label-t">&nbsp;</div>
					        <div class="label_value info_btn">
                                @csrf
                                <input type="hidden" name="code" value="{{ $cfg['code'] }}" />
                                <input type="hidden" name="handler" value="{{ $cfg['handler'] ?? '' }}">
                                <input type="submit" class="button btn-danger bg-red" value="{{ lang('admin/common.button_submit') }}" />
					        </div>
					    </div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">

    //file移动上去的js
    $(".type-file-box").hover(function () {
        $(this).addClass("hover");
    }, function () {
        $(this).removeClass("hover");
    });

    //弹出框
    $(".fancybox").fancybox({
        width: '60%',
        height: '60%',
        closeBtn: true,
        title: ''
    });


	// 验证提交
	$(".form-horizontal").submit(function(){

        var cfg_value;

        var checkbox = $('input[name="cfg_value[]"]');
        for (var i = 0; i < checkbox.length; i++) {

            cfg_value = $('input[name="cfg_value[]"]:eq('+i+')').val();

            if (!cfg_value) {
                layer.msg('{{ lang('admin/express.cfg_value_empty') }}');
                return false;
			}
		}

	});
</script>
