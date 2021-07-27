@extends('ERP.test0')
@section('content')
    <div style="margin: 0 20px">
        <div>收料單+發票</div>
        <form action="/test0/ro1" method="post">
            {{ csrf_field() }}
            <label for="start">
                <input id="start" name="start" type="date" value="{{ old('start') }}" required
                       onchange="document.getElementById('export_start').value = this.value;">
            </label>
            <label for="end">
                <input id="end" name="end" type="date" value="{{ old('end') }}" required
                       onchange="document.getElementById('export_end').value = this.value;">
            </label>
            <label for="status">
                <select id="status" name="status"
                        onchange="document.getElementById('export_status').value = this.value;">
                    <option value="ALL" @if (old('status') == "ALL") {{ 'selected' }} @endif>ALL</option>
                    <option value="RECEIVED" @if (old('status') == "RECEIVED") {{ 'selected' }} @endif>RECEIVED</option>
                    <option value="CLOSED" @if (old('status') == "CLOSED") {{ 'selected' }} @endif>CLOSED</option>
                </select>
            </label>
            <label for="vendor">
                <input id="vendor" name="vendor" type="text" value="{{ old('vendor') }}"
                       placeholder="供應商"
                       onchange="document.getElementById('export_vendor').value = this.value;">
            </label>
            <label for="partNumber">
                <input id="partNumber" name="partNumber" type="text" value="{{ old('partNumber') }}"
                       placeholder="工具號"
                       onchange="document.getElementById('export_partNumber').value = this.value;">
            </label>
            <button type="submit">預覽</button>
            <a href=""
               onclick="event.preventDefault(); document.getElementById('export-form').submit();">
                {{ __('匯出') }}
            </a>
        </form>
        <form id="export-form" action="/test0/ro1export" method="POST" style="display: none;">
            @csrf
            <input type="hidden" id="export_start" name="start" value={{ old('start') }}>
            <input type="hidden" id="export_end" name="end" value={{ old('end') }}>
            <input type="hidden" id="export_status" name="status" value={{ old('status') }}>
            <input type="hidden" id="export_vendor" name="vendor" value={{ old('vendor') }}>
            <input type="hidden" id="export_partNumber" name="partNumber" value={{ old('partNumber') }}>
        </form>
    </div>
    <div style="margin: 0 20px">
        @if (count(session()->get('list')??[]) > 0)
            <div>共{{ count(session()->get('list')??[]) }}筆</div>
            <table style="width: 100%;" border='1'>
                <thead>
                <tr>
                    <td>狀態</td>
                    <td>收貨日期</td>
                    <td>收料單編號</td>
                    <td>項號</td>
                    <td>零件號</td>
                    <td>類別</td>
                    <td>描述</td>
                    <td>採購單號</td>
                    <td>供應商編號</td>
                    <td>採購單數量</td>
                    <td>採購單收貨數</td>
                    <td>收貨數</td>
                    <td>貨位</td>
                    <td>成本/單位</td>
                    <td>稅率</td>
                    <td>折扣率</td>
                    <td>小計成本</td>
                    <td>貨幣</td>
                    <td>發票</td>
                    <td>發票日期</td>
                    <td>供應商銷售單號</td>
                    <td>應付/應付申請</td>
                    <td>供應商發貨單</td>
                    <td>採購收料日期</td>
                    <td>最初發貨日期</td>
                    <td>供應商發貨日期</td>
                    <td>標準成本</td>
                    <td>標準成本小計</td>
                    <td>匯率</td>
                    <td>會計科目</td>
                    <td>成本中心</td>
                    <td>採購員</td>
                    <td>收貨人</td>
                    <td>條件</td>
                </tr>
                </thead>
                <tbody>
                @foreach(session()->get('list')??[] as $key => $value)
                    <tr>
                        <td>{{ $value['status'] }}</td>
                        <td>{{ $value['receivedDate'] }}</td>
                        <td>{{ $value['receiverNumber'] }}</td>
                        <td>{{ $value['itemNumber'] }}</td>
                        <td>{{ $value['partNumber'] }}</td>
                        <td>{{ $value['partType'] }}</td>
                        <td>{{ $value['description'] }}</td>
                        <td>{{ $value['poNumber'] }}</td>
                        <td>{{ $value['vendorId'] }}</td>
                        <td>{{ floatval($value['qtyOrdered']) }}</td>
                        <td>{{ floatval($value['poReceived']) }}</td>
                        <td>{{ floatval($value['qtyReceived']) }}</td>
                        <td>{{ $value['location'] }}</td>
                        <td>{{ floatval($value['costPerUnit']) }}</td>
                        <td>{{ floatval($value['taxRate']) }}</td>
                        <td>{{ floatval($value['discount']) }}</td>
                        <td>{{ floatval($value['extendedCost']) }}</td>
                        <td>{{ $value['currency'] }}</td>
                        <td>{{ $value['invoice'] }}</td>
                        <td>{{ $value['invoiceDate'] }}</td>
                        <td>{{ $value['vendorSalesOrder'] }}</td>
                        <td>{{ $value['payable'] }}</td>
                        <td>{{ $value['vendorPackage'] }}</td>
                        <td>{{ $value['poShipDate'] }}</td>
                        <td>{{ $value['origialShipDate'] }}</td>
                        <td>{{ $value['vendorShipDate'] }}</td>
                        <td>{{ floatval($value['standardCost']) }}</td>
                        <td>{{ floatval($value['standardExtendedCost']) }}</td>
                        <td>{{ floatval($value['exchangeRate']) }}</td>
                        <td>{{ $value['accountNumber'] }}</td>
                        <td>{{ $value['companyUnitId'] }}</td>
                        <td>{{ $value['buyer'] }}</td>
                        <td>{{ $value['receivedBy'] }}</td>
                        <td>{{ $value['conditions'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
