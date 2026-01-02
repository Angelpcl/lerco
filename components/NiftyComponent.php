<?php

namespace app\components;

use Yii;
use yii\helpers\Url;
use yii\base\Component;
use yii\helpers\Html;
use yii\widgets\Menu;
use app\models\user\User;
use app\models\Esys;

class NiftyComponent extends Component
{
	private $menuItems;

	public function __construct($config = [])
	{
		// ... initialization before configuration is applied

		parent::__construct($config);
	}


	public function init()
	{
		parent::init();

		if (!isset(Yii::$app->user->identity)) {
			$this->menuItems[] = ['label' =>  Yii::$app->name, 'options' => ['class' => 'list-header']];
			$this->menuItems[] = ['label' => '<i class="fa fa-lock"></i><span class="menu-title">Iniciar sesión</span>', 'url' => ['/admin/user/login']];
			$this->menuItems[] = ['label' => '<i class="fa fa-lock"></i><span class="menu-title">¿Olvidaste tu contraseña?</span>', 'url' => ['/admin/user/request-password-reset']];
		} else {
			
			

			$operacionSeguimiento = [];


			if (Yii::$app->user->can('ticketView'))
				$operacionSeguimiento[] = ['label' => '<i class="fa fa-ticket" aria-hidden="true"></i> Tickets ', 'url' => ['/operacion/ticket/index'], 'template' => '<a href="{url}">{label}</a>'];

			if (Yii::$app->user->can('ticketView'))
				$operacionSeguimiento[] = ['label' => '<i class="fa fa-users" aria-hidden="true"></i> Usuarios ', 'url' => ['/admin/user/index'], 'template' => '<a href="{url}">{label}</a>'];

			if (Yii::$app->user->can('ticketView'))
				$operacionSeguimiento[] = ['label' => '<i class="fa fa-desktop" aria-hidden="true"></i> Proyectos', 'url' => ['/operacion/ticket/index-proyectos'], 'template' => '<a href="{url}">{label}</a>'];

			if (Yii::$app->user->can('ticketView'))
				$operacionSeguimiento[] = ['label' => '<i class="fa fa-folder-open" aria-hidden="true"></i> Productos ', 'url' => ['/operacion/ticket/index-productos'], 'template' => '<a href="{url}">{label}</a>'];
			
			if (Yii::$app->user->can('ticketView'))
				$operacionSeguimiento[] = ['label' => '<i class="fa fa-handshake-o" aria-hidden="true"></i> Empresas', 'url' => ['/operacion/ticket/index-clientes'], 'template' => '<a href="{url}">{label}</a>'];

			/*if(Yii::$app->user->can('showAperturaCierre'))
					$envio[] = ['label' => '<i class="fa fa-shopping-basket"></i><span class="nav-label">Aperturas y Cierres </span>', 'url' => ['/caja/apertura-cierre/index']];
				*/

			/*********************************
			 *	Logistica y Transporte
			 **********************************/
			$logistica = [];
			$logisticaRuta = [];

			if (Yii::$app->user->can('viajeMexView'))
				$logisticaRuta[] = ['label' => '<i class="fa fa-bus"></i><span style="margin-left: 5px;" >TICKETS</span>', 'url' => ['/logistica/viaje-mex/index'], 'template' => '<a href="{url}">{label}</a>'];
		
			/*****************************
			 * Menú Items
			 *****************************/
			
			?>
		
			
		<div style="position: fixed; top: 0; right: 0; z-index: 1000; background-color: #fff; padding: 10px;">
			<?php
			if (Yii::$app->user->can('seguimiento')) {
				// Menú desplegable de opciones
				$envio[] = [
					'label' => '<i class="fa fa-tasks" aria-hidden="true"></i> <span class="nav-label">OPCIONES</span> <span class="fa fa-caret-down"></span>',
					'url' => '#',
					'items' => $operacionSeguimiento,
					'submenuTemplate' => "\n<ul role='menu' class='dropdown-menu' style='position: absolute; top: 100%; right: 0; '>\n{items}\n</ul>\n",
					'options' => ['class' => 'dropdown']
				];
			}

			if (!empty($envio)) {
				foreach ($envio as $key => $item) {
					$this->menuItems[] = $item;
				}
			}

			// Enlace para cerrar sesión
			echo Html::a('<i class="fa fa-sign-out"></i>', ['/admin/user/logout'], ['data-method' => 'post']);
			?>
		</div>

<?php
			
		}
	}


