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
                    <p class="text-2x mar-no text-semibold lbl_venta_money" style="font-size: 20px;"></p>
                    <p class="mar-no">TOTAL</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-mint panel-colorful media middle pad-all">
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_venta_lb" style="font-size: 20px;"></p>
                    <p class="mar-no">TOTAL LB</p>
                </div>

            </div>
        </div>
        <div class="col-md-4 col-xs-4 col-sm-4">
            <div class="panel panel-mint panel-colorful media middle pad-all">
                <div class="media-body text-center">
                    <p class="text-2x mar-no text-semibold lbl_venta_pz" style="font-size: 20px;"></p>
                    <p class="mar-no">TOTAL PZ</p>
                </div>
            </div>
        </div>
    </div>


	<div class="row">
	    <div class="col-lg-12">
	    	<div id="demo-panel-network" class="panel">
	            <div class="panel-heading">
	                <h3 class="panel-title">TRANSCURSO - TRAILER</h3>
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
	var total_preventa 	= 0;
	var total_mostrador = 0;
	var array_item 		= [];
	var pz_transcurso 	= 0;
	var pz_bodega 		= 0;
	var pz_reparto 		= 0;
	var pz_entregado 	= 0;


	$(document).ready(function(){
		load_ini();
	});

	$filterArray.change(function(){
		load_ini();
	});



	var load_ini = function(){
		array_item = [];

		$.get("<?= Url::to(['reporte-data-trailer-info'])  ?>",{ filters: $filterArray.serialize() },function($response){
			if ($response) {

				$('.lbl_venta_money').html( ($response.trailer.total ? btf.conta.money(parseFloat($response.trailer.total)) : 0));
				$('.lbl_venta_lb').html(($response.trailer.total_lb ? parseFloat($response.trailer.total_lb).toFixed(2) : 0));
				$('.lbl_venta_pz').html(($response.trailer.total_pz ? $response.trailer.total_pz :0 ));

				$.get("<?= Url::to(['reporte-data-trailer'])  ?>",{ filters: $filterArray.serialize() },function($response2){

					if ($response2) {

						pz_transcurso	= $response2.transcurso.pz_venta ? parseInt($response2.transcurso.pz_venta)  : 0;
						pz_bodega		= $response2.bodega.pz_venta ? parseInt($response2.bodega.pz_venta)  : 0;
						pz_reparto		= $response2.reparto.pz_venta ? parseInt($response2.reparto.pz_venta)  : 0;
						pz_entregado	= $response2.entregado.pz_venta ? parseInt($response2.entregado.pz_venta)  : 0;

						console.log(pz_transcurso);


						chart_pie();
					}
				},'json');



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
	            name: 'TRANSCURSO - '+ pz_transcurso+ " pz",
	            y: pz_transcurso,
	            sliced: true,
	            selected: true
	        }, {
	            name: 'BODEGA - '+ pz_bodega+ " pz",
	            y: pz_bodega
	        }, {
	            name: 'REPARTO - '+ pz_reparto+ " pz",
	            y: pz_reparto
	        }, {
	            name: 'ENTREGADO - '+ pz_entregado+ " pz",
	            y: pz_entregado
	        }]
	    }]
	});
}

</script>


