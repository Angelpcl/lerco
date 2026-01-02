<?php
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use app\assets\BootstrapTableAsset;
use app\models\user\User;
use app\models\viaje\Viaje;

HighchartsAsset::register($this);
BootstrapTableAsset::register($this);

$this->title = 'Transcurso mx / Bodega mx';
$this->params['breadcrumbs'][] = 'Reportes Ejecutivo';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="reporte-descarga-index">
	<div class="row">
	    <div class="col-lg-12">
	        <div id="demo-panel-network" class="panel col-lg-12">
	            <div class="panel-heading">
	                <h3 class="panel-title">Filtros</h3>
	                <div class="btt-toolbar filter-top">
				        <div class="panel mar-btm-5px">
				            <div id="toolbar-panel-collapse" class="collapse in" aria-expanded="false">
				                <div class="panel-body pad-btm-15px">
				                    <div>
				                        <strong class="pad-rgt">Filtrar:</strong>

				                        <?=  Html::dropDownList('sucursal_id', null, User::getSucursalesMex() , ['prompt' => 'Sucursal Receptor', 'class' => 'max-width-170px'])  ?>

				                        <?=  Html::dropDownList('viaje_id', null, Viaje::getItems() , ['class' => 'max-width-170px'])  ?>

				                    </div>
			                     	<div class="mar-top">
				                        <strong class="pad-rgt">Agrupar:</strong>
			                          	<?= Html::checkbox("agrupar[sucursal]", false, ["id" => "agrupar-sucursal", "class" => "magic-checkbox"]) ?>

			                            <?= Html::label("Sucursal", "agrupar-sucursal", ["style" => "display:inline"]) ?>

				                    </div>
				                </div>
				            </div>
				        </div>
					</div>
	            </div>
	        </div>
	        <br>
			<div class="row">
				<div class="col-lg-12">
					<div class="panel">
						<div class="panel-heading">
			                <div class="panel-control">
			                    <ul class="nav nav-tabs">
			                        <li class="active ">
							            <a data-toggle="tab" href="#tab-index">Dashboard</a>
							        </li>
							        <li>
							            <a data-toggle="tab" href="#tab-reporte-descarga">Reportes</a>
							        </li>
							        <li>
							            <a data-toggle="tab" href="#tab-reporte-adeudo"> Paquetes / Adeudo</a>
							        </li>
							        <li>
							            <a data-toggle="tab" href="#tab-reporte-checkList"> Paquetes / Check list (500 - 720)</a>
							        </li>
			                    </ul>
			                </div>
			                <h3 class="panel-title">Paquetes</h3>
			            </div>

					    <div class="tab-content">
					        <div id="tab-index" class="tab-pane fade active in">
					            <?= $this->render('_dashboard_descarga') ?>
					        </div>
					        <div id="tab-reporte-descarga" class="tab-pane fade">
					        	<?= $this->render('_reporte_descarga') ?>
				            </div>
				            <div id="tab-reporte-adeudo" class="tab-pane fade">
					        	<?= $this->render('_reporte_adeuda') ?>
				            </div>
				            <div id="tab-reporte-checkList" class="tab-pane fade">
					        	<?= $this->render('_reporte_check_list') ?>
				            </div>
					    </div>
				    </div>
				</div>
			</div>
	    </div>
	</div>
</div>
