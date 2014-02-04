
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <!--[if lt IE 9]>
      <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
<?php
foreach ($collections as $title => $collection) { ?>
  <section class="collection">
    
    <div class="articles">
    <?php foreach ($collection['articles'] as $article) {

      $img = $article['image_url'];
      if (!$img) continue;
      ?>

      <a class="article" href="<?php echo $article['url']; ?>" style="<?php echo $img ? 'background-image:url('.$img.');' : ''; ?>">
        <div class="content"><i><?php echo $title; ?></i>
        <?php if ($article['content'] && false) { ?><p><?php echo substr($article['content'], 0, 100); ?></p><?php } ?>
        <h3><?php echo $article['title']; ?></h3></div>
      </a>
    <?php } ?>
    </div>
  </section>

<?php } ?>


<style>
* {
  margin:0;
  padding:0;
}
body {
  padding:10px;
}
a.article {
  width:320px;
  height:200px;
  display:block;
  margin:0 10px 10px 0;
  position:relative;
  background-position: center center;
  background-color:#555;
  background-size:cover;
  float:left;
  color:#FFF;
  text-decoration: none;
}
.content {
  width:100%;
  position:absolute;
  bottom:0;
  background:rgba(0,0,0,0.35);
}
a.article i {
  display:block;
  padding:10px 10px 0 10px;
  font-size:12px;
  font-family: 'Museo Sans', sans-serif;
  opacity: 0.5;
}
h3 {
  font-family: 'BentonSans', 'Museo Sans', sans-serif;
  padding:10px;
  font-size: 16px;
  line-height: 20px;
  color:#FFF;
  font-weight: 500;
}
p {
  font-weight: 300;
  padding:10px 10px 0 10px;
  font-family: 'Museo Sans', sans-serif;
  font-size:12px;
  line-height: 16px;
  color:#FFF;
}
</style>
</body>
</html>