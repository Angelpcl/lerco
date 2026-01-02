<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use app\models\cliente\Cliente;
use app\models\esys\EsysListaDesplegable;
use app\models\Esys;
use app\models\envio\Envio;
use app\models\cliente\ClienteCodigoPromocion;

/* @var $this yii\web\View */
/* @var $model backend\models\cliente\Cliente */

$this->title = $model->nombre . ' '. $model->apellidos;
//$this->params['breadcrumbs'][] = 'Clientes';
$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
 <button  type="button"  data-target="#modal-promocion" data-toggle="modal"  class="modal-create btn btn-purple btn-lg add " ><i id="icon_emisor" class="fa fa-gift mar-rgt-5px icon-lg"></i> Promoción y Descuentos</button>
</p>

<div class="clientes-cliente-update">
   <div class="row">
        <?php $form = ActiveForm::begin(['id' => 'form-cliente']) ?>
        <div class="col-lg-7">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Actualiza información personal del  cliente </h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <?= $form->field($model, 'atraves_de_id')->dropDownList(EsysListaDesplegable::getItems('origen_cliente'), ['prompt' => '']) ?>
                        <div class="col-md-7">
                            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'apellidos')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'servicio_preferente')->dropDownList(Cliente::$servicioList, ['prompt' => '']) ?>

                            <?= $form->field($model, 'medio_contacto_id')->dropDownList(EsysListaDesplegable::getItems('medio_contacto'), ['prompt' => '']) ?>

                            <?= $form->field($model, 'status_venta_id')->dropDownList(EsysListaDesplegable::getItems('status_venta'), ['prompt' => '']) ?>

                            <?= $form->field($model, 'comportamiento_id')->dropDownList(EsysListaDesplegable::getItems('comportamiento_cliente'), ['prompt' => '']) ?>
                        </div>
                        <div class="col-md-5">
                            <?= $form->field($model, 'tipo_cliente_id')->dropDownList(EsysListaDesplegable::getItems('tipo_cliente'), ['prompt' => '']) ?>
                            <?= $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'telefono_movil')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'costo_venta')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'notas')->textarea(['rows' => 6]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Cuenta de cliente y datos personales</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-7">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    "email:email",
                                ],
                            ]) ?>
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                  [
                                     'attribute' =>  "Sexo",
                                     'format'    => 'raw',
                                     'value'     => $model->sexo ?  Cliente::$sexoList[$model->sexo] : '',
                                 ]
                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-5">
                            <?php if ($model->origen == Cliente::ORIGEN_MX): ?>
                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    "esysDireccionCodigoPostal.estado.singular",
                                    "esysDireccionCodigoPostal.municipio.singular",
                                ]
                            ]) ?>
                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    'referencia',
                                    'direccion',
                                    'num_ext',
                                    'num_int',
                                    'esysDireccionCodigoPostal.colonia',

                                ]
                            ]) ?>
                            <?php else: ?>
                                <?php if ($model->origen == Cliente::ORIGEN_USA): ?>
                                    <?= DetailView::widget([
                                        'model' => $model->direccion,
                                        'attributes' => [
                                            "estado_usa",
                                            "municipio_usa",
                                        ]
                                    ]) ?>

                                    <?= DetailView::widget([
                                        'model' => $model->direccion,
                                        'attributes' => [
                                            'referencia',
                                            'direccion',
                                            'num_ext',
                                            'num_int',
                                            'colonia_usa',

                                        ]
                                    ]) ?>
                                <?php endif ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                             [
                                 'attribute' => 'Asignado',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->asignadoCliente->nombre) ?  Html::a($model->asignadoCliente->nombre ." ". $model->asignadoCliente->apellidos , ['/admin/user/view', 'id' => $model->asignadoCliente->id], ['class' => 'text-primary']) : '' ,
                             ]
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <?php //ActiveForm::end(); ?>
            <?php //$formCall = ActiveForm::begin(['id' => 'form-cliente-call']) ?>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Ingresar estatus de llamada</h3>
                </div>
                <div class="panel-body">
                    <?= $form->field($model->cliente_call, 'tipo_respuesta_id')->dropDownList(EsysListaDesplegable::getItems('status_respuesta_call'), ['prompt' => '']) ?>
                    <?= $form->field($model->cliente_call, 'telefono')->dropDownList([ $model->telefono => $model->telefono ,$model->telefono_movil => $model->telefono_movil] ) ?>
                    <?= $form->field($model->cliente_call, 'comentario')->textarea(['rows' => 6]); ?>
                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton($model->cliente_call->isNewRecord ? 'Guardar cambios' : 'Guardar cambios', ['class' => $model->cliente_call->isNewRecord ? 'btn btn-primary' : 'btn btn-primary']) ?>
                <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Historial de llamadas</h3>
                </div>
                <div class="panel-body">
                    <?php foreach ($model->historialCall as $key => $history): ?>
                    <li class="mar-btn" style="list-style:none;">
                        <div>
                            <span class="pull-right">
                                <p class="text-muted">hace <small title="<?= Esys::fecha_en_texto($history->created_at) ?>"><?= Esys::hace_tiempo_en_texto($history->created_at) ?></small></p>
                            </span>
                            <span><?= html::a(
                                $history->createdBy->nombreCompleto . ' [' . $history->created_by . ']',
                                ['/admin/user/view', 'id' => $history->created_by ],
                                ['class' => 'text-primary']
                            ) ?></span>
                        </div>
                        <div class="mar-btm">
                             <b>Tel: <?= $history->telefono ?></b> &nbsp; comentario:  &nbsp; <b><?= $history->comentario ?></b>
                        </div>
                    </li>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?= $this->render('_modal_promocion_cliente', [
    'model' => $model,
]) ?>

