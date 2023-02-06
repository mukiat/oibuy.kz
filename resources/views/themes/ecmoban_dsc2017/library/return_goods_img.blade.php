
<ul class="img-list-li">
    
@foreach($img_list as $img)

    <li>
        <a href="{{ $img['img_file'] }}" target="_blank"><img class="err-product" width="60" height="60" src="{{ $img['img_file'] }}" /></a>
    </li>
    
@endforeach

</ul>