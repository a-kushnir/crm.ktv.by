<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>
</div>
<div class="span9">
<?php echo page_header($title, $subtitle); ?>

<?php 
  $user_events = User::recent_events(1440); // 24 hours
  if (count($user_events) > 0) {
    foreach($user_events as $user_event) {
      $secondes = floor($user_event['seconds_ago']);
      echo '<p style="margin:0;">'.
        '<small>'.format_datetime($user_event['created_at'], SHORT_TIME_FORMAT).'</small> '.
        $user_event['name'].' '.
        '<a href="'.$user_event['link'].'" class="tooltip-balloon" data-content="'.
        $user_event['about'].
        '" rel="popover" href="#" data-original-title="'.time_since($secondes).' назад'.'">'.$user_event['message'].'</a>'.
      
      '</p>';
    }
  } else {
    echo '<p style="margin:0;"><i>Ничего не произошло</i></p>';
  }
?>

</div>
</div>
