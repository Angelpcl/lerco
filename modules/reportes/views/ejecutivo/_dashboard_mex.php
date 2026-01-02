<?php
use yii\helpers\Url;
use app\assets\HighchartsAsset;
use kartik\daterange\DateRangePicker;

HighchartsAsset::register($this);

?>

<div class="reporte-documentacion-index">
	<br>
	<div id="demo-panel-network" class="panel col-lg-12">
        <div class="panel-heading">
            <div class="btt-toolbar filter-top">
		        <div class="panel mar-btm-5px">
		            <div id="toolbar-panel-collapse" class="collapse in" aria-expanded="false">
		                <div class="panel-body pad-btm-15px">
		                    <div>
		                        <strong class="pad-rgt">Filtrar:</strong>
		                         <div class="DateRangePicker   kv-drp-dropdown  ">
				                    <?= DateRangePicker::widget([
				                        'name'           => 'date_range',
				                        'presetDropdown' => true,
				                        'hideInput'      => true,
				                        'value'=> date('Y-m').'-01 - '. date('Y') .'-'.date('m').'-' . date("d",(mktime(0,0,0,date('m') + 1,1,date('Y'))-1)),
				                        'useWithAddon'   => true,
				                        'convertFormat'  => true,
				                        'pluginOptions'  => [
				                            'locale' => [
				                                'format'    => 'Y-m-d',
				                                'separator' => ' - ',
				                            ],
				                            'opens' => 'left',
				                            "autoApply" => true,
				                        ],
				                    ])
				                    ?>
				                </div>
				            </div>
		                </div>
		            </div>
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
	                <h3 class="panel-title">MEX</h3>
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
	var pz_porpagar 	= 0;
	var pz_entregado 	= 0;
	var pz_pagoparcial 	= 0;


	$(document).ready(function(){
		load_ini();
	});

	$filterArray.change(function(){
		load_ini();
	});



	var load_ini = function(){
		array_item = [];

		$.get("<?= Url::to(['reporte-data-mx'])  ?>",{ filters: $filterArray.serialize() },function($response){
			if ($response) {

				$('.lbl_venta_money').html( ($response.trailer.total ? btf.conta.money(parseFloat($response.trailer.total)) : 0));
				$('.lbl_venta_lb').html(($response.trailer.total_lb ? parseFloat($response.trailer.total_lb).toFixed(2) : 0));
				$('.lbl_venta_pz').html(($response.trailer.total_pz ? $response.trailer.total_pz :0 ));

				$.get("<?= Url::to(['reporte-mx'])  ?>",{ filters: $filterArray.serialize() },function($response2){

					if ($response2) {

						pz_entregado= $response2.entregado.total ? parseInt($response2.entregado.total)  : 0;
						pz_porpagar = $response2.porpagar.total ? parseInt($response2.porpagar.total)  : 0;

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
	            name: 'ENTREGADO - $'+ pz_entregado,
	            y: pz_entregado,
	            sliced: true,
	            selected: true
	        }, {
	            name: 'PAGO PARCIAL - $'+ pz_pagoparcial,
	            y: pz_pagoparcial
	        }, {
	            name: 'POR PAGAR - $'+ pz_porpagar,
	            y: pz_porpagar
	        }]
	    }]
	});
}

</script>


