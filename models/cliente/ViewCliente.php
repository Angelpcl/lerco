<?php
namespace app\models\cliente;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\esys\EsysDireccion;
use app\models\esys\EsysDireccionCodigoPostal;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;

/**
 * This is the model class for table "view_cliente".
 *
 * @property int $id Id
 * @property int $titulo_personal_id Titulo personal
 * @property string $titulo_personal Singular
 * @property string $email Correo electrónico
 * @property string $email2 Correo secundario
 * @property string $empresa Empresa
 * @property string $nombre Nombre
 * @property string $apellidos Apellidos
 * @property string $sexo Sexo
 * @property string $cargo Cargo
 * @property string $departamento Departamento
 * @property int $origen_id Se entero través de
 * @property string $origen Singular
 * @property int $asignado_a_id Asignado a
 * @property string $asignado_a
 * @property string $tel Teléfono trabajo
 * @property string $tel_ext Extensión
 * @property string $tel2 Otro teléfono
 * @property string $movil Teléfono movil
 * @property string $pag_web Página web
 * @property string $notas Notas / Comentarios
 * @property int $api_enabled Habilitar API
 * @property string $api_username Nombre de usuario (API)
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewCliente extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_cliente';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo_personal_id' => 'Titulo Personal ID',
            'titulo_personal' => 'Titulo Personal',
            'nombre_completo' => 'Nombre Completo',
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'email' => 'Email',
            'sexo' => 'Sexo',
            'origen' => 'Origen',
            'telefono' => 'Telefono',
            'telefono_movil' => 'Telefono Movil',
            'status' => 'Status',
            'notas' => 'Notas',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'created_by_user' => 'Created By User',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'updated_by_user' => 'Updated By User',
        ];
        /*return [
            'id' => 'Id',
            'titulo_personal_id' => 'Titulo personal',
            'titulo_personal' => 'Singular',
            'email' => 'Correo electrónico',
            'email2' => 'Correo secundario',
            'empresa' => 'Empresa',
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'sexo' => 'Sexo',
            'cargo' => 'Cargo',
            'departamento' => 'Departamento',
            'origen_id' => 'Se entero través de',
            'origen' => 'Singular',
            'asignado_a_id' => 'Asignado a',
            'asignado_a' => 'Asignado A',
            'tel' => 'Teléfono trabajo',
            'tel_ext' => 'Extensión',
            'tel2' => 'Otro teléfono',
            'movil' => 'Teléfono movil',
            'pag_web' => 'Página web',
            'notas' => 'Notas / Comentarios',
            'api_enabled' => 'Habilitar API',
            'api_username' => 'Nombre de usuario (API)',
            'status' => 'Estatus',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'created_by_user' => 'Created By User',
            'updated_at' => 'Modificado',
            'updated_by' => 'Modificado por',
            'updated_by_user' => 'Updated By User',
        ];*/
    }

    public static function primaryKey()
    {
        return ['id'];
    }


