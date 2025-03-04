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

//Asignamos las variables de latitud y longitud
if ($coordenadas) {
    $pronosticoHoras = $tiempo->getPronosticoPorHoras($coordenadas['lat'], $coordenadas['lon']);
} else {
    echo "No se pudo obtener la geolocalización de la ciudad.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pronóstico por Horas</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>Pronóstico por Horas para <?php echo $ciudad; ?></h1>

        <?php if ($pronosticoHoras): ?>
            <div class="grafico-container">
                <canvas id="graficoHoras"></canvas>
            </div>

            <script>
                
                const horas = <?php echo json_encode(array_map(function($hora) {
                    return date('H:i', $hora['dt']);
                }, $pronosticoHoras)); ?>;

                const temperaturas = <?php echo json_encode(array_map(function($hora) {
                    return $hora['main']['temp'];
                }, $pronosticoHoras)); ?>;

                const lluvia = <?php echo json_encode(array_map(function($hora) {
                    return $hora['rain']['3h'] ?? 0; 
                }, $pronosticoHoras)); ?>;

                
                const ctx = document.getElementById('graficoHoras').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: horas,
                        datasets: [
                            {
                                label: 'Temperatura (°C)',
                                data: temperaturas,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                type: 'line',
                                yAxisID: 'temperaturaAxis',
                            },
                            {
                                label: 'Lluvia (mm)',
                                data: lluvia,
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                yAxisID: 'lluviaAxis',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Temperatura y Lluvia por Horas'
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Hora'
                                }
                            },
                            temperaturaAxis: {
                                type: 'linear',
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Temperatura (°C)'
                                },
                                grid: {
                                    display: false,
                                }
                            },
                            lluviaAxis: {
                                type: 'linear',
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
        <?php endif; ?>
         <!-- link para ir al inicio -->       
        <div class="nav-links">
            <a href="../index.php?ciudad=<?php echo urlencode($ciudad); ?>">Volver al clima actual</a>
        </div>
    </div>
</body>
</html>
