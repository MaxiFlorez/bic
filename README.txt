/bic
├── includes
│   ├── db.php
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── assets
│   ├── css
│   ├── js
│   ├── imagenes
│   │   ├── personas
│   │   └── vehículos
│   └── database
│       └── schema.sql
├── login.php
├── dashboard.php
└── modules
│    ├── detenidos
│    │   ├── listar.php
│    │   ├── agregar_detenido.php
│    │   └── detalle_detenido.php
│    ├── prófugos
│    └── vehículos
└── admin/                  # Módulo de administración (opcional)  
│   ├── usuario/  
│   │   ├── perfil_usuario.php      # Lista de usuarios  
│   │   ├── agregar.php     # Agregar usuario  
│   │   └── editar.php      # Editar usuario  
│   └── configuracion.php   # Configuración del sistema  













/bic/  
│  
├── index.php               # Página principal (login o redirección al dashboard)  
├── login.php               # Inicio de sesión  
├── logout.php              # Cerrar sesión  
├── dashboard.php           # Panel de control principal  
│  
├── includes/               # Archivos PHP reutilizables  
│   ├── auth.php            # Autenticación y roles  
│   ├── db.php              # Conexión a la base de datos  
│   ├── header.php          # Encabezado común  
│   ├── footer.php          # Pie de página común  
│   └── funciones.php       # Funciones útiles (opcional)  
│  
├── modules/                # Módulos principales del sistema  
│   ├── detenidos/  
│   │   ├── listar.php      # Lista de detenidos  
│   │   ├── agregar.php     # Formulario de agregar detenido  
│   │   ├── editar.php      # Editar detenido  
│   │   └── detalle.php     # Detalle completo del detenido  
│   │  
│   ├── profugos/  
│   │   ├── listar.php      # Lista de prófugos  
│   │   ├── agregar.php     # Formulario de agregar prófugo  
│   │   ├── editar.php      # Editar prófugo  
│   │   └── detalle.php     # Detalle completo del prófugo  
│   │  
│   └── vehiculos/  
│       ├── listar.php      # Lista de vehículos  
│       ├── agregar.php     # Formulario de agregar vehículo  
│       ├── editar.php      # Editar vehículo  
│       └── detalle.php     # Detalle completo del vehículo  
│  
├── assets/                 # Archivos estáticos  
│   ├── css/                # Hojas de estilo (Bootstrap, personalizadas)  
│   ├── js/                 # Scripts JavaScript  
│   ├── imagenes/           # Fotos de personas/vehículos  
│   └── pdf/                # Archivos PDF (órdenes de captura, etc.)  
│  
├── admin/                  # Módulo de administración (opcional)  
│   ├── usuarios/  
│   │   ├── listar.php      # Lista de usuarios  
│   │   ├── agregar.php     # Agregar usuario  
│   │   └── editar.php      # Editar usuario  
│   └── configuracion.php   # Configuración del sistema  
│  
└── database/               # Scripts y backups de la base de datos  
    ├── schema.sql          # Estructura de la base de datos  
    └── datos_iniciales.sql # Datos de prueba (opcional)  