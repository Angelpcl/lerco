<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\sucursal\Sucursal;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Estados de cuenta ', 'url' => ['estado-cuenta']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = $model->nombre;
?>

<div class="estado-cuenta-view">
    <div class="row">
        <div class="col-md-7">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información sucursal</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'nombre',
                            "email:email",
                            "rfc",
                            'telefono',
                            "telefono_movil",
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel">
                    <?= Html::a('<i class="fa fa-print mar-rgt-5px"></i> Estado de cuenta',null,['id' => 'imprimir_download_estado_cuenta','class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Dirección</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'direccion',
                            'num_ext',
                            'num_int',
                            'esysDireccionCodigoPostal.colonia',
                            'colonia_new',
                        ]
                    ]) ?>
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            "estado.singular",
                            "municipio.singular",
                        ]
                    ]) ?>

                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'esysDireccionCodigoPostal.codigo_postal',
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var $imprimir_download_estado_cuenta    = $('#imprimir_download_estado_cuenta'),
        $sucursal_id                        = <?= $model->id  ?>,
        $date_ini                          = "<?= $date_ini  ?>",
        $date_fin                          = "<?= $date_fin  ?>";

    $imprimir_download_estado_cuenta.click(function(event){
        event.preventDefault();
        window.open('<?= Url::to(['estado-cuenta-ajax']) ?>?sucursal_id=' + $sucursal_id + '&date_ini=' + $date_ini + "&date_fin=" + $date_fin,
            'imprimir',
            'width=700,height=900');
    });
</script>
