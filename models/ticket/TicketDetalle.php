<?php
namespace app\models\ticket;

use Yii;
use app\models\envio\EnvioDetalle;


/**
 * This is the model class for table "ticket_detalle".
 *
 * @property int $ticket_id Ticket ID
 * @property int $paquete_id Paquete ID
 *
 * @property EnvioDetalle $paquete
 * @property Ticket $ticket
 */
class TicketDetalle extends \yii\db\ActiveRecord
{
    public $ticket_detalle_array;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ticket_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ticket_id', 'paquete_id'], 'integer'],
            [['ticket_detalle_array'], 'safe'],
            [['tracked'], 'string', 'max' => 32],
            [['ticket_id', 'paquete_id'], 'unique', 'targetAttribute' => ['ticket_id', 'paquete_id']],
            [['paquete_id'], 'exist', 'skipOnError' => true, 'targetClass' => EnvioDetalle::className(), 'targetAttribute' => ['paquete_id' => 'id']],
            [['ticket_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ticket::className(), 'targetAttribute' => ['ticket_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ticket_id' => 'Ticket ID',
            'paquete_id' => 'Paquete ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaquete()
    {
        return $this->hasOne(EnvioDetalle::className(), ['id' => 'paquete_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTicket()
    {
        return $this->hasOne(Ticket::className(), ['id' => 'ticket_id']);
    }

    public function trickend_detalle_array_save($ticket_id)
    {
        $ticket_detalle_array = json_decode($this->ticket_detalle_array);
        if ($ticket_detalle_array) {
            foreach ($ticket_detalle_array as $key => $env_detalle) {

                $TicketDetalle = new  TicketDetalle();
                $TicketDetalle->ticket_id = $ticket_id;
                $TicketDetalle->paquete_id  = $env_detalle->paquete_id;
                $TicketDetalle->tracked     = $env_detalle->tracked;
                $TicketDetalle->save();
            }
        }
        return true;
    }
}