//------------------------------------------------------------------------------------------------//
// JSON Bootstrap Table
//------------------------------------------------------------------------------------------------//
    public static function getJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    "SQL_CALC_FOUND_ROWS `id`",
                    'titulo_personal_id',
                    'titulo_personal',
                    'nombre_completo',
                    'nombre',
                    'apellidos',
                    'email',
                    'sexo',
                    'origen',
                    'telefono',
                    'telefono_movil',
                    'status',
                    'status_call',
                    'tipo_cliente',
                    'notas',
                    'created_at',
                    'created_by',
                    'created_by_user',
                    'updated_at',
                    'updated_by',
                    'updated_by_user',
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }
            if (isset($filters['asignado_id']) && $filters['asignado_id'])
                $query->andWhere(['asignado_id' =>  $filters['asignado_id']]);


            if (isset($filters['origen']) && $filters['origen'])
                $query->andWhere(['origen' =>  $filters['origen']]);

            if (isset($filters['status_respuesta_call']) && $filters['status_respuesta_call'])
                $query->andWhere(['status_call_id' =>  $filters['status_respuesta_call']]);

            if (isset($filters['tipo_cliente']) && $filters['tipo_cliente'])
                $query->andWhere(['tipo_cliente_id' =>  $filters['tipo_cliente']]);


            /**==============================================
             * Filtamos por asiganacion agente de ventas
             ================================================*/

            if (Yii::$app->user->can('agenteVenta')) {
                 $query->andWhere(['asignado_id' =>  Yii::$app->user->identity->id ]);
                 $query->andWhere(['status' =>  Cliente::STATUS_ACTIVE ]);
            }

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'telefono_movil', $search],
                    ['like', 'telefono', $search],
                    ['like', 'nombre_completo', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
//        die();

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
    //------------------------------------------------------------------------------------------------//
// JSON Bootstrap Table
//------------------------------------------------------------------------------------------------//
    public static function getHistoricoVentasJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
            $select = [];

            if (isset($filters['tipo_servicio']) && $filters['tipo_servicio']) {
                $select = array_merge($select, [
                    "SQL_CALC_FOUND_ROWS `id`",
                    '(SELECT COUNT(*) FROM envio where envio.cliente_emisor_id = view_cliente.id and envio.status != 1  and envio.tipo_envio = '. $filters['tipo_servicio'] . ') AS envios_count',
                    '(SELECT SUM(envio.total) FROM envio where envio.cliente_emisor_id = view_cliente.id and envio.status != 1  and envio.tipo_envio = '. $filters['tipo_servicio'] . ') AS monto_total',
                ]);
            }else{
            $select = array_merge($select, [
                    "SQL_CALC_FOUND_ROWS `id`",
                    '(SELECT COUNT(*) FROM envio where envio.cliente_emisor_id = view_cliente.id and envio.status != 1  ) AS envios_count',
                    '(SELECT SUM(envio.total) FROM envio where envio.cliente_emisor_id = view_cliente.id and envio.status != 1) AS monto_total',
                ]);
            }

            $select = array_merge($select, [

                    'titulo_personal_id',
                    'titulo_personal',
                    'nombre_completo',
                    'nombre',
                    'apellidos',
                    'email',
                    'sexo',
                    'origen',
                    'telefono',
                    'telefono_movil',
                    'status',
                    'status_call',
                    'tipo_cliente',
                    'notas',
                    'created_at',
                    'created_by',
                    'created_by_user',
                    'updated_at',
                    'updated_by',
                    'updated_by_user',
            ]);

            $query = (new Query())
                ->select($select)
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }
            if (isset($filters['asignado_id']) && $filters['asignado_id'])
                $query->andWhere(['asignado_id' =>  $filters['asignado_id']]);


            if (isset($filters['origen']) && $filters['origen'])
                $query->andWhere(['origen' =>  $filters['origen']]);

            if (isset($filters['status_respuesta_call']) && $filters['status_respuesta_call'])
                $query->andWhere(['status_call_id' =>  $filters['status_respuesta_call']]);

            if (isset($filters['tipo_cliente']) && $filters['tipo_cliente'])
                $query->andWhere(['tipo_cliente_id' =>  $filters['tipo_cliente']]);


            /**==============================================
             * Filtamos por asiganacion agente de ventas
             ================================================*/

            if (Yii::$app->user->can('agenteVenta')) {
                 $query->andWhere(['asignado_id' =>  Yii::$app->user->identity->id ]);
                 $query->andWhere(['status' =>  Cliente::STATUS_ACTIVE ]);
            }

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'telefono_movil', $search],
                    ['like', 'telefono', $search],
                    ['like', 'nombre_completo', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getHistoricoSucursalJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/

            $query = (new Query())
                ->select([
                    "sucursal.id",
                    "sucursal.nombre",
                    "sucursal.telefono",
                    "sucursal.telefono_movil",
                    "sucursal.status",
                    "concat_ws(' ',`encargado`.`nombre`,`encargado`.`apellidos`) AS `encargado`",
                    "count(envio.id) as n_envio",
                    "sum(envio_detalle.cantidad)  as n_paquetes",
                    "sum(envio.total) as n_total",
                    "ROUND(sum(envio.peso_total),2) as n_peso_total",
                    "ROUND(sum(envio_detalle.peso),2) as n_peso_total_paquete"
                ])
                ->from("envio")
                ->innerJoin("envio_detalle","envio.id = envio_detalle.envio_id")
                ->innerJoin("cliente","envio.cliente_emisor_id = cliente.id")
                ->innerJoin("sucursal","envio_detalle.sucursal_receptor_id = sucursal.id")
                ->leftJoin("user `encargado`","`sucursal`.`encargado_id` = `encargado`.`id`")
                ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ])
                ->andWhere(["<>","envio_detalle.status",EnvioDetalle::STATUS_CANCELADO ])
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','envio.created_at', $date_ini, $date_fin]);
            }
            if (isset($filters['asignado_id']) && $filters['asignado_id'])
                $query->andWhere(['asignado_id' =>  $filters['asignado_id']]);


            if (isset($filters['origen']) && $filters['origen'])
                $query->andWhere(['origen' =>  $filters['origen']]);


            if (isset($filters['tipo_cliente']) && $filters['tipo_cliente'])
                $query->andWhere(['tipo_cliente_id' =>  $filters['tipo_cliente']]);


            /**==============================================
             * Filtamos por asiganacion agente de ventas
             ================================================*/

            if (Yii::$app->user->can('agenteVenta')) {
                 $query->andWhere(['cliente.asignado_id' =>  Yii::$app->user->identity->id ]);
                 $query->andWhere(['cliente.status' =>  Cliente::STATUS_ACTIVE ]);
            }

            $query->groupBy("sucursal.id");


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'telefono_movil', $search],
                    ['like', 'telefono', $search],
                    ['like', 'nombre', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getHistoricoPromocionJsonBtt($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);




        $query = (new Query())
            ->select([
                "SQL_CALC_FOUND_ROWS `cliente_codigo_promocion`.`id`",
                "cliente_codigo_promocion.clave",
                "cliente_codigo_promocion.tipo",
                "promocion.nombre as promocion_nombre",
                "cliente_codigo_promocion.requiered_libras",
                "cliente_codigo_promocion.tipo_condonacion",
                "cliente_codigo_promocion.descuento",
                "cliente_codigo_promocion.status as status_code",
                'view_cliente.nombre_completo',
                'view_cliente.origen',
                'view_cliente.telefono',
                'view_cliente.telefono_movil',
                'view_cliente.status',
                'view_cliente.tipo_cliente',
                'cliente_codigo_promocion.created_at',
                'cliente_codigo_promocion.created_by',
                'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`',
                'cliente_codigo_promocion.updated_at',
                'cliente_codigo_promocion.updated_by',
                'concat_ws(" ",`updated`.`nombre`,`updated`.`apellidos`) AS `updated_by_user`',

            ])
            ->from('cliente_codigo_promocion')
            ->innerJoin(self::tableName(),'view_cliente.id = cliente_codigo_promocion.cliente_id')
            ->leftJoin('promocion','cliente_codigo_promocion.promocion_id = promocion.id')
            ->leftJoin('`user` `created`','`cliente_codigo_promocion`.`created_by` = `created`.`id`')
            ->leftJoin('`user` `updated`','`cliente_codigo_promocion`.`updated_by` = `updated`.`id`')
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['asignado_id']) && $filters['asignado_id'])
                $query->andWhere(['view_cliente.asignado_id' =>  $filters['asignado_id']]);


            if (isset($filters['tipo_cliente']) && $filters['tipo_cliente'])
                $query->andWhere(['view_cliente.tipo_cliente_id' =>  $filters['tipo_cliente']]);

            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['cliente_codigo_promocion.tipo' =>  $filters['tipo']]);


            /**==============================================
             * Filtamos por asiganacion agente de ventas
             ================================================*/

            if (Yii::$app->user->can('agenteVenta')) {
                 $query->andWhere(['view_cliente.asignado_id' =>  Yii::$app->user->identity->id ]);
                 $query->andWhere(['view_cliente.status' =>  Cliente::STATUS_ACTIVE ]);
            }

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'view_cliente.telefono_movil', $search],
                    ['like', 'view_cliente.telefono', $search],
                    ['like', 'view_cliente.nombre_completo', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
    public static function getClienteAjax($q,$search_opt = false,$pais_id = false)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        /* $query = (new Query())
            ->select([
                "view_cliente.`id`",
                "if(origen = 2 , CONCAT_WS(' ', `nombre_completo`,'','[ Tel: ',`telefono_movil`,'/',`telefono`,']' )  , CONCAT_WS(' ', `nombre_completo`,'','[ Tel: ',`telefono_movil`,'/',`telefono`,']')) AS `text`",
                "nombre",
                "apellidos",
                "email",
                "telefono",
                "telefono_movil",
                "origen",
                "colonia",
                "esys_direccion_codigo_postal.id as codigo_postal_id",
                "codigo_postal",
                "colonia_usa",
                "estado_usa",
                "esys_direccion.estado_id",
                "municipio_usa",
                "esys_direccion.municipio_id",
                "codigo_postal_usa",
                "direccion",
                "num_ext",
                "num_int",
                "referencia",
                'country_id'
            ])
            ->from(self::tableName()) 
            
            $query = (new Query())
            ->select([
                "user.`id`",
                "CONCAT_WS(' ', user.`nombre`, user.`apellidos`, '[', IFNULL(cliente_razon_social.`nombre`, ''), ']') AS `text`",
                "user.`nombre`",
                "user.`apellidos`"
            ])
            ->from("user")
            ->leftJoin('cliente_razon_social', 'user.`cliente_razon_social` = cliente_razon_social.`id`') // LEFT JOIN con la tabla cliente_razon_social
            ->orderBy('user.id desc')
            ->limit(50);*/

            $query = (new Query())
            ->select([
                "user.`id`",
                " CONCAT_WS(' ', `nombre`,`apellidos`, '[' ,`cliente_razon_social`, ']' ) AS `text`",
                "nombre",
                "apellidos",
                
            ])
            ->from("user")

            ->orderBy('id desc')
            ->limit(50);


           /* $query->andWhere(['view_cliente.status' => Cliente::STATUS_ACTIVE ]);
            if($pais_id){
                $query->andWhere(['view_cliente.country_id' => $pais_id]);
            } */

            if ($search_opt)
                $query->andWhere(['user.id' => $q]);
            else{
                 $query->andFilterWhere([
                    'or',
                    ['like', 'telefono_movil', $q],
                    ['like', 'telefono', $q],
                    ['like', 'nombre', $q],
                    ['like', 'apellidos', $q],
                ]);
                //$query->andWhere(['like', 'nombre_completo', $q]);

            }
           
            /*
            $query->innerJoin(EsysDireccion::tableName(),"esys_direccion.cuenta_id = view_cliente.id");
            $query->leftJoin(EsysDireccionCodigoPostal::tableName(),"esys_direccion_codigo_postal.id = esys_direccion.codigo_postal_id");
            $query->andWhere(['esys_direccion.cuenta' => EsysDireccion::CUENTA_CLIENTE, 'esys_direccion.tipo' => EsysDireccion::TIPO_PERSONAL]);
            */

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $search_opt ? $query->one() :$query->all();
    }

    public static function getClienteVendedorAjax($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;
        parse_str($arr['filters'], $filters);

        /************************************
        / Preparamos consulta
        /***********************************/

            $select =[
                "concat_ws(' ', us_venta.nombre,us_venta.apellidos) as nombre_completo",
                "date_format(FROM_UNIXTIME(us_call.created_at),'%e') AS `fecha_dia`",
                "date_format(FROM_UNIXTIME(us_call.created_at),'%m') AS `fecha_mes`",
                "date_format(FROM_UNIXTIME(us_call.created_at),'%Y') AS `fecha_ano`"
            ];

            if (isset($filters['is_reporte']) && $filters['is_reporte']) {
                $select = array_merge($select,[
                    "c.id AS clie_id",
                    "concat_ws(' ', c.nombre,c.apellidos) AS cliente_nombre",
                    "esys_ld_tipo.id AS clie_tipo_id",
                    "esys_ld_tipo.singular AS clie_tipo",
                    "c.asignado_id AS asignado_id",
                    "esys_ld.id AS status_call_id",
                    "us_call.telefono",
                    "date_format(FROM_UNIXTIME(us_call.created_at),'%Y-%m-%d') AS `fecha`",
                    "date_format(FROM_UNIXTIME(us_call.created_at),'%H:%i:%s') AS `hora`",
                    "esys_ld.singular as tipo_respuesta",
                    "us_call.comentario",
                ]);
            }else{
                $select = array_merge($select,[
                    "count(*) as count_contact",
                ]);
            }

            $query = (new Query())
                ->select($select)
                ->from("user us_venta")
                ->leftJoin("cliente_historico_call us_call","us_venta.id = us_call.created_by")
                ->innerJoin("cliente as c","us_call.cliente_id = c.id")
                ->andWhere(["us_call.tipo" => ClienteHistoricoCall::TIPO_CLIENTE ])
                ->orderBy("us_call.created_at asc");


            /************************************
            / Agrupamos si no es reporte
            /***********************************/

            if (!isset($filters['is_reporte'])) {
                $query->groupBy(["us_venta.id","fecha_dia","fecha_mes","fecha_ano"]);
            }

            /************************************
            / Agregamos relacion para reporte
            /***********************************/
            if (isset($filters['is_reporte']) && $filters['is_reporte']) {
                $query->leftJoin("esys_lista_desplegable esys_ld","us_call.tipo_respuesta_id = esys_ld.id");
                $query->leftJoin("esys_lista_desplegable esys_ld_tipo","c.tipo_cliente_id = esys_ld_tipo.id");
            }

            /************************************
            / Filtramos la consulta
            /***********************************/
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','us_call.created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['asignado_id']) && $filters['asignado_id'])
                $query->andWhere(['us_venta.id' =>  $filters['asignado_id']]);


            if (isset($filters['origen']) && $filters['origen'])
                $query->andWhere(['c.origen' =>  $filters['origen']]);

            if (isset($filters['status_respuesta_call']) && $filters['status_respuesta_call'])
                $query->andWhere(['us_call.tipo_respuesta_id' =>  $filters['status_respuesta_call']]);

            if (isset($filters['tipo_cliente']) && $filters['tipo_cliente'])
                $query->andWhere(['c.tipo_cliente_id' =>  $filters['tipo_cliente']]);

            //echo ($query->createCommand()->rawSql) . '<br/><br/>';
