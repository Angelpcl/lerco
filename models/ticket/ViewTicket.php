<?php

namespace app\models\ticket;

use Yii;
use yii\db\Query;
use yii\web\Response;
/**
 * This is the model class for table "view_ticket".
 *
 * @property int $id ID
 * @property string $clave Clave / Codigo de seguimiento
 * @property int $tipo_id Tipo
 * @property string $tipo Singular
 * @property int $envio_id Envio ID
 * @property string $descripcion Descripcion
 * @property int $is_libra_free Is Libras Gratis
 * @property int $is_descuento_envio Is Descuento Envio
 * @property int $libra_free Libras Gratis
 * @property double $descuento_envio Descuento Envio
 * @property int $is_used Utilizado / Usado
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property string $created_by_user
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewTicket extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_ticket';
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clave' => 'Clave',
            'tipo_id' => 'Tipo ID',
            'tipo' => 'Tipo',
            'envio_id' => 'Envio ID',
            'descripcion' => 'Descripcion',
            'is_libra_free' => 'Is Libra Free',
            'is_descuento_envio' => 'Is Descuento Envio',
            'libra_free' => 'Libra Free',
            'descuento_envio' => 'Descuento Envio',
            'is_used' => 'Is Used',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by_user' => 'Created By User',
            'created_by' => 'Created By',
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
        'clave',
        'folio',
        'tipo_id',
        'tipo',
        'envio_id',
        'descripcion',
        'cliente_razon_social_name',
        'is_libra_free',
        'is_descuento_envio',
        'libra_free',
        'descuento_envio',
        'is_used',
        'is_rembolso',
        'num_rembolso',
        'fecha_rembolso',
        'total_reembolsado',
        'num_rembolso_realizados',
        'status',
        'producto',
        'clasificacion',
        'proyecto',
        'created_at',
        'created_by_user',
        'created_by',
        'updated_at',
        'updated_by',
        'updated_by_user',
            ])
            ->from(self::tableName());
        
        // Si existe clienteRazonSocial, filtrar por cliente_id
        if (isset(Yii::$app->user->identity->clienteRazonSocial)) {
            $query->where(['cliente_id' => Yii::$app->user->identity->clienteRazonSocial->id]);
        }
        
        // Continuar con el orden, paginación, etc.
        $query->orderBy($orderBy)
              ->offset($offset)
              ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);

            if (isset($filters['tipo_id']) && $filters['tipo_id'])
                $query->andWhere(['tipo_id' =>  $filters['tipo_id']]);

            if (isset($filters['clave']) && $filters['clave'])
                $query->andWhere(['clave' =>  $filters['clave']]);


            if (isset($filters['reembolso_status']) && $filters['reembolso_status']){
                if (Ticket::REEMBOLSO_STATUS_PROGRESO == $filters['reembolso_status']) {
                    $query->where('num_rembolso_realizados < num_rembolso_faltantes');
                }
                elseif (Ticket::REEMBOLSO_STATUS_TERMINADO == $filters['reembolso_status'])
                    $query->andWhere(['num_rembolso_faltantes' =>  0 ]);
                else{
                    $query->orWhere(['or',
                        ['<', 'num_rembolso_realizados', 'num_rembolso_faltantes'],
                        ['>=', 'num_rembolso_faltantes', 0],
                    ]);
                }
            }

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'clave', $search],
                    ['like', 'folio', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
            $row = [];
            foreach ($query->all() as $key => $item) {
                $fecha_rembolso     = $item['fecha_rembolso'] ? json_decode($item['fecha_rembolso']) : [];
                $totalReembolsar    = 0;
                foreach ($fecha_rembolso as $key => $reem) {
                    $totalReembolsar = $totalReembolsar + $reem->monto;
                }
                $item["total_a_reembolsar"] = $totalReembolsar;
                array_push($row, $item);
            }

        return [
            'rows'  => $row,
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
