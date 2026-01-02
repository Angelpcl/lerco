<?php

namespace app\models\ticket;

use Yii;

/**
 * This is the model class for table "contactos_cliente".
 *
 * @property int $id
 * @property string $nombre
 * @property string $apellidos
 * @property int $telefono
 * @property string $email
 * @property int $user_id
 * @property int $fecha_register
 */
class ContactosCliente extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contactos_cliente';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'apellidos', 'telefono', 'email', 'user_id', 'fecha_register'], 'required'],
            [['nombre', 'apellidos'], 'string'],
            [['telefono', 'user_id', 'fecha_register'], 'integer'],
            [['email'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'telefono' => 'Telefono',
            'email' => 'Email',
            'user_id' => 'User ID',
            'fecha_register' => 'Fecha Register',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