//            die();
        return $query->all();
    }

    public static function getClienteAsignacionAjax($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;
        parse_str($arr['filters'], $filters);

        /************************************
        / Preparamos consulta
        /***********************************/

            $query = (new Query())
                ->select([
                    "(SELECT concat_ws(' ',trim(u_old.`nombre`),trim(u_old.`apellidos`)) FROM `user` u_old WHERE u_old.id = esys_cambio_log.valor_anterior ) AS asigando_old",
                    "(SELECT concat_ws(' ',trim(u_new.`nombre`),trim(u_new.`apellidos`)) FROM `user` u_new WHERE u_new.id = esys_cambio_log.valor_nuevo ) AS asigando_new",
                    "FROM_UNIXTIME(esys_cambio_log.created_at) AS fecha_cambio",
                    "concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS cambio_realizado_por",
                ])
                ->from("esys_cambio_log")
                ->innerJoin("`user` `created`","esys_cambio_log.created_by = `created`.id")
                ->andWhere(["esys_cambio_log.modulo" => 'cliente'])
                ->andWhere(["esys_cambio_log.registro" => 'asignado_id'])
                ->orderBy("esys_cambio_log.created_at DESC");



            /************************************
            / Filtramos la consulta
            /***********************************/
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','esys_cambio_log.created_at', $date_ini, $date_fin]);
            }

            return $query->all();
    }


    public static function getClienteCodigoAjax($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    'cliente_codigo_promocion.id',
                    'promocion_id',
                    'cliente_id',
                    'promocion.nombre',
                    'clave',
                    'cliente_codigo_promocion.tipo',
                    'requiered_libras',
                    'descuento',
                    'date_format(FROM_UNIXTIME(fecha_rango_ini),"%Y-%m-%d") as fecha_rango_ini',
                    'date_format(FROM_UNIXTIME(fecha_rango_fin),"%Y-%m-%d") as fecha_rango_fin',
                    'cliente_codigo_promocion.status'
                ])
                ->from('cliente_codigo_promocion')
                ->leftJoin('promocion','promocion.id = cliente_codigo_promocion.promocion_id')
                ->andWhere(['cliente_codigo_promocion.cliente_id' => $arr['cliente_id']])
                ->orderBy('cliente_codigo_promocion.id asc');

        return  $query->all();
    }

    public static function getClienteDepuracionAjax($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);

        /************************************
        / Preparamos consulta
        /***********************************/
        $select = [];
        $select = array_merge($select, [
            "SQL_CALC_FOUND_ROWS  `cliente`.`id`",
            "concat_ws(' ',trim(`cliente`.`nombre`),trim(`cliente`.`apellidos`)) AS `nombre_completo`",
            "`cliente`.`nombre`",
            "`cliente`.`apellidos`",
            "`cliente`.`telefono`",
            "`cliente`.`telefono_movil`",
            "`cliente`.`email`",
            "`cliente`.`status`",
            "`cliente`.`created_at`",
            "`cliente`.`updated_at`",
        ]);


        if( isset($filters['agrupar']['nombre']) || isset($filters['agrupar']['apellidos']) || isset($filters['agrupar']['movil']) || isset($filters['agrupar']['movilcasa']) || isset($filters['agrupar']['email']) ) {

            if(isset($filters['agrupar']['nombre']))
                $select = array_merge($select, [
                    "(SELECT count(*) FROM cliente filter_nombre where filter_nombre.status = 10 and filter_nombre.id != cliente.id and concat_ws(' ',filter_nombre.nombre,filter_nombre.apellidos) like concat_ws(' ',cliente.nombre,cliente.apellidos) ) as count_item_nombre",
                ]);

            if(isset($filters['agrupar']['apellidos']))
                $select = array_merge($select, [
                    "(SELECT count(*) FROM cliente filter_apellidos where filter_apellidos.status = 10 and filter_apellidos.id != cliente.id and filter_apellidos.apellidos != ''  and filter_apellidos.apellidos = cliente.apellidos ) as count_item_apellidos"
                ]);
            if(isset($filters['agrupar']['movil']))
                $select = array_merge($select, [
                    "(SELECT count(*) FROM cliente filter_telefono_movil where filter_telefono_movil.status = 10 and filter_telefono_movil.id != cliente.id  and SUBSTRING(`filter_telefono_movil`.`telefono_movil`,1,8) like SUBSTRING(`cliente`.`telefono_movil`,1,8)  ) as count_item_telefono",
                ]);

            if(isset($filters['agrupar']['movilcasa']))
                $select = array_merge($select, [
                    "(SELECT count(*) FROM cliente filter_telefono_casa where  filter_telefono_casa.status = 10 and filter_telefono_casa.id != cliente.id  and SUBSTRING(`filter_telefono_casa`.`telefono`,1,8) like SUBSTRING(`cliente`.`telefono_movil`,1,8)) as count_item_telefono_2",
                ]);

            if(isset($filters['agrupar']['email']))
                $select = array_merge($select, [
                    "(SELECT count(*) FROM cliente filter_email where filter_email.status = 10 and filter_email.id != cliente.id and filter_email.email != '' and filter_email.email = cliente.email) as count_item_email"
                ]);
        }



        $query = (new Query())
            ->select($select)
            ->from('cliente')
            ->andWhere(['cliente.status' => Cliente::STATUS_ACTIVE])
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);



            if (isset($filters['asignado_id']) && $filters['asignado_id'])
                $query->andWhere(['asignado_id' =>  $filters['asignado_id']]);

            if(isset($filters['agrupar']['nombre']))
                $query->orWhere(['>', "(SELECT count(*) FROM cliente filter_nombre where filter_nombre.status = 10 and filter_nombre.id != cliente.id and  concat_ws(' ',filter_nombre.nombre,filter_nombre.apellidos) like concat_ws(' ',cliente.nombre,cliente.apellidos))", 0 ]);

            if(isset($filters['agrupar']['apellidos']))
                $query->orWhere(
                    ['>', "(SELECT count(*) FROM cliente filter_apellidos where filter_apellidos.status = 10 and filter_apellidos.id != cliente.id and filter_apellidos.apellidos != ''  and filter_apellidos.apellidos = cliente.apellidos )", 0 ]);

            if(isset($filters['agrupar']['movil']))
                $query->orWhere(['>', '(SELECT count(*) FROM cliente filter_telefono_movil where  filter_telefono_movil.status = 10 and filter_telefono_movil.id != cliente.id and  SUBSTRING(`filter_telefono_movil`.`telefono_movil`,1,8) like SUBSTRING(`cliente`.`telefono_movil`,1,8)  )', 0 ]);


            if(isset($filters['agrupar']['movilcasa']))
                $query->orWhere(
                    ['>', '(SELECT count(*) FROM cliente filter_telefono_casa where  filter_telefono_casa.status = 10 and filter_telefono_casa.id != cliente.id  and SUBSTRING(`filter_telefono_casa`.`telefono`,1,8) like SUBSTRING(`cliente`.`telefono_movil`,1,8))', 0]);


            if(isset($filters['agrupar']['email']))
                $query->orWhere(['>', '(SELECT count(*) FROM cliente filter_email where filter_email.status = 10 and filter_email.id != cliente.id and filter_email.email != "" and filter_email.email = cliente.email)', 0]
                );


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
