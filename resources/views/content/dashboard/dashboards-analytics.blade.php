@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
    /* Estilos adicionais para tela cheia */
    html,
    body,
    
    #map {
        height: 100vh;
        width: 100%;
    }

    #btn{
        z-index: 1111;
        padding: 20px;
    }
     
</style>
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
@endsection

@section('page-script')

@endsection

@section('content')
<div id="map"></div>
<button class="btn btn-info">CADASTRAR DEVICE</button>



@endsection
