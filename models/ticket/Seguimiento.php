<?php

namespace app\models\ticket;

use Yii;
use yii\db\ActiveRecord;
use app\models\user\User;            
use app\models\ticket\Ticket;

/**
 * Modelo para los seguimientos (chat).
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $mensaje
 * @property string $created_at
 *
 * @property User $usuario
 * @property Ticket $ticket
 */
class Seguimiento extends ActiveRecord
{
    public static function tableName()
    {
        return 'seguimiento';
    }

    public function rules()
    {
        return [
            [['ticket_id', 'user_id', 'mensaje'], 'required'],
            [['ticket_id', 'user_id'], 'integer'],
            [['mensaje'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    public function getUsuario()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id']);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);

    }

    
}

