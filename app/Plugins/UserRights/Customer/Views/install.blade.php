
<style>

</style>
<div class="warpper">
	<div class="title"><a href="{{ route('admin/user_rights/list') }}" class="s-back">{{ lang('common.back') }}</a> {{ $cfg['name'] }}</div>
	<div class="content">

		<div class="explanation" id="explanation">
			<div class="ex_tit">
				<i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4>
			</div>
			<ul>

				@foreach(lang('admin/users.user_rights_tips') as $v)
					<li>{!! $v !!}</li>
				@endforeach

			</ul>
		</div>

		<div class="flexilist">
			<div class="main-info">
				<form action="{{ route('admin/user_rights/edit', array('code' => $cfg['code'])) }}" method="post" enctype="multipart/form-data" role="form" class="form-horizontal" >
					<div class="switch_info">

						{{--基本信息--}}
						<div class="item_title">
							<div class="vertical"></div>
							<div class="f15">{{ lang('admin/users.base_title') }}</div>
						</div>

						@includeIf('admin/userrights.base_configure', ['cfg' => $cfg ?? ''])

						{{--权益设置--}}
						<div class="item_title @if(empty($cfg['rights_configure']) && empty($cfg['trigger_point'])) hide @endif ">
							<div class="vertical"></div>
							<div class="f15">{{ lang('admin/users.rights_config') }}</div>
						</div>

						{{--发放方式--}}
						<div class="item @if(empty($cfg['trigger_point'])) hide @endif ">
							<div class="label-t">{{ lang('admin/users.trigger_point') }}：</div>
							<div class="label_value">
								<input type="hidden" name="data[trigger_point]" value="{{ $cfg['trigger_point'] ?? '' }}" />
								<div class="">{{ $cfg['trigger_point_format'] ?? '' }}</div>
							</div>
						</div>

						<div class="item @if(isset($cfg['trigger_point']) &&  $cfg['trigger_point'] != 'scheduled') hide @endif ">
							<div class="label-t">{{ lang('admin/users.trigger_configure') }}：</div>
							<div class="label_value">
								<div class="w300">
									<input type="text" name="data[trigger_configure]" class="form-control" value="{{ $cfg['trigger_configure'] ?? '' }}" placeholder="" />
								</div>
							</div>
						</div>

						@includeIf('admin/userrights.rights_configure', ['rights_configure' => $cfg['rights_configure'] ?? ''])

						<div class="item">
							<div class="label-t">&nbsp;</div>
							<div class="label_value info_btn">
								@csrf
								<input type="hidden" name="code" value="{{ $cfg['code'] }}" />
								<input type="hidden" name="handler" value="{{ $cfg['handler'] ?? '' }}">
								<input type="submit" class="button btn-danger bg-red" value="{{ lang('admin/common.button_submit') }}" />

								@if (isset($cfg['install']) && $cfg['install'] == 1)
									<a class="btn btn-default" href="{{ route('admin/user_rights/list') }}" >{{ lang('admin/users.button_uninstall') }}</a>
								@endif

							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="panel panel-default" style="display: none;" id="inline">
	<div class="panel-body">
		<img src="{{ $cfg['icon'] ?? '' }}" class="img-responsive"/>
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

    // 上传图片预览
    $("input[name=rights_icon]").change(function (event) {
        // 根据这个 <input> 获取文件的 HTML5 js 对象
        var files = event.target.files, file;
        if (files && files.length > 0) {
            // 获取目前上传的文件
            file = files[0];

            // 那么我们可以做一下诸如文件大小校验的动作
            if (file.size > 1024 * 1024 * 5) {
                alert('{{ lang('admin/users.file_size_limit_5') }}');
                return false;
            }

            // 预览图片
            var reader = new FileReader();
            // 将文件以Data URL形式进行读入页面
            reader.readAsDataURL(file);
            reader.onload = function (e) {
                $(".img-responsive").attr("src", this.result);
            };
        }
    });

    // 验证提交
    $(".form-horizontal").submit(function(){

    });
</script>
