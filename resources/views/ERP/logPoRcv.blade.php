@php
    $po_total = session()->get('po_total') ?? null;
    $columns = session()->get('columns') ?? null;
@endphp
@extends('ERP.test0')
@section('content')
    <div style="margin: 0 20px">
        <div>採購單→收料單(NTD)</div>
        <form action="/test0/logPoRcv" method="post">
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
            @if (!is_null($po_total) && count((array)$po_total))
                <a href=""
                   onclick="event.preventDefault(); document.getElementById('export-form').submit();">
                    {{ __('明細') }}
                </a>
            @endif
        </form>
        <form id="export-form" action="/test0/logPoRcvExport" method="POST" style="display: none;">
            @csrf
            <input type="hidden" id="export_start" name="start" value={{ old('start') }}>
            <input type="hidden" id="export_end" name="end" value={{ old('end') }}>
        </form>
    </div>
    <div style="margin: 0 20px">
        @if (!is_null($po_total) && count((array)$po_total))
            <div>共{{ count((array)$po_total) }}筆</div>
            <table style="border:1px #cccccc solid; width: 100%;" border='1'>
                <thead>
                <tr>
                    <td style="width: 10%">供應商</td>
                    @foreach($columns as $column)
                        <td>{{ $column }}</td>
                    @endforeach
                    <td style="width: 5%">總計</td>
                </tr>
                </thead>
                <tbody>
                @foreach($po_total as $key => $value)
                    <tr>
                        <td>{{ $key }}</td>
                        @foreach($columns as $column)
                            @php
                                $net = isset($po_total->{$key}->{$column}) ? $po_total->{$key}->{$column}->net :'';
                                $tax = isset($po_total->{$key}->{$column}) ? $po_total->{$key}->{$column}->tax :'';
                            @endphp
                            <td>
                                <div>{{ round($net, 3) }}</div>
                                <div>{{ round($tax, 3) }}</div>
                            </td>
                        @endforeach
                        <td>
                            <div>
                                {{ round(isset($po_total->{$key}) ? $po_total->{$key}->net :'', 3) + round(isset($po_total->{$key}) ? $po_total->{$key}->tax :'', 3) }}
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
