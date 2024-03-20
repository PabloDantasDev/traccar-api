@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
    /* Estilos adicionais para tela cheia */
    html,
    body,
    #map {
        height: 100%;
        margin: 0;
        padding: 0;
    }
</style>
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
@endsection

@section('page-script')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Recupera o identificador do localStorage
        var identificador = localStorage.getItem('identificador');

        var map = L.map('map').setView([-14.235, -51.925], 4);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var markers = [];

        function addMarkerToMap(device, position) {
            var myLatLng = L.latLng(position.latitude, position.longitude);
            var existingMarker = markers.find((m) => m.options.deviceId === device.id);

            if (existingMarker) {
                existingMarker.setLatLng(myLatLng);
            } else {
               var customIcon = L.icon({
    iconUrl: "https://cdn-icons-png.flaticon.com/512/3085/3085411.png",
    iconSize: [40, 40], // Define o tamanho do ícone do carro
    iconAnchor: [20, 20], // Define o ponto de ancoragem do ícone do carro
    popupAnchor: [0, -20], // Define o ponto onde o popup deve ser aberto em relação ao ícone do carro
});

                var newMarker = L.marker(myLatLng, { icon: customIcon }).addTo(map);
                newMarker.options.deviceId = device.id;

                // Bind popup with device name and position information
                newMarker.bindPopup(`
                    <strong>Dispositivo:</strong> ${device.name}<br>
                    <strong>Data do dispositivo:</strong> ${position.deviceTime}<br>
                    <strong>Data do servidor:</strong> ${position.serverTime}<br>
                    <strong>Latitude:</strong> ${position.latitude}<br>
                    <strong>Longitude:</strong> ${position.longitude}<br>
                    <strong>Altitude:</strong> ${position.altitude}<br>
                    <strong>Velocidade:</strong> ${position.speed} Km/h<br>
                    <strong>Curso:</strong> ${position.course}<br>
                    <strong>Endereço:</strong> ${position.road}<br>
                `);

                markers.push(newMarker);
            }
        }


    function updateMarkers() {
        fetchPositionsData().then((positions) => {
            positions.forEach((position) => {
                var device = devices.find((d) => d.id === position.deviceId);
                if (device) {
                    addMarkerToMap(device, position);
                }
            });
        }).catch((error) => console.error("Erro ao obter posições:", error));
    }

    function fetchPositionsData() {
        return fetch(`${selectedHost}/api/positions`, {
            headers: {
                Authorization: "Basic " + btoa(`${userEmail}:${userPassword}`),
            },
        })
        .then((response) => {
            if (!response.ok) {
                throw new Error(
                    `Erro na requisição: ${response.status} ${response.statusText}`
                );
            }
            return response.json();
        });
    }

    var hostsAndAccounts = [{
        host: "https://rastreamento.braso.net.br/",
        email: "canguaretama@gmail.com",
        password: "102030@@##",
    }];

    var selectedHostIndex = localStorage.getItem("selectedHostIndex") || 0;
    var selectedHostInfo = hostsAndAccounts[selectedHostIndex];
    var selectedHost = selectedHostInfo.host;
    var userEmail = selectedHostInfo.email;
    var userPassword = selectedHostInfo.password;

    var devices = [];

    function getDevicesData() {
        return fetch(`${selectedHost}/api/devices`, {
            headers: {
                Authorization: "Basic " + btoa(`${userEmail}:${userPassword}`),
            },
        }).then((response) => {
            if (!response.ok) {
                throw new Error(
                    `Erro na requisição: ${response.status} ${response.statusText}`
                );
            }
            return response.json();
        });
    }

    function initMap() {
        getDevicesData().then((data) => {
            devices = data;
            updateMarkers();
        }).catch((error) => console.error("Erro ao obter dispositivos:", error));

        setInterval(updateMarkers, 10000); // Update markers every 10 seconds
    }

    initMap(); // Initialize the map
  });
</script>
@endsection

@section('content')
<div id="map"></div>
@endsection
