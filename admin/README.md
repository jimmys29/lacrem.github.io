# Sistema de Gestión de Clientes y Pedidos - La Crem

## 📋 Descripción

Sistema completo de gestión con autenticación, administración de clientes, pedidos, vendedores y reportes avanzados. 

### ✨ Características Principales

✅ **Sistema de Login** - Autenticación con sesiones PHP  
✅ **Gestión de Clientes** - Crear, editar y eliminar clientes  
✅ **Gestión de Vendedores** - Administrar usuarios del sistema  
✅ **Crear Pedidos** - Vincular pedidos con clientes y vendedores  
✅ **Reportes Básicos** - Ver resumen de ventas por cliente  
✅ **Reportes Avanzados** - Filtros por fecha, vendedor, periodo  
✅ **Exportación de Datos** - CSV y JSON para dashboards  
✅ **Seguimiento de Ventas** - Por vendedor, diarias, semanales, mensuales  

---

## 🚀 Cómo Acceder

### Login
```
http://tupagina.com/admin/login.html
```

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

> ⚠️ **IMPORTANTE:** Cambia estas credenciales después del primer acceso

### Páginas disponibles:

1. **Login** (`/admin/login.html`)
   - Autenticación de usuarios
   - Validación de credenciales
   - Sesiones seguras

2. **Panel Principal** (`/admin/index.html`)
   - Dashboard con estadísticas
   - Acceso rápido a todas las funciones
   - Información personalizada según rol

3. **Gestionar Clientes** (`/admin/clientes.html`)
   - Crear nuevos clientes
   - Editar clientes existentes
   - Ver lista completa de clientes
   - Eliminar clientes

4. **Gestionar Vendedores** (`/admin/vendedores.html`) - Solo Admin
   - Crear usuarios del sistema
   - Editar vendedores/administradores
   - Activar/Desactivar usuarios
   - Ver lista de todos los usuarios

5. **Crear Pedidos** (`/admin/pedidos.html`)
   - Seleccionar cliente
   - Agregar productos y cantidades
   - Aplicar descuentos
   - Asignar automáticamente al vendedor actual
   - Ver historial de pedidos

6. **Reportes Básicos** (`/admin/reportes.html`)
   - Resumen de ventas por cliente
   - Top 5 mejores clientes
   - Estadísticas generales
   - Exportar a Excel

7. **Reportes Avanzados** (`/admin/reportes_avanzados.html`)
   - Filtros por fecha (inicio/fin)
   - Filtros por periodo (diario/semanal/mensual)
   - Filtros por vendedor
   - Ventas por vendedor con ranking
   - Productos más vendidos
   - Ventas por día
   - Exportar CSV/JSON para dashboards

---

## 👥 Roles y Permisos

### Administrador
- ✅ Acceso completo al sistema
- ✅ Gestionar vendedores/usuarios
- ✅ Ver reportes de todos los vendedores
- ✅ Todas las funcionalidades

### Vendedor
- ✅ Gestionar clientes
- ✅ Crear pedidos (asignados automáticamente a él)
- ✅ Ver sus propios reportes
- ✅ No puede gestionar usuarios

---

## 📊 Estructura de Datos

### Usuarios/Vendedores

```json
{
  "id": "usr_12345abc",
  "usuario": "juan_vendedor",
  "password": "(hash)",
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "rol": "vendedor",
  "activo": true,
  "fecha_creacion": "2024-02-25 10:30:00"
}
```

### Clientes

```json
{
  "id": "cli_12345abc",
  "nombre": "María González",
  "email": "maria@example.com",
  "telefono": "+34 123 456 789",
  "direccion": "Calle Principal 123, Madrid",
  "empresa": "Empresa XYZ",
  "notas": "Cliente preferido",
  "fecha_creacion": "2024-02-25 10:30:00"
}
```

### Pedidos

```json
{
  "id": "ped_67890def",
  "cliente_id": "cli_12345abc",
  "vendedor_id": "usr_12345abc",
  "vendedor_nombre": "Juan Pérez",
  "items": [
    {
      "producto_id": "sig",
      "nombre": "Signature",
      "cantidad": 5,
      "precio": 150,
      "subtotal": 750
    }
  ],
  "descuento": 10,
  "notas": "Entrega rápida",
  "fecha": "2024-02-25 15:45:00"
}
```

### Reportes Exportados (JSON)

```json
{
  "resumen": {
    "total_pedidos": 50,
    "total_ventas": 15600.00,
    "total_productos": 234,
    "promedio_por_pedido": 312.00
  },
  "ventas_por_vendedor": [
    {
      "vendedor_id": "usr_123",
      "vendedor_nombre": "Juan Pérez",
      "cantidad_pedidos": 25,
      "total_ventas": 8500.00
    }
  ],
  "productos_mas_vendidos": [...],
  "ventas_por_dia": [...]
}
```

---

## 🛠️ Archivos del Sistema

