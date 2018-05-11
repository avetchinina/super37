<?php echo $header; ?><?php echo $column_left; ?>

<div id="content">
  	<div class="page-header">
    	<div class="container-fluid">
      		<h1>Парсинг</h1>
		</div>
	</div>

	<div class="container-fluid">
		<div class="jumbotron">
			<div class="row">
				<div class="col-lg-4 col-md-5 col-sm-6">
					<div class="btn-group">
						<button class="btn btn-lg btn-info" data-action="parse-lb">Запарсим LenaBasco</button>
						<button class="btn btn-lg btn-info" data-action="update-data">Загрузим спарсенные данные</button>
					</div>
				</div>
				<div class="col-lg-8 col-md-7 col-sm-6">
					<div class="cssload-contain" data-element="loader">
						<div class="cssload-dot"></div>
						<div class="cssload-dot"></div>
						<div class="cssload-dot"></div>
						<div class="cssload-dot"></div>
						<div class="cssload-dot"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="view/javascript/parser.js" defer='true'></script>
<style>
.cssload-contain {
	height: 49px;
	width: 114px;
	margin-top: 15px;
	margin-bottom: -15px;
}

.cssload-dot {
	float: left;
	height: 9px;
	width: 9px;
	border-radius: 50%;
	background-color: rgb(0,0,0);
	background-image: -webkit-linear-gradient(rgba(255,255,255,0.3), transparent), radial-gradient(rgb(155,89,182), rgb(142,68,173));
		-o-radial-gradient(rgb(155,89,182), rgb(142,68,173));
		-ms-radial-gradient(rgb(155,89,182), rgb(142,68,173));
		-webkit-radial-gradient(rgb(155,89,182), rgb(142,68,173));
		-moz-radial-gradient(rgb(155,89,182), rgb(142,68,173));
	background-position: top center;
	margin: 0 2.5px;
	animation: cssload-bounce 0.52s infinite ease alternate;
		-o-animation: cssload-bounce 0.52s infinite ease alternate;
		-ms-animation: cssload-bounce 0.52s infinite ease alternate;
		-webkit-animation: cssload-bounce 0.52s infinite ease alternate;
		-moz-animation: cssload-bounce 0.52s infinite ease alternate;
}
.cssload-dot:nth-child(1) {
	animation-delay: -0.09s;
		-o-animation-delay: -0.09s;
		-ms-animation-delay: -0.09s;
		-webkit-animation-delay: -0.09s;
		-moz-animation-delay: -0.09s;
}
.cssload-dot:nth-child(2) {
	animation-delay: -0.17s;
		-o-animation-delay: -0.17s;
		-ms-animation-delay: -0.17s;
		-webkit-animation-delay: -0.17s;
		-moz-animation-delay: -0.17s;
}
.cssload-dot:nth-child(3) {
	animation-delay: -0.26s;
		-o-animation-delay: -0.26s;
		-ms-animation-delay: -0.26s;
		-webkit-animation-delay: -0.26s;
		-moz-animation-delay: -0.26s;
}
.cssload-dot:nth-child(4) {
	animation-delay: -0.35s;
		-o-animation-delay: -0.35s;
		-ms-animation-delay: -0.35s;
		-webkit-animation-delay: -0.35s;
		-moz-animation-delay: -0.35s;
}
.cssload-dot:nth-child(5) {
	animation-delay: -0.43s;
		-o-animation-delay: -0.43s;
		-ms-animation-delay: -0.43s;
		-webkit-animation-delay: -0.43s;
		-moz-animation-delay: -0.43s;
}
.cssload-dot:nth-child(6) {
	animation-delay: -0.52s;
		-o-animation-delay: -0.52s;
		-ms-animation-delay: -0.52s;
		-webkit-animation-delay: -0.52s;
		-moz-animation-delay: -0.52s;
}



@keyframes cssload-bounce {
	0% {
		transform: translateY(0);
		box-shadow: 0 2.5px 1px rgba(71,34,86,0.2);
	}
	100% {
		transform: translateY(-38px);
		box-shadow: 0 85px 11px rgba(0,0,0,0.1);
	}
}

@-o-keyframes cssload-bounce {
	0% {
		-o-transform: translateY(0);
		box-shadow: 0 2.5px 1px rgba(71,34,86,0.2);
	}
	100% {
		-o-transform: translateY(-38px);
		box-shadow: 0 85px 11px rgba(0,0,0,0.1);
	}
}

@-ms-keyframes cssload-bounce {
	0% {
		-ms-transform: translateY(0);
		box-shadow: 0 2.5px 1px rgba(71,34,86,0.2);
	}
	100% {
		-ms-transform: translateY(-38px);
		box-shadow: 0 85px 11px rgba(0,0,0,0.1);
	}
}

@-webkit-keyframes cssload-bounce {
	0% {
		-webkit-transform: translateY(0);
		box-shadow: 0 2.5px 1px rgba(71,34,86,0.2);
	}
	100% {
		-webkit-transform: translateY(-38px);
		box-shadow: 0 85px 11px rgba(0,0,0,0.1);
	}
}

@-moz-keyframes cssload-bounce {
	0% {
		-moz-transform: translateY(0);
		box-shadow: 0 2.5px 1px rgba(71,34,86,0.2);
	}
	100% {
		-moz-transform: translateY(-38px);
		box-shadow: 0 85px 11px rgba(0,0,0,0.1);
	}
}
</style>
<?php echo $footer; ?>