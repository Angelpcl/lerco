<?php

namespace app\models\promocion;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\cliente\ClienteCodigoPromocion;
use app\models\envio\Envio;
/**
 * This is the model class for table "view_promocion".
 *
 * @property int $id ID
 * @property string $nombre Nombre de la promoción
 * @property int $fecha_inicia Fecha Inicial
 * @property int $fecha_expira Fecha expira
 * @property int $is_code_promocional Is Code Promocional
 * @property string $banner_imagen Banner
 * @property int $tipo_servicio Tipo servicio
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewPromocion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_promocion';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'fecha_inicia' => 'Fecha Inicia',
            'fecha_expira' => 'Fecha Expira',
            'is_code_promocional' => 'Is Code Promocional',
            'banner_imagen' => 'Banner Imagen',
            'tipo_servicio' => 'Tipo Servicio',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'created_by_user' => 'Created By User',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'updated_by_user' => 'Updated By User',
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
                    'nombre',
                    'fecha_inicia',
                    'fecha_expira',
                    'is_code_promocional',
                    'banner_imagen',
                    'status',
                    'tipo',
                    'tipo_servicio',
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
            if (isset($filters['tipo_servicio']) && $filters['tipo_servicio'])
                $query->andWhere(['tipo_servicio' =>  $filters['tipo_servicio']]);

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'nombre', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getPromocionDetalleAjax($arr){
        Yii::$app->response->format = Response::FORMAT_JSON;

        parse_str($arr['filters'], $filters);

        /************************************
        / Preparamos consulta
        /***********************************/

        $query = (new Query())
            ->select([
             "id",
             "nombre",
             "fecha_expira",
             'is_manual',
             "is_code_promocional",
            ])
            ->from(self::tableName());

            if (isset($filters['tipo_servicio']) && $filters['tipo_servicio']) {
                $query->andWhere(['tipo_servicio' =>  $filters['tipo_servicio'] ]);
            }

            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['tipo' =>  $filters['tipo']]);

            if (isset($filters['promocion_id']) && $filters['promocion_id'])
                $query->andWhere(['id' =>  $filters['promocion_id']]);
            else{

                $query->andWhere(['status' =>  Promocion::STATUS_ACTIVE ]);
                $query->andWhere(['>', 'fecha_expira', time() ]);
            }

        return  $query->one();
    }



    public static function getPromocionMexAjax($tipo = false, $tipo_servicio){

        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
             "id",
             "nombre",
             "fecha_expira",
             "is_code_promocional",
            ])
            ->from(self::tableName())
            ->andWhere(['>', 'fecha_expira', time() ])
            ->andWhere(['status' =>  Promocion::STATUS_ACTIVE ]);

            if ($tipo_servicio)
                $query->andWhere(['tipo_servicio' =>  $tipo_servicio ]);



        if (isset($tipo) && $tipo)
            $query->andWhere(['tipo' =>  $tipo]);

        return  $query->all();
    }


    public static function getPromocionComplementoAjax($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        parse_str($arr['filters'], $filters);



        $promocion_d = (new Query())
                ->select([
                    "p_d.id",
                    "p_d.lb_requerida",
                    "p_d.costo_libra_code",
                    "p_d.costo_libra_sin_code",
                 ])
                ->from('`promocion_detalle` p_d')
                ->orderBy("p_d.lb_requerida desc")
                ->andWhere(['p_d.promocion_id' =>  $filters['promocion_id']])
                ->andWhere(['<','p_d.lb_requerida' ,$filters['peso_requerido']]);
        $PromocionDetalle = $promocion_d->all();

        //$val_p_detalle = self::validaLibrasPagadasMin($PromocionDetalle);

        $PromocionComplemento = [];
        foreach ($PromocionDetalle as $key => $p_detalle) {
            $query = (new Query())
                ->select([
                    "p_c.id",
                    "p_d.id  as producto_detalle_id",
                    "pdc.tipo_complemento",
                    "`pdc`.`producto_id`",
                    "pdc.categoria_id",
                    "pdc.is_categoria",
                    "pdc.num_producto",
                    "esys_lista_desplegable.singular as categoria",
                    "producto.nombre as producto",
                    "pdc.is_valor_paquete",
                    "pdc.valor_paquete_aprox",
                    "pdc.is_producto",
                    "p_c.lb_free",
                    "p_c.cobro_impuesto",
                    "p_c.is_lb_free",
                    "p_c.is_envio_free",
                    "p_c.is_lbexcedente",
                    "p_c.lbexcedente",
                    "p_c.costo_libraexcedente"
                ])
                ->from('`promocion_detalle` p_d')
                ->innerJoin('promocion_detalle_complemento pdc','`pdc`.`promocion_detalle_id` = `p_d`.`id`')
                ->leftJoin('producto','`producto`.`id` = `pdc`.`producto_id`')
                ->leftJoin('esys_lista_desplegable','`esys_lista_desplegable`.`id` = `pdc`.`categoria_id`')
                ->innerJoin('`promocion_complemento` p_c','`pdc`.`promocion_complemento_id` = `p_c`.`id`')
                ->andWhere(['pdc.promocion_detalle_id' =>  $p_detalle["id"] ]);
            array_push($PromocionComplemento, $query->all());
        }

        $PromocionAnexo = [];

        foreach ($PromocionDetalle as $key => $p_detalle) {
            $query = (new Query())
                ->select([
                    "pda.id",
                    "p_d.id  as producto_detalle_id",
                    /*"pda.categoria_id",
                    "pda.is_categoria",*/
                    //"esys_lista_desplegable.singular as categoria",
                    "pda.lb_free",
                ])
                ->from('`promocion_detalle` p_d')
                ->innerJoin('promocion_detalle_anexo pda','`pda`.`promocion_detalle_id` = `p_d`.`id`')
                //->leftJoin('esys_lista_desplegable','`esys_lista_desplegable`.`id` = `pda`.`categoria_id`')
                ->andWhere(['pda.promocion_detalle_id' =>  $p_detalle["id"] ]);
                array_push($PromocionAnexo, $query->all());
        }

        $PromocionAnexoCategoria = [];

        foreach ($PromocionAnexo as $key => $pda_anexos) {
            foreach ($pda_anexos as $key3 => $anexo) {
                $query = (new Query())
                    ->select([
                        "pda_categoria.id",
                        "pda.id  as anexo_id",
                        "pda_categoria.categoria_id",
                        "pda_categoria.is_categoria",
                        "esys_lista_desplegable.singular as categoria",
                    ])
                    ->from('`promocion_anexo_categoria` pda_categoria')
                    ->innerJoin('promocion_detalle_anexo pda','`pda`.`id` = `pda_categoria`.`promocion_detalle_anexo_id`')
                    ->leftJoin('esys_lista_desplegable','`esys_lista_desplegable`.`id` = `pda_categoria`.`categoria_id`')
                    ->andWhere(['pda_categoria.promocion_detalle_anexo_id' =>  $anexo["id"] ]);
                    array_push($PromocionAnexoCategoria, $query->all());
            }
        }

        $PromocionAnexoFilter    = [];
        $PromocionDetalleFilter  = [];
        if (isset($arr['paquetePeso']) && $arr['paquetePeso'] ) {
            foreach ($PromocionAnexo as $key2 => $anexos) {
                if (self::valida_promocion_anexo($arr['paquetePeso'],$anexos, $PromocionAnexoCategoria /*,$val_p_detalle,$filters['peso_requerido']*/)) {

                    foreach ($anexos as $key => $anexo) {
                        $anexos[$key]["categoria"] = [];
                        foreach ($PromocionAnexoCategoria as $key2 => $categoria) {
                            $is_categoria = 0;
                            foreach ($categoria as $key3 => $item) {
                                if ($item["anexo_id"] == $anexo["id"] ) {
                                    $is_categoria = $is_categoria != 1 ? 10 : 1;
                                }else{
                                    $is_categoria = 1;
                                }
                            }
                            if($is_categoria == 10)
                                array_push($anexos[$key]["categoria"], $categoria);
                        }
                    }
                    array_push($PromocionAnexoFilter, $anexos);
                }
            }
        }
        $is_promocion_anexos  =  $PromocionAnexoFilter ? true : false; // Validamos si encuentra anexos
        $is_item_sin_anexos   =  3;   // Numero de items a mostrar si no tiene anexos
        $is_count_anexo       =  0;  // Numero de anexos que tiene por item
        $is_one_anexo         =  2;  // Numero de anexos que mostrara con un anexo (1)
        $sin_anexo = 0;
        $one_anexo = false;
        $countPromocionDetalle = 1;


        foreach ($PromocionDetalle as $key => $p_detalle) {
            if ($is_promocion_anexos) {
                $lb_free =  $filters['peso_requerido'] - $p_detalle['lb_requerida'];
                $is_add  =  0;
                $is_count_anexo =  0;


                foreach ($PromocionAnexoFilter as $key => $anexos) {
                    foreach ($anexos as $key3 => $anexo) {
                        if ($p_detalle["id"] == $anexo['producto_detalle_id']) {
                            $is_count_anexo =  $is_count_anexo + 1;
                            if ($lb_free >= 0  ) {
                                $is_add   = $is_add != 1 ? 10 : 1;
                                $lb_free  = $lb_free - $anexo['lb_free'];
                            }else{
                                $is_add  =  1;
                            }
                        }
                    }
                }



                if ($is_add == 0){
                    $sin_anexo =  self::valida_is_anexo($p_detalle,$PromocionAnexo) ? 1 : $one_anexo ? 1 : 10 ;
                    $one_anexo =  $sin_anexo == 10 ?  true  :  $one_anexo ==  true  ? true : false;
                }

                if ($is_add == 0 && $sin_anexo == 10) {
                    array_push($PromocionDetalleFilter, $p_detalle);
                }elseif ($is_add == 10 ) {
                    if ($is_count_anexo == 1 &&  $is_one_anexo > 0) {
                        array_push($PromocionDetalleFilter, $p_detalle);
                        $is_one_anexo = $is_one_anexo - 1;

                    }elseif($is_count_anexo != 1){
                        array_push($PromocionDetalleFilter, $p_detalle);
                    }
                }

            }else{
                /*
                    Ingresara siempre que no tenga anexos la prmoción y solo mostrara dos items de la promoción
                */

                if ($is_item_sin_anexos > 0 )
                    array_push($PromocionDetalleFilter, $p_detalle);
                $is_item_sin_anexos = $is_item_sin_anexos - 1;
            }



        }

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'PromocionComplemento'  => $PromocionComplemento,
            'PromocionDetalle' => $PromocionDetalleFilter,
            'PromocionAnexo' => $PromocionAnexoFilter,
        ];
    }

    public static function valida_is_anexo($p_detalle,$listAnexos)
    {
        foreach ($listAnexos as $key => $anexos) {
            foreach ($anexos as $key3 => $anexo) {
                if ($p_detalle["id"] == $anexo['producto_detalle_id']) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function valida_promocion_anexo($pesoCategoria,$anexos,$anexo_categoria/*,$val_minimo,$val_total*/){

        $is_addAnexo = 0;

        foreach ($anexos as $key3 => $anexo) {

            $categoria_array = [];

            /****************************************************
                Obtiene sus categorias que le coresponde al anexo
            *******************************************************/
            foreach ($anexo_categoria as $key => $categoria) {
                $is_categoria = 0;
                foreach ($categoria as $key => $item) {
                    if ($item["anexo_id"] == $anexo["id"] ) {
                        $is_categoria = $is_categoria != 1 ? 10 : 1;
                    }else{
                        $is_categoria = 1;
                    }
                }
                if($is_categoria == 10)
                    array_push($categoria_array, $categoria);
            }

            if (count($categoria_array) > 0 ) {


                /******************************************
                    Recorrido de las categorias del anexo
                *******************************************/


                foreach ($categoria_array as $key => $categoria) {
                    $add_categoria = 1;
                    foreach ($categoria as $key => $item) {
                        switch ($item["is_categoria"]) {
                            case PromocionAnexoCategoria::IS_CATEGORIA_ON:
                                if (self::validaPesoAnexo($pesoCategoria, $item['categoria_id'])) //$item['categoria_id'] == $pesoCategoria['categoria_id'])
                                    $add_categoria =  10;

                            break;
                            case PromocionAnexoCategoria::IS_CATEGORIA_OFF:
                                $add_categoria = 10;
                            break;
                        }
                    }
                    $is_addAnexo = $is_addAnexo != 1 ?  $add_categoria : 1;
                }

            }else{
                return false;
            }
        }
        return  $is_addAnexo == 10 ? true : false ;
    }

    /*public function validaLibrasPagadasMin($listPromocionDetalle){
        $val_minimo = 0;
        foreach ($listPromocionDetalle as $key => $p_detalle) {
            $val_minimo = $val_minimo == 0 ? $p_detalle["lb_requerida"] : $val_minimo;
            if ($p_detalle["lb_requerida"] <=  $val_minimo ) {
                $val_minimo = $p_detalle["lb_requerida"];
            }
        }
        return $val_minimo;
    }*/

    public static function validaPesoAnexo($listPesoCategoria, $categoria_id){
        foreach ($listPesoCategoria as $key => $item) {
            if ($item["categoria_id"] == $categoria_id)
                return true;
        }
        return false;
    }

    public static function getPromocionDetalleComplementoAjax($arr){
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        //parse_str($arr['filters'], $filters);

        $promocion_d = (new Query())
                ->select([
                    "p_d.id",
                    "p_d.lb_requerida",
                    "p_d.costo_libra_code",
                    "p_d.costo_libra_sin_code",
                 ])
                ->from('`promocion_detalle` p_d')
                ->orderBy("p_d.lb_requerida desc")
                ->andWhere(['p_d.promocion_id' =>  $arr['promocion_id']]);

        $PromocionDetalle = $promocion_d->all();

        $PromocionComplemento = [];

        foreach ($PromocionDetalle as $key => $promocion_detalle) {

            $query = (new Query())
                ->select([
                    "p_d.id as promocion_detalle_id",
                    "p_c.id",
                    "pdc.tipo_complemento",
                    "`pdc`.`producto_id`",
                    "pdc.categoria_id",
                    "pdc.is_categoria",
                    "pdc.num_producto",

                    "esys_lista_desplegable.singular as categoria",
                    "producto.nombre as producto",
                    "pdc.is_producto",
                    "pdc.is_valor_paquete",
                    "pdc.valor_paquete_aprox",
                    "p_c.lb_free",
                    "p_c.cobro_impuesto",
                    "p_c.is_lb_free",
                    "p_c.is_envio_free",
                    "p_c.is_lbexcedente",
                    "p_c.lbexcedente",
                    "p_c.costo_libraexcedente"
                ])
                ->from('`promocion_detalle` p_d')
                ->innerJoin('promocion_detalle_complemento pdc','`pdc`.`promocion_detalle_id` = `p_d`.`id`')
                ->leftJoin('producto','`producto`.`id` = `pdc`.`producto_id`')
                ->leftJoin('esys_lista_desplegable','`esys_lista_desplegable`.`id` = `pdc`.`categoria_id`')
                ->innerJoin('`promocion_complemento` p_c','`pdc`.`promocion_complemento_id` = `p_c`.`id`')
                ->andWhere(['pdc.promocion_detalle_id' =>  $promocion_detalle["id"] ]);

            array_push($PromocionComplemento, $query->all());
        }

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return [
            'PromocionComplemento'  => $PromocionComplemento,
            'PromocionDetalle' => $PromocionDetalle,
        ];
    }

    public static function getCodePromocionAjax($arr){
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
                    ->select([
                        'cliente_codigo_promocion.id  as code_id',
                        'cliente_codigo_promocion.promocion_id',
                        'cliente_id',
                        'clave',
                        'tipo',
                        'requiered_libras',
                        'descuento',
                        'fecha_rango_ini',
                        'fecha_rango_fin',
                        'costo_libra_code as costo_libra_con_code',
                        'status'
                    ])
                    ->from('cliente_codigo_promocion')
                    ->leftJoin('promocion_detalle','promocion_detalle.promocion_id = cliente_codigo_promocion.promocion_id')
                    ->andWhere([ 'cliente_id'           => $arr['cliente_emisor'] ])
                    ->andWhere([ 'clave'                => $arr['clave'] ]);

        if (isset($arr['promocion_detalle_id']) &&  $arr['promocion_detalle_id'])
            $query->andWhere(['promocion_detalle.id'  => $arr['promocion_detalle_id']]);

        if ($query->one()){

            $query->andWhere(['cliente_codigo_promocion.promocion_id' => $arr['promocion_id']]);

            if($pro = $query->one()){
                switch ($pro['status'] ) {
                    case ClienteCodigoPromocion::STATUS_ACTIVE:
                        return [
                                'code'      => 202,
                                'message'   => "Se ingreso correctamente el Codigo Promocional",
                                'data'      => $pro,
                            ];
                         break;

                    case ClienteCodigoPromocion::STATUS_USADO:
                        return [
                                'code' => 11,
                                'message' => "El codigo promocional ya ha sido utlizado en un envio",
                            ];
                        break;

                    case ClienteCodigoPromocion::STATUS_NO_AUTORIZADO:
                        return [
                                'code' => 12,
                                'message' => "No se autorizado la promoción, contacte al administrador.",
                            ];
                        break;

                    case ClienteCodigoPromocion::STATUS_INACTIVE:
                        return [
                                'code' => 13,
                                'message' => "Se cancelo ó se elimino el codigo promocional.",
                            ];
                        break;
                 }
            }else{
                return [
                        'code' => 14,
                        'message' => "El codígo de promoción NO pertence a la vigente, solicite un nuevo codígo con un agente.",
                ];
            }

        }else{

            //echo ($query->createCommand()->rawSql) . '<br/><br/>';

            return [
                'code' => 10,
                'message' => "Error en el codígo ó no existe para este cliente",
            ];
        }

        //$arr['cliente_emisor'];
    }


}