```
/admin/
├── login.html                  # Página de login
├── index.html                  # Panel principal
├── clientes.html               # Gestión de clientes (CRUD completo)
├── vendedores.html             # Gestión de vendedores/usuarios
├── pedidos.html                # Crear pedidos
├── reportes.html               # Reportes básicos
├── reportes_avanzados.html     # Reportes con filtros y exportación
├── api/
│   ├── api.php                 # API principal con autenticación
│   ├── auth.php                # Sistema de autenticación
│   ├── db_functions.php        # Funciones de base de datos
│   └── config.php              # Configuración (migración MySQL)
├── data/
│   ├── usuarios.json           # Datos de usuarios
│   ├── clientes.json           # Datos de clientes
│   └── pedidos.json            # Datos de pedidos
└── README.md                   # Esta documentación
```

---

## 🔐 Seguridad

### Características Implementadas

- ✅ Contraseñas hasheadas con `password_hash()`
- ✅ Sesiones PHP con timeout (1 hora)
- ✅ Validación de autenticación en cada request
- ✅ Protección de rutas según rol
- ✅ No se puede eliminar el administrador principal

### Recomendaciones Adicionales

Para entorno de producción:
1. Usar HTTPS
2. Configurar cabeceras de seguridad
3. Implementar rate limiting
4. Agregar CSRF tokens
5. Migrar a base de datos SQL
6. Implementar logs de auditoría

---

## 📈 Reportes y Exportación

### Reportes Disponibles

1. **Reporte Básico**
   - Vista general de ventas por cliente
   - Top 5 mejores clientes
   - Totales generales

2. **Reportes Avanzados**
   - **Por Periodo:**
     - Diario (hoy)
     - Semanal (esta semana)
     - Mensual (este mes)
     - Personalizado (rango de fechas)
   
   - **Por Vendedor:**
     - Individual o todos
     - Ranking de vendedores
     - Comparativas
   
   - **Métricas:**
     - Total de pedidos
     - Total de ventas
     - Productos vendidos
     - Promedio por pedido
   
   - **Desglose:**
     - Ventas por vendedor
     - Productos más vendidos
     - Ventas diarias

### Formatos de Exportación

1. **CSV** - Para Excel y análisis
   - Separado por comas
   - Compatible con Excel
   - Incluye resumen, vendedores y productos

2. **JSON** - Para dashboards y APIs
   - Estructura completa de datos
   - Fácil integración con Power BI, Tableau, etc.
   - Ideal para visualización de datos

---

## 💾 Almacenamiento

**Actual:** Archivos JSON locales
- `/admin/data/usuarios.json`
- `/admin/data/clientes.json`
- `/admin/data/pedidos.json`

**Requisitos:**
- Carpeta `/admin/data/` con permisos de escritura (775)
- PHP habilitado en el servidor

**Futuro:** Migración a MySQL incluida en `config.php`

---

## 🔄 Flujo de Trabajo

### Para Administradores

1. Login con credenciales admin
2. Crear vendedores en "Gestionar Vendedores"
3. Ver reportes globales
4. Administrar todo el sistema

### Para Vendedores

1. Login con credenciales asignadas
2. Crear clientes en "Gestionar Clientes"
3. Crear pedidos vinculados a clientes
4. Ver sus propios reportes y métricas

### Seguimiento de Ventas

1. Ir a "Reportes Avanzados"
2. Seleccionar periodo (diario/semanal/mensual)
3. Filtrar por vendedor (opcional)
4. Ver métricas y gráficos
5. Exportar datos (CSV/JSON)
6. Usar en dashboard externo si es necesario

---

## 🆘 Solución de Problemas

### "No puedo iniciar sesión"
- Verifica que el archivo `/admin/data/usuarios.json` existe
- Usa las credenciales por defecto: admin/admin123
- Borra las cookies del navegador

### "No se guardan los datos"
- Verifica permisos en `/admin/data/` (775 o 777)
- En Linux:`chmod 775 /admin/data/`
- Verifica que PHP tiene permisos de escritura

### "No veo la opción de gestionar vendedores"
- Solo visible para usuarios con rol "admin"
- Los vendedores no tienen acceso a esta función

### "Los reportes no muestran datos"
- Asegúrate de tener al menos un pedido creado
- Verifica los filtros de fecha
- Recarga la página (F5)

---

## 📞 Soporte

Sistema desarrollado para La Crem  
Versión: 2.0 (con autenticación y reportes avanzados)  
Fecha: Febrero 2024  

---

## 🔮 Próximas Actualizaciones

- [ ] Gráficos visuales en reportes
- [ ] Notificaciones por email
- [ ] Dashboard en tiempo real
- [ ] App móvil
- [ ] Integración con WhatsApp
- [ ] Sistema de metas y comisiones
- [ ] Gestión de inventario

---

**¡Sistema completo y listo para usar!** 🎉  

---

## 🚀 Cómo Acceder

El panel de administración está disponible en:

```
http://tupagina.com/admin/
```

### Páginas disponibles:

1. **Panel Principal** (`/admin/index.html`)
   - Estadísticas generales
   - Acceso rápido a todas las funciones

2. **Gestionar Clientes** (`/admin/clientes.html`)
   - Crear nuevos clientes
   - Ver lista de clientes registrados
   - Eliminar clientes

