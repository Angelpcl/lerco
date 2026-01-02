<?php

namespace app\models\producto;

use Yii;

/**
 * This is the model class for table "caja_sin_limite".
 *
 * @property int $id
 * @property int|null $largo Largo
 * @property int|null $ancho Ancho
 * @property int|null $alto Alto
 * @property int|null $created_at created_at
 * @property int|null $created_by created_by
 * @property int|null $updated_at updated_at
 * @property int|null $updated_by updated_by
 * @property float|null $costo_suc costo_suc
 * @property float|null $costo_cli costo_cli
 */
class CajaSinLimite extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'caja_sin_limite';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['largo', 'ancho', 'alto','costo_cli','costo_suc'], 'required'],
            [['largo', 'ancho', 'alto', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['costo_suc', 'costo_cli'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'largo' => 'Largo',
            'ancho' => 'Ancho',
            'alto' => 'Alto',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'costo_suc' => 'Costo total (Sucursal)',
            'costo_cli' => 'Costo total (Publico)',
        ];
    }
}