	/*********************************
	/ Navigation Bar - Elements Template
	/********************************/
	public function get_notification_dropdown()
	{
		if (!isset(Yii::$app->user->identity))
			return false;
		ob_start();
?>
		<div class="navbar-header">
			<a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
			<form role="search" class="navbar-form-custom" action="search_results.html">
				<div class="form-group">
					<input type="text" placeholder="Search for something..." class="form-control" name="top-search" id="top-search">
				</div>
			</form>
		</div>
		<ul class="nav navbar-top-links navbar-right">
			<li>
				<span class="m-r-sm text-muted welcome-message">Bienvenido a <?= Yii::$app->name ?>.</span>
			</li>
			<li class="dropdown">
				<a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
					<i class="fa fa-envelope"></i> <span class="label label-warning">16</span>
				</a>
				<ul class="dropdown-menu dropdown-messages">
					<li>
						<div class="dropdown-messages-box">
							<a class="dropdown-item float-left" href="profile.html">
								<img alt="image" class="rounded-circle" src="img/profile-photos/1.png">
							</a>
							<div class="media-body">
								<small class="float-right">46h ago</small>
								<strong>Mike Loreipsum</strong> started following <strong>Monica Smith</strong>. <br>
								<small class="text-muted">3 days ago at 7:58 pm - 10.06.2014</small>
							</div>
						</div>
					</li>
					<li class="dropdown-divider"></li>
					<li>
						<div class="dropdown-messages-box">
							<a class="dropdown-item float-left" href="profile.html">
								<img alt="image" class="rounded-circle" src="img/profile-photos/1.png">
							</a>
							<div class="media-body ">
								<small class="float-right text-navy">5h ago</small>
								<strong>Chris Johnatan Overtunk</strong> started following <strong>Monica Smith</strong>. <br>
								<small class="text-muted">Yesterday 1:21 pm - 11.06.2014</small>
							</div>
						</div>
					</li>
					<li class="dropdown-divider"></li>
					<li>
						<div class="dropdown-messages-box">
							<a class="dropdown-item float-left" href="profile.html">
								<img alt="image" class="rounded-circle" src="img/profile-photos/1.png">
							</a>
							<div class="media-body ">
								<small class="float-right">23h ago</small>
								<strong>Monica Smith</strong> love <strong>Kim Smith</strong>. <br>
								<small class="text-muted">2 days ago at 2:30 am - 11.06.2014</small>
							</div>
						</div>
					</li>
					<li class="dropdown-divider"></li>
					<li>
						<div class="text-center link-block">
							<a href="mailbox.html" class="dropdown-item">
								<i class="fa fa-envelope"></i> <strong>Read All Messages</strong>
							</a>
						</div>
					</li>
				</ul>
			</li>
			<li class="dropdown">
				<a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
					<i class="fa fa-bell"></i> <span class="label label-primary">8</span>
				</a>
				<ul class="dropdown-menu dropdown-alerts">
					<li>
						<a href="mailbox.html" class="dropdown-item">
							<div>
								<i class="fa fa-envelope fa-fw"></i> You have 16 messages
								<span class="float-right text-muted small">4 minutes ago</span>
							</div>
						</a>
					</li>
					<li class="dropdown-divider"></li>
					<li>
						<a href="profile.html" class="dropdown-item">
							<div>
								<i class="fa fa-twitter fa-fw"></i> 3 New Followers
								<span class="float-right text-muted small">12 minutes ago</span>
							</div>
						</a>
					</li>
					<li class="dropdown-divider"></li>
					<li>
						<a href="grid_options.html" class="dropdown-item">
							<div>
								<i class="fa fa-upload fa-fw"></i> Server Rebooted
								<span class="float-right text-muted small">4 minutes ago</span>
							</div>
						</a>
					</li>
					<li class="dropdown-divider"></li>
					<li>
						<div class="text-center link-block">
							<a href="notifications.html" class="dropdown-item">
								<strong>See All Alerts</strong>
								<i class="fa fa-angle-right"></i>
							</a>
						</div>
					</li>
				</ul>
			</li>
			<li>
				<?= Html::a('<i class="fa fa-sign-out"></i> Cerrar sesión', ['/admin/user/logout'], ['data-method' => 'post']) ?>
			</li>
		</ul>
	<?php
		return ob_get_clean();
	}

