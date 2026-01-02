<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\FullCalendarAsset;

FullCalendarAsset::register($this);
$this->title = 'CALENDARIO DE ENTREGAS';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="reporte-entrega-index">
    <div class="wrapper wrapper-content">
        <div class="row  border-bottom white-bg dashboard-header">
            <div class="col-md-12">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<div class="fade modal inmodal" id="modal-show-evento" role="dialog" tabindex="-1" aria-labelledby="modal-evento-label">
    <div class="modal-dialog "  >
        <div class="modal-content" >
            <!--Modal header-->

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">PAQUETE # <strong class="title-consulta"></strong></h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">

                        <div class="show-event">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h1 class="title-evento"></h1>
                                        <strong> <h2>CLIENTE: </h2> <span class="title-cliente"></span> </strong>

                                        <strong><h2>SUCURSAL QUE RECIBE:</h2> <span class="title-sucursal"></span></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-12 text-center">
                                        <h4 class="text-primary title-nota" style="font-weight: bold;"></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
	 var paquetes_array        = [],
        $error_div          = $('.agenda-errors');

	$(document).ready(function() {
    	render_agenda();
		$('#calendar').fullCalendar({
	        header: {
	            left: 'prev,next today',
	            center: 'title',
	            //right: 'month,agendaWeek,agendaDay'
	        },
	        eventClick: function(item) {
	            $('#modal-show-evento').modal('show');
	            $(".show-event").show();
	            $(".edit-event").hide();
	            $.get("<?= Url::to(['get-paquete']) ?>",{ id_paquete : item.id },function($response){
	                if ($response.code == 202) {
	                    $('.title-consulta').html($response.event.tracked);
	                    $('.title-cliente').html($response.event.cliente);
	                    $('.title-sucursal').html($response.event.sucursal);
	                }else{
	                    /**
	                     *  ERRORS
	                     * */
	                }

	            },'json');

	        },
	        //editable: true,
	        droppable: true, // this allows things to be dropped onto the calendar
	        eventLimit: true,
	        drop: function() {
	            // is the "remove after drop" checkbox checked?
	            if ($('#drop-remove').is(':checked')) {
	                // if so, remove the element from the "Draggable Events" list
	                $(this).remove();
	            }
	        },
	        events: paquetes_array,
		});
	});

var render_agenda = function(){
    paquetes_array = [];
    $.get("<?=  Url::to(['get-paquetes']) ?>",function($response){

        if ($response.code == 202 ) {
            $.each($response.items,function(key,item){

                paquetes_array.push({
                    id: item.id,
                    title: "PAQUETE #" + item.tracked,

                    start: new Date( new Date(item.fecha_entrega *1000).getFullYear(),  new Date(item.fecha_entrega *1000).getMonth() , new Date(item.fecha_entrega *1000).getDate()),
                });
            });
            $('#calendar').fullCalendar('removeEvents');
            $('#calendar').fullCalendar('addEventSource', paquetes_array);
                //$('#calendar').addEventSource(paquetes_array);
            $('#calendar').fullCalendar('rerenderEvents');

            // -----------------------------------------------------------------
        }
    },'json');

}
</script>