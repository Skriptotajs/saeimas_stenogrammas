<!DOCTYPE html>
<html lang="lv">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Saeimas stenogrammas</title>
	
	<link href="<?php echo base_url("css/bootstrap.min.css");?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url("css/jquery.bonsai.css");?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url("css/jquery-ui.min.css");?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url("css/jquery-ui.structure.min.css");?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url("css/jquery-ui.theme.min.css");?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url("css/global.css");?>" rel="stylesheet" type="text/css" />
	

</head>
<body>


<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		  <a class="navbar-brand" href="<?php echo site_url('');?>">Saeimas stenogrammas</a>
		</div>
		<div class="collapse navbar-collapse">
		  <ul class="nav navbar-nav">
				<li>
					<a href="<?php echo site_url('search');?>">Meklēšana</a>
				</li>
		  </ul>
		</div><!--/.nav-collapse -->
	</div>
</div>

<div class="container" id="contet">