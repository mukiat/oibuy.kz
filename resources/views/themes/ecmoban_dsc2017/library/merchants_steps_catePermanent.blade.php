
<table id="detailCategoryQuaTable" class="table">
    <thead>
        <tr>
            <th width="250">{{ $lang['leimu_name'] }}</th>
            <th width="150">{{ $lang['zizhi_name'] }}</th>
            <th width="250">{{ $lang['Electronic'] }}</th>
            <th width="260">{{ $lang['Due_date'] }}</th>
        </tr>
    </thead>
    <tbody>
    	
@foreach($permanent_list as $pk => $permanent)

        <tr>
            <td>
                {{ $permanent['cat_name'] }}<input type="hidden" value="{{ $permanent['cat_id'] }}" name="permanentCat_id_{{ $permanent['cat_id'] }}[]">
            </td>
            <td>
                {{ $permanent['dt_title'] }}
                <input type="hidden" value="{{ $permanent['dt_id'] }}" name="permanent_title_{{ $permanent['cat_id'] }}[]">
            </td>
            <td>
                <div class="type-file-box">
                    <input type="button" name="button" class="type-file-button" id="button" value="" />
                    <input type="file" name="permanentFile_{{ $permanent['cat_id'] }}[]" class="type-file-file" value="{{ $permanent['permanent_file'] }}" data-state="" hidefocus="true" />
                    
@if($permanent['permanent_file'])
<a href="{{ $permanent['permanent_file'] }}" class="chakan" target="_blank">{{ $lang['view'] }}</a>
@endif

                    <input type="text" name="textfile" class="type-file-text" style="width:150px;" value="{{ $permanent['permanent_file'] }}" readonly />
                </div>
            </td>
            <td>
                
@if($permanent['permanent_date'])

                <div class="cart-checkbox">
                    <input id="categoryId_date_{{ $permanent['dt_id'] }}" class="text text-3 jdate narrow" type="text" size="17" readonly value="{{ $permanent['permanent_date'] }}" name="categoryId_date_{{ $permanent['cat_id'] }}[]">
                    <input type="checkbox" id="categoryId_permanent_{{ $permanent['dt_id'] }}" class="ui-checkbox CheckBoxShop" value="1" name="categoryId_permanent_{{ $permanent['cat_id'] }}[]">
                    <label for="categoryId_permanent_{{ $permanent['dt_id'] }}">{{ $lang['permanent'] }}</label>
                </div>
                
@else

                <div class="cart-checkbox">
                <input id="categoryId_date_{{ $permanent['dt_id'] }}" class="text text-3 jdate narrow" type="text" size="17" readonly value="" name="categoryId_date_{{ $permanent['cat_id'] }}[]">
                <input type="checkbox" id="categoryId_permanent_{{ $permanent['dt_id'] }}" class="ui-checkbox CheckBoxShop" 
@if($permanent['cate_title_permanent'] == 1)
checked
@endif
 value="1" name="categoryId_permanent_{{ $pk }}">
                <label for="categoryId_permanent_{{ $permanent['dt_id'] }}">{{ $lang['permanent'] }}</label>
                </div>
                
@endif

            </td>
        </tr>
        <script type="text/javascript">
			var opts = {
				'targetId':'categoryId_date_{{ $permanent['dt_id'] }}',
				'triggerId':['categoryId_date_{{ $permanent['dt_id'] }}'],
				'alignId':'categoryId_date_{{ $permanent['dt_id'] }}',
				'hms':'off',
				'format':'-'
			}
			xvDate(opts);
		</script>
        
@endforeach

    </tbody>
</table>