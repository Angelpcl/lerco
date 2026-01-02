<?php

namespace app\models\sucursal;

use Yii;

/**
 * This is the model class for table "promociones".
 *
 * @property int $id
 * @property string $fecha_inicio fecha de inicio
 * @property string $fecha_fin fecha de termino
 * @property int $status status
 * @property float $costo_libra_peso_suc costo por libra de peso para sucursal
 * @property float $costo_libra_peso_cli costo por libra de peso para cliente
 * @property float $costo_libra_caja_cli costo por libra de peso para cliente
 * @property float $costo_libra_caja_suc costo por libra de peso para sucursal
 * @property float $costo_caja_limite_cli costo por caja limite para cliente
 * @property float $costo_caja_limite_suc costo por caja limite para sucursal
 * @property int $sucursal_id sucursal_id
 * @property int|null $created_at created_at
 * @property int|null $created_by created_by
 * @property int|null $updated_at updated_at
 * @property int|null $updated_by updated_by
 *
 * @property Sucursal $sucursal
 */
class Promociones extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 20;

    public static $statusList = [
        self::STATUS_ACTIVE => 'Activo',
        self::STATUS_INACTIVE => 'Inactivo',
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promociones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_inicio', 'fecha_fin'], 'safe'],
            [['status', 'costo_libra_peso_suc', 'costo_libra_peso_cli', 'costo_libra_caja_cli', 'costo_libra_caja_suc', 'costo_caja_limite_cli', 'costo_caja_limite_suc', 'sucursal_id'], 'required'],
            [['status', 'sucursal_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['costo_libra_peso_suc', 'costo_libra_peso_cli', 'costo_libra_caja_cli', 'costo_libra_caja_suc', 'costo_caja_limite_cli', 'costo_caja_limite_suc'], 'number'],
            [['sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_id' => 'id']],

            // Validaciones condicionales para fechas cuando el status es 10
            //['fecha_inicio', 'validateDateStart', 'when' => function ($model) {
            //    return $model->status == self::STATUS_ACTIVE;
            //}],
            //['fecha_fin', 'validateDateEnd', 'when' => function ($model) {
            //    return $model->status == self::STATUS_ACTIVE;
            //}],
            //['fecha_fin', 'validateDateRange', 'when' => function ($model) {
            //    return $model->status == self::STATUS_ACTIVE;
            //}],
        ];
    }

    public function validateDateStart($attribute, $params)
    {
        if (strtotime($this->$attribute) < time()) {
            $this->addError($attribute, 'La fecha de inicio debe ser mayor a la fecha actual.');
        }
    }

    public function validateDateEnd($attribute, $params)
    {
        if (strtotime($this->$attribute) < time()) {
            $this->addError($attribute, 'La fecha de fin debe ser mayor a la fecha actual.');
        }
    }

    public function validateDateRange($attribute, $params)
    {
        if (strtotime($this->fecha_fin) < strtotime($this->fecha_inicio)) {
            $this->addError($attribute, 'La fecha de fin debe ser mayor a la fecha de inicio.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha_inicio' => 'Fecha Inicio',
            'fecha_fin' => 'Fecha Fin',
            'status' => 'Status',
            'costo_libra_peso_suc' => 'Po libra para productos (sucursal)',
            'costo_libra_peso_cli' => 'Por libra para productos (cliente)',
            'costo_libra_caja_cli' => 'Por libra para caja (cliente)',
            'costo_libra_caja_suc' => 'Por libra para caja (sucursal)',
            'costo_caja_limite_cli' => 'Caja sin límite (cliente)',
            'costo_caja_limite_suc' => 'Caja sin límite (sucursal)',
            'sucursal_id' => 'Sucursal',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Sucursal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
    }



    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            } else {
                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            }

            return true;
        } else
            return false;
    }
}
