<?php
class MessagesController extends ApplicationController
{
  function destroy()
  {
    check_access('home/message_notifications');

    if ($_POST)
    {
      $for = get_field_value('for');
      $condition = ($for == 'admin' ? 'subscriber_id is null' :
                   ($for == 'subs' ? 'subscriber_id is not null' : '1 <> 1'));
      $count = $this->factory->connection->execute_scalar('SELECT COUNT(id) FROM sms_queue WHERE '.$condition);
      $this->factory->connection->execute_void('DELETE FROM sms_queue WHERE '.$condition);
      
      flash_notice($count.' сообщений было удалено');
    }
    
    redirect_to(url_for('home', 'index'));
  }
}
?>