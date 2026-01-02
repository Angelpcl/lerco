<?php
use yii\helpers\Url;
use app\assets\HighchartsAsset;

HighchartsAsset::register($this);
?>

<div class="reporte-documentacion-index">
	<br>
	<div class="row">
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-warning panel-colorful media middle pad-all">
                <div class="media-left">
                    <div class="pad-hor">
                        <i class="pli-coins icon-3x"></i>
                    </div>
                </div>
                <div class="media-body">
                    <p class="text-2x mar-no text-semibold lbl_preventa_money" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA</p>
                </div>
                <div class="media-body">
                    <p class="text-2x mar-no text-semibold lbl_ventanilla_money" style="font-size: 20px;"></p>
                    <p class="mar-no">MOSTRADOR</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-mint panel-colorful media middle pad-all">
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_prevneta_lb" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA LB</p>
                </div>
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_ventanilla_lb" style="font-size: 20px;"></p>
                    <p class="mar-no">MOSTRADOR LB</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-mint panel-colorful media middle pad-all">
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_preventa_pz" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA PZ</p>
                </div>
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_ventanilla_pz" style="font-size: 20px;"></p>
                    <p class="mar-no">MOSTRADOR PZ</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-warning panel-colorful media middle pad-all">
                <div class="media-left">
                    <div class="pad-hor">
                        <i class="pli-coins icon-3x"></i>
                    </div>
                </div>
                <div class="media-body ">
                    <p class="text-2x mar-no text-semibold lbl_preventa_documentada_money" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA - DOCUMENTADA</p>
                </div>
                <div class="media-body ">
                    <p class="text-2x mar-no text-semibold lbl_preventa_tardia_money" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA - TARDIA</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-mint panel-colorful media middle pad-all">
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_preventa_documentado_lb" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA LB - DOCUMENTADA </p>
                </div>
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_preventa_tardia_lb" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA LB - TARDIA </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-mint panel-colorful media middle pad-all">
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_preventa_documentada_pz" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA PZ - BODEGA</p>
                </div>
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_preventa_tardia_pz" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA PZ - TARDIA </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-warning panel-colorful media middle pad-all">
                <div class="media-left">
                    <div class="pad-hor">
                        <i class="pli-coins icon-3x"></i>
                    </div>
                </div>
                <div class="media-body ">
                    <p class="text-2x mar-no text-semibold lbl_preventa_faltante_money" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA - FALTANTE</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-mint panel-colorful media middle pad-all">
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_preventa_faltante_lb" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA LB - FALTANTE </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-mint panel-colorful media middle pad-all">
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_preventa_faltante_pz" style="font-size: 20px;"></p>
                    <p class="mar-no">PREVENTA PZ - FALTANTE </p>
                </div>
            </div>
        </div>
    </div>



	<div class="row">
	    <div class="col-lg-12">
	    	<div id="demo-panel-network" class="panel">
	            <div class="panel-heading">
	                <h3 class="panel-title">PREVENTA / MOSTRADOR </h3>
	            </div>

	            <div class="dashboard-index" style="margin-bottom: 10%;">
                    <div id="demo-chart"></div>
                </div>

	        </div>
	    </div>
	</div>
</div>

<script>
	var $filterArray 	= $('.btt-toolbar :input');
	var total_preventa 			= 0;
	var total_preventa_almacen 	= 0;
	var total_mostrador = 0;

	$filterArray.change(function(){
		load_ini();
	});



	var load_ini = function(){
		$.get("<?= Url::to(['reporte-data-documentacion'])  ?>",{ filters: $filterArray.serialize() },function($response){
			if ($response) {
				$('.lbl_ventanilla_money').html(  ($response.ventanilla.total_venta ? btf.conta.money(parseFloat($response.ventanilla.total_venta)) : 0 ) );
				$('.lbl_ventanilla_lb').html(($response.ventanilla.lb_venta ? parseFloat($response.ventanilla.lb_venta).toFixed(2) : 0));
				$('.lbl_ventanilla_pz').html(($response.ventanilla.pz_venta ? $response.ventanilla.pz_venta : 0));

				$('.lbl_preventa_money').html( ($response.preventa.total_preventa ? btf.conta.money(parseFloat($response.preventa.total_preventa)) : 0));
				$('.lbl_prevneta_lb').html(($response.preventa.lb_preventa ? parseFloat($response.preventa.lb_preventa).toFixed(2) : 0));
				$('.lbl_preventa_pz').html(($response.preventa.pz_preventa ? $response.preventa.pz_preventa :0 ));

				$('.lbl_preventa_documentada_money').html( ($response.preventaTerminada.total_preventaTerminada ? btf.conta.money(parseFloat($response.preventaTerminada.total_preventaTerminada)) : 0));
				$('.lbl_preventa_documentado_lb').html(($response.preventaTerminada.lb_preventaTerminada ? parseFloat($response.preventaTerminada.lb_preventaTerminada).toFixed(2) : 0));
				$('.lbl_preventa_documentada_pz').html(($response.preventaTerminada.pz_preventaTerminada ? $response.preventaTerminada.pz_preventaTerminada :0 ));

                $('.lbl_preventa_faltante_money').html( ($response.preventaFaltante.total_preventa_faltante ? btf.conta.money(parseFloat($response.preventaFaltante.total_preventa_faltante)) : 0));
                $('.lbl_preventa_faltante_lb').html(($response.preventaFaltante.lb_preventa_faltante ? parseFloat($response.preventaFaltante.lb_preventa_faltante).toFixed(2) : 0));
                $('.lbl_preventa_faltante_pz').html(($response.preventaFaltante.pz_preventa_faltante ? $response.preventaFaltante.pz_preventa_faltante :0 ));


				$('.lbl_preventa_tardia_money').html( ($response.preventaDesfasado.total_preventaDesfasado ? btf.conta.money(parseFloat($response.preventaDesfasado.total_preventaDesfasado)) : 0));
				$('.lbl_preventa_tardia_lb').html(($response.preventaDesfasado.lb_preventaDesfasado ? parseFloat($response.preventaDesfasado.lb_preventaDesfasado).toFixed(2) : 0));
				$('.lbl_preventa_tardia_pz').html(($response.preventaDesfasado.pz_preventaDesfasado ? $response.preventaDesfasado.pz_preventaDesfasado :0 ));

				total_mostrador = parseFloat(($response.ventanilla.total_venta ? $response.ventanilla.total_venta : 0 ));
				total_preventa  = parseFloat(($response.preventaTerminada.total_preventaTerminada ? $response.preventaTerminada.total_preventaTerminada : 0));

				total_preventa_almacen  = parseFloat(($response.preventaDesfasado.total_preventaDesfasado ? $response.preventaDesfasado.total_preventaDesfasado : 0));

				chart_pie();
			}
		},'json');

	};


var chart_pie = function(){
	Highcharts.chart('demo-chart', {
	    chart: {
	        plotBackgroundColor: null,
	        plotBorderWidth: null,
	        plotShadow: false,
	        type: 'pie'
	    },
	    title: {
	        text: 'TOTAL DE VENTA'
	    },
	    tooltip: {
	        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
	    },
	    accessibility: {
	        point: {
	            valueSuffix: '%'
	        }
	    },
	    plotOptions: {
	        pie: {
	            allowPointSelect: true,
	            cursor: 'pointer',
	            dataLabels: {
	                enabled: true,
	                format: '<b>{point.name}</b>: {point.percentage:.1f} %'
	            }
	        }
	    },
	    series: [{
	        name: 'Porcentaje',
	        colorByPoint: true,
	        data: [{
	            name: 'TOTAL EN MOSTRADOR - '  + btf.conta.money(parseFloat(total_mostrador)),
	            y: total_mostrador,
	            sliced: true,
	            selected: true
	        }, {
	            name: 'TOTAL EN PREVENTA - '  + btf.conta.money(parseFloat(total_preventa)),
	            y: total_preventa
	        }, {
	            name: 'TOTAL EN PREVENTA TARDIA - '  + btf.conta.money(parseFloat(total_preventa_almacen)),
	            y: total_preventa_almacen
	        }]
	    }]
	});
}

</script>
