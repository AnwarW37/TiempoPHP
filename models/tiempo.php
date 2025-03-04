<?php
class Tiempo {
    private $token;
    private $errores = [];

    // Constructor para inicializar el token
    public function __construct($token) {
        $this->token = $token;
    }

    // Método para obtener los errores
    public function getErrores() {
        return $this->errores;
    }

    // Método privado para obtener datos de la API
    private function getDatosApi($url) {
        //la api nos da un archivo json y lo guardamos en la variable $json
        $json = @file_get_contents($url);

        // 
        if ($json == false) {
            $this->errores[] = "Hubo un problema al conectar con la API.";
            return null;
        }

        $datos = json_decode($json, true);

        if (isset($datos['cod']) && $datos['cod'] != 200) {
            $this->errores[] = "Error en la API: " . $datos['message'];
            return null;
        }

        return $datos;
    }

    // Método para obtener la geolocalización de una ciudad
    public function getGeolocalizacion($ciudad) {

        $ciudad_codificada = urlencode($ciudad);
    
        $url = "http://api.openweathermap.org/data/2.5/weather?q=$ciudad_codificada&appid=" . $this->token;
        $datos = $this->getDatosApi($url);
        
        if (isset($datos['coord'])) {
            return $datos['coord'];
        } else {
            $this->errores[] = "No se pudo obtener la geolocalización de la ciudad.";
            return null;
        }
    }

    // Método para obtener el pronóstico actual
    public function getPronosticoActual($lat, $lon) {
        $url = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=" . $this->token . "&units=metric&lang=es";
        return $this->getDatosApi($url);
    }

    // Método para obtener el pronóstico por horas
    public function getPronosticoPorHoras($lat, $lon) {
        $url = "https://api.openweathermap.org/data/2.5/forecast?lat=$lat&lon=$lon&appid=" . $this->token . "&units=metric&lang=es";
        $datos = $this->getDatosApi($url);
        return $datos ? $datos['list'] : null; 
    }

    // Método para obtener el pronóstico semanal
    public function getPronosticoSemanal($lat, $lon) {
        $url = "https://api.openweathermap.org/data/2.5/onecall?lat=$lat&lon=$lon&exclude=hourly,minutely&appid=" . $this->token . "&units=metric&lang=es";
        $datos = $this->getDatosApi($url);
        return $datos ? $datos['daily'] : null; 
    }
}
?>