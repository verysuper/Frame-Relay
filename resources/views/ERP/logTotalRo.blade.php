@extends('ERP\test0')
@section('content')
    <div style="margin: 20px">
        <div>log</div>
        <form action="/test0/logTotalRo" method="post">
            {{ csrf_field() }}
            <label for="start">
                <input id="start" name="start" type="date" value="{{ $start }}" required
                       onchange="document.getElementById('export_start').value = this.value;">
            </label>
            <label for="end">
                <input id="end" name="end" type="date" value="{{ $end }}" required
                       onchange="document.getElementById('export_end').value = this.value;">
            </label>
            <button type="submit">預覽</button>
        </form>
        <form id="export-form" action="/test0/logTotalRoExport" method="POST" style="display: none;">
            @csrf
            <input type="hidden" id="export_start" name="start" value={{ $start }}>
            <input type="hidden" id="export_end" name="end" value={{ $end }}>
        </form>
    </div>
    <div>
        @if (!is_null($ro_total) && count((array)$ro_total))
            <a href=""
               onclick="event.preventDefault(); document.getElementById('export-form').submit();">
                {{ __('明細') }}
            </a>
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
                @foreach($ro_total as $key => $value)
                    <tr>
                        <td>{{ $key }}</td>
                        @foreach($columns as $column)
                            @php
                                $net = isset($ro_total->{$key}->{$column}) ? $ro_total->{$key}->{$column}->net :'';
                                $tax = isset($ro_total->{$key}->{$column}) ? $ro_total->{$key}->{$column}->tax :'';
                            @endphp
                            <td>
                                <div>{{ round($net, 3) }}</div>
                                <div>{{ round($tax, 3) }}</div>
                            </td>
                        @endforeach
                        <td>
                            <div>{{ round(isset($ro_total->{$key}) ? $ro_total->{$key}->net :'', 3) }}</div>
                            <div>{{ round(isset($ro_total->{$key}) ? $ro_total->{$key}->tax :'', 3) }}</div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
