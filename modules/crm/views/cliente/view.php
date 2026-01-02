<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\envio\Envio;
use app\models\cliente\ClienteCodigoPromocion;
use app\models\promocion\Promocion;
/* @var $this yii\web\View */
/* @var $model common\models\ViewCliente */

$this->title = $model->nombre . ' '. $model->apellidos;

$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
    <?= $can['update'] ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?= $can['delete']?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar este cliente?',
                'method' => 'post',
            ],
        ]): '' ?>

    <?= $can['updateAgente']?
        Html::a('Editar', ['update-agente', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?php /* ?>
    <?= $can['update'] && $can['create'] ?
     '<button  type="button"  data-target="#modal-promocion" data-toggle="modal"  class="modal-create btn btn-purple btn-lg add " ><i id="icon_emisor" class="fa fa-gift mar-rgt-5px icon-lg"></i> Promoción y Descuentos</button>'
        : '' ?>
    */?>

</p>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a data-toggle="tab" class="nav-link active" href="#tab-index">Información cliente</a>
        </li>
        <li>
            <a data-toggle="tab" class="nav-link" href="#tab-envio-cliente">Historial de envios</a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel"  id="tab-index" class="tab-pane active ">
            <?= $this->render('_view',[
                "can"   => $can,
                "model" => $model,
            ]) ?>
        </div>
        <div role="tabpanel"  id="tab-envio-cliente" class="tab-pane">
            <?= $this->render('_historial_envio',[
                "can"   => $can,
                "model" => $model,
            ]) ?>
        </div>
    </div>
</div>

<?= $this->render('_modal_promocion_cliente', [
    'model' => $model,
]) ?>

<script>

var $alertSinPromocion  = $('.alert-sin-promocion'),
    $alertSinSucursal   = $('.alert-sin-sucursal'),
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

    filters = "tipo_servicio="+ "<?= Envio::TIPO_ENVIO_TIERRA  ?>" + "&tipo="+ "<?= Promocion::TIPO_GENERAL ?>";

    $.get('<?= Url::to(['promocion-info-ajax']) ?>',{ filters: filters},function(json){
        if (json.id) {
           promocionVigente = json;
        }else{
            $formPromocionBasica.prop( "disabled", true );
            $alertSinPromocion.show();
        }
    },'json');

    filterSucursal = "tipo_servicio="+ "<?= Envio::TIPO_ENVIO_TIERRA  ?>"+ "&tipo=<?=  Promocion::TIPO_ESPECIAL  ?>";

    $.get('<?= Url::to(['promocion-sucursal-info-ajax']) ?>',{ filters: filterSucursal},function(json){
        if (json.id) {
           promocionVigenteSucursal = json;
        }else{
            $formPromocionSucursal.prop( "disabled", true );
            $alertSinSucursal.show();
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
