

@if($cate_list)


@foreach($cate_list as $cate)


@if($user_center)

<div class="checkbox_item">
	<input type="checkbox" name="cateChild[]" class="ui-checkbox" value="{{ $cate['cat_id'] }}" id="cateChild_{{ $cate['cat_id'] }}">
	<label for="cateChild_{{ $cate['cat_id'] }}" class="ui-label">{{ $cate['cat_name'] }}</label>
</div>

@else

<div class="cart-checkbox"><input type="checkbox" name="cateChild[]" class="ui-checkbox CheckBoxShop" value="{{ $cate['cat_id'] }}" id="cate_{{ $cate['cat_id'] }}"><label for="cate_{{ $cate['cat_id'] }}">{{ $cate['cat_name'] }}</label></div>

@endif


@endforeach

<input name="oneCat_id" value="{{ $cat_id }}" id="oneCat_id" type="hidden">

@else

{{ $lang['select_one_category'] }}

@endif
