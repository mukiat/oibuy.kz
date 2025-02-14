

@if($favourable_list)

    <div class="ncc-title" style="height:25px; border:0px;">
		<h3 style="font-size:18px;">{{ $lang['label_favourable'] }}</h3>
	</div>

@foreach($favourable_list as $favourable)

        <form action="flow.php" method="post">
          <table width="99%" class="ncc-table-style" align="center" border="0" cellpadding="5" cellspacing="1" bgcolor="#dddddd">
            <tr>
              <td align="right" bgcolor="#ffffff">{{ $lang['favourable_name'] }}</td>
              <td bgcolor="#ffffff" style="text-align:left;"><strong>{{ $favourable['act_name'] }}</strong></td>
            </tr>
            <tr>
              <td align="right" bgcolor="#ffffff">{{ $lang['favourable_period'] }}</td>
              <td bgcolor="#ffffff" style="text-align:left;">{{ $favourable['start_time'] }} --- {{ $favourable['end_time'] }}</td>
            </tr>
            <tr>
              <td align="right" bgcolor="#ffffff">{{ $lang['favourable_range'] }}</td>
              <td bgcolor="#ffffff" style="text-align:left;">{{ $lang['far_ext'][$favourable['act_range']] }}<br />
              {{ $favourable['act_range_desc'] }}</td>
            </tr>
            <tr>
              <td align="right" bgcolor="#ffffff">{{ $lang['favourable_amount'] }}</td>
              <td bgcolor="#ffffff" style="text-align:left;">{{ $favourable['formated_min_amount'] }} --- {{ $favourable['formated_max_amount'] }}</td>
            </tr>
            <tr>
              <td align="right" bgcolor="#ffffff">{{ $lang['favourable_type'] }}</td>
              <td bgcolor="#ffffff" style="text-align:left;">
                <span class="STYLE1 fl clearfix">{{ $favourable['act_type_desc'] }}</span>

@if($favourable['act_type'] == 0)


@foreach($favourable['gift'] as $gift)

                  <span style="padding-top:5px; clear:both;" class="fl clearfix"><input type="checkbox" value="{{ $gift['id'] }}" name="gift[]" />
                  <a href="goods.php?id={{ $gift['id'] }}" target="_blank" class="f6" style="color:#d93600">{{ $gift['name'] }}</a> [{{ $gift['formated_price'] }}]
                  </span>

@endforeach


@endif
          </td>
            </tr>

@if($favourable['available'])

            <tr>
              <td align="right" bgcolor="#ffffff">&nbsp;</td>
              <td align="center" bgcolor="#ffffff"><input type="image" alt=""  border="0" class="addto_cart" value="" /></td>
            </tr>

@endif

          </table>
          <input type="hidden" name="act_id" value="{{ $favourable['act_id'] }}" />
          <input type="hidden" name="step" value="add_favourable" />
        @csrf </form>

@endforeach


@endif
