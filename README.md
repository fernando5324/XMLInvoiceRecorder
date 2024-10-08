# Registrador de facturas XML 

Ejercicio de APIS para gestionar la información de facturas con archivos XML


Base de datos utilizada: [aqui](https://github.com/fernando5324/lbaltazarDev-InvoiceRecorderChalleng/blob/54b2973959dd744532ab531cab915f1c23e0753f/BD.sql)

### Función 1: Registro de serie, número, tipo del comprobante y moneda
Utilice el endpoint ya existente y agregue los campos serie, número, tipo de comprobante y moneda. También cree un nuevo endpoint para actualizar los registros ya guardados con los del campo xml_content.
Tenía pensado hacerlo cada vez que se guarda, pero teniendo en cuenta que no sería muy recurrente agregar nuevos campos a la tabla decidí hacerlo aparte.

    Nuevos campos a tabla vouchers:
    ALTER TABLE `vouchers` ADD COLUMN `voucher_serie` VARCHAR(200) DEFAULT NULL;
    ALTER TABLE `vouchers` ADD COLUMN `currency_type` VARCHAR(200) DEFAULT NULL;
    ALTER TABLE `vouchers` ADD COLUMN `voucher_type` VARCHAR(200) DEFAULT NULL;
    ALTER TABLE `vouchers` ADD COLUMN `voucher_number` VARCHAR(200) DEFAULT NULL;

    Ruta: /api/v1/vouchers/restore_values
	Metodo: GET
	Función: Restaurar la data actual con los nuevos campos requeridos
 
	Ruta: /api/v1/vouchers
	Metodo: POST
    Form-data: Files
	Función: Guardar comprobantes por multiples archivos .xml


### Función 2: Carga de comprobantes en segundo plano
Ahora se guarda en segundo plano los comprobantes y a la vez también el correo, hay varias validaciones por hacer pero en este caso solo agregue solo una validación de comprobante repetido.
Para esto el QUEUE_CONNECTION de .env debe estar en database y no en sync. Tambien se debe agregar un correo en .env para que le lleguen las notificaciones.

    Ruta: /api/v1/vouchers
	Metodo: POST
    Form-data: Files
	Función: Guardar comprobantes por multiples archivos .xml

### Función 3: Endpoint de montos totales
Suma total de los comprobantes con el tipo de monera de PEN y USD por separado para mostrarlo. Para esto hay un archivo en la carpeta example_files del proyecto donde contiene el tipo de moneda en usd.

	Ruta: api/v1/vouchers/total_mont
	Metodo: GET

### Función 4: Eliminación de comprobantes
Note que agregar la configuración que por cada consulta revise si hay el campo deleted_at es null para no mostrarlo. Lo que hice fue que al eliminar solo se agrega la fecha actual a ese campo y por defecto no se mostraría en ninguna consulta.

	Ruta: /api/v1/vouchers/delete
	Metodo: DELETE
	Parametros: id

### Función 5: Filtro en listado de comprobantes
nuevo endpoint con los filtros serie, número y por un rango de fechas. Hice que el rango de fechas sea opcional en caso solo se quiera revisar por serie y número que si son requeridos para su uso.

	Ruta: /api/v1/vouchers/search
	Metodo: GET
	Parametros: serie , number , initial_date , end_date

### Comentarios finales:
Me tomo más tiempo entender el proyecto y el uso de la autentificación por JWT, ya que era necesario iniciar sesión primero para poder hacer las consultas.
Me tomo más tiempo entender el proyecto y el uso de la autentificación por JWT, ya que era necesario iniciar sesión primero para poder hacer las consultas.



