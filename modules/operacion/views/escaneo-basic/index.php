<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootboxAsset;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use app\models\envio\Envio;
use app\models\movimiento\MovimientoPaquete;
use app\models\caja\CajaMex;
use app\models\envio\EnvioDetalle;
use app\models\viaje\Viaje;

BootboxAsset::register($this);
/* @var $this yii\web\View */

$this->title =  'Escaneo por paquete ';
$this->params['breadcrumbs'][] = 'Escaneo por paquete ';
?>

<div class="row">
    <div class="col-md-6  offset-sm-3">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Ingresa el folio del paquete</h5>
            </div>
            <div class="ibox-content">
            	<?php $form = ActiveForm::begin(['id' => 'form-escaneo', 'action' => ['escaneo-paquete'] ,'method' => 'GET']) ?>
            		<div class="row">
            			<div class="col-sm-6 offset-sm-3">
							<?=  Html::input('text','tracked',isset($tracked) || isset($caja) ? isset($tracked) && !isset($caja) ? $tracked->tracked_movimiento: $caja->tracked_movimiento : null,[ 'class' => 'form-control','placeholder'=>'TIE-00000']); ?>
							<br>
            	    		<?= Html::submitButton( '<i class="fa fa-cube"></i>  Buscar paquete', ['class' => 'btn btn-primary btn-block btn-lg']) ?>
						</div>
					</div>
            	<?php ActiveForm::end(); ?>
            </div>
        </div>
        <?php if (isset($clave_servicio)): ?>
            <?php switch ($clave_servicio) {
                case Envio::TIPO_ENVIO_MEX :
                case Envio::TIPO_ENVIO_TIERRA : ?>

                <div class="panel">
                    <div class="panel-body">

                        <?php $form = ActiveForm::begin(['id' => 'form-escaneo', 'action' => ['movimiento-paquete'] ]) ?>
                            <?= Html::hiddenInput('paquete_id', $tracked->id,['id' => 'paquete_id']) ?>
                            <?= Html::hiddenInput('tipo_envio',   $tracked->envio->tipo_envio, ['id' => 'tipo_envio']) ?>
                            <?= Html::hiddenInput('tracked_movimiento', $tracked->tracked_movimiento,['id'=> 'tracked_movimiento']) ?>
                            <?php if ( $tracked->envio->tipo_envio == Envio::TIPO_ENVIO_MEX): ?>
                                <div class="form-group">
                                    <h3>Movimientos Mex</h3>
                                    <?= Html::dropDownList('tipo_movimiento', EnvioDetalle::getMovimientoTop($tracked->tracked_movimiento) ? EnvioDetalle::getMovimientoTop($tracked->tracked_movimiento) : null , MovimientoPaquete::$tipoMexList, [ 'class' => 'form-control tipo_movimiento_lax_tierra', 'prompt' => 'Selecciona tipo de movimiento']) ?>
                                </div>
                                <div class="form-group">
                                    <div class="form-group">
                                        <div class="div_caja_select" style="display: none">
                                            <?= Html::dropDownList('caja_id',EnvioDetalle::getMovimientoTop($tracked->tracked_movimiento) ? EnvioDetalle::getMovimientoTop($tracked->tracked_movimiento) : null , CajaMex::getCajaHabilitado(), [ 'class' => 'form-control','id' => 'caja_id', 'prompt' => 'Selecciona una caja']) ?>
                                        </div>
                                        <div class="div_trancurso_mex_select" style="display: none">
                                            <?= Html::dropDownList('viaje_mex_id',null , Viaje::getTranscursoMex(), [ 'class' => 'form-control','id' => 'viaje_mex_id', 'prompt' => 'Selecciona una viaje MEX']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="form-group">
                                    <h3>Movimientos Lax y Tierra </h3>
                                    <?= Html::dropDownList('tipo_movimiento', EnvioDetalle::getMovimientoTop($tracked->tracked_movimiento) ? EnvioDetalle::getMovimientoTop($tracked->tracked_movimiento) : null  , MovimientoPaquete::$tipoLaxTierList, [ 'class' => 'form-control tipo_movimiento_lax_tierra','prompt' => 'Selecciona tipo de movimiento']) ?>
                                </div>
                                <div class="form-group">
                                    <div class="form-group">
                                        <div class="div_trancurso_tierra_select" style="display: none">
                                            <?= Html::dropDownList('viaje_tierra_id',EnvioDetalle::getMovimientoTop($tracked->tracked_movimiento) ? EnvioDetalle::getMovimientoTop($tracked->tracked_movimiento) : null , Viaje::getTranscursoTierra(), [ 'class' => 'form-control','id' => 'viaje_tierra_id', 'prompt' => 'Selecciona un viaje TIERRA']) ?>
                                        </div>
                                        <div class="div_entregado_por_select" style="display: none">
                                            <?= Html::label('Paqueteria','paqueteria_name') ?>
                                            <?=  Html::input('text','paqueteria_name',  null,[ 'class' => 'form-control','placeholder'=>'Paqueteria']); ?>
                                            <?= Html::label('N° de guia','paqueteria_guia') ?>
                                            <?=  Html::input('text','paqueteria_guia',  null,[ 'class' => 'form-control','placeholder'=>'N° de guia']); ?>
                                        </div>
                                        <div class="div_proceso_entrega_select" style="display: none">
                                             <?=  DatePicker::widget([
                                                'name' => 'fecha_entrega',
                                                'options' => ['placeholder' => 'Fecha de entrega'],
                                                'language' => 'es',
                                                'pickerIcon' => '<i class="fa fa-calendar"></i>',
                                                'removeIcon' => '<i class="fa fa-trash "></i>',
                                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                                'pluginOptions' => [
                                                    'autoclose'=>true,
                                                    'format' => 'yyyy-mm-dd',
                                                ]
                                            ])?>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-warning alert_aviso_movimiento" style="display: none">
                                    <strong>Aviso!</strong> El movimiento que quieres realizar es incorrecto conforme a las reglas de la empresa.
                                </div>
                            <?php endif ?>

                            <?php if ($tracked->status == EnvioDetalle::STATUS_CANCELADO): ?>
                                <div class="alert alert-danger " >
                                    <strong>AVISO!</strong> El paquete a sido cancelado, no se pueden realizar ningun movimiento
                                </div>
                            <?php endif ?>
                            <?php if ($tracked->envio->status == Envio::STATUS_CANCELADO): ?>
                                <div class="alert alert-danger " >
                                    <strong>AVISO!</strong> El envío  al que pertenece el paquete a sido cancelado, no se pueden realizar ningun movimiento
                                </div>
                            <?php endif ?>

                            <?php if ($tracked->status != EnvioDetalle::STATUS_CANCELADO && $tracked->envio->status != Envio::STATUS_CANCELADO): ?>
                                <?= Html::submitButton( '<i class="fa fa-gear"></i>  Guardar movimiento', ['class' => 'btn btn-primary btn-block btn-lg', 'id' => 'btnMovimientoEscaneo' ]) ?>
                            <?php elseif(Yii::$app->user->can('theCreator')): ?>
                                <?= Html::submitButton( '<i class="fa fa-gear"></i>  Guardar movimiento', ['class' => 'btn btn-primary btn-block btn-lg', 'id' => 'btnMovimientoEscaneo' ]) ?>
                            <?php endif ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
                <?php  break;
                case CajaMex::CLAVE_CAJA_MEX : ?>
                <div class="panel">
                    <div class="panel-body">
                        <?php $form = ActiveForm::begin(['id' => 'form-escaneo', 'action' => ['movimiento-caja'] ]) ?>
                        <?= Html::hiddenInput('paquete_id', $caja->id,['id' => 'paquete_id']) ?>
                        <?= Html::hiddenInput('tipo_envio',   Envio::TIPO_ENVIO_MEX , ['id' => 'tipo_envio']) ?>
                        <?= Html::hiddenInput('tracked_movimiento', $caja->tracked_movimiento,['id'=> 'tracked_movimiento']) ?>
                            <div class="form-group">
                                <h3>Movimientos Caja Mex</h3>
                                <?= Html::dropDownList('tipo_movimiento', CajaMex::getMovimientoTop($caja->tracked_movimiento) ? CajaMex::getMovimientoTop($caja->tracked_movimiento) : null , [
                                    MovimientoPaquete::MEX_BODEGA => MovimientoPaquete::$tipoMexList[MovimientoPaquete::MEX_BODEGA],
                                    MovimientoPaquete::MEX_TRANSCURSO => MovimientoPaquete::$tipoMexList[MovimientoPaquete::MEX_TRANSCURSO],
                                    MovimientoPaquete::MEX_APERTURA => MovimientoPaquete::$tipoMexList[MovimientoPaquete::MEX_APERTURA],
                                ] , [ 'class' => 'form-control','id' => 'tipo_movimiento', 'prompt' => 'Selecciona tipo de movimiento']) ?>
                            </div>
                            <div class="form-group">
                                <div class="form-group">
                                    <div class="div_trancurso_mex_caja_select" style="display: none">
                                        <?= Html::dropDownList('viaje_mex_caja_id',null , Viaje::getTranscursoMex(), [ 'class' => 'form-control','id' => 'viaje_mex_caja_id', 'prompt' => 'Selecciona una viaje MEX']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php if ($caja->status == CajaMex::STATUS_ACTIVE ): ?>
                            <div class="alert alert-warning">
                                <strong>Aviso!</strong> No se puede realizar ningun movimientos si la caja aun sigue <strong><?= CajaMex::$statusList[CajaMex::STATUS_ACTIVE] ?></strong>
                            </div>
                        <?php else: ?>
                            <?= Html::submitButton( '<i class="fa fa-gear"></i>  Guardar movimiento', ['class' => 'btn btn-primary btn-block btn-lg']) ?>
                        <?php endif ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
                <?php  break; ?>
            <?php } ?>

        <?php endif ?>
    </div>
</div>
<script>
    var $btnMovimientoEscaneo       = $('#btnMovimientoEscaneo'),
        $tipo_movimiento_lax_tierra = $('.tipo_movimiento_lax_tierra'),
        $alert_aviso_movimiento     = $('.alert_aviso_movimiento'),
        movimiento_paquete          = $tipo_movimiento_lax_tierra.val();

        $tipo_movimiento_lax_tierra.change(function(){
            if ($(this).val() < movimiento_paquete ) {
                $alert_aviso_movimiento.show();
                bootbox.confirm("¿Estas seguro que deseas realizar el movimiento?", function(result) {
                    if (result) {

                        $('#btnGuardarEnvio').submit();
                    }else{

                        window.location.href = "<?= Url::to(['index']) ?>";
                    };


                });
            }else{
                $alert_aviso_movimiento.hide();
            }
        });


</script>
<?php if (isset($clave_servicio)): ?>
    <?php switch ($clave_servicio) {
        case Envio::TIPO_ENVIO_MEX :
        case Envio::TIPO_ENVIO_TIERRA : ?>
            <?= $this->render('_view_paquete', [
                'tracked' => $tracked,
            ]) ?>

        <?php  break;
        case CajaMex::CLAVE_CAJA_MEX : ?>
            <?= $this->render('_view_caja', [
                'caja' => $caja,
            ]) ?>
        <?php  break; ?>

    <?php } ?>

<?php endif ?>


