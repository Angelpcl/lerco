<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use app\assets\FlotAsset;
use app\models\pais\PaisesLatam;

FlotAsset::register($this);

$this->title = '';
$PAISES = PaisesLatam::find()->all();

?>
<?php if (!isset(Yii::$app->user->identity)): ?>

    <div class="site-index">

        <div class="jumbotron">
            <h1>Paqueteria</h1>
        </div>
    </div>

<?php else: ?>


    <div class="wrapper wrapper-content">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h3 class="display-4 font-weight-bold mb-3" style="color: #333;">¿A dónde deseas enviar?</h3>
                <p class="lead" style="color: #666;">Selecciona un país para proceder con el envío.</p>
            </div>
        </div>

      

        <div class="row">
            <?php foreach ($PAISES as $pais): ?>
                <?php if (strtoupper($pais->nombre) !== strtoupper('United States')): // Excluir Estados Unidos 
                ?>
                    <div class="col-md-4 text-center">
                        <a href="<?= Url::to(['operacion/envio/create', 'pais' => $pais->id]) ?>" class="d-block">
                            <?php if (isset($pais->imagen) && !empty($pais->imagen)) : ?>
                                <?= Html::img('@web/uploads/flags/' . $pais->imagen, [
                                    'alt' => $pais->nombre,
                                    'class' => 'img-flag btn btn-light border shadow-sm',
                                    'style' => 'border-radius: 5px; width: 100%; max-width: 300px; height: 180px; object-fit: cover; border: 2px solid #ddd;'
                                ]) ?>
                            <?php else : ?>
                                <?= Html::img('@web/uploads/flags/default.jpeg', [
                                    'alt' => $pais->nombre,
                                    'class' => 'img-flag btn btn-light border shadow-sm',
                                    'style' => 'border-radius: 5px; width: 100%; max-width: 300px; height: 180px; object-fit: cover; border: 2px solid #ddd;'
                                ]) ?>
                            <?php endif; ?>
                        </a>
                        <p class="mt-2 font-weight-bold" style="color: #444;"><?= Html::encode($pais->nombre) ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>


        <div class="text-center mb-4">
            <?= "" // Html::a('CREAR NUEVO ENVÍO', ['operacion/envio/create'], ['class' => 'btn btn-primary btn-lg']) 
            ?>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <span class="label label-success float-right">LIBRAS</span>
                        <h5>SUCURSAL</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">0</h1>
                        <div class="stat-percent font-bold text-success">0% <i class="fa fa-bolt"></i></div>
                        <small>Total</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <span class="label label-info float-right">LIBRAS</span>
                        <h5>BODEGA</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">0</h1>
                        <div class="stat-percent font-bold text-info">0% <i class="fa fa-level-up"></i></div>
                        <small>Total</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <span class="label label-primary float-right">LIBRAS</span>
                        <h5>TRANS.</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">0</h1>
                        <div class="stat-percent font-bold text-navy">0% <i class="fa fa-level-up"></i></div>
                        <small>Total</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <span class="label label-danger float-right">LIBRAS</span>
                        <h5>REPARTO</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">0</h1>
                        <div class="stat-percent font-bold text-danger">0% <i class="fa fa-level-down"></i></div>
                        <small>Total</small>
                    </div>
                </div>
            </div>
        </div>








        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>ENVIOS</h5>
                        <div class="float-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-outline-secondary active">Hoy</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary">Mensual</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary">Anual</button>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-9">
                                <div class="flot-chart">
                                    <div class="flot-chart-content" id="flot-dashboard-chart"></div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <ul class="stat-list">
                                    <li>
                                        <h2 class="no-margins">2,346</h2>
                                        <small>Total de envio TIERRA</small>
                                        <div class="stat-percent">48% <i class="fa fa-level-up text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 48%;" class="progress-bar bg-primary"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <h2 class="no-margins">4,422</h2>
                                        <small>Total de envio AIRE</small>
                                        <div class="stat-percent">60% <i class="fa fa-level-down text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 60%;" class="progress-bar bg-success"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <h2 class="no-margins">9,180</h2>
                                        <small>Preventa</small>
                                        <div class="stat-percent">22% <i class="fa fa-bolt text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 22%;" class="progress-bar bg-warning"></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <script>
        document.body.style.zoom = "90%";

        $(document).ready(function() {
            $('.chart').easyPieChart({
                barColor: '#f8ac59',
                //                scaleColor: false,
                scaleLength: 5,
                lineWidth: 4,
                size: 80
            });

            $('.chart2').easyPieChart({
                barColor: '#1c84c6',
                //                scaleColor: false,
                scaleLength: 5,
                lineWidth: 4,
                size: 80
            });

            var data2 = [
                [gd(2012, 1, 1), 7],
                [gd(2012, 1, 2), 6],
                [gd(2012, 1, 3), 4],
                [gd(2012, 1, 4), 8],
                [gd(2012, 1, 5), 9],
                [gd(2012, 1, 6), 7],
                [gd(2012, 1, 7), 5],
                [gd(2012, 1, 8), 4],
                [gd(2012, 1, 9), 7],
                [gd(2012, 1, 10), 8],
                [gd(2012, 1, 11), 9],
                [gd(2012, 1, 12), 6],
                [gd(2012, 1, 13), 4],
                [gd(2012, 1, 14), 5],
                [gd(2012, 1, 15), 11],
                [gd(2012, 1, 16), 8],
                [gd(2012, 1, 17), 8],
                [gd(2012, 1, 18), 11],
                [gd(2012, 1, 19), 11],
                [gd(2012, 1, 20), 6],
                [gd(2012, 1, 21), 6],
                [gd(2012, 1, 22), 8],
                [gd(2012, 1, 23), 11],
                [gd(2012, 1, 24), 13],
                [gd(2012, 1, 25), 7],
                [gd(2012, 1, 26), 9],
                [gd(2012, 1, 27), 9],
                [gd(2012, 1, 28), 8],
                [gd(2012, 1, 29), 5],
                [gd(2012, 1, 30), 8],
                [gd(2012, 1, 31), 25]
            ];

            var data3 = [
                [gd(2012, 1, 1), 800],
                [gd(2012, 1, 2), 500],
                [gd(2012, 1, 3), 600],
                [gd(2012, 1, 4), 700],
                [gd(2012, 1, 5), 500],
                [gd(2012, 1, 6), 456],
                [gd(2012, 1, 7), 800],
                [gd(2012, 1, 8), 589],
                [gd(2012, 1, 9), 467],
                [gd(2012, 1, 10), 876],
                [gd(2012, 1, 11), 689],
                [gd(2012, 1, 12), 700],
                [gd(2012, 1, 13), 500],
                [gd(2012, 1, 14), 600],
                [gd(2012, 1, 15), 700],
                [gd(2012, 1, 16), 786],
                [gd(2012, 1, 17), 345],
                [gd(2012, 1, 18), 888],
                [gd(2012, 1, 19), 888],
                [gd(2012, 1, 20), 888],
                [gd(2012, 1, 21), 987],
                [gd(2012, 1, 22), 444],
                [gd(2012, 1, 23), 999],
                [gd(2012, 1, 24), 567],
                [gd(2012, 1, 25), 786],
                [gd(2012, 1, 26), 666],
                [gd(2012, 1, 27), 888],
                [gd(2012, 1, 28), 900],
                [gd(2012, 1, 29), 178],
                [gd(2012, 1, 30), 555],
                [gd(2012, 1, 31), 993]
            ];


            var dataset = [{
                label: "Number of orders",
                data: data3,
                color: "#1ab394",
                bars: {
                    show: true,
                    align: "center",
                    barWidth: 24 * 60 * 60 * 600,
                    lineWidth: 0
                }

            }, {
                label: "Payments",
                data: data2,
                yaxis: 2,
                color: "#1C84C6",
                lines: {
                    lineWidth: 1,
                    show: true,
                    fill: true,
                    fillColor: {
                        colors: [{
                            opacity: 0.2
                        }, {
                            opacity: 0.4
                        }]
                    }
                },
                splines: {
                    show: false,
                    tension: 0.6,
                    lineWidth: 1,
                    fill: 0.1
                },
            }];


            var options = {
                xaxis: {
                    mode: "time",
                    tickSize: [3, "day"],
                    tickLength: 0,
                    axisLabel: "Date",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Arial',
                    axisLabelPadding: 10,
                    color: "#d5d5d5"
                },
                yaxes: [{
                    position: "left",
                    max: 1070,
                    color: "#d5d5d5",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Arial',
                    axisLabelPadding: 3
                }, {
                    position: "right",
                    clolor: "#d5d5d5",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: ' Arial',
                    axisLabelPadding: 67
                }],
                legend: {
                    noColumns: 1,
                    labelBoxBorderColor: "#000000",
                    position: "nw"
                },
                grid: {
                    hoverable: false,
                    borderWidth: 0
                }
            };

            function gd(year, month, day) {
                return new Date(year, month - 1, day).getTime();
            }

            var previousPoint = null,
                previousLabel = null;

            $.plot($("#flot-dashboard-chart"), dataset, options);

            var mapData = {
                "US": 298,
                "SA": 200,
                "DE": 220,
                "FR": 540,
                "CN": 120,
                "AU": 760,
                "BR": 550,
                "IN": 200,
                "GB": 120,
            };

            $('#world-map').vectorMap({
                map: 'world_mill_en',
                backgroundColor: "transparent",
                regionStyle: {
                    initial: {
                        fill: '#e4e4e4',
                        "fill-opacity": 0.9,
                        stroke: 'none',
                        "stroke-width": 0,
                        "stroke-opacity": 0
                    }
                },

                series: {
                    regions: [{
                        values: mapData,
                        scale: ["#1ab394", "#22d6b1"],
                        normalizeFunction: 'polynomial'
                    }]
                },
            });
        });
    </script>

<?php endif ?>