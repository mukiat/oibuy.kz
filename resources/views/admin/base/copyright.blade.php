@if(!empty(config('shop.show_copyright')))
    <div class="copyright">
        @if(!empty(config('shop.copyright_link')))
            <p><a href="{!! config('shop.copyright_link') !!}" target="_blank">{!! config('shop.copyright_text') !!}</a></p>
        @else
            <p>{!! config('shop.copyright_text') !!}</p>
        @endif
    </div>
@endif