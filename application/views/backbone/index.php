<!DOCTYPE html>
<html>
  <head>
    <title>Collector &bull; Your personal news reader</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta content="initial-scale=1.0 maximum-scale=1.0 user-scalable=no width=device-width" name="viewport" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Collector"/>
    <meta name="msapplication-TileColor" content="#C42C2C"/>
    <meta name="msapplication-TileImage" content="<?php echo site_url('assets/img/apple-icon.png'); ?>"/>
    <link rel="apple-touch-icon" href="<?php echo site_url('assets/img/apple-icon.png'); ?>">
    <!--[if lt IE 9]>
        <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="<?php echo site_url('assets/css/core.css'); ?>" type="text/css" media="screen" />
    <script src="<?php echo site_url('assets/js/backbone.js'); ?>"></script>
    <?php if (ENVIRONMENT == 'development') { ?><script src="http://localhost:35729/livereload.js"></script><?php } ?>
  </head>
  <body><div id="wrapper"><div id="header"></div><div id="sidebar"></div><div id="content"></div></body></div>
  <script type="text/javascript">Collector.start({url:'<?php echo site_url("api"); ?>', user:<?php echo $user; ?>})</script>
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', "<?php echo $this->config->item('analytics_id'); ?>", "<?php echo $_SERVER['SERVER_NAME']; ?>");
  ga('send', 'pageview');
  </script>
</html>
