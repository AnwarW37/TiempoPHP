<?php
// Archivos necesarios para el funcionamiento del script
require_once '../config/config.php';
require_once '../models/tiempo.php';


// Crear una instancia de la clase Tiempo pasando el token de la API.
$tiempo = new Tiempo($token); 

// Obtener el nombre de la ciudad desde la URL (por defecto 'Madrid' si no se proporciona).
$ciudad = isset($_GET['ciudad']) ? $_GET['ciudad'] : 'Madrid';

// Obtener la geolocalización de la ciudad utilizando la función de la clase Tiempo.
$coordenadas = $tiempo->getGeolocalizacion($ciudad);

// Si todo es correcto , asignamos las variables de latitud y longitud
if ($coordenadas) {
    $lat = $coordenadas['lat'];
    $lon = $coordenadas['lon'];
} else {
    echo "No se pudo obtener la geolocalización de la ciudad.";
    exit;
}

// Obtener el pronóstico semanal desde la clase Tiempo utilizando las coordenadas de latitud y longitud.
$pronosticoSemanal = $tiempo->getPronosticoSemanal($lat, $lon);

// URL de la API de OpenWeatherMap para obtener el pronóstico semanal en base a las coordenadas.
$url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&appid={$token}&units=metric&lang=es";
// Obtener la respuesta de la API.
$response = @file_get_contents($url);

// Si la respuesta es false es por que hay un error al obtener la respuesta de la API
if ($response === FALSE) {
    echo "<p class='error'>Error al obtener la previsión semanal.</p>";
    exit;
}

// json de la API a la variable $data
$data = json_decode($response, true);

// Agrupar los datos de la previsión por día.
$dailyData = [];
foreach ($data['list'] as $forecast) {
    // Formatear la fecha de cada pronóstico a 'Año-Mes-Día'
    $date = date('Y-m-d', $forecast['dt']);
    // Si no existe un dato para este día, inicializarlo
    if (!isset($dailyData[$date])) {
        $dailyData[$date] = [
            'min_temp' => $forecast['main']['temp_min'],
            'max_temp' => $forecast['main']['temp_max'],
            'rain' => $forecast['rain']['3h'] ?? 0, // Precipitación en mm (3 horas)
            'icon' => $forecast['weather'][0]['icon'], // Icono del clima
            'date' => $date
        ];
    } else {
        // Si ya existe, actualizar las temperaturas mínimas y máximas si es necesario
        if ($forecast['main']['temp_min'] < $dailyData[$date]['min_temp']) {
            $dailyData[$date]['min_temp'] = $forecast['main']['temp_min'];
        }
        if ($forecast['main']['temp_max'] > $dailyData[$date]['max_temp']) {
            $dailyData[$date]['max_temp'] = $forecast['main']['temp_max'];
        }
        // Si hay datos de lluvia, sumarlos
        if (isset($forecast['rain']['3h'])) {
            $dailyData[$date]['rain'] += $forecast['rain']['3h'];
        }
    }
}

// Preparar los datos para el gráfico
$labels = [];
$temperatures = [];
$rain = [];
$icons = [];

// Recorrer los datos agrupados por día para preparar la información que se pasará al gráfico
foreach ($dailyData as $day) {
    $labels[] = date('D, M j', strtotime($day['date']));
    $temperatures[] = [
        'min' => $day['min_temp'],
        'max' => $day['max_temp']
    ];
    $rain[] = $day['rain'];
    $icons[] = $day['icon'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pronóstico Semanal</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>Pronóstico Semanal para <?php echo $ciudad; ?></h1>

        <div class="forecast-container">
            <!-- Crear un canvas para el gráfico del pronóstico semanal. -->
            <canvas id="weeklyChart"></canvas>
            <div id="weatherIcons">
                <?php foreach ($icons as $index => $icon) { ?>
                    <div class="weather-icon">
                        <!-- Mostrar el icono del clima con la fecha correspondiente. -->
                        <img src="http://openweathermap.org/img/wn/<?php echo $icon; ?>@2x.png" alt="Icono del clima">
                        <p><?php echo $labels[$index]; ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="nav-links">
             <!-- link para ir al inicio -->       
            <a href="../views/index.php?ciudad=<?php echo urlencode($ciudad); ?>">Volver al clima actual</a>
        </div>
    </div>

    <script>
        // Datos de PHP que se pasarán a JS para generar el gráfico
        const labels = <?php echo json_encode($labels); ?>;
        const temperatures = <?php echo json_encode($temperatures); ?>;
        const rain = <?php echo json_encode($rain); ?>;

        const ctx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Temperatura Mínima (°C)',
                        data: temperatures.map(temp => temp.min),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Temperatura Máxima (°C)',
                        data: temperatures.map(temp => temp.max),
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Lluvia (mm)',
                        data: rain,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'rainAxis', 
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Temperaturas y Lluvia Semanal'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Día'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Temperatura (°C)'
                        }
                    },
                    rainAxis: {
                        position: 'right', 
                        title: {
                            display: true,
                            text: 'Lluvia (mm)'
                        },
                        grid: {
                            display: false, 
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
