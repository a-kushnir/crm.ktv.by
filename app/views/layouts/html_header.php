<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title><?php echo $title.($subtitle ? ' ('.$subtitle.')' : '') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="Andrew Kushnir">
  
  <!-- styles -->
  <link rel="stylesheet" type="text/css" href="/stylesheets/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/bootstrap-responsive.min.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/bootstrap-overrides.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/bootstrap-datepicker.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/html_layout.css" />

  <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode/svn/trunk/html5.js" type="text/javascript"></script>
  <![endif]-->

  <!-- fav-icons -->
  <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
<?php $javascripts = array(
  '/javascripts/jquery.min.js',
  '/javascripts/jquery.datetime.js',
  '/javascripts/jquery.maskedinput.js',
  '/javascripts/bootstrap.min.js',
  '/javascripts/bootstrap-datepicker.js',
  '/javascripts/rails.min.js',
  '/javascripts/application.js'
); ?>
</head>
<body>

  <!-- nav-bar -->
  <div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="brand" href="<?php echo isset($_SESSION['user_id']) ? '/home' : '/' ?>"><span class="hidden-phone">ТелеСпутник</span></a>
  
  <div class="hidden-desktop pull-right">
    <div id="primary_menu_search" class="navbar-search" style="display:none;">
      <input type="text" class="search-query input-large" placeholder="Поиск<?php echo isset($_SESSION['selected_region']) ?  ' по '.$_SESSION['selected_region']['name'] : ''?>" value="<?php echo isset($primary_menu_filter) ? htmlspecialchars($primary_menu_filter) : null ?>"/>
    </div>
    <div id="primary_menu_buttons">
    <?php if (isset($primary_menu_search_url) && $primary_menu_search_url) { ?>
      <a id="primary_menu_search_button" class="btn" href="<?php echo $primary_menu_search_url; ?>"><i class="icon-search"> </i></a>
    <?php } ?>
    <?php if (isset($primary_menu_create_url) && $primary_menu_create_url) { ?>
      <a class="btn" href="<?php echo $primary_menu_create_url; ?>"><i class="icon-edit"> </i></a>
    <?php } ?>
    <?php if (isset($primary_menu_back_url) && $primary_menu_back_url) { ?>
      <a id="primary_menu_create_button" class="btn" href="<?php echo $primary_menu_back_url; ?>"><i class="icon-arrow-left"> </i></a>
    <?php } ?>
    <?php if (isset($primary_menu_edit_url) && $primary_menu_edit_url) { ?>
      <a id="primary_menu_create_button" class="btn" href="<?php echo $primary_menu_edit_url; ?>"><i class="icon-edit"> </i></a>
    <?php } ?>
    <?php if (isset($primary_menu_destroy_url) && $primary_menu_destroy_url) { ?>
      <a id="primary_menu_create_button" class="btn" href="<?php echo $primary_menu_destroy_url; ?>"><i class="icon-trash"> </i></a>
    <?php } ?>
    </div>
  </div>
        
        <div class="nav-collapse collapse">
          <?php include('_primary_menu.php'); ?>
        </div>
      </div>
    </div>
  </div>
  
  <?php if (isset($_SESSION['user_id'])) include('_choose_region.php'); ?>
  <?php if (isset($_SESSION['user_id']) && isset($attachments)) include('_attachments.php'); ?>
  
  <div class="container">
  <?php if (isset($_SESSION['flash-alert'])) { ?>
    <div class='alert alert-error'><a class='close' data-dismiss='alert'>×</a><?php echo $_SESSION['flash-alert'] ?></div>
  <?php unset($_SESSION['flash-alert']); } ?>
  <?php if (isset($_SESSION['flash-notice'])) { ?>
    <div class='alert alert-success'><a class='close' data-dismiss='alert'>×</a><?php echo $_SESSION['flash-notice'] ?></div>
  <?php unset($_SESSION['flash-notice']); } ?>
  
<!-- page begin -->