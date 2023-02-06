<script type="text/javascript">

@foreach($lang['js_languages'] as $key => $item)

var {{ $key }} = "{!! $item !!}";

@endforeach

</script>