	public function get_mega_dropdown()
	{
		if (!isset(Yii::$app->user->identity))
			return false;

		ob_start();
	?>
		<li class="mega-dropdown">
			<a href="#" class="mega-dropdown-toggle">
				<i class="fa fa-th-large fa-lg"></i>
			</a>
			<div class="dropdown-menu mega-dropdown-menu">
				<div class="clearfix">

				</div>
			</div>
		</li>

	<?php

		return ob_get_clean();
	}

	public function get_language_selector()
	{
		ob_start();
	?>
		<!--Language selector-->
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<!--End language selector-->
	<?php

		return ob_get_clean();
	}

	public function get_user_dropdown()
	{
		if (!isset(Yii::$app->user->identity))
			return false;

		ob_start();
	?>
		<li id="dropdown-user" class="dropdown">
			<a href="#" data-toggle="dropdown" class="dropdown-toggle text-right">
				<span class="pull-right">
					<i class="demo-pli-male ic-user"></i>
				</span>
				<div class="username hidden-xs"><?= Yii::$app->user->identity->email ?></div>
			</a>

			<div class="dropdown-menu dropdown-menu-md dropdown-menu-right panel-default">

				<!-- User dropdown menu -->
				<ul class="head-list">
					<li>
						<?= Html::a('<i class="demo-pli-male icon-lg icon-fw"></i> Mi perfil', ['/admin/user/mi-perfil']) ?>
					</li>
					<li>
						<?= Html::a('<i class="demo-psi-lock-2 icon-lg icon-fw"></i> Cambiar contraseña', ['/admin/user/change-password']) ?>
					</li>
					<li>
						<?= Html::a('<i class="fa fa-code icon-fw"></i> Acerca de . . .', ['/site/about']) ?>
					</li>
				</ul>

				<!-- Dropdown footer -->
				<div class="pad-all text-right">
					<?= Html::a('<i class="fa fa-sign-out fa-fw"></i> Cerrar sesión', ['/admin/user/logout'], [
						'class' => 'btn btn-primary',
						'data-method' => 'post'
					]) ?>
				</div>
			</div>
		</li>
	<?php

		return ob_get_clean();
	}

