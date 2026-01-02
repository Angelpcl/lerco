<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootboxAsset;
use yii\widgets\ActiveForm;
use app\models\envio\Envio;
use app\models\movimiento\MovimientoPaquete;
use app\models\caja\CajaMex;
use app\models\envio\EnvioDetalle;
use app\models\viaje\Viaje;

BootboxAsset::register($this);
/* @var $this yii\web\View */

$this->title =  'ESCANEO MASIVO';
$this->params['breadcrumbs'][] = 'Escaneo por paquete ';
?>

<div class="row">
    <div class="col-md-6  offset-sm-3">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Ingresa FOLIO</h5>
            </div>
            <div class="ibox-content">
                <?php $form = ActiveForm::begin(['id' => 'form-escaneo', 'action' => ['change'] ,'method' => 'POST', 'options' => ['enctype' => 'multipart/form-data'] ]) ?>
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3">
                            <h3>Movimientos Lax y Tierra </h3>
                            <?= Html::dropDownList('tipo_movimiento',  null, MovimientoPaquete::$tipoLaxTierList, [ 'class' => 'form-control tipo_movimiento_lax_tierra','prompt' => 'Selecciona tipo de movimiento']) ?>
                            <br>
                            <div class="form-group">
                                <div class="form-group">
                                    <div class="div_trancurso_tierra_select" style="display: none">
                                        <?= Html::dropDownList('viaje_tierra_id', null , Viaje::getTranscursoTierra(), [ 'class' => 'form-control','id' => 'viaje_tierra_id', 'prompt' => 'Selecciona un viaje TIERRA']) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning alert_aviso_movimiento" style="display: none">
                                <strong>Aviso!</strong> El movimiento que quieres realizar es incorrecto conforme a las reglas de la empresa.
                            </div>

                            <?= Html::fileInput('csv_file', null, ['class' => 'btn btn-default mar-btm','accept' => '.csv, .txt' ]) ?>

                            <br>
                            <br>
                            <?= Html::submitButton( '<i class="fa fa-cube"></i>  Buscar paquete', ['class' => 'btn btn-primary btn-block btn-lg']) ?>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php if (isset($errors) &&  $errors): ?>
    <div id="results"></div>

        <div class="ibox rows-details">
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">#</th>
                                <th style="text-align: center;">TRACKEND</th>
                                <th style="text-align: center;">MENSSAGE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $error_count = 0; ?>
                            <?php $success_count = 0; ?>
                            <?php foreach ($errors as $key => $item):
                                if($item["code"] == 10 ){
                                    $error_count = $error_count + 1;
                                    $estatus  = '<span class="label label-danger">No fue posible crear el registro.</span>';
                                    $estatus .= '<br><span class="label label-danger">'. $item["tracked"] .' / ' . $item['message'] . '</span>';
                                }else{
                                    $success_count = $success_count + 1;
                                    $estatus = '<span class="label label-success">'. $item["tracked"] .' / '. $item["message"] .'</span>';
                                }
                            ?>
                                <tr>
                                    <td align="center"><?= $key +1 ?></td>
                                    <td align="center"><?= $item['tracked'] ?></td>
                                    <td align="center"><?= $estatus ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ibox results">
            <div class="ibox-content">
                <h5 >MOVIMIENTOS REGISTRADOS</h5>
            </div>
            <div class="ibox-content">
                <p>TOTAL DE MOVIMIENTOS: <strong><?= count($errors) ?></strong></p>
                <p>MOVIMIENTO CORRECTOS: <strong><?= $success_count ?></strong></p>
                <p>MOVIMIENTOS INCORRECTOS: <strong><?= $error_count ?></strong></p>
            </div>
        </div>


    <script type="text/javascript">
        $(document).ready(function(){
            $('.results')
                .detach()
                .appendTo('#results');
        });
    </script>

<?php endif ?>

<script>
    var $btnMovimientoEscaneo       = $('#btnMovimientoEscaneo'),
        $tipo_movimiento_lax_tierra = $('.tipo_movimiento_lax_tierra'),
        $alert_aviso_movimiento     = $('.alert_aviso_movimiento'),
        movimiento_paquete          = $tipo_movimiento_lax_tierra.val();

    var $tipo_movimiento        = $('.tipo_movimiento_lax_tierra'),
    $tipo_servicio              = $('#tipo_envio'),
    $div_caja_select            = $('.div_caja_select'),
    $div_trancurso_mex_select   = $('.div_trancurso_mex_select'),
    $div_trancurso_tierra_select= $('.div_trancurso_tierra_select'),
    $div_trancurso_lax_select   = $('.div_trancurso_lax_select'),
    $MEX_CAJA                   = <?= MovimientoPaquete::MEX_CAJA ?>,
    $MEX_TRANSCURSO             = <?= MovimientoPaquete::MEX_TRANSCURSO ?>,
    $LAX_TIER_TRANSCURSO        = <?= MovimientoPaquete::LAX_TIER_TRANSCURSO ?>,
    $TIPO_ENVIO_MEX             =  <?= Envio::TIPO_ENVIO_MEX  ?>,

    $TIPO_ENVIO_TIERRA          =  <?= Envio::TIPO_ENVIO_TIERRA  ?>;
$(document).ready(function() {
    $tipo_movimiento.trigger('change');
});
$tipo_movimiento.change(function(){
    $div_trancurso_tierra_select.hide();

    switch(parseInt($(this).val())){

        case $LAX_TIER_TRANSCURSO:
            console.log("entroo");
            $div_trancurso_tierra_select.show();
        break;
    }
});

</script>