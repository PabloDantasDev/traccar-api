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

        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

           var satelliteLayer = L.tileLayer( {
        attribution: '&copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });

        var markers = [];

        function addMarkerToMap(device, position) {
    var myLatLng = L.latLng(position.latitude, position.longitude);
    var existingMarker = markers.find((m) => m.options.deviceId === device.id);

    if (existingMarker) {
        existingMarker.setLatLng(myLatLng);
    } else {
        var customIcon = L.icon({
            iconUrl: "https://cdn-icons-png.flaticon.com/512/809/809998.png",
            iconSize: [40, 40], // Define o tamanho do ícone do carro
            iconAnchor: [20, 20], // Define o ponto de ancoragem do ícone do carro
            popupAnchor: [0, -19], // Define o ponto onde o popup deve ser aberto em relação ao ícone do carro
        });

        var newMarker = L.marker(myLatLng, { icon: customIcon }).addTo(map);
        newMarker.options.deviceId = device.id;

        // Chamada para obter detalhes do endereço
        getAddressFromCoordinates(position.latitude, position.longitude)
            .then((address) => {
                // Adicione detalhes do endereço ao popup do marcador
                newMarker.bindPopup(`
                    <strong>Dispositivo:</strong> ${device.name}<br>
            <strong>Rua|Av:</strong> ${address.road || 'Não disponível'}<br>
            <strong>Bairro:</strong> ${address.suburb || 'Não disponível'}<br>
            <strong>Cidade:</strong> ${address.city || 'Não disponível'}<br>
            <strong>Estado:</strong> ${address.state || 'Não disponível'}<br>
            <strong>Região:</strong> ${address.region || 'Não disponível'}<br>
            <strong>CEP:</strong> ${address.postcode || 'Não disponível'}<br>
            <strong>Data do dispositivo:</strong> ${position.deviceTime}<br>
            <strong>Data do servidor:</strong> ${position.serverTime}<br>
            <strong>Latitude:</strong> ${position.latitude}<br>
            <strong>Longitude:</strong> ${position.longitude}<br>
            <strong>Altitude:</strong> ${position.altitude}<br>
            <strong>Velocidade:</strong> ${position.speed} Km/h<br>
            <strong>Curso:</strong> ${position.course}<br>
                       
                `);
            })
            .catch((error) => {
                console.error("Erro ao obter detalhes do endereço:", error);
                // Se ocorrer um erro ao obter detalhes do endereço, ainda exibimos as informações disponíveis
                newMarker.bindPopup(`
                       <strong>Dispositivo:</strong> ${device.name}<br>
                    <strong> Rua|Av:</strong> ${data.road}<br>
                    <strong> Bairro:</strong> ${address.suburb}<br>
                    <strong> Cidade:</strong> ${address.city}<br>
                    <strong> Estado:</strong> ${address.state}<br>
                    <strong> Região:</strong> ${address.region}<br>
                    <strong> CEP:</strong> ${address.postcode}<br>
                    <strong>Data do dispositivo:</strong> ${position.deviceTime}<br>
                    <strong>Data do servidor:</strong> ${position.serverTime}<br>
                    <strong>Latitude:</strong> ${position.latitude}<br>
                    <strong>Longitude:</strong> ${position.longitude}<br>
                    <strong>Altitude:</strong> ${position.altitude}<br>
                    <strong>Velocidade:</strong> ${position.speed} Km/h<br>
                    <strong>Curso:</strong> ${position.course}<br>
                `);
            });

        markers.push(newMarker);
    }
}

        function getAddressFromCoordinates(latitude, longitude) {
    var opencageApiKey = '9a65ec499784491fb808036bb84e3276'; // Substitua 'SUA_CHAVE_DE_API_DO_OPENCAGE' pela sua chave de API

    var opencageEndpoint = "https://api.opencagedata.com/geocode/v1/json?key=${opencageApiKey}&q=${latitude}+${longitude}&pretty=1";

    return fetch(opencageEndpoint)
        .then((response) => {
            if (!response.ok) {
                throw new Error(
                   " Erro na requisição OpenCage Geocoding: ${response.status} ${response.statusText}"
                );
            }
            return response.json();
        })
        .then((data) => {
            if (data.results && data.results.length > 0) {
                return data.results[0].components;
            } else {
                throw new Error("Detalhes do endereço não encontrados.");
            }
        });
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
            email: "host_braso@braso.com.br",
            password: "8524565",
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
    }).then((data) => {
        // Armazenar o nome do primeiro dispositivo na localStorage
        if (data.length > 0) {
            localStorage.setItem('deviceName', data[0].name);
        }
        return data; // Retorna os dados para serem usados posteriormente
    });
}

        function initMap() {
            getDevicesData().then((data) => {
                devices = data;
                updateMarkers();
                renderDeviceList(data);
            }).catch((error) => console.error("Erro ao obter dispositivos:", error));

            setInterval(updateMarkers, 30000); // Update markers every 10 seconds
        }


        initMap(); // Initialize the map
    });
</script>
@endsection

@section('content')
<div id="map"></div>
<ul id="device-list"></ul>
@endsection