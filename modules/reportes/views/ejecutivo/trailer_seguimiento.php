<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\viaje\Viaje;


$this->title = 'Trailer - Seguimiento';
$this->params['breadcrumbs'][] = 'Reportes Ejecutivo';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="reporte-seguimiento-index">
	<div class="row">
	    <div class="col-lg-12">
	        <div id="demo-panel-network" class="panel col-lg-12">
	            <div class="panel-heading">
	                <div class="btt-toolbar filter-top">
				        <div class="panel mar-btm-5px">
				            <div id="toolbar-panel-collapse" class="collapse in" aria-expanded="false">
				                <div class="panel-body pad-btm-15px">
				                    <div>
				                        <strong class="pad-rgt">Filtrar:</strong>
				                        <?=  Html::dropDownList('viaje_id', null, Viaje::getItems() , ['class' => 'max-width-170px'])  ?>
				                    </div>
				                </div>
				            </div>
				        </div>
					</div>
	            </div>
	        </div>
	        <br>
			<div class="row">
		        <div class="col-md-3 col-xs-3 col-sm-3">
		            <div class="panel text-dark panel-colorful">
			            <div class="panel-body text-center">
			                <i class="fa fa-plane icon-5x icons_panel"></i>
			                <div class="load1 loader_icons" style="display: none;" >
		                        <div class="loader"></div>
		                    </div>
			            </div>
			            <div class="pad-all text-center">
			                <p class="text-semibold text-lg mar-no">TIEs</p>
			                <p class="text-2x text-bold mar-no lbl_ties"></p>

			            </div>
			        </div>
		        </div>
		        <div class="col-md-3 col-xs-3 col-sm-3">
		            <div class="panel text-dark panel-colorful">
			            <div class="panel-body text-center">
			                <i class="fa fa-cubes icon-5x icons_panel"></i>
			                <div class="load1 loader_icons" style="display: none;" >
		                        <div class="loader"></div>
		                    </div>
			            </div>
			            <div class="pad-all text-center">
			                <p class="text-semibold text-lg mar-no">Paquetes</p>
			                <p class="text-2x text-bold mar-no lbl_paquetes"></p>
			                <p class="text-overflow pad-top">
			                    <span class="label label-dark lbl_envio_ini"></span> -
			                    <span class="label label-dark lbl_envio_fin"></span>
			                </p>
			            </div>
			        </div>
		        </div>
		        <div class="col-md-3 col-xs-3 col-sm-3">
		            <div class="panel text-dark panel-colorful">
			            <div class="panel-body text-center">
			                <i class="fa fa-truck icon-5x icons_panel"></i>
			                <div class="load1 loader_icons" style="display: none;" >
		                        <div class="loader"></div>
		                    </div>
			            </div>
			            <div class="pad-all text-center">
			                <p class="text-semibold text-lg mar-no">Paquetes Trailer</p>
			                <p class="text-2x text-bold mar-no lbl_paquetes_trailer"></p>

			            </div>
			        </div>
		        </div>
		        <div class="col-md-3 col-xs-3 col-sm-3">
		            <div class="panel text-dark panel-colorful">
			            <div class="panel-body text-center">
			                <i class="fa fa-truck icon-5x icons_panel"></i>
			                <div class="load1 loader_icons" style="display: none;" >
		                        <div class="loader"></div>
		                    </div>
			            </div>
			            <div class="pad-all text-center">
			                <p class="text-semibold text-lg mar-no">Paquetes en otro Trailer</p>
			                <p class="text-2x text-bold mar-no lbl_otro_trailer"></p>

			            </div>
			        </div>
		        </div>
		    </div>
		    <div class="row">
		        <div class="col-md-4 col-sm-4">
		            <div class="panel panel-dark panel-colorful media middle pad-all">
		                <div class="media-left">
		                    <div class="pad-hor">
		                        <i class="fa fa-cubes icon-3x icons_panel"></i>
		                        <div class="load1 loader_icons" style="display: none;" >
			                        <div class="loader"></div>
			                    </div>
		                    </div>
		                </div>
		                <div class="media-body">
		                    <p class="text-2x mar-no text-semibold lbl_propuestos"></p>
		                    <p class="mar-no">Propuestos</p>
		                </div>
		            </div>
		        </div>
		        <div class="col-md-4 col-sm-4">
		            <div class="panel panel-dark panel-colorful media middle pad-all">
		                <div class="media-left">
		                    <div class="pad-hor">
		                        <i class="fa fa-cloud-download icon-3x icons_panel"></i>
		                        <div class="load1 loader_icons" style="display: none;" >
			                        <div class="loader"></div>
			                    </div>
		                    </div>
		                </div>
		                <div class="media-body">
		                    <p class="text-2x mar-no text-semibold lbl_cargados"></p>
		                    <p class="mar-no">Cargados</p>

		                </div>
		            </div>
		        </div>
		        <div class="col-md-4 col-sm-4">
		            <div class="panel panel-dark panel-colorful media middle pad-all">
		                <div class="media-left">
		                    <div class="pad-hor">
		                        <i class="fa fa-window-close-o icon-3x icons_panel"></i>
		                        <div class="load1 loader_icons" style="display: none;" >
			                        <div class="loader"></div>
			                    </div>
		                    </div>
		                </div>
		                <div class="media-body">
		                    <p class="text-2x mar-no text-semibold lbl_no_cargados"></p>
		                    <p class="mar-no">No cargados</p>
		                    <button data-target = "#modal-show" data-toggle ="modal" class="btn btn-danger btn-circle" style="position: absolute;left: 80%;bottom: 30%;" onclick="show_paquete_modal('no_cargado')"><i class="fa fa-eye" ></i></button>
		                </div>
		            </div>
		        </div>
		    </div>
		    <div class="row">
		        <div class="col-md-4 col-xs-4 col-sm-4">
		            <div class="panel text-dark panel-colorful">
			            <div class="panel-body text-center">
			                <i class="fa fa-plane icon-5x icons_panel"></i>
			                <div class="load1 loader_icons" style="display: none;" >
		                        <div class="loader"></div>
		                    </div>
			            </div>
			            <div class="pad-all text-center">
			                <p class="text-semibold text-lg mar-no">Cargados</p>
			                <p class="text-2x text-bold mar-no lbl_cargados_descarga"></p>
			                <p class="text-overflow pad-top">
			                    <span class="label label-dark ">Repetidos:</span> -
			                    <span class="label label-dark lbl_paquete_repeat">0</span>
			                </p>
			                <button data-target = "#modal-show" data-toggle ="modal" class="btn btn-danger btn-circle" style="position: absolute;left: 80%;bottom: 30%;" onclick="show_paquete_modal('paquete_repeat')"><i class="fa fa-eye" ></i></button>

			            </div>
			        </div>
		        </div>
		        <div class="col-md-4 col-xs-4 col-sm-4">
		            <div class="panel text-dark panel-colorful">
			            <div class="panel-body text-center">
			                <i class="fa fa-building icon-5x icons_panel"></i>
			                <div class="load1 loader_icons" style="display: none;" >
		                        <div class="loader"></div>
		                    </div>
			            </div>
			            <div class="pad-all text-center">
			                <p class="text-semibold text-lg mar-no">Bodega MX</p>
			                <p class="text-2x text-bold mar-no lbl_bodega_mx "></p>

			            </div>
			        </div>
		        </div>
		        <div class="col-md-4 col-xs-4 col-sm-4">
		            <div class="panel text-dark panel-colorful">
			            <div class="panel-body text-center">
			                <i class="fa fa-random icon-5x icons_panel"></i>
			                <div class="load1 loader_icons" style="display: none;" >
		                        <div class="loader"></div>
		                    </div>
			            </div>
			            <div class="pad-all text-center">
			                <p class="text-semibold text-lg mar-no">Diferencia</p>
			                <p class="text-2x text-bold mar-no lbl_diferencia_descarga"></p>
			                <button data-target = "#modal-show" data-toggle ="modal" class="btn btn-danger btn-circle" style="position: absolute;left: 80%;bottom: 30%;" onclick="show_paquete_modal('diferencia_paquete')"><i class="fa fa-eye" ></i></button>
			            </div>
			        </div>
		        </div>
		    </div>
		    <div class="row">
		        <div class="col-md-4 col-sm-4">
		            <div class="panel panel-dark panel-colorful media middle pad-all">
		                <div class="media-left">
		                    <div class="pad-hor">
		                        <i class="fa fa-truck icon-3x icons_panel"></i>
		                        <div class="load1 loader_icons" style="display: none;" >
			                        <div class="loader"></div>
			                    </div>
		                    </div>
		                </div>
		                <div class="media-body">
		                    <p class="text-2x mar-no text-semibold lbl_reparto"></p>
		                    <p class="mar-no">Reparto</p>
		                </div>
		            </div>
		        </div>
		        <div class="col-md-4 col-sm-4">
		            <div class="panel panel-dark panel-colorful media middle pad-all">
		                <div class="media-left">
		                    <div class="pad-hor">
		                        <i class="fa fa-handshake-o icon-3x icons_panel"></i>
		                        <div class="load1 loader_icons" style="display: none;" >
			                        <div class="loader"></div>
			                    </div>
		                    </div>
		                </div>
		                <div class="media-body">
		                    <p class="text-2x mar-no text-semibold lbl_entregado"></p>
		                    <p class="mar-no">Entregado</p>
		                </div>
		            </div>
		        </div>
		        <div class="col-md-4 col-sm-4">
		            <div class="panel panel-dark panel-colorful media middle pad-all">
		                <div class="media-left">
		                    <div class="pad-hor">
		                        <i class="fa fa-building icon-3x icons_panel"></i>
		                        <div class="load1 loader_icons" style="display: none;" >
			                        <div class="loader"></div>
			                    </div>
		                    </div>
		                </div>
		                <div class="media-body">
		                    <p class="text-2x mar-no text-semibold lbl_bodega_resagados"></p>
		                    <p class="mar-no">Bodega / Resagados </p>
		                    <button data-target = "#modal-show" data-toggle ="modal" class="btn btn-danger btn-circle" style="position: absolute;left: 80%;bottom: 30%;" onclick="show_paquete_modal('bodega_resagado')"><i class="fa fa-eye"></i></button>
		                </div>
		            </div>
		        </div>
		    </div>
	    </div>
	</div>