3. **Crear Pedidos** (`/admin/pedidos.html`)
   - Seleccionar cliente
   - Agregar productos y cantidades
   - Calcular total con descuentos
   - Guardar pedido

4. **Reportes de Ventas** (`/admin/reportes.html`)
   - Resumen de ventas por cliente
   - Top 5 mejores clientes
   - Estadísticas generales
   - Exportar a Excel

---

## 📊 Estructura de Datos

### Clientes

```json
{
  "id": "cli_12345abc",
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "telefono": "+34 123 456 789",
  "direccion": "Calle Principal 123, Madrid",
  "empresa": "Empresa XYZ",
  "notas": "Cliente preferido",
  "fecha_creacion": "2024-02-25 10:30:00"
}
```

### Pedidos

```json
{
  "id": "ped_67890def",
  "cliente_id": "cli_12345abc",
  "items": [
    {
      "producto_id": "sig",
      "nombre": "Signature",
      "cantidad": 5,
      "precio": 150,
      "subtotal": 750
    }
  ],
  "descuento": 10,
  "notas": "Entrega rápida",
  "fecha": "2024-02-25 15:45:00"
}
```

### Resumen de Ventas

```json
{
  "cliente_id": "cli_12345abc",
  "cliente_nombre": "Juan Pérez",
  "cliente_email": "juan@example.com",
  "total_productos": 25,
  "total_ventas": 4500.00,
  "cantidad_pedidos": 8
}
```

---

## 🛠️ Archivos del Sistema

```
/admin/
├── index.html              # Panel principal
├── clientes.html           # Gestión de clientes
├── pedidos.html            # Crear pedidos
├── reportes.html           # Reportes y estadísticas
├── api/
│   ├── api.php             # API principal
│   └── db_functions.php    # Funciones de base de datos
├── data/
│   ├── clientes.json       # Datos de clientes
│   └── pedidos.json        # Datos de pedidos
└── js/
    └── (Scripts adicionales)
```

---

## 🔧 Funcionalidades

### ✨ Clientes

- **Crear**: Nombre, email, teléfono, dirección, empresa, notas
- **Listar**: Ver todos los clientes registrados
- **Eliminar**: Remover un cliente del sistema
- **Editar**: Funcionalidad próxima

### 📦 Pedidos

- **Crear**: Seleccionar cliente y agregar productos
- **Productos**: Signature, Premium, Tradicional, Refrescante, Elite
- **Cantidades**: Indicar cuántos de cada producto
- **Descuentos**: Aplicar descuentos porcentuales
- **Notas**: Agregar instrucciones especiales
- **Listar**: Ver últimos 10 pedidos registrados
- **Eliminar**: Remover un pedido

### 📈 Reportes

- **Estadísticas Generales**:
  - Total de clientes
  - Total de pedidos
  - Total de productos vendidos
  - Ingresos totales

- **Tabla Completa**: Resumen de todas las ventas por cliente
  - Categorías: Nuevo, VIP Plata, VIP Oro
  - Ordenadas por monto de ventas

- **Top 5 Clientes**: Mejores clientes clasificados

- **Exportar**: Descargar datos en formato CSV/Excel

---

## 💾 Almacenamiento

Actualmente, los datos se guardan en **archivos JSON**:
- `/admin/data/clientes.json`
- `/admin/data/pedidos.json`

Estos archivos se crean automáticamente en la primera ejecución.

### **Requisitos:**

1. La carpeta `/admin/data/` debe tener permisos de escritura (775 en Linux/Mac)
2. PHP debe estar habilitado en el servidor

---

## 🔄 Migración a MySQL (Futuro)

Cuando tengas acceso a una base de datos MySQL:

1. Proporciona los datos de conexión:
   - Host
   - Usuario
   - Contraseña
   - Nombre de BD

2. Se crearán las tablas:
   - `clientes`
   - `pedidos`
   - `pedido_items`

3. Se migrarán automáticamente los datos existentes

---

## 🆘 Solución de Problemas

### "No se guardan los datos"
- Verifica que `/admin/data/` exista y tenga permisos de escritura
- En servidor: `chmod 755 /admin/data/`

### "No aparecer el listado de clientes"
- Recarga la página (F5)
- Verifica la consola del navegador (F12) para ver errores

### "Los datos no aparecen en reportes"
- Asegúrate de haber creado al menos un cliente
- Asegúrate de haber creado un pedido vinculado a ese cliente

---

## 📱 Compatibilidad

✅ Funciona en todos los navegadores modernos  
✅ Responsive (adaptado para móviles)  
✅ Compatible con Chrome, Firefox, Safari, Edge  

---

## 🔐 Nota de Seguridad

Este sistema está diseñado para uso interno. Para un entorno de producción, se recomienda:

1. Agregar autenticación (login)
2. Validación más estricta de datos
3. Migrare a una base de datos SQL
4. Usar HTTPS
5. Implementar protección CSRF

---

## 📞 Soporte

Para preguntas o problemas, contacta al equipo de desarrollo.

---

**Última actualización:** Febrero 2024
**Versión:** 1.0
