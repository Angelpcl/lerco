<?php

namespace app\models\cliente;

use Yii;

/**
 * This is the model class for table "cliente_acceso".
 *
 * @property int $id Id
 * @property int $user_id Usuario cliente
 * @property string $user Usuario
 * @property string $apikey APIKEY
 * @property string $wrong_password
 * @property string $ip IP del cliente
 * @property int $metodo Método de accesos
 * @property int $access Acceso concedido
 * @property int $created_at Creado
 * @property int $logout_at Cerro sesión
 *
 * @property ClienteAccesoLog[] $clienteAccesoLogs
 */
class ClienteAcceso extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente_acceso';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'metodo', 'access', 'created_at', 'logout_at'], 'integer'],
            [['access', 'created_at'], 'required'],
            [['user', 'apikey'], 'string', 'max' => 50],
            [['wrong_password'], 'string', 'max' => 100],
            [['ip'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user' => 'User',
            'apikey' => 'Apikey',
            'wrong_password' => 'Wrong Password',
            'ip' => 'Ip',
            'metodo' => 'Metodo',
            'access' => 'Access',
            'created_at' => 'Created At',
            'logout_at' => 'Logout At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClienteAccesoLogs()
    {
        return $this->hasMany(ClienteAccesoLog::className(), ['acceso_id' => 'id']);
    }
}