</div>


<div class="fade modal " id="modal-show"  tabindex="-1" role="dialog" aria-labelledby="modal-show-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-cubes mar-rgt-5px icon-lg"></i> Detalle de paquetes </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                	<div class="col-sm-12">
                		<div class="container_paquete">

                		</div>
                	</div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
	var $filterArray 		= $('.btt-toolbar :input'),
		$container_paquete 	= $('.container_paquete'),
		no_cargados_trailer 	= [],
		diferencia_paquete 		= [],
		bodega_resagado_array 	= [],
		paquete_repeat_array 	= [];
	$(document).ready(function(){
		load_ini();
	});

	$filterArray.change(function(){
		load_ini();
	});

	var load_ini = function(){
		$container_paquete.html('');
		$('.icons_panel').hide();
		$('.loader_icons').show();
		$.get("<?= Url::to(['reporte-fases'])  ?>",{ filters: $filterArray.serialize() },function($response){
			if ($response) {

				$('.lbl_envio_ini').html( $response.reporteFasesArray.viaje_envio_ini);
				$('.lbl_envio_fin').html( $response.reporteFasesArray.viaje_envio_fin);
				$('.lbl_ties').html( $response.reporteFasesArray.ties);
				$('.lbl_paquetes').html( $response.reporteFasesArray.paquete);
				$('.lbl_paquetes_trailer').html( $response.reporteFasesArray.paquete_este_trailer);
				$('.lbl_otro_trailer').html( ( $response.reporteFasesArray.paquetes_otro_trailer.length > 0 ? $response.reporteFasesArray.paquetes_otro_trailer : 0) );
				$('.lbl_propuestos').html($response.reporteFasesArray.propuestos);
				$('.lbl_cargados').html($response.reporteFasesArray.carga_trailer_all);
				$('.lbl_no_cargados').html($response.reporteFasesArray.no_cargados_trailer.length > 0 ? $response.reporteFasesArray.no_cargados_trailer.length : 0 );

				no_cargados_trailer = $response.reporteFasesArray.no_cargados_trailer;
				diferencia_paquete  = $response.reporteFasesArray.diferencia_paquete;

				$('.lbl_paquete_repeat').html( $response.reporteFasesArray.paquete_repeat.length ? $response.reporteFasesArray.paquete_repeat.length : 0 );
				paquete_repeat_array = $response.reporteFasesArray.paquete_repeat;

				$('.lbl_cargados_descarga').html( $response.reporteFasesArray.carga_trailer_all);
				$('.lbl_bodega_mx').html( $response.reporteFasesArray.bodegaDescarga.length > 0 ?$response.reporteFasesArray.bodegaDescarga.length  : 0);
				$('.lbl_diferencia_descarga').html( (($response.reporteFasesArray.carga_trailer_all ? parseInt($response.reporteFasesArray.carga_trailer_all): 0) - ($response.reporteFasesArray.bodegaDescarga.length ? parseInt($response.reporteFasesArray.bodegaDescarga.length) : 0)) -  ($response.reporteFasesArray.paquete_repeat.length ? $response.reporteFasesArray.paquete_repeat.length : 0));

				$('.lbl_bodega_resagados').html( $response.reporteFasesArray.bodega.length > 0 ? parseInt($response.reporteFasesArray.bodega.length) : 0);
				   bodega_resagado_array =  $response.reporteFasesArray.bodega;

				$('.lbl_reparto').html( $response.reporteFasesArray.reparto.pz_venta ? parseInt($response.reporteFasesArray.reparto.pz_venta) : 0);

				$('.lbl_entregado').html( $response.reporteFasesArray.entregado.pz_venta ? parseInt($response.reporteFasesArray.entregado.pz_venta) : 0);

				$('.icons_panel').show();
				$('.loader_icons').hide();
			}
		},'json');

	};

	var show_paquete_modal = function(opc)
	{
		$container_paquete.html('');
		switch(opc){
			case 'no_cargado':
				$container_html = '';
				count = 1;
				$.each(no_cargados_trailer,function(key,item){
					$container_html += "<div class='col-sm-3' ><li>"+ count +".-"+ item.tracked +"</li></div>";
					count++;
				});
				$container_paquete.html($container_html);

			break;

			case 'diferencia_paquete':
				$container_html = '';
				count = 1;
				$.each(diferencia_paquete,function(key,item){
					$container_html += "<div class='col-sm-3' ><li>"+ count +".-"+ item.tracked +"</li></div>";
					count++;
				});
				$container_paquete.html($container_html);
			break;

			case 'paquete_repeat':
				$container_html = '';
				count = 1;
				$.each(paquete_repeat_array,function(key,item){
					$container_html += "<div class='col-sm-3' ><li>"+ count +".-"+ item.tracked +"</li></div>";
					count++;
				});
				$container_paquete.html($container_html);
			break;

			case 'bodega_resagado':
				$container_html = '';
				count = 1;
				$.each(bodega_resagado_array,function(key,item){
					$container_html += "<div class='col-sm-3' ><li>"+ count +".-"+ item.tracked +"</li></div>";
					count++;
				});
				$container_paquete.html($container_html);
			break;
		}
	}

</script>