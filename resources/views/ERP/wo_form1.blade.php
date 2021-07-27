@extends('ERP.test0')
@section('content')
    <div style="margin: 0 20px">
        <div>工單調度</div>
        <form action="/test0/wo1" method="post">
            {{ csrf_field() }}
            <label for="start">
                <input id="start" name="start" type="date" value="{{ old('start') }}" required
                       onchange="document.getElementById('export_start').value = this.value;">
            </label>
            <label for="end">
                <input id="end" name="end" type="date" value="{{ old('end') }}" required
                       onchange="document.getElementById('export_end').value = this.value;">
            </label>
            <button type="submit">預覽</button>
            <a href=""
               onclick="event.preventDefault(); document.getElementById('export-form').submit();">
                {{ __('匯出') }}
            </a>
        </form>
        <form id="export-form" action="/test0/wo1export" method="POST" style="display: none;">
            @csrf
            <input type="hidden" id="export_start" name="start" value="{{ old('start') }}">
            <input type="hidden" id="export_end" name="end" value="{{ old('end') }}">
        </form>
    </div>
    <div style="margin: 0 20px">
        @if (count(session()->get('list')??[]) > 0)
            <div>共{{ count(session()->get('list')??[]) }}筆</div>
            <table style="width: 100%;" border='1'>
                <thead>
                <tr>
                    <td>工單</td>{{--orderNumber--}}
                    <td>工單項</td>{{--orderItemNumber--}}
                    <td>pNum</td>{{--partNumber--}}
                    <td>pType</td>{{--partType--}}
                    <td>pDes</td>{{--partDescription--}}
                    <td>數量</td>{{--quantity--}}
                    <td>已發</td>{{--issuedQuatity--}}
                    <td>已收</td>{{--quantityReceived--}}
                    <td>庫存</td>{{--quantityOnhand--}}
                    <td>在途數</td>{{--在途數--}}
                    <td>調整數</td>{{--調整數--}}
                    <td>短缺預測</td>{{--短缺預測--}}
                    <td>供應商</td>{{--vendorID--}}
                    {{--                <td>供應商</td>--}}
                    <td>需求日</td>{{--dueDate--}}
                    <td>起始日</td>{{--startDate--}}
                    {{--                <td>buyerCode</td>--}}
                    <td>status</td>{{--status--}}
                    <td>makeBuy</td>{{--makeBuy--}}
                    {{--                    <td>inSit</td>--}}{{--inSit--}}
                    <td>首選供應商</td>{{--preferredPo--}}
                    <td>採購單</td>{{--採購單--}}
                    <td>採購單項</td>{{--採購單項--}}
                </tr>
                </thead>
                <tbody>
                @foreach(session()->get('list')??[] as $key => $value)
                    <tr
                        @if (intval($value['lv']) === 1)
                        style="background:rgba(255,140,0,0.5)" ;
                        @endif
                        @if (intval($value['lv']) === 0)
                        style="background:rgba(0,136,255,0.5)" ;
                        @endif
                    >
                        <td>{{ $value['orderNumber'] }}</td>
                        <td>{{ $value['orderItemNumber'] }}</td>
                        <td>{{ $value['partNumber'] }}</td>
                        <td>{{ $value['partType'] }}</td>
                        <td>{{ $value['partDescription'] }}</td>
                        <td>{{ floatval($value['quantity']) }}</td>
                        <td>{{ floatval($value['issuedQuatity']) }}</td>
                        <td>{{ floatval($value['quantityReceived']) }}</td>
                        <td>{{ floatval($value['quantityOnhand']) }}</td>
                        <td>{{ floatval($value['在途數']) }}</td>{{--在途數--}}
                        <td>{{ floatval($value['調整數']) }}</td>{{--調整數--}}
                        <td
                            @if (intval($value['短缺預測']) < 0)
                            style="color:red";
                            @endif
                        >{{ floatval($value['短缺預測']) }}</td>{{--短缺預測--}}
                        <td>{{ $value['vendorID'] }}</td>
                        {{--                    <td>{{ $value['vendorName'] }}</td>--}}
                        <td>{{ $value['dueDate'] }}</td>
                        <td>{{ $value['startDate'] }}</td>
                        {{--                    <td>{{ $value['buyerCode'] }}</td>--}}
                        <td>{{ $value['status'] }}</td>
                        <td>{{ $value['makeBuy'] }}</td>
                        {{--                        <td>{{ $value['inSit'] }}</td>--}}
                        <td>{{ $value['preferredPo'] }}</td>
                        <td>{{ $value['採購單'] }}</td>
                        <td>{{ $value['採購單項'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
