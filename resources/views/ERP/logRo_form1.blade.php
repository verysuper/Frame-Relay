@extends('ERP\test0')
@section('content')
    <div style="margin: 20px">
        <div>log</div>
        <form action="/test0/logRo1" method="post">
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
            <a href=""
               onclick="event.preventDefault(); document.getElementById('export-form').submit();">
                {{ __('匯出') }}
            </a>
        </form>
        <form id="export-form" action="/logRo1export" method="POST" style="display: none;">
            @csrf
            <input type="hidden" id="export_start" name="start" value={{ $start }}>
            <input type="hidden" id="export_end" name="end" value={{ $end }}>
        </form>
    </div>
    <div>
    </div>
@endsection
