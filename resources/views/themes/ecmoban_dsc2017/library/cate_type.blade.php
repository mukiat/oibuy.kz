
<div class="panel-body">
    <div class="panel-tit"><span>{{ $title['fields_titles'] }}</span></div>
    <div class="cue">{!! $title['titles_annotation'] !!}</div>
    <div class="list">
        @include('frontend::library/cententFields')
        <div class="item">
            <div class="label">
                <em>*</em>
                <span>{{ $lang['Main_category'] }}：</span>
            </div>
            <div class="value">
                <div class="imitate_select w200 shop_categoryMain" id="shop_categoryMain_id">
                    <div class="cite"><span>{{ $lang['Please_select'] }}</span><i class="iconfont icon-down"></i></div>
                    <ul>
                        <li><a href="javascript:void(0);" data-value="0">{{ $lang['Please_select'] }}</a></li>

@foreach($title['first_cate'] as $cate)

                        <li><a href="javascript:void(0);" data-value="{{ $cate['cat_id'] }}">{{ $cate['cat_name'] }}</a></li>

@endforeach

                    </ul>
                    <input type="hidden" name="ec_shop_categoryMain" value="{{ $title['parentType']['shop_category_main'] }}" id="shop_categoryMain_id_val" />
                </div>
                <label class="error" id="cate_Html"></label>
            </div>
        </div>
        <div class="item">
            <div class="label">
                <em>*</em>
                <span>{{ $lang['Detailed_category'] }}：</span>
            </div>
            <div class="value">
                <input id="addCategoryBtn" class="btns" type="button" value="{{ $lang['add'] }}">
                <a class="ml10 ftx-05" target="_blank" href="article.php?id=42">{{ $lang['category_Cost'] }} >></a>
                <input type="hidden" name="detailed_category" value="{{ $category_count }}" />
                <div id="divSCA">
                    <div class="mod">
                        <div class="mod_list">
                            <div class="mod-label">{{ $lang['one_category'] }}：</div>
                            <div class="mod-value">
                                <div class="imitate_select w200" id="addCategoryMain_Id">
                                    <div class="cite"><span>{{ $lang['Please_select'] }}</span><i class="iconfont icon-down"></i></div>
                                    <ul>
                                        <li><a href="javascript:void(0);" data-value="0">{{ $lang['Please_select'] }}</a></li>

@foreach($title['first_cate'] as $cate)

                                        <li><a href="javascript:void(0);" data-value="{{ $cate['cat_id'] }}">{{ $cate['cat_name'] }}</a></li>

@endforeach

                                    </ul>
                                    <input type="hidden" name="addCategoryMain" value="{{ $title['parentType']['shop_category_main'] }}" id="addCategoryMain_Id_val" />
                                </div>
                            </div>
                        </div>
                        <div class="mod_list">
                            <div class="mod-label">{{ $lang['two_category'] }}：</div>
                            <div class="mod-value">
                                <div class="cart-checkbox">
                                    <input type="checkbox" class="ui-checkbox CheckBoxShop" name="addCategoryBtn[]" id="getCateAll">
                                    <label for="getCateAll">{{ $lang['check_all_back'] }}</label>
                                </div>
                            </div>
                            <div class="mod_span" id="steps_re_span">{{ $lang['select_one_category'] }}</div>
                        </div>
                    </div>
                </div>
                <div id="detailCategoryTable">
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
                </div>
            </div>
        </div>
        <div class="item">
            <div class="label">
                <span>{{ $lang['Corresponding_zizhi'] }}：</span>
            </div>
            <div class="value deletePane">
                <div class="tit">{{ $lang['Corresponding_zizhi_one'] }}<a class="ftx-05" target="_blank" href="article.php?id=42">《{{ $lang['hangye_zizhi_bz'] }}》</a>。</div>
                <div id="category_permanent">
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
                                    <input id="categoryId_date_{{ $permanent['dt_id'] }}" class="text text-2 jdate narrow" type="text" size="17" readonly value="{{ $permanent['permanent_date'] }}" name="categoryId_date_{{ $permanent['cat_id'] }}[]">
                                    <input type="checkbox" id="categoryId_permanent_{{ $permanent['dt_id'] }}" value="1" name="categoryId_permanent_{{ $permanent['cat_id'] }}[]" class="ui-checkbox CheckBoxShop" >
                                    <label for="categoryId_permanent_{{ $permanent['dt_id'] }}" class="ui-label-14">{{ $lang['permanent'] }}</label>
                                    </div>

@else

                                    <div class="cart-checkbox">
                                    <input id="categoryId_date_{{ $permanent['dt_id'] }}" class="text text-2 jdate narrow" type="text" size="17" readonly value="" name="categoryId_date_{{ $permanent['cat_id'] }}[]">
                                    <input type="checkbox" id="categoryId_permanent_{{ $permanent['dt_id'] }}"
@if($permanent['cate_title_permanent'] == 1)
checked
@endif
 value="1" name="categoryId_permanent_{{ $pk }}" class="ui-checkbox CheckBoxShop">
                                    <label for="categoryId_permanent_{{ $permanent['dt_id'] }}" class="ui-label-14">{{ $lang['permanent'] }}</label>
                                    </div>

@endif

                                </td>
                            </tr>
                            <script type="text/javascript">
                                var opts_{{ $permanent['dt_id'] }} = {
                                    'targetId':'categoryId_date_{{ $permanent['dt_id'] }}',
                                    'triggerId':['categoryId_date_{{ $permanent['dt_id'] }}'],
                                    'alignId':'categoryId_date_{{ $permanent['dt_id'] }}',
                                    'hms':'off',
                                    'format':'-'
                                }
                                xvDate(opts_{{ $permanent['dt_id'] }});
                            </script>

@endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
