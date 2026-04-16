# Proyecto de Innovacion Gestion de contratos RONCEROS

## Instalacion
Luego de clonar el repositorio lo que sigue es instalar correctamente las dependencias via composer:
    
```bash
composer install
```
## Configuracion
Luego creamos una bd a la cual nos conectaremos.
```bash
composer install
```
Para terminar esta seccion pasamos a configurar nuestro archivo .env
```dotenv
#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = localhost
database.default.database = tu_db
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

```
## Base de Datos
Pasamos a corremos las migraciones y semillas:
```bash
php spark migrate
```
Semillas:
```bash
php spark db:seed DatabaseSeeder
```

## Ejecucion
Por ultimo ejecutamos nuestro codigo con el comando:
```bash
php spark serve
```


