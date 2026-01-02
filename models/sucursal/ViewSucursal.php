<?php

namespace app\models\sucursal;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\db\Expression;
use app\models\sucursal\Sucursal;
use app\models\cliente\Cliente;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;
/**
 * This is the model class for table "view_sucursal".
 *
 * @property int $id
 * @property string $clave Clave
 * @property int $encargado_id Encargado Id
 * @property string $encargado
 * @property string $nombre
 * @property string $rfc
 * @property string $email
 * @property int $status
 * @property string $img_banner Imagen banner
 * @property string $logo Logo
 * @property string $telefono
 * @property string $telefono_movil Telefono movil
 * @property string $informacion InformaciÃ³n
 * @property string $comentarios Comentarios
 * @property int $tipo Tipo
 * @property string $direccion Dirección
 * @property string $num_ext Número interior
 * @property string $num_int Número exterior
 * @property string $colonia Colonia
 * @property int $estado_id Estado
 * @property string $municipio Singular
 * @property string $estado Singular
 * @property int $created_at Creado
 * @property string $cp Código Postal
 * @property string $created_by_user
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property string $updated_by_user
 * @property int $updated_by Modificado por
 */
class ViewSucursal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_sucursal';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clave' => 'Clave',
            'encargado_id' => 'Encargado Id',
            'encargado' => 'Encargado',
            'nombre' => 'Nombre',
            'rfc' => 'Rfc',
            'email' => 'Email',
            'status' => 'Status',
            'img_banner' => 'Imagen banner ',
            'logo' => 'Logo',
            'telefono' => 'Telefono',
            'telefono_movil' => 'Telefono movil',
            'informacion' => 'InformaciÃ³n',
            'comentarios' => 'Comentarios',
            'tipo' => 'Tipo',
            'direccion' => 'Dirección',
            'num_ext' => 'Número interior',
            'num_int' => 'Número exterior',
            'estado_id' => 'Estado',
            'municipio' => 'Singular',
            'estado' => 'Singular',
            'created_at' => 'Creado',
            'cp' => 'Código Postal',
            'created_by_user' => 'Created By User',
            'created_by' => 'Creado por',
            'updated_at' => 'Modificado',
            'updated_by_user' => 'Updated By User',
            'updated_by' => 'Modificado por',
        ];
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
                    'clave',
                    'encargado_id',
                    'encargado',
                    'nombre',
                    'rfc',
                    'email',
                    'status',
                    'telefono',
                    'telefono_movil',
                    'informacion',
                    'comentarios',
                    'tipo',
                    'direccion',
                    'num_ext',
                    'num_int',
                    'estado_id',
                    'municipio',
                    'estado',
                    'created_at',
                    "origen",
                    'created_by_user',
                    'created_by',
                    'updated_at',
                    'updated_by_user',
                    'updated_by',
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

            if (isset($filters['origen']) && $filters['origen'])
                $query->andWhere(['origen' =>  $filters['origen']]);

             if (isset($filters['estado_id']) && $filters['estado_id'])
                $query->andWhere(['estado_id' =>  $filters['estado_id']]);

            if($search)
                $query->andFilterWhere([
                    'or',

                    ['like', 'nombre', $search],
                    ['like', 'email', $search],

                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        $rows = $query->all();

        return [
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
            'rows'  => $rows
        ];
    }

    public static function getSucursalAjax($id)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "`id`",
                "`clave`",
                "`nombre` AS `nombre`",
                "`tipo` AS `tipo`",
                'estado',
                'estado_id',
                'direccion',
                'telefono',
                'is_reenvio',
                'encargado',
            ])
            ->from(self::tableName())
            ->andWhere([ 'id' =>  $id]);

        $response = $query->one();


        $listaDestino   =  (new Query())
        ->select([
            "lista_precio_mx.`id`",
            "`precio_libra`",
            "`destino_id`",
            "esys_lista_desplegable.singular  as destino_text",
            "`default`",
        ])->from('lista_precio_mx')
        ->leftJoin("esys_lista_desplegable","lista_precio_mx.destino_id = esys_lista_desplegable.id")
        ->andWhere(["and",
            ['=','sucursal_recibe_id', $id],
            ['=','default', ListaPrecioMx::IS_DEFAULT ],
            ['=','tipo', ListaPrecioMx::TIPO_LIBRA ],
        ])
        ->all();



        $response["lista_destino"] = count($listaDestino) >  0  && $listaDestino ? $listaDestino : [];

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return $response;
    }

    public static function getSucursalesEstadoAjax($sucursal)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "`id`",
                "`clave`",
                "`nombre` AS `nombre`",
                "CONCAT_WS(' ', `nombre`,'[',`clave`,']') AS `text`",
            ])
            ->from(self::tableName())
            ->andWhere(['like', 'nombre', $sucursal])
            ->andWhere(['origen' =>  Sucursal::ORIGEN_MX ])
            ->andWhere(['status' =>  Sucursal::STATUS_ACTIVE ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->all();

    }

    public static function getSucursalesUsaAjax($sucursal)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "`id`",
                "`clave`",
                "`nombre` AS `nombre`",
                "CONCAT_WS(' ', `nombre`,'[',`clave`,']') AS `text`",
            ])
            ->from(self::tableName())
            ->andWhere(['like', 'nombre', $sucursal])
            ->andWhere(['origen' =>  Sucursal::ORIGEN_USA ])
            ->andWhere(['status' =>  Sucursal::STATUS_ACTIVE ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->all();

    }

    public static function getSucursalHistoricoAjax($arr){

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
                    "SQL_CALC_FOUND_ROWS `envio_detalle`.`id`",
                    "envio.folio as folio",
                    "envio_detalle.tracked as tracked",
                    "concat_ws(' ',`cliente`.`nombre`,`cliente`.`apellidos`) AS `nombre_emisor`",
                    "envio_detalle.peso as peso",
                    "envio_detalle.cantidad as cantidad",
                    "envio.total as total",
                    "envio.status as status_envio",
                    "envio_detalle.status as status_paquete",
                    "envio.created_at as fecha",
                    "estado.singular as estado",
                    "municipio.singular as municipio",
                    "esys_direccion.direccion",
                ])
                ->from('envio_detalle')
                ->innerJoin("envio","envio_detalle.envio_id = envio.id")
                ->innerJoin("cliente", "envio.cliente_emisor_id = cliente.id")
                ->innerJoin("sucursal","envio_detalle.sucursal_receptor_id = sucursal.id")
                ->leftJoin('esys_direccion','envio_detalle.id = esys_direccion.cuenta_id and esys_direccion.cuenta = 5 and  esys_direccion.tipo =1')
                ->leftJoin('esys_lista_desplegable estado','esys_direccion.estado_id = estado.id_2 and estado.label = "crm_estado"')
                ->leftJoin('esys_lista_desplegable municipio','esys_direccion.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
                ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ])
                ->andWhere(["<>","envio_detalle.status",EnvioDetalle::STATUS_CANCELADO ])
                ->andWhere(['sucursal_receptor_id' => $filters["sucursal_id"] ])
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/

          if (Yii::$app->user->can('agenteVenta')){
                $query->andWhere(['cliente.asignado_id' =>  Yii::$app->user->identity->id ]);
                $query->andWhere(['cliente.status' =>  Cliente::STATUS_ACTIVE ]);
          }


            if($search)
                $query->andFilterWhere([
                    'or',

                    ['like', 'nombre', $search],
                    ['like', 'email', $search],

                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        $rows = $query->all();

        return [
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
            'rows'  => $rows
        ];
    }

    public static function getEstadoCuentaJsonBtt($arr){

         Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);

        $select = [];
        $select = [
            "SQL_CALC_FOUND_ROWS `sucursal`.`id`",

            "`sucursal`.`clave` AS `clave`",
            "concat_ws(' ',`encargado`.`nombre`,`encargado`.`apellidos`) AS `encargado`",
            "`sucursal`.`nombre` AS `nombre`",
            "`sucursal`.`tipo` AS `tipo`",
            "`sucursal`.`status` AS `status`",
            "`sucursal`.`origen` AS `origen`",
            "`sucursal`.`telefono` AS `telefono`",
            "`sucursal`.`telefono_movil` AS `telefono_movil`",
            "`esys_direccion`.`direccion` AS `direccion`",
            "`esys_direccion`.`num_ext` AS `num_ext`",
            "`esys_direccion`.`num_int` AS `num_int`",
            "`esys_direccion`.`estado_id` AS `estado_id`",
            "`municipio`.`singular` AS `municipio`",
            "`estado`.`singular` AS `estado`",
            "`sucursal`.`created_at` AS `created_at`",
            "concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`",
            "`sucursal`.`created_by` AS `created_by`",
            "`sucursal`.`updated_at` AS `updated_at`",
            "concat_ws(' ',`updated`.`nombre`,`updated`.`apellidos`) AS `updated_by_user`",
            "`sucursal`.`updated_by` AS `updated_by`"
        ];

        if( isset($filters['date_range']) && $filters['date_range']) {
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;


            $select = array_merge($select, [
                '(SELECT SUM(envio_detalle.cantidad) FROM envio_detalle
                    INNER JOIN envio ON envio_detalle.envio_id = envio.id
                    WHERE envio_detalle.status = 10 and envio_detalle.sucursal_receptor_id = sucursal.id AND
                    envio.tipo_envio = '. Envio::TIPO_ENVIO_TIERRA .' AND created_at
                     BETWEEN '. $date_ini.' AND '.$date_fin.')  AS paquete_tierra',
            ]);

            $select = array_merge($select, [
                '(SELECT SUM(envio_detalle.cantidad) FROM envio_detalle
                    INNER JOIN envio ON envio_detalle.envio_id = envio.id
                    WHERE envio_detalle.status = 10 and envio_detalle.sucursal_receptor_id = sucursal.id AND
                    envio.tipo_envio = '. Envio::TIPO_ENVIO_LAX .' AND created_at
                     BETWEEN '. $date_ini.' AND '.$date_fin.')  AS paquete_lax',
            ]);

            $select = array_merge($select, [
                '(SELECT SUM(envio_detalle.cantidad) FROM envio_detalle
                    INNER JOIN envio ON envio_detalle.envio_id = envio.id
                    WHERE  envio_detalle.status = 10 and  envio.sucursal_emisor_id = sucursal.id AND
                    envio.tipo_envio = '. Envio::TIPO_ENVIO_MEX .' AND created_at
                     BETWEEN '. $date_ini.' AND '.$date_fin.')  AS paquete_mex',
            ]);
        }else{

            $select = array_merge($select, [
                '(SELECT SUM(envio_detalle.cantidad) FROM envio_detalle
                    INNER JOIN envio ON envio_detalle.envio_id = envio.id
                    WHERE envio_detalle.status = 10 and envio_detalle.sucursal_receptor_id = sucursal.id AND
                    envio.tipo_envio = '. Envio::TIPO_ENVIO_TIERRA .')  AS paquete_tierra',
            ]);

            $select = array_merge($select, [
                '(SELECT SUM(envio_detalle.cantidad) FROM envio_detalle
                    INNER JOIN envio ON envio_detalle.envio_id = envio.id
                    WHERE envio_detalle.status = 10 and envio_detalle.sucursal_receptor_id = sucursal.id AND
                    envio.tipo_envio = '. Envio::TIPO_ENVIO_LAX .')  AS paquete_lax',
            ]);

            $select = array_merge($select, [
                '(SELECT SUM(envio_detalle.cantidad) FROM envio_detalle
                    INNER JOIN envio ON envio_detalle.envio_id = envio.id
                    WHERE envio_detalle.status = 10 and envio.sucursal_emisor_id = sucursal.id AND
                    envio.tipo_envio = '. Envio::TIPO_ENVIO_MEX .')  AS paquete_mex',
            ]);

        }

        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select($select)
                ->from('sucursal')
                ->leftJoin("`user` `encargado`","`sucursal`.`encargado_id` = `encargado`.`id`")
                ->leftJoin("`user` `created`","`sucursal`.`created_by` = `created`.`id`")
                ->leftJoin("`user` `updated`","`sucursal`.`updated_by` = `updated`.`id`")
                ->leftJoin("esys_direccion","`sucursal`.`id` = `esys_direccion`.`cuenta_id` and `esys_direccion`.`cuenta` = 3 and `esys_direccion`.`tipo` = 1")
                ->leftJoin("`esys_lista_desplegable` `estado`","`esys_direccion`.`estado_id` = `estado`.`id_2` and `estado`.`label` = 'crm_estado'")
                ->leftJoin("`esys_lista_desplegable` `municipio`","`esys_direccion`.`municipio_id` = `municipio`.`id_2` and `esys_direccion`.`estado_id` = `municipio`.`param1` and  `municipio`.`label` = 'crm_municipio'")
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        if (isset($filters['origen']) && $filters['origen'])
            $query->andWhere(['sucursal.origen' =>  $filters['origen']]);



        if($search)
            $query->andFilterWhere([
                'or',
                ['like', 'sucursal.nombre', $search],
            ]);


        $rows = $query->all();

        return [
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
            'rows'  => $rows
        ];

    }
}
