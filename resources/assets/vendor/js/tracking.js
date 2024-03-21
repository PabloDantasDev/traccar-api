
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
                    iconUrl: "https://cdn-icons-png.flaticon.com/512/809/809998.png",
                    iconSize: [25, 25], // Define o tamanho do ícone do carro
                    iconAnchor: [20, 20], // Define o ponto de ancoragem do ícone do carro
                    popupAnchor: [0, -19], // Define o ponto onde o popup deve ser aberto em relação ao ícone do carro
                });

                getAddressFromCoordinates(position.latitude, position.longitude)
                    .then((address) => {
                        var newMarker = L.marker(myLatLng, { icon: customIcon }).addTo(map);
                        newMarker.options.deviceId = device.id;

                        newMarker.bindPopup(`
                            <strong>Dispositivo:</strong> ${device.name}<br>
                            <strong>Rua|Av:</strong> ${address.road}<br>
                            <strong>Bairro:</strong> ${address.suburb}<br>
                            <strong>Cidade:</strong> ${address.city}<br>
                            <strong>Estado:</strong> ${address.state}<br>
                            <strong>Região:</strong> ${address.region}<br>
                            <strong>CEP:</strong> ${address.postcode}<br>
                            <strong>Data do dispositivo:</strong> ${position.deviceTime}<br>
                            <strong>Data do servidor:</strong> ${position.serverTime}<br>
                            <strong>Latitude:</strong> ${position.latitude}<br>
                            <strong>Longitude:</strong> ${position.longitude}<br>
                            <strong>Altitude:</strong> ${position.altitude}<br>
                            <strong>Velocidade:</strong> ${position.speed} Km/h<br>
                            <strong>Curso:</strong> ${position.course}<br>
                        `);
                        
                        markers.push(newMarker);
                    })
                    .catch((error) => console.error("Erro ao obter detalhes do endereço:", error));
            }
        }

        function getAddressFromCoordinates(latitude, longitude) {
            var opencageApiKey = '9a65ec499784491fb808036bb84e3276'; // Substitua 'SUA_CHAVE_DE_API_DO_OPENCAGE' pela sua chave de API

            var opencageEndpoint = `https://api.opencagedata.com/geocode/v1/json?key=${opencageApiKey}&q=${latitude}+${longitude}&pretty=1`;

            return fetch(opencageEndpoint)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(
                            `Erro na requisição OpenCage Geocoding: ${response.status} ${response.statusText}`
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
            if (position.deviceId === 45) { 
                // Verifica se o ID do dispositivo é 45
                var device = devices.find((d) => d.id === position.deviceId);
                if (device) {
                    addMarkerToMap(device, position);
                }
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
                renderDeviceList(data);
            }).catch((error) => console.error("Erro ao obter dispositivos:", error));

            setInterval(updateMarkers, 20000); // Update markers every 10 seconds
        }

        function renderDeviceList(devices) {
            var deviceListContainer = document.getElementById('device-list');
            devices.forEach((device) => {
                var deviceItem = document.createElement('li');
                deviceItem.textContent = device.name;
                deviceListContainer.appendChild(deviceItem);
            });
        }

        initMap(); // Initialize the map
    });
