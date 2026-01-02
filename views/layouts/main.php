<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\widgets\Alert;
use app\assets\AppAsset;

$this->registerLinkTag(['rel' => 'shortcut icon', 'href' => Url::to(Yii::$app->params['settings']['img-ico']), 'type' => "image/x-icon"]);
$this->registerLinkTag(['rel' => 'icon', 'href' => Url::to(Yii::$app->params['settings']['img-ico']), 'type' => "image/x-icon"]);
// CSS del datepicker
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css');

// JS del datepicker (después de jQuery)
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js', [
    'depends' => [\yii\web\JqueryAsset::class]
]);

AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;subset=latin" rel="stylesheet">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title . ($this->title? ' | ': '') . Yii::$app->name) ?></title>
        <?php $this->head() ?>
    </head>
    <body data-url-root="<?= Url::home(true) ?>" class="top-navigation" >
        <?php $this->beginBody() ?>

        <div id="wrapper">
            <div id="page-wrapper" class="white-bg">
                <div class="row border-bottom ">
                    <nav class="navbar navbar-expand-lg navbar-static-top" role="navigation">

                          <!--<a href="<?= Url::home(true) ?>" class="navbar-brand"><?= Yii::$app->name ?></a>-->
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-label="Toggle navigation">
                            <i class="fa fa-reorder"></i>
                        </button>

                        <!--<div class="navbar-collapse collapse"  id="navbar">
                                <?= Yii::$app->nifty->get_menu() ?>
                        </div>-->
                            <div class="navbar-collapse collapse" id="navbar" style="display: flex; justify-content: space-between; align-items: center; width: 100%; background-color: #040c33;">
                            <!-- Logo y menú si es admin -->
                            <div style="display: flex; align-items: center;">
                                <div style="font-size: 40px; font-weight: bold; color: #0f1a2b; margin-right: 30px;  padding: 10px; border-radius: 5px;">
                                        <img src="<?= Url::to('@web/img/lerco_logo.png') ?>" alt="Logo" style="width: 150px; height: auto;">
                                </div>

                                <?php if (Yii::$app->user->can('ticketView')): ?>
                                    <!-- Menú admin -->
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color:rgb(255, 255, 255); font-weight: bold; font-size: 20px;">
                                            <i class="fa fa-tasks" aria-hidden="true"></i> <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?= Url::to(['/operacion/ticket/index']) ?>"><i class="fa fa-ticket"></i> Tickets</a></li>
                                            <li><a href="<?= Url::to(['/admin/user/index']) ?>"><i class="fa fa-users"></i> Usuarios</a></li>
                                            <li><a href="<?= Url::to(['/operacion/ticket/index-proyectos']) ?>"><i class="fa fa-desktop"></i> Proyectos</a></li>
                                            <li><a href="<?= Url::to(['/operacion/ticket/index-productos']) ?>"><i class="fa fa-folder-open"></i> Productos</a></li>
                                            <li><a href="<?= Url::to(['/operacion/ticket/index-clientes']) ?>"><i class="fa fa-handshake-o"></i> Empresas</a></li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Íconos a la derecha -->
                            <div style="display: flex; align-items: center;">
                                
                                <?= Html::a(
                                    '<i class="fa fa-sign-out" style="font-size: 30px; color:white; margin-right:20px;"></i>',
                                    ['/admin/user/logout'],
                                    ['data-method' => 'post', 'style' => 'color: black;', 'title' => 'Cerrar sesión']
                                ) ?>
                            </div>
                        </div>      
                    </nav>
                </div>


               <?php /* ?>
                <div class="row border-bottom">
                    <nav class="navbar navbar-static-top  " role="navigation" style="margin-bottom: 0">
                        <?= Yii::$app->nifty->get_notification_dropdown() ?>
                    </nav>
                </div>

                <div class="row wrapper border-bottom white-bg page-heading" style="margin-bottom: 1%;">
                    <div class="col-sm-4" id = "page-title">
                        <h2><?=$this->title?></h2>
                         <?= Breadcrumbs::widget([
                            'homeLink' => [
                                'label' => Yii::t('yii', 'Inicio'),
                                'url'   => Yii::$app->homeUrl,
                            ],
                            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                            'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
                            'activeItemTemplate' => "<li class=\"breadcrumb-item \"><strong>{link}</strong></li>",
                            'tag' => 'ol',
                        ]) ?>
                    </div>
                </div>
                */?>

                <div class="row wrapper border-bottom white-bg page-heading" >
                    <div class="col-sm-4" id = "page-title">
                        <h2><?=$this->title?></h2>
                         <?= Breadcrumbs::widget([
                            'homeLink' => [
                                'label' => Yii::t('yii', 'Inicio'),
                                'url'   => Yii::$app->homeUrl,
                            ],
                            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                            'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
                            'activeItemTemplate' => "<li class=\"breadcrumb-item \"><strong>{link}</strong></li>",
                            'tag' => 'ol',
                        ]) ?>
                    </div>
                </div>

                <div id="page-content" class=" wrapper wrapper-content animated fadeInRight">
                  <!--  <?= Alert::widget(); ?>-->
                    <?= $content ?>
                </div>
                <div id="footer" class="footer">
                    <div class="float-right">Versión <strong><?=Yii::$app->version?></strong></div>
                    <div>
                        <p class="text-sm">Powered by <strong><a target='_blank' href="http://lerco.mx">Lerco solutions</a></strong>
                            &#0169; <?= date('Y') . ' ' . Yii::$app->name?>
                        </p>
                    </div>
                </div>

            </div>
        </div>

        
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
