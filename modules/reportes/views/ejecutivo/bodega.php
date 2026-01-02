<?php
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use app\assets\BootstrapTableAsset;
use app\models\user\User;
use app\models\viaje\Viaje;

HighchartsAsset::register($this);
BootstrapTableAsset::register($this);

$this->title = 'Almacen / Trancurso mx';
$this->params['breadcrumbs'][] = 'Reportes Ejecutivo';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="reporte-bodega-index">
	<div class="row">
	    <div class="col-lg-12">
	        <div id="demo-panel-network" class="panel col-lg-12">
	            <div class="panel-heading">
	                <h3 class="panel-title">Filtros</h3>
	                <div class="btt-toolbar filter-top">
				        <div class="panel mar-btm-5px">
				           	<div class="panel-heading">
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
				            <div id="toolbar-panel-collapse" class="collapse in" aria-expanded="false">
				                <div class="panel-body pad-btm-15px">
				                    <div>
				                        <strong class="pad-rgt">Filtrar:</strong>

				                        <?=  Html::dropDownList('sucursal_id', null, User::getSucursalesMex() , ['prompt' => 'Sucursal Receptor', 'class' => 'max-width-170px'])  ?>

				                        <?=  Html::dropDownList('viaje_id', null, Viaje::getItems() , ['class' => 'max-width-170px','style' =>'display:none'])  ?>
				                    </div>
			                     	<div class="mar-top">
				                        <strong class="pad-rgt">Agrupar:</strong>
			                          	<?= Html::checkbox("agrupar[sucursal]", false, ["id" => "agrupar-sucursal", "class" => "magic-checkbox"]) ?>

			                            <?= Html::label("Sucursal", "agrupar-sucursal", ["style" => "display:inline"]) ?>

			                            <?= Html::checkbox("agrupar[agente]", false, ["id" => "agrupar-agente", "class" => "magic-checkbox"]) ?>

			                            <?= Html::label("Agente", "agrupar-agente", ["style" => "display:inline"]) ?>

			                            <?= Html::checkbox("agrupar[dia]", false, ["id" => "agrupar-dia", "class" => "magic-checkbox"]) ?>

			                            <?= Html::label("Dia", "agrupar-dia", ["style" => "display:inline"]) ?>

			                            <?= Html::checkbox("agrupar[mes]", false, ["id" => "agrupar-mes", "class" => "magic-checkbox"]) ?>

			                            <?= Html::label("Mes", "agrupar-mes", ["style" => "display:inline"]) ?>

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
							            <a data-toggle="tab" href="#tab-bodega">Bodega LA</a>
							        </li>
							        <li>
							            <a data-toggle="tab" href="#tab-transcurso">Transcurso</a>
							        </li>
			                    </ul>
			                </div>
			                <h3 class="panel-title">Paquetes</h3>
			            </div>

					    <div class="tab-content">
					        <div id="tab-index" class="tab-pane fade active in">
					            <?= $this->render('_dashboard_bodega') ?>
					        </div>
					        <div id="tab-bodega" class="tab-pane fade">
					        	<?= $this->render('_reporte_bodega') ?>
				            </div>
				            <div id="tab-transcurso" class="tab-pane fade">
					        	<?= $this->render('_reporte_descarga') ?>
				            </div>
					    </div>
				    </div>
				</div>
			</div>
	    </div>
	</div>
</div>
