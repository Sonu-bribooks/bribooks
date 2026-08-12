

<style>
	.card {
		border-radius: 0px;
		padding: 5px 5px 0 5px;
		min-height: 84px;
	}
	.card-green, .card.active {
		border: 1px solid #4ca95a;
		background-color: #4ca95a;
		color: #ffffff;
	}

	.card-yellow {
		border: 1px solid #e7953c;
		background-color: #e7953c;
	}

	.card-light-yellow {
		border: 1px solid #c3d444;
		background-color: #c3d444;
	}
	.card-blue {
		border: 1px solid #3a99ca;
		background-color: #3a99ca;
	}
	section.my-dashboard-area {
		padding: 40px 0;
	}
	.my-dashboard-area .card p {
		color: #ffffff;
	}
	.course h5, .quick h5 {
		text-transform: uppercase;
		background-color: #e66f38;
		border: 1px solid #e66f38;
		border-radius: 20px;
		width: fit-content;
		padding: 0 10px;
		color: #ffffff;
		font-size: 120%;
		font-weight: 600;
	}
	.course h3 {
		margin-top: 50px;
		text-align: center;
		background-color: #e7953c;
		border: 1px solid #e7953c;
		border-radius: 0px;
		width: fit-content;
		padding: 0 10px;
		color: #ffffff;
		font-weight: 600;
		margin-left: auto;
		margin-right: auto;
	}

	.quick h2 {
		margin-top: 50px;
		text-align: center;
		color: #e7953c;
		width: fit-content;
		font-weight: 600;
		font-size: 50px;
		margin-left: auto;
		margin-right: auto;
	}
	.course p {
		font-weight: 600;
	}
	.stage {
		background-image: url("<?=base_url().'assets/frontend/default/img/footer-top.png'; ?>");
		background-size: cover;
		background-position: bottom;
		background-repeat: no-repeat;
		padding: 147px 0 !important;
		margin-top: -163px;
	}

	.cards {
		background-image: url("<?=base_url().'assets/frontend/default/img/card-active.png'; ?>");
		background-size: contain;
		background-repeat: no-repeat;
	}

	.zoom-video img {
		width: 100%;
	}
	.stage .card {
		min-height: 1px;
		float: left;
		margin-left: 20px;
		margin-top: 10px;
	}

	.stage .card p {
		color: #000000;
	}
	.stage .active p {
		color: #ffffff;
	}

	.countdownHolder{
	margin:0 auto;
	font: 40px/1.5 'Open Sans Condensed',sans-serif;
	text-align:center;
	letter-spacing:-3px;
}

.position{
	display: inline-block;
	height: 1.6em;
	overflow: hidden;
	position: relative;
	width: 1.05em;
}

.digit{
	position:absolute;
	display:block;
	width:1em;
	background-color:#e7953c;
	border-radius:0.2em;
	text-align:center;
	color:#fff;
	letter-spacing:-1px;
}

.digit.static{
	box-shadow:1px 1px 1px rgba(4, 4, 4, 0.35);

	background-image: linear-gradient(bottom, #df8423 50%, #e7953c 50%);
	background-image: -o-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
	background-image: -moz-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
	background-image: -webkit-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
	background-image: -ms-linear-gradient(bottom, #df8423 50%, #e7953c 50%);

	background-image: -webkit-gradient(
		linear,
		left bottom,
		left top,
		color-stop(0.5, #df8423),
		color-stop(0.5, #e7953c)
	);
}

/**
 * You can use these classes to hide parts
 * of the countdown that you don't need.
 */

.countDays{ /* display:none !important;*/ }
.countDiv0{ /* display:none !important;*/ }
.countHours{}
.countDiv1{}
.countMinutes{}
.countDiv2{}
.countSeconds{}


.countDiv{
	display:inline-block;
	width:16px;
	height:1.6em;
	position:relative;
}

.countDiv:before,
.countDiv:after{
	position:absolute;
	width:5px;
	height:5px;
	background-color:#df8423;
	border-radius:50%;
	left:50%;
	margin-left:-3px;
	top:0.5em;
	box-shadow:1px 1px 1px rgba(4, 4, 4, 0.5);
	content:'';
}

.countDiv:after{
	top:0.9em;
}

.countDays, .countDays+.countDiv {
	display: none;
}

.progressbar{
  counter-reset: step;
  list-style: none;
}
.progressbar li{
  float: left;
  width: 20%;
  position: relative;
  text-align: center;
}
.progressbar li:before{
  content:counter(step);
  counter-increment: step;
  width: 50px;
  height: 50px;
  border: 2px solid #bebebe;
  display: block;
  margin: 0 auto 10px auto;
  border-radius: 50%;
  line-height: 47px;
  background: white;
  color: #bebebe;
  text-align: center;
  font-weight: bold;
}
.progressbar li:after{
  content: '';
  position: absolute;
  width:100%;
  height: 3px;
  background: #979797;
  top: 25px;
  left: -50%;
  z-index: -1;
}
.progressbar li.active:after{
 background: #df8423;
}
.progressbar li.active:before{
border-color: #df8423;
background: #df8423;
color: white
}
.progressbar li:first-child:after{
content: none;
}
</style>
<section class="page-header-area my-course-area">
	<div class="container">
		<div class="row">
			<div class="col col-lg-6">
				<h1 class="page-title"><?php echo _l('welcome'); ?> <?php echo $this->session->name; ?></h1>
				<br />
			</div>
            <div class="col col-lg-6 text-right">
				<div class="btn btn-success">Play Game</div>
			</div>
		</div>
	</div>
</section>




<section class="my-dashboard-area stage">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-lg-12">
				
			</div>
		</div>
	</div>
</section>

