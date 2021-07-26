@extends('ERP.test0')
@section('content')
    <div style="margin: 20px">
        <div>採購單紀錄</div>
        <form action="/test0/po1" method="post">
            {{ csrf_field() }}
            <label for="start">
                <input id="start" name="start" type="date" value="{{ $start }}" required
                       onchange="document.getElementById('export_start').value = this.value;">
            </label>
            <label for="end">
                <input id="end" name="end" type="date" value="{{ $end }}" required
                       onchange="document.getElementById('export_end').value = this.value;">
            </label>
            <label for="status">
                <select id="status" name="status"
                        onchange="document.getElementById('export_status').value = this.value;">
                    <option value="ALL" @if ($status == "ALL") {{ 'selected' }} @endif>ALL</option>
                    <option value="OPEN" @if ($status == "OPEN") {{ 'selected' }} @endif>OPEN</option>
                    <option value="NOT APPROVED" @if ($status == "NOT APPROVED") {{ 'selected' }} @endif>NOT APPROVED</option>
                    <option value="CLOSED" @if ($status == "CLOSED") {{ 'selected' }} @endif>CLOSED</option>
                    <option value="CANCELLED" @if ($status == "CANCELLED") {{ 'selected' }} @endif>CANCELLED</option>
                </select>
            </label>
            <label for="vendor">
                <input id="vendor" name="vendor" type="text" value="{{ $vendor }}"
                       placeholder="供應商"
                       onchange="document.getElementById('export_vendor').value = this.value;">
            </label>
            <label for="partNumber">
                <input id="partNumber" name="partNumber" type="text" value="{{ $partNumber }}"
                       placeholder="工具號"
                       onchange="document.getElementById('export_partNumber').value = this.value;">
            </label>
            <button type="submit">預覽</button>
            <a href=""
               onclick="event.preventDefault(); document.getElementById('export-form').submit();">
                {{ __('匯出') }}
            </a>
        </form>
        <form id="export-form" action="/test0/po1export" method="POST" style="display: none;">
            @csrf
            <input type="hidden" id="export_start" name="start" value={{ $start }}>
            <input type="hidden" id="export_end" name="end" value={{ $end }}>
            <input type="hidden" id="export_status" name="status" value={{ $status }}>
            <input type="hidden" id="export_vendor" name="vendor" value={{ $vendor }}>
            <input type="hidden" id="export_partNumber" name="partNumber" value={{ $partNumber }}>
        </form>
    </div>
    <div style="margin: 20px">
        @if (count($list) > 0)
            <div>共{{ count($list) }}筆</div>
            <table style="border:1px #cccccc solid; width: 100%;" border='1'>
                <thead>
                <tr>
                    <td>供應商</td>
                    <td>採購單日期</td>
                    <td>採購單號</td>
                    <td>項號</td>
                    <td>刀具工具號</td>
                    <td>種類</td>
                    <td>描述</td>
                    <td>原始發貨日期</td>
                    <td>發貨日期</td>
                    <td>採購單數量</td>
                    <td>收貨數量</td>
                    <td>未交數量</td>
                    <td>工具備註</td>
                    <td>成本單位</td>
                    <td>貨幣</td>
                    <td>單位</td>
                    <td>採購員</td>
                    <td>折扣率</td>
                    <td>小計</td>
                    <td>狀態</td>
                    <td>項目</td>
                    <td>申請人</td>
                    <td>delayDays</td>
                    <td>未收料金額</td>
                    <td>收料金額</td>
                    <td>收貨方</td>
                    <td>稅小計</td>
                    <td>到達時間</td>
                    <td>相關文檔</td>
                    <td>最後收貨日期</td>
                    <td>會計科目</td>
                    <td>companyUnitId</td>
                    <td>產品編碼</td>
                    <td>在途數</td>
                    <td>exchange</td>
                    <td>發貨方式</td>
                    <td>供應商銷售單號</td>
                </tr>
                </thead>
                <tbody>
                @foreach($list as $key => $value)
                    <tr>
                        <td>{{ $value['供應商'] }}</td>
                        <td>{{ $value['採購單日期'] }}</td>
                        <td>{{ $value['採購單號'] }}</td>
                        <td>{{ $value['項號'] }}</td>
                        <td>{{ $value['刀具工具號'] }}</td>
                        <td>{{ $value['種類'] }}</td>
                        <td>{{ $value['描述'] }}</td>
                        <td>{{ $value['原始發貨日期'] }}</td>
                        <td>{{ $value['發貨日期'] }}</td>
                        <td>{{ floatval($value['採購單數量']) }}</td>
                        <td>{{ floatval($value['收貨數量']) }}</td>
                        <td>{{ floatval($value['未交數量']) }}</td>
                        <td>{{ $value['工具備註'] }}</td>
                        <td>{{ floatval($value['成本單位']) }}</td>
                        <td>{{ $value['貨幣'] }}</td>
                        <td>{{ $value['單位'] }}</td>
                        <td>{{ $value['採購員'] }}</td>
                        <td>{{ floatval($value['折扣率']) }}</td>
                        <td>{{ floatval($value['小計']) }}</td>
                        <td>{{ $value['狀態'] }}</td>
                        <td>{{ $value['項目'] }}</td>
                        <td>{{ $value['申請人'] }}</td>
                        <td>{{ $value['delayDays'] }}</td>
                        <td>{{ floatval($value['未收料金額']) }}</td>
                        <td>{{ floatval($value['收料金額']) }}</td>
                        <td>{{ $value['收貨方'] }}</td>
                        <td>{{ floatval($value['稅小計']) }}</td>
                        <td>{{ $value['到達時間'] }}</td>
                        <td>{{ $value['相關文檔'] }}</td>
                        <td>{{ $value['最後收貨日期'] }}</td>
                        <td>{{ $value['會計科目'] }}</td>
                        <td>{{ $value['companyUnitId'] }}</td>
                        <td>{{ $value['產品編碼'] }}</td>
                        <td>{{ floatval($value['在途數']) }}</td>
                        <td>{{ floatval($value['exchange']) }}</td>
                        <td>{{ $value['發貨方式'] }}</td>
                        <td>{{ $value['供應商銷售單號'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
