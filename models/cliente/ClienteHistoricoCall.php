<?php

namespace app\models\cliente;

use Yii;
use app\models\user\User;
use app\models\cliente\Cliente;
use app\models\esys\EsysListaDesplegable;

/**
 * This is the model class for table "cliente_historico_call".
 *
 * @property int $id ID
 * @property int $cliente_id Cliente ID
 * @property int $tipo_respuesta_id Tipo de respuesta
 * @property string $comentario Comentario
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property Cliente $cliente
 * @property User $createdBy
 * @property EsysListaDesplegable $tipoRespuesta
 */
class ClienteHistoricoCall extends \yii\db\ActiveRecord
{

    const TIPO_CLIENTE      = 1;
    const TIPO_SEGUIMIENTO  = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente_historico_call';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'tipo_respuesta_id'], 'required'],
            [['cliente_id', 'tipo_respuesta_id', 'created_at', 'created_by','envio_id'], 'integer'],
            [['comentario','telefono'], 'string'],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['tipo_respuesta_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['tipo_respuesta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cliente_id' => 'Cliente ID',
            'envio_id' => 'Envio ID',
            'tipo_respuesta_id' => 'Tipo de respuesta',
            'comentario' => 'Comentarios / Observaciones',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCliente()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_id']);
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
    public function getTipoRespuesta()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'tipo_respuesta_id']);
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

            }

            return true;

        } else
            return false;
    }
}
