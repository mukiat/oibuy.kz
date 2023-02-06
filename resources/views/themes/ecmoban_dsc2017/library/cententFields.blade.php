@foreach($title['cententFields'] as $fields)

    <div class="item">
        <div class="label">
            <em>
                @if($fields['will_choose'] == 1 && $choose_process == 1)
                    *
                @endif
            </em>
            <span>{{ $fields['fieldsFormName'] }}：</span>
        </div>
        <div class="value">

            @if($fields['chooseForm'] == 'input')

                <input class="text
                @if($fields['will_choose'] == 1 && $choose_process == 1)
                        required
                    @if($fields['textFields'] == 'contactEmail')
                        email
                    @endif

                    @if($fields['textFields'] == 'contactPhone')
                        isMobile
                    @endif
                @endif
                        " type="text" value="{{ $fields['titles_centents'] }}" size="{{ $fields['inputForm'] }}"
                       name="{{ $fields['textFields'] }}"
                       @if ($fields['will_choose'] == 1 && $choose_process == 1) data-process="1" id="{{ $fields['textFields'] }}" data-fields_name="{{ $fields['fieldsFormName'] }}" @else data-process="0" @endif>

            @elseif ($fields['chooseForm'] == 'other')


                @if($fields['otherForm'] == 'textArea')

                    <select name="{{ $fields['textFields'] }}[]" class="catselectB"
                            id="selCountries_{{ $fields['textFields'] }}_{{ $sn }}"
                            onchange="region.changed(this, 1, 'selProvinces_{{ $fields['textFields'] }}_{{ $sn }}')"
                            @if($fields['will_choose'] == 1 && $choose_process == 1)
                            required
                            @endif
                    >
                        <option value="">{{ $lang['please_select'] }}{{ $lang['country'] }}</option>

                        @foreach($country_list as $country)

                            <option value="{{ $country['region_id'] }}"
                                    @if($fields['textAreaForm']['country'] == $country['region_id'])
                                    selected
                                    @endif
                            >{{ $country['region_name'] }}</option>

                        @endforeach

                    </select>
                    <select name="{{ $fields['textFields'] }}[]" class="catselectB"
                            id="selProvinces_{{ $fields['textFields'] }}_{{ $sn }}"
                            onchange="region.changed(this, 2, 'selCities_{{ $fields['textFields'] }}_{{ $sn }}')"
                            @if($fields['will_choose'] == 1 && $choose_process == 1)
                            required
                            @endif
                    >
                        <option value="">{{ $lang['please_select'] }}{{ $lang['province'] }}</option>

                        @if($fields['province_list'])


                            @foreach($fields['province_list'] as $province)

                                <option value="{{ $province['region_id'] }}"
                                        @if($fields['textAreaForm']['province'] == $province['region_id'])
                                        selected
                                        @endif
                                >{{ $province['region_name'] }}</option>

                            @endforeach


                        @else


                            @foreach($province_list as $province)

                                <option value="{{ $province['region_id'] }}">{{ $province['region_name'] }}</option>

                            @endforeach


                        @endif

                    </select>
                    <select name="{{ $fields['textFields'] }}[]" class="catselectB"
                            id="selCities_{{ $fields['textFields'] }}_{{ $sn }}"
                            onchange="region.changed(this, 3, 'selDistricts_{{ $fields['textFields'] }}_{{ $sn }}')"
                            @if($fields['will_choose'] == 1 && $choose_process == 1)
                            required
                            @endif
                    >
                        <option value="">{{ $lang['please_select'] }}{{ $lang['city'] }}</option>

                        @if($fields['city_list'])


                            @foreach($fields['city_list'] as $city)

                                <option value="{{ $city['region_id'] }}"
                                        @if($fields['textAreaForm']['city'] == $city['region_id'])
                                        selected
                                        @endif
                                >{{ $city['region_name'] }}</option>

                            @endforeach


                        @else


                            @foreach($city_list as $city)

                                <option value="{{ $city['region_id'] }}">{{ $city['region_name'] }}</option>

                            @endforeach


                        @endif

                    </select>
                    <select name="{{ $fields['textFields'] }}[]" class="catselectB"
                            id="selDistricts_{{ $fields['textFields'] }}_{{ $sn }}"
                            @if($fields['textAreaForm']['district'] == 0)
                            style="display:none"
                            @endif
                    >
                        <option value="">{{ $lang['please_select'] }}{{ $lang['area'] }}</option>

                        @if($fields['district_list'])


                            @foreach($fields['district_list'] as $district)

                                <option value="{{ $district['region_id'] }}"
                                        @if($fields['textAreaForm']['district'] == $district['region_id'])
                                        selected
                                        @endif
                                >{{ $district['region_name'] }}</option>

                            @endforeach


                        @else


                            @foreach($district_list as $district)

                                <option value="{{ $district['region_id'] }}">{{ $district['region_name'] }}</option>

                            @endforeach


                        @endif

                    </select>

                @elseif ($fields['otherForm'] == 'dateFile')

                    <div class="type-file-box">
                        <input type="button" name="button" class="type-file-button" id="button" value=""/>
                        <input type="file" name="{{ $fields['textFields'] }}" class="type-file-file"
                               value="{{ $fields['titles_centents'] }}"
                               @if($fields['will_choose'] == 1 && $choose_process == 1)
                               required
                               @endif
                               data-state="" hidefocus="true"/>

                        <input type="text" name="textfile" class="type-file-text" value="" readonly/>
                        <input type="hidden" name="text_{{ $fields['textFields'] }}"
                               value="{{ $fields['titles_centents'] }}"/>
                    </div>

                @elseif ($fields['otherForm'] == 'dateTime')


                    @foreach($fields['dateTimeForm'] as $dk => $date)


                        @if($dk == 0)

                            <input id="{{ $fields['textFields'] }}_{{ $dk }}" class="text text-2 jdate narrow"
                                   type="text" size="{{ $date['dateSize'] }}" readonly
                                   value="{{ $date['dateCentent'] }}" name="{{ $fields['textFields'] }}[]">

                        @else

                            <span>—</span>
                            <input id="{{ $fields['textFields'] }}_{{ $dk }}" class="text text-2 jdate narrow"
                                   type="text" size="{{ $date['dateSize'] }}" readonly
                                   value="{{ $date['dateCentent'] }}" name="{{ $fields['textFields'] }}[]">
                            <div class="cart-checkbox fr">
                                <input type="checkbox" class="ui-checkbox CheckBoxShop"
                                       onclick="get_shopTime_term(this)" name="shopTime_term" value="1"
                                       id="shopTime_term"
                                       @if($fields['shopTime_term'] == 1)
                                       checked
                                        @endif
                                >
                                <label for="shopTime_term">{{ $lang['permanent'] }}</label>
                            </div>

                        @endif


                    @endforeach


                @endif


            @elseif ($fields['chooseForm'] == 'textarea')

                <textarea name="{{ $fields['textFields'] }}" cols="{{ $fields['cols'] }}" rows="{{ $fields['rows'] }}"
                          @if($fields['will_choose'] == 1 && $choose_process == 1)
                          required
                        @endif
                >{{ $fields['titles_centents'] }}</textarea>

            @elseif ($fields['chooseForm'] == 'select')

                <select name="{{ $fields['textFields'] }}"
                        @if($fields['will_choose'] == 1 && $choose_process == 1)
                        required
                        @endif
                >
                    <option value="0" selected="selected">{{ $lang['please_select'] }}</option>

                    @foreach($fields['selectList'] as $selectList)

                        <option value="{{ $selectList }}"
                                @if($fields['titles_centents'] == $selectList)
                                selected="selected"
                                @endif
                        >{{ $selectList }}</option>

                    @endforeach

                </select>

            @elseif ($fields['chooseForm'] == 'radio')

                <div class="value-checkbox">

                    @foreach($fields['radioCheckboxForm'] as $rc_k => $radio)

                        <div class="value-item
                        @if($fields['titles_centents'])

                            @if($fields['titles_centents'] == $radio['radioCheckbox'])
                                    selected
                            @else
                                @if($rc_k == 0)
                                        checked
                                @endif
                            @endif

                        @else
                            @if($loop->iteration<2)
                                    selected
                            @endif
                        @endif
                                "><input name="{{ $fields['textFields'] }}" class="ui-radio"
                                         id="{{ $fields['textFields'] }}-{{ $loop->index }}" type="radio"
                                         value="{{ $radio['radioCheckbox'] }}"
                                         @if($fields['titles_centents'] == $radio['radioCheckbox'])
                                         checked="checked"
                                         @else

                                         @if($rc_k == 0)
                                         checked="checked"
                                    @endif

                                    @endif
                            /><label for="{{ $fields['textFields'] }}-{{ $loop->index }}"
                                     class="ui-radio-label">{{ $radio['radioCheckbox'] }}</label></div>

                    @endforeach

                </div>

            @elseif ($fields['chooseForm'] == 'checkbox')

                <div class="chekbox-items">

                    @foreach($fields['radioCheckboxForm'] as $rc_k => $checkbox)

                        <div class="chekbox-item fl mr20">
                            <input name="{{ $fields['textFields'] }}[]" type="checkbox"
                                   value="{{ $checkbox['radioCheckbox'] }}" class="ui-checkbox"
                                   id="{{ $fields['textFields'] }}_{{ $checkbox['radioCheckbox'] }}"
                                   @if($fields['titles_centents'] == $checkbox['radioCheckbox'])
                                   checked="checked"
                                   @else

                                   @if($rc_k == 0)
                                   checked="checked"
                                   @endif

                                   @endif

                                   @if($fields['will_choose'] == 1 && $choose_process == 1)
                                   required
                                    @endif
                            />
                            <label class="ui-label"
                                   for="{{ $fields['textFields'] }}_{{ $checkbox['radioCheckbox'] }}">{{ $checkbox['radioCheckbox'] }}</label>
                        </div>

                    @endforeach

                </div>

            @endif

            <div class="org hide">{{ $fields['formSpecial'] }}</div>

            @if ($choose_process == 1)
                <div class="verify" id="error_{{ $fields['textFields'] }}"></div>
            @endif
        </div>
    </div>

@endforeach
