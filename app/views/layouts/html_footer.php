<!-- page end -->
    <hr/>
    <footer>
      <p class="pull-right"><a href="#">Наверх</a></p>
      <p>&copy; ТелеСпутник, <?php echo date('Y'); ?></p>
    </footer>
  </div>
  
<!-- Placed at the end of the document so the pages load faster -->
<?php
if (isset($javascripts) && count($javascripts) > 0)
  foreach($javascripts as $javascript)
    echo strrpos($javascript, '<script') === false ? '<script type="text/javascript" src="'.$javascript.'"></script>' : $javascript;
?>
</body>
