<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use app\assets\BootstrapTableAsset;
use app\models\user\User;
use app\models\envio\Envio;

HighchartsAsset::register($this);
BootstrapTableAsset::register($this);

$this->title = 'Envios / Mex';
$this->params['breadcrumbs'][] = 'Reportes Ejecutivo';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="reporte-ejecutivo-index">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel">
				<div class="panel-heading">
	                <div class="panel-control">
	                    <ul class="nav nav-tabs">
	                    	<li class="active ">
					            <a data-toggle="tab" href="#tab-index">DASHBOARD</a>
					        </li>
	                        <li>
					            <a data-toggle="tab" href="#tab-entregado">ENTREGADOS / PAGADOS</a>
					        </li>
					        <li>
					            <a data-toggle="tab" href="#tab-pago-parcial">PAGO PARCIAL</a>
					        </li>
					        <li>
					            <a data-toggle="tab" href="#tab-por-pagar">POR PAGAR</a>
					        </li>
	                    </ul>
	                </div>
	                <h3 class="panel-title">Envios Mex </h3>
	            </div>

			    <div class="tab-content">
			        <div id="tab-index" class="tab-pane fade active in">
			            <?= $this->render('_dashboard_mex') ?>
			        </div>
			        <div id="tab-entregado" class="tab-pane fade">
			            <?= $this->render('paquetes_entregados_mex') ?>
			        </div>
			        <div id="tab-pago-parcial" class="tab-pane fade">
			        	<?= $this->render('paquetes_pagado_bodega_mex') ?>
		            </div>
		            <div id="tab-por-pagar" class="tab-pane fade">
			        	<?= $this->render('por_pagar') ?>
		            </div>
			    </div>
		    </div>
		</div>
	</div>
</div>
