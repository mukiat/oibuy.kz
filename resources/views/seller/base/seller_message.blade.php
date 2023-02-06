@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<div class="list-div" style="text-align:center;">
    <div class="fh_message">
        <div class="fr_content">
            <div class="img">
                @if(isset($data['type']) && $data['type'] == 1)
                    <i class="fh_icon information"></i>
                @elseif(isset($data['type']) && $data['type'] == 2)
                    <i class="fh_icon confirm"></i>
                @else
                    <i class="fh_icon warning"></i>
                @endif
            </div>
            <h3 class="
                    @if(isset($data['type']) && $data['type'] == 1)
                    information
                    @elseif(isset($data['type']) && $data['type'] == 2)
                    confirm
                    @else
                    warning
                    @endif
                    ">{!! $data['message'] ?? '' !!}</h3>
            <span class="ts" id="redirectionMsg">{!! __('admin/common.auto_redirection') !!}</span>
            <ul class="msg-link">
                @if(!empty($data['url']))
                    <li><a href="{!! $data['url'] !!}">{{ __('common.back_up_page') }}</a></li>
                @endif

                @if(!empty($data['links']) && is_array($data['links']))
                    @foreach($data['links'] as $val)
                        <li><a href="{!! $val['href'] !!}" >{{ $val['text'] ?? '' }}</a></li>
                    @endforeach
                @endif

            </ul>
        </div>
    </div>
</div>

<script type="text/javascript">
    // 自动跳转
    $(function () {

        var time = '{{ $data['second'] ?? 2 }}';
        var href = '{!! $data['url'] !!}';

        document.getElementById('spanSeconds').innerHTML = time;

        var interval = setInterval(function () {
            --time;

            document.getElementById('spanSeconds').innerHTML = time;

            if (time <= 0) {
                window.location.href = href;
                clearInterval(interval);
            }
        }, 1000);
    });
</script>

</body>
</html>