	public function get_aside()
	{
		if (!isset(Yii::$app->user->identity))
			return false;

		ob_start();
	?>
		<!--Nav tabs-->
		<!--================================-->
		<ul class="nav nav-tabs nav-justified">
			<li class="active">
				<a href="#demo-asd-tab-1" data-toggle="tab">
					<i class="demo-pli-speech-bubble-7"></i>
				</a>
			</li>
			<li>
				<a href="#demo-asd-tab-2" data-toggle="tab">
					<i class="demo-pli-information icon-fw"></i> Report
				</a>
			</li>
			<li>
				<a href="#demo-asd-tab-3" data-toggle="tab">
					<i class="demo-pli-wrench icon-fw"></i> Settings
				</a>
			</li>
		</ul>
		<!--================================-->
		<!--End nav tabs-->



		<!-- Tabs Content -->
		<!--================================-->
		<div class="tab-content">

			<!--First tab (Contact list)-->
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<div class="tab-pane fade in active" id="demo-asd-tab-1">
				<p class="pad-hor mar-top text-semibold text-main">
					<span class="pull-right badge badge-warning">3</span> Family
				</p>

				<!--Family-->
				<div class="list-group bg-trans">
					<a href="#" class="list-group-item">
						<div class="media-left pos-rel">
							<!--
									<img class="img-circle img-xs" src="img/profile-photos/2.png" alt="Profile Picture">
								-->
							<i class="badge badge-success badge-stat badge-icon pull-left"></i>
						</div>
						<div class="media-body">
							<p class="mar-no">Stephen Tran</p>
							<small class="text-muted">Availabe</small>
						</div>
					</a>
					<a href="#" class="list-group-item">
						<div class="media-left pos-rel">
							<!--
									<img class="img-circle img-xs" src="img/profile-photos/7.png" alt="Profile Picture">
								-->
						</div>
						<div class="media-body">
							<p class="mar-no">Brittany Meyer</p>
							<small class="text-muted">I think so</small>
						</div>
					</a>
					<a href="#" class="list-group-item">
						<div class="media-left pos-rel">
							<!--
									<img class="img-circle img-xs" src="img/profile-photos/1.png" alt="Profile Picture">
								-->
							<i class="badge badge-info badge-stat badge-icon pull-left"></i>
						</div>
						<div class="media-body">
							<p class="mar-no">Jack George</p>
							<small class="text-muted">Last Seen 2 hours ago</small>
						</div>
					</a>
					<a href="#" class="list-group-item">
						<div class="media-left pos-rel">
							<!--
									<img class="img-circle img-xs" src="img/profile-photos/4.png" alt="Profile Picture">
								-->
						</div>
						<div class="media-body">
							<p class="mar-no">Donald Brown</p>
							<small class="text-muted">Lorem ipsum dolor sit amet.</small>
						</div>
					</a>
					<a href="#" class="list-group-item">
						<div class="media-left pos-rel">
							<!--
									<img class="img-circle img-xs" src="img/profile-photos/8.png" alt="Profile Picture">
								-->
							<i class="badge badge-warning badge-stat badge-icon pull-left"></i>
						</div>
						<div class="media-body">
							<p class="mar-no">Betty Murphy</p>
							<small class="text-muted">Idle</small>
						</div>
					</a>
					<a href="#" class="list-group-item">
						<div class="media-left pos-rel">
							<!--
									<img class="img-circle img-xs" src="img/profile-photos/9.png" alt="Profile Picture">
								-->
							<i class="badge badge-danger badge-stat badge-icon pull-left"></i>
						</div>
						<div class="media-body">
							<p class="mar-no">Samantha Reid</p>
							<small class="text-muted">Offline</small>
						</div>
					</a>
				</div>

				<hr>
				<p class="pad-hor text-semibold text-main">
					<span class="pull-right badge badge-success">Offline</span> Friends
				</p>

				<!--Works-->
				<div class="list-group bg-trans">
					<a href="#" class="list-group-item">
						<span class="badge badge-purple badge-icon badge-fw pull-left"></span> Joey K. Greyson
					</a>
					<a href="#" class="list-group-item">
						<span class="badge badge-info badge-icon badge-fw pull-left"></span> Andrea Branden
					</a>
					<a href="#" class="list-group-item">
						<span class="badge badge-success badge-icon badge-fw pull-left"></span> Johny Juan
					</a>
					<a href="#" class="list-group-item">
						<span class="badge badge-danger badge-icon badge-fw pull-left"></span> Susan Sun
					</a>
				</div>


				<hr>
				<p class="pad-hor mar-top text-semibold text-main">News</p>

				<div class="pad-hor">
					<p class="text-muted">Lorem ipsum dolor sit amet, consectetuer
						<a data-title="45%" class="add-tooltip text-semibold" href="#">adipiscing elit</a>, sed diam nonummy nibh. Lorem ipsum dolor sit amet.
					</p>
					<small class="text-muted"><em>Last Update : Des 12, 2014</em></small>
				</div>


			</div>
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<!--End first tab (Contact list)-->


			<!--Second tab (Custom layout)-->
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<div class="tab-pane fade" id="demo-asd-tab-2">

				<!--Monthly billing-->
				<div class="pad-all">
					<p class="text-semibold text-main">Billing &amp; reports</p>
					<p class="text-muted">Get <strong>$5.00</strong> off your next bill by making sure your full payment reaches us before August 5, 2016.</p>
				</div>
				<hr class="new-section-xs">
				<div class="pad-all">
					<span class="text-semibold text-main">Amount Due On</span>
					<p class="text-muted text-sm">August 17, 2016</p>
					<p class="text-2x text-thin text-main">$83.09</p>
					<button class="btn btn-block btn-success mar-top">Pay Now</button>
				</div>


				<hr>

				<p class="pad-hor text-semibold text-main">Additional Actions</p>

				<!--Simple Menu-->
				<div class="list-group bg-trans">
					<a href="#" class="list-group-item"><i class="demo-pli-information icon-lg icon-fw"></i> Service Information</a>
					<a href="#" class="list-group-item"><i class="demo-pli-mine icon-lg icon-fw"></i> Usage Profile</a>
					<a href="#" class="list-group-item"><span class="label label-info pull-right">New</span><i class="demo-pli-credit-card-2 icon-lg icon-fw"></i> Payment Options</a>
					<a href="#" class="list-group-item"><i class="demo-pli-support icon-lg icon-fw"></i> Message Center</a>
				</div>


				<hr>

				<div class="text-center">
					<div><i class="demo-pli-old-telephone icon-3x"></i></div>
					Questions?
					<p class="text-lg text-semibold text-main"> (415) 234-53454 </p>
					<small><em>We are here 24/7</em></small>
				</div>
			</div>
			<!--End second tab (Custom layout)-->
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


			<!--Third tab (Settings)-->
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<div class="tab-pane fade" id="demo-asd-tab-3">
				<ul class="list-group bg-trans">
					<li class="pad-top list-header">
						<p class="text-semibold text-main mar-no">Account Settings</p>
					</li>
					<li class="list-group-item">
						<div class="pull-right">
							<input class="toggle-switch" id="demo-switch-1" type="checkbox" checked>
							<label for="demo-switch-1"></label>
						</div>
						<p class="mar-no">Show my personal status</p>
						<small class="text-muted">Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</small>
					</li>
					<li class="list-group-item">
						<div class="pull-right">
							<input class="toggle-switch" id="demo-switch-2" type="checkbox" checked>
							<label for="demo-switch-2"></label>
						</div>
						<p class="mar-no">Show offline contact</p>
						<small class="text-muted">Aenean commodo ligula eget dolor. Aenean massa.</small>
					</li>
					<li class="list-group-item">
						<div class="pull-right">
							<input class="toggle-switch" id="demo-switch-3" type="checkbox">
							<label for="demo-switch-3"></label>
						</div>
						<p class="mar-no">Invisible mode </p>
						<small class="text-muted">Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. </small>
					</li>
				</ul>


				<hr>

				<ul class="list-group pad-btm bg-trans">
					<li class="list-header">
						<p class="text-semibold text-main mar-no">Public Settings</p>
					</li>
					<li class="list-group-item">
						<div class="pull-right">
							<input class="toggle-switch" id="demo-switch-4" type="checkbox" checked>
							<label for="demo-switch-4"></label>
						</div>
						Online status
					</li>
					<li class="list-group-item">
						<div class="pull-right">
							<input class="toggle-switch" id="demo-switch-5" type="checkbox" checked>
							<label for="demo-switch-5"></label>
						</div>
						Show offline contact
					</li>
					<li class="list-group-item">
						<div class="pull-right">
							<input class="toggle-switch" id="demo-switch-6" type="checkbox" checked>
							<label for="demo-switch-6"></label>
						</div>
						Show my device icon
					</li>
				</ul>



				<hr>

				<p class="pad-hor text-semibold text-main mar-no">Task Progress</p>
				<div class="pad-all">
					<p>Upgrade Progress</p>
					<div class="progress progress-sm">
						<div class="progress-bar progress-bar-success" style="width: 15%;"><span class="sr-only">15%</span></div>
					</div>
					<small class="text-muted">15% Completed</small>
				</div>
				<div class="pad-hor">
					<p>Database</p>
					<div class="progress progress-sm">
						<div class="progress-bar progress-bar-danger" style="width: 75%;"><span class="sr-only">75%</span></div>
					</div>
					<small class="text-muted">17/23 Database</small>
				</div>

			</div>
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<!--Third tab (Settings)-->
		</div>
	<?php

		return ob_get_clean();
	}


