<?php

namespace app\models\pais;

use Yii;

/**
 * This is the model class for table "zonas_rojas".
 *
 * @property int $id
 * @property string $code nombre
 * @property string|null $estado estado
 * @property int $pais_id pais_id
 * @property int|null $created_at created_at
 * @property int|null $created_by created_by
 * @property int|null $updated_at updated_at
 * @property int|null $updated_by updated_by
 *
 * @property PaisesLatam $pais
 */
class ZonasRojas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'zonas_rojas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'pais_id'], 'required'],
            [['pais_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['estado'], 'string', 'max' => 200],
            [['pais_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaisesLatam::className(), 'targetAttribute' => ['pais_id' => 'id']],
            [['code', 'pais_id'], 'unique', 'targetAttribute' => ['code', 'pais_id'], 'message' => 'El código ya existe para este país.']
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Código postal',
            'estado' => 'Nombre del Estado',
            'pais_id' => 'País',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Pais]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPais()
    {
        return $this->hasOne(PaisesLatam::className(), ['id' => 'pais_id']);
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
