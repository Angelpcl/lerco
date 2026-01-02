<?php
namespace app\models\cliente;

use Yii;

/**
 * This is the model class for table "cliente_paquete_csd".
 *
 * @property int $id Id
 * @property int $paquete_id Paquete
 * @property int $csd_id Certificado Sello Digital
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $updated_at Modificado
 *
 * @property ClienteCsd $csd
 * @property ClientePaquete $paquete
 */
class ClientePaqueteCsd extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente_paquete_csd';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['paquete_id', 'csd_id', 'status', 'created_at'], 'required'],
            [['paquete_id', 'csd_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['csd_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClienteCsd::className(), 'targetAttribute' => ['csd_id' => 'id']],
            [['paquete_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientePaquete::className(), 'targetAttribute' => ['paquete_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'paquete_id' => 'Paquete',
            'csd_id' => 'Certificado Sello Digital',
            'status' => 'Estatus',
            'created_at' => 'Creado',
            'updated_at' => 'Modificado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCsd()
    {
        return $this->hasOne(ClienteCsd::className(), ['id' => 'csd_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaquete()
    {
        return $this->hasOne(ClientePaquete::className(), ['id' => 'paquete_id']);
    }
}