	/*********************************
	/ MAIN NAVIGATION - Elements Template
	/********************************/
	public function get_profile_widget()
	{
		if (!isset(Yii::$app->user->identity))
			return false;

		ob_start();
	?>

		<li class="nav-header">
			<div class="dropdown profile-element">
				<?= Html::img(User::getAvatar(), ["class" => "rounded-circle", "alt" => "IFNB"]) ?>
				<a data-toggle="dropdown" class="dropdown-toggle" href="#">
					<span class="block m-t-xs font-bold"><?= Yii::$app->user->identity->username ?></span>
					<span class="text-muted text-xs block"><?= Yii::$app->user->identity->email ?> <b class="caret"></b></span>
				</a>
				<ul class="dropdown-menu animated fadeInRight m-t-xs">
					<li>
						<?= Html::a('Mi perfil', ['/admin/user/mi-perfil'], ['class' => 'dropdown-item']) ?>
					</li>
					<li>
						<?= Html::a('Cambiar contraseña', ['/admin/user/change-password'], ['class' => 'dropdown-item']) ?>
					</li>
					<li>
						<?= Html::a('Cerrar sesión', ['/admin/user/logout'], ['class' => 'dropdown-item', 'data-method' => 'post']) ?>
					</li>
				</ul>
			</div>
			<div class="logo-element">
				PE
			</div>
		</li>
	<?php

		return ob_get_clean();
	}

