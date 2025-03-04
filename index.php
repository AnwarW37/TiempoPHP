<?php
// Archivos necesarios para el funcionamiento del script
require_once 'models/tiempo.php';
require_once 'config/config.php';

// Crear una instancia de la clase Tiempo, utilizando un token de autenticación
$tiempo = new Tiempo($token);

// Obtener la ciudad desde el formulario (si no se proporciona, se usa "Badajoz" por defecto)
$ciudad = isset($_GET['ciudad']) ? $_GET['ciudad'] : 'Badajoz';

// Obtener las coordenadas de la ciudad 
$coordenadas = $tiempo->getGeolocalizacion($ciudad);

// Variable para almacenar la información del pronóstico actual
$pronosticoActual = null;
// Variable para manejar los errores en la geolocalización
$errorGeolocalizacion = '';

// Verificar si se obtuvieron coordenadas válidas
if ($coordenadas) {
    // Si las coordenadas son válidas, obtener el pronóstico del clima actual
    $pronosticoActual = $tiempo->getPronosticoActual($coordenadas['lat'], $coordenadas['lon']);
} else {
    // Si el usuario no introduce una ciudad
    if (empty($ciudad)) {
        $errorGeolocalizacion = "Introduce una ciudad.";
    // Si la ciudad ingresada no existe
    } else {
        $errorGeolocalizacion = "La ciudad: '$ciudad' no existe.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clima Actual</title>
    <link rel="stylesheet" href="css/estilos.css"> <!-- Enlace al archivo CSS -->
</head>
<body>
    
    <div class="container">
        <h1>Clima Actual</h1>
        <!-- Formulario para ingresar la ciudad y consultar su clima -->
        <form method="GET" action="">
            <input type="text" name="ciudad" placeholder="Ingresa una ciudad" value="<?php echo ($ciudad); ?>">
            <input type="submit" value="BUSCAR">
        </form>
        
        <!-- Mostrar mensaje de error si existe un problema con la geolocalización -->
        <?php if ($errorGeolocalizacion): ?>
            <div class="error">
                <p><?php echo $errorGeolocalizacion; ?></p>
            </div>
        
        <!-- Si se obtuvo un pronóstico válido, mostrar la información del clima -->
        <?php elseif ($pronosticoActual): ?>
            <div class="clima-info">
                <h2><?php echo $pronosticoActual['name']; ?></h2>
                <div class="descripcion-clima">
                    <!-- Mostrar el icono del clima correspondiente -->
                    <img src="http://openweathermap.org/img/wn/<?php echo $pronosticoActual['weather'][0]['icon']; ?>@2x.png" alt="Icono del clima">
                    <p><?php echo ucfirst($pronosticoActual['weather'][0]['description']); ?></p>
                </div>
                <p>Temperatura: <?php echo $pronosticoActual['main']['temp']; ?>°C</p>
                <p>Humedad: <?php echo $pronosticoActual['main']['humidity']; ?>%</p>
            </div>

            <!-- Enlaces para ver pronósticos por horas y por semana -->
            <div class="nav-links">
                <a href="views/pronostico-horas.php?ciudad=<?php echo urlencode($ciudad); ?>">Ver pronóstico por horas</a>
                <a href="views/pronostico-semanal.php?ciudad=<?php echo urlencode($ciudad); ?>">Ver pronóstico semanal</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
