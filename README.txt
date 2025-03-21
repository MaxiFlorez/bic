BIC - Sistema de Gestión

¡Bienvenido al Sistema de Gestión BIC! Este proyecto está diseñado para la administración de información de detenidos, vehículos y otros módulos.

Tecnologías Utilizadas
PHP: Lógica del servidor
MySQL: Base de datos relacional
HTML/CSS/JavaScript: Interfaz
Bootstrap 5: Estilos y componentes responsivos
Estructura del Proyecto

bic/
├── admin/
│   └── usuarios/
│       └── perfil_usuario.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── imagenes/
│   │   ├── personas/  (Fotos de personas)
│   │   └── vehiculos/  (Fotos de vehículos)
│   ├── js/
│   │   └── scripts.js
│   ├── database/
│   │   └── schema.sql
│   ├── includes/
│   │   ├── auth.php
│   │   ├── db.php
│   │   ├── footer.php
│   │   ├── header.php
│   │   └── navbar.php
│   └── documentos/
│       ├── vehiculos/  (PDFs de secuestro de vehículos)
│       └── personas/   (PDFs de detención de personas)
├── modules/
│   ├── detenidos/
│   │   ├── agregar_detenido.php
│   │   ├── detalle_detenido.php
│   │   └── listar.php
│   ├── profugos/  (Pendiente)
│   └── vehiculos/
│       ├── agregar_vehiculo.php
│       ├── detalle_vehiculo.php
│       └── listar_vehiculos.php
├── crear_usuario.php
├── dashboard.php
├── importar_detenido.php
├── login.php
└── logout.php

Instalación y Configuración
Requisitos:

XAMPP o similar con PHP y MySQL
Git
Clonar el repositorio:

bash
git clone https://github.com/MaxiFlorez/bic.git
Coloca la carpeta del proyecto en el directorio htdocs de XAMPP.

Base de datos:

Importa assets/database/schema.sql en tu servidor MySQL.
Configura las credenciales en assets/includes/db.php.
Configuración de archivos:

Verifica que las rutas en los archivos de inclusión sean correctas.
Asegúrate de que el servidor web tenga permisos para todas las carpetas.
Uso del Sistema
Autenticación:

Inicia sesión en login.php.
Cierra sesión en logout.php.
Dashboard:

Accede a dashboard.php para ver el panel principal.
Gestión de Usuarios:

Administra perfiles en admin/usuarios/perfil_usuario.php.
Gestión de Detenidos:

Agregar: modules/detenidos/agregar_detenido.php
Listar: modules/detenidos/listar.php
Detalles: modules/detenidos/detalle_detenido.php
Gestión de Vehículos:

Agregar: modules/vehiculos/agregar_vehiculo.php
Listar: modules/vehiculos/listar_vehiculos.php
Detalles: modules/vehiculos/detalle_vehiculo.php
Documentos PDF:

Vehículos: assets/documentos/vehiculos/
Personas: assets/documentos/personas/
Contribuciones
¡Tu ayuda es bienvenida! Haz un fork del repositorio, realiza tus cambios y envía un pull request.

Licencia
Este proyecto es de uso libre. Si lo utilizas, por favor cita la fuente original.