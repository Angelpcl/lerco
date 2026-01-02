<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = "Ticket #" . $model->clave;
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>

<div class="ticket-view">
    <h1><strong>Tickets</strong></h1>

    <h3 style="color: orange;">¡Hola <?= Yii::$app->user->identity->username ?>! Aquí puedes ver tu ticket y su estado actual.</h3>

    <!-- DATOS GENERALES -->
    <div class="row">
        <div class="col-md-8">
            <table class="table table-bordered">
                <tr style="background-color: #061635; color: white;">
                    <th>ID</th>
                    <td style="background-color: #e5eaef; color: black;"><?= Html::encode($model->clave) ?></td>
                </tr>
                <tr style="background-color: #061635; color: white;">
                    <th>Fecha de asignación</th>
                    <td style="background-color: #e5eaef; color: black; "><?= Yii::$app->formatter->asDate($model->created_at, 'php:d/m/Y') ?></td>
                </tr>
                <tr style="background-color: #061635; color: white;">
                    <th>Empresa</th>
                    <td style="background-color: #e5eaef; color: black;"><?= $model->envio && $model->envio->clienteEmisor ? $model->envio->clienteEmisor->nombreCompleto : 'N/A' ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-4">
            <div style="background-color: #061635; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                <h4>Estatus</h4>
            </div>
            <div style="background-color: #eee; padding: 15px; text-align: center;">
                <strong style="color: brown; font-size: 18px;">
                    <?= \app\models\ticket\Ticket::$statusList[$model->status] ?>
                </strong>
            </div>
        </div>
    </div>

    <!-- CHAT -->
    <div class="row mt-4">
    <!-- CHAT -->
    <div class="col-md-8">
        <div style="background-color: #061635; color: white; padding: 10px; border-radius: 5px;">
            <strong>Notas de Seguimiento</strong>
        </div>
        <div style="background-color: #eee; padding: 10px; min-height: 150px; max-height: 300px; overflow-y: auto;">
            <?php foreach ($model->seguimientos as $nota): ?>
                <div style="margin-bottom: 10px; background: #ddd; padding: 10px; border-radius: 5px;">
                    <strong><?= Html::encode($nota->usuario->username ?? 'Usuario') ?></strong><br>
                    <?= Html::encode($nota->mensaje) ?><br>
                    <small><?= Yii::$app->formatter->asDatetime(strtotime($nota->created_at) - 3600) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulario de chat -->
        <?php $form = ActiveForm::begin([
            'action' => ['enviar-nota', 'id' => $model->id],
            'method' => 'post',
        ]); ?>
        <div class="input-group mt-2">
            <?= Html::hiddenInput('ticket_id', $model->id) ?>
            <?= Html::textInput('mensaje', '', ['class' => 'form-control', 'placeholder' => 'Escribe un mensaje']) ?>
            <span class="input-group-btn">
                <?= Html::submitButton('Enviar', ['class' => 'btn btn-primary']) ?>
            </span>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <!-- EVIDENCIAS -->
    <div class="col-md-4">
        <div style="background-color: #061635; color: white; padding: 10px; border-radius: 5px;">
            <strong>Evidencia</strong>
        </div>
        <div style="background-color: #eee; padding: 10px;">
            <?php if ($model->ticket_evidencia): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Descargar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (json_decode($model->ticket_evidencia, true) as $item): ?>
                            <tr>
                                <td><?= reset($item) ?></td>
                                <td><?= pathinfo(reset($item), PATHINFO_EXTENSION) ?></td>
                                <td> <?= Html::a(
                                    'Ver',
                                    Yii::getAlias('@web') . '/ticket/' . reset($item),
                                    ['class' => 'text-primary', 'target' => '_blank']
                                ) ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay archivos.</p>
            <?php endif; ?>

        </div>

        <!-- Formulario para subir archivo -->
        <?php $form = ActiveForm::begin([
            'action' => ['upload-evidencia', 'id' => $model->id],
            'options' => ['enctype' => 'multipart/form-data']
        ]); ?>
        
        
        <?php ActiveForm::end(); ?>
    </div>
</div>
    </br>    
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.querySelector('input[type="file"][name="ticket_evidencia_array[]"]');
    var preview = document.getElementById('preview-evidencias');
    if (input) {
        input.addEventListener('change', function () {
            preview.innerHTML = '';
            if (input.files.length > 0) {
                var list = document.createElement('ul');
                for (var i = 0; i < input.files.length; i++) {
                    var item = document.createElement('li');
                    item.textContent = input.files[i].name;
                    list.appendChild(item);
                }
                preview.appendChild(list);
            }
        });
    }
});
</script>

