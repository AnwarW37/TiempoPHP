# TiempoPHP

## Descripción
TiempoPHP es una aplicación desarrollada en PHP que permite consultar el tiempo atmosférico de cualquier ciudad del mundo utilizando la API de [OpenWeatherMap](https://openweathermap.org/).

Este proyecto está desplegado en una instancia de AWS y utiliza Apache como servidor web, asegurando su accesibilidad mediante HTTPS y un dominio personalizado.

---

## Instalación y Configuración

### 1. Creación de Instancia en AWS
Se ha configurado una instancia en AWS con los siguientes elementos:
- Asignación de una dirección IP pública.
- Configuración del grupo de seguridad para permitir tráfico HTTP/HTTPS.

### 2. Instalación de Apache y PHP
Una vez creada la instancia, se accede a ella mediante SSH y se instalan Apache y PHP:
```bash
#Instalacion Apache2
sudo apt update
sudo apt install apache2 -y
# Instalacion PHP
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip -y
#Permisos
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/

```

### 3. Clonación del Repositorio
Se clona el repositorio de la aplicación en el directorio de Apache:
```bash
cd /var/www/html/
sudo git clone https://github.com/usuario/TiempoPHP.git
```

### 4. Configuración del Servidor Web
Se edita la configuración de Apache para establecer el DocumentRoot en la carpeta del proyecto:
```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```
Modificar la línea correspondiente:
```
DocumentRoot /var/www/html/TiempoPHP
```
Aplicar los cambios:
```bash
sudo systemctl restart apache2
```

### 5. Configuración de HTTPS y Certificado SSL
Se instala y configura un certificado SSL con Let's Encrypt:
```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d tudominio.com
```
Renovación automática:
```bash
sudo certbot renew --dry-run
```

### 6. Configuración del Dominio
Se asigna un dominio al servidor mediante DNS, vinculando la IP pública de la instancia con el dominio deseado.

---

## Uso
1. Acceder a la aplicación mediante el dominio o la dirección IP pública.
2. Introducir el nombre de una ciudad en el formulario.
3. Obtener la información meteorológica en tiempo real proporcionada por OpenWeatherMap.

---

## Tecnologías Utilizadas
- PHP
- Apache
- AWS (EC2)
- OpenWeatherMap API
- Let's Encrypt (SSL)

---

## Autor
[Anwar Warrak Amar]