<script>
    var $alertSinPromocion = $('.alert-sin-promocion'),
    promocionVigente    = [],
    promocionAsignadas  = [],
    tipoList            = JSON.parse('<?= json_encode(ClienteCodigoPromocion::$tipoList)  ?>'),
    statusList            = JSON.parse('<?= json_encode(ClienteCodigoPromocion::$statusList)  ?>'),
    $table_complemento_promocion = $('.table_complemento_promocion');

    $('.modal-create').click(function(){
        $alertPromocionBasica.hide();
        $alertPromocionEspecial.hide();
        $alertInfoBasic.hide();
        $alertInfoEspecial.hide();

        filters = "tipo_servicio="+ "<?= Envio::TIPO_ENVIO_TIERRA  ?>";

        $.get('<?= Url::to(['promocion-info-ajax']) ?>',{ filters: filters},function(json){
                if (json.id) {
                   promocionVigente = json;
                }else{
                    $formPromocionBasica.prop( "disabled", true );
                    $alertSinPromocion.show();
                }
        },'json');

        load_promocion_descuento();

    });

    var load_promocion_descuento = function(){
        $.get('<?= Url::to(['cliente-codigo-ajax']) ?>',{ cliente_id: <?= $model->id  ?>},function(json){
            promocionAsignadas = json;
            render_promocion_template();
        },'json');


    }

/*====================================================
*               RENDERIZA TODO LOS PAQUETE
*====================================================*/
var render_promocion_template = function()
{
    $table_complemento_promocion.html("");

    $.each(promocionAsignadas, function(key, promocion){

        if (promocion.id) {
            template_promocion = $template_complemento.html();
            template_promocion = template_promocion.replace("{{promocion_id}}",promocion.id);

            $table_complemento_promocion.append(template_promocion);

            $tr        =  $("#complemento_id_" + promocion.id, $table_complemento_promocion);

            $("#table-id",$tr).html(promocion.id);
            $("#table-tipo",$tr).html(tipoList[promocion.tipo]);
            $("#table-nombre",$tr).html(promocion.nombre);
            $("#table-clave",$tr).html(promocion.clave);

            $("#table-libra_envia",$tr).html(promocion.requiered_libras);
            $("#table-descuento",$tr).html(parseFloat(promocion.descuento));

            $("#table-fecha_ini",$tr).html(promocion.fecha_rango_ini);
            $("#table-fecha_fin",$tr).html(promocion.fecha_rango_fin);
            $("#table-status",$tr).html("<strong>"+ statusList[promocion.status] + "</strong>");
        }
    });
};
</script>
