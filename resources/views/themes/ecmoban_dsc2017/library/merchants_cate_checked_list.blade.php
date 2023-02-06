
<table class="table">
    <thead>
        <tr>
            <th width="200">{{ $lang['Serial_number'] }}</th>
            <th width="300">{{ $lang['one_category'] }}</th>
            <th width="300">{{ $lang['two_category'] }}</th>
            <th width="110">{{ $lang['handle'] }}</th>
        </tr>
    </thead>
    <tbody>
    
@if($category_info)

    
@foreach($category_info as $k => $category)

        <tr class="seller_category">
            <td>
                <p>
                    <span class="index">{{ $k }}</span>
                    <input type="hidden" value="{{ $category['cat_id'] }}" name="cat_id[]" class="cId">
                </p>
            </td>
            <td>
                <p>
                    <input type="hidden" value="{{ $category['parent_name'] }}" name="parent_name[]" class="cl1Name">
                    {{ $category['parent_name'] }}
                </p>
            </td>
            <td>
                <p>
                    <input type="hidden" value="{{ $category['cat_name'] }}" name="cat_name[]" class="cl2Name">
                    {{ $category['cat_name'] }}
                </p>
            </td>
            <td align="center"><p><a class="ftx-05 removeDetailCategoryBtn" href="javascript:void(0);" onClick="deleteChildCate({{ $category['ct_id'] }})"><span>{{ $lang['drop'] }}</span></a></p></td>
        </tr>
    
@endforeach
    
    
@endif

    </tbody>
</table>