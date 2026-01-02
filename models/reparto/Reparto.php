<?php
namespace app\models\reparto;

use Yii;
use yii\db\Query;
use app\models\esys\EsysListaDesplegable;
use app\models\user\User;
use app\models\ruta\Ruta;
use app\models\viaje\Viaje;



/**
 * This is the model class for table "reparto".
 *
 * @property int $id id
 * @property int $chofer_id Chofer ID
 * @property int $num_unidad_id Unidad ID
 * @property int $status
 * @property string $nota Nota / Comentarios
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property EsysListaDesplegable $chofer
 * @property User $createdBy
 * @property EsysListaDesplegable $numUnidad
 * @property User $updatedBy
 * @property RepartoFila[] $repartoFilas
 * @property RepartoRecoleccion[] $repartoRecoleccions
 */
class Reparto extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE     = 10;
    const STATUS_CERRADO    = 20;
    const STATUS_TERMINADO  = 30;
    const STATUS_CANCEL     = 2;


    public static $statusList = [
        self::STATUS_ACTIVE     => 'Habilitado',
        self::STATUS_CERRADO    => 'Cerrado / Enviado',
        self::STATUS_TERMINADO  => 'Terminado / Concluido',
        self::STATUS_CANCEL     => 'Cancelado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chofer_id', 'num_unidad_id', 'status' ], 'required'],
            [['chofer_id', 'num_unidad_id', 'status', 'created_at', 'created_by', 'viaje_id','updated_at', 'updated_by','ruta_id'], 'integer'],
            [['ruta_nombre'], 'string', 'max' => 150],
            [['nota'], 'string'],
            [['chofer_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['chofer_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['num_unidad_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['num_unidad_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chofer_id' => 'Chofer',
            'chofer.singular' => 'Chofer',
            'numUnidad.singular' => 'N° de Unidad',
            'num_unidad_id' => 'N° de Unidad',
            'ruta_id' => 'Ruta',
            'ruta_nombre' => 'Cuidad / Referencia',
            'status' => 'Estatus',
            'nota' => 'Nota',
            'viaje_id' => 'Viaje',
            'ruta.nombre' => 'Ruta',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChofer()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'chofer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumUnidad()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'num_unidad_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuta()
    {
        return $this->hasOne(Ruta::className(), ['id' => 'ruta_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViaje()
    {
        return $this->hasOne(Viaje::className(), ['id' => 'viaje_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepartoDetalles()
    {
        return $this->hasMany(RepartoDetalle::className(), ['reparto_id' => 'id']);
    }


    public function getSucursalReparto()
    {
        //return (new Query())->select(["envio_detalle.sucursal_receptor_id"])->from('reparto')->innerJoin("reparto_detalle","reparto.id = reparto_detalle.reparto_id")->innerJoin("envio_detalle","reparto_detalle.paquete_id = envio_detalle.id")->andWhere(["reparto.id" => $this->id])->groupBy("envio_detalle.sucursal_receptor_id")->all();

        return (new Query())->select(["envio_detalle.sucursal_receptor_id"])->from('reparto')->innerJoin("reparto_detalle","reparto.id = reparto_detalle.reparto_id")->innerJoin("envio_detalle","reparto_detalle.paquete_id = envio_detalle.id")->andWhere(["reparto.id" => $this->id])->groupBy("envio_detalle.sucursal_receptor_id")->all();
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepartoRecoleccions()
    {
        return $this->hasMany(RepartoRecoleccion::className(), ['reparto_id' => 'id']);
    }

    public static function getPaqueteRepartos($repartos_array)
    {
        return (new Query())
                ->select([
                    "reparto_detalle.reparto_id AS reparto_id",
                    "envio_detalle.envio_id AS envio_id",
                    "reparto_detalle.tracked AS tracked",
                    "concat_ws(' ','[',`viaje`.`placas`,']',`viaje`.`nombre_chofer`) AS `viaje`",
                    "conductor.singular AS conductor",
                    "unidad.singular AS unidad",
                    "estado.singular as estado",
                    "municipio.singular as municipio",
                    "esys_direccion.direccion",
                    "reparto.created_at AS fecha_unix",
                ])
                ->from("reparto_detalle")
                ->innerJoin("envio_detalle","reparto_detalle.paquete_id = envio_detalle.id")
                ->leftJoin('esys_direccion','envio_detalle.id = esys_direccion.cuenta_id and esys_direccion.cuenta = 5 and  esys_direccion.tipo =1')
                ->leftJoin('esys_lista_desplegable estado','esys_direccion.estado_id = estado.id_2 and estado.label = "crm_estado"')
                ->leftJoin('esys_lista_desplegable municipio','esys_direccion.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
                ->innerJoin("reparto","reparto_detalle.reparto_id = reparto.id")
                ->leftJoin("esys_lista_desplegable conductor","reparto.chofer_id = conductor.id")
                ->leftJoin("esys_lista_desplegable unidad","reparto.num_unidad_id = unidad.id")
                ->leftJoin("viaje","reparto.viaje_id = viaje.id")
                ->andWhere([ "IN","reparto_detalle.reparto_id",$repartos_array ])
                ->all();
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }else{

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }

            return true;

        } else
            return false;
    }
}