	public function get_shortcut_buttons()
	{
		ob_start();
		/*
			?>
			<div id="mainnav-shortcut">
				<ul class="list-unstyled">
					<?php if(Yii::$app->user->can('flexzoneAdmin') || Yii::$app->user->can('cafeteriaAdmin')): ?>
					<li class="col-xs-4" data-content="Usuarios internos">
						<?= Html::a('<i class="fa fa-users"></i>', ['/admin/user/index'], ["id" => "shortcut-usuarios", "class" => "shortcut-grid"]) ?>
					</li>
					<?php endif?>

					<?php if(Yii::$app->user->can('flexzoneComprasGastos') || Yii::$app->user->can('flexzoneVentas') || Yii::$app->user->can('flexzoneFacturacion')): ?>
					<li class="col-xs-4" data-content="Clientes">
						<?= Html::a('<i class="fa fa-child"></i>', ['/flexzone/cliente/index'], ["id" => "shortcut-clientes", "class" => "shortcut-grid"]) ?>
					</li>
					<?php endif?>

					<?php if(Yii::$app->user->can('flexzoneVentas')): ?>
					<li class="col-xs-4" data-content="Nueva ventas">
						<?= Html::a('<i class="fa fa-shopping-cart"></i>', ['/flexzone/venta/create'], ["id" => "shortcut-ventas", "class" => "shortcut-grid"]) ?>
					</li>
					<?php endif?>

					<?php if(Yii::$app->user->can('flexzoneAccesos')): ?>
					<li class="col-xs-4" data-content="Comprobar membresía">
						<?= Html::a('<i class="fa fa-credit-card"></i>', ['/flexzone/venta/comprobar-membresia'], ["id" => "shortcut-comprobar-membresia", "class" => "shortcut-grid"]) ?>
					</li>
					<?php endif?>
				</ul>
			</div>
			<?php
			*/

		return ob_get_clean();
	}

	public function get_menu()
	{
		return  Menu::widget([
			'options'         => ['class' => 'nav navbar-nav mr-auto'],
			'encodeLabels'    => false,
			'activateParents' => false,
			'linkTemplate' 	  => '<a href="{url}" aria-expanded="false" role="button" href="#" class="dropdown-toggle" data-toggle="dropdown">{label}</a>',

			'activeCssClass'  => 'show',

			//'items'           => $this->menuItems == null ? ['label' => ''] : $this->menuItems,
			'items' => empty($this->menuItems) ? [] : $this->menuItems

		]);
	}


	public function get_widget()
	{
		if (!Yii::$app->user->can('recursosServidor'))
			return false;

		ob_start();
	?>
		<div class="mainnav-widget">
			<div class="show-small">
				<a href="#" data-toggle="menu-widget" data-target="#wg-server">
					<i class="fa fa-desktop"></i>
				</a>
			</div>

			<div id="wg-server" class="hide-small mainnav-widget-content">
				<ul class="list-group">
					<li class="list-header pad-no pad-ver">Estado del servido</li>
					<li class="mar-btm">
						<span class="label label-primary pull-right label-cpu-use"></span>
						<p>Uso de CPU</p>
						<div class="progress progress-sm">
							<div class="progress-bar progress-bar-cpu progress-bar-primary">
								<span class="sr-only label-cpu-use"></span>
							</div>
						</div>
					</li>
					<li class="mar-btm">
						<span class="label label-purple label-mem-use pull-right"></span>
						<p>Uso de Memoria</p>
						<div class="progress progress-sm">
							<div class="progress-bar progress-bar-mem progress-bar-purple">
								<span class="sr-only label-mem-use"></span>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>

		<script>
			$(document).ready(function() {
				var avg_url = '<?= Yii::getAlias('@web') ?>',
					avg_interval = <?= Yii::$app->params['settings']['avg_interval'] ?>;

				nifty_avg(avg_url, avg_interval);
			});
		</script>
<?php

		return ob_get_clean();
	}
}
