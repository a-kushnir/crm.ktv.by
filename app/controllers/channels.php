<?php
class ChannelsController extends ApplicationController
{
  function index()
  {
    check_access('channels');

    $this->station_id = get_field_value('station');
    $this->channels = $this->station_id ? Channel::load(null, $this->station_id == 'all' ? null : $this->station_id) : array();
    if ($this->layout != 'print') $this->stations = Channel::stations_filter();

    $this->title = 'Каналы';
    $this->load_attachments('Channel');
  }

  function add()
  {
    check_access('channel/edit');
    
    $this->title = 'Новый канал';
    $this->channel = new Channel();
  }

  function create()
  {
    check_access('channel/edit');
    
    $this->channel = new Channel();
    $this->channel->load_attributes($_POST['channel']);
    if ($this->channel->valid()) {
      $id = $this->channel->create();
      flash_notice('Канал был добавлен');
      redirect_to(url_for('channels', 'new'));
    } else {
      $this->channel['type'] = get_field_value('channel', 'type');
      $this->title = 'Новый канал';
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('channels');
    
    $this->channel = Channel::load($this->id, null, true);
    if (!$this->channel) show_404();

    $this->load_attachments('Channel', $this->id);
    
    $this->title = 'Канал';
    $this->subtitle = $this->channel['name'];
  }

  function edit()
  {
    check_access('channel/edit');

    $this->channel = Channel::load($this->id);
    
    $this->title = 'Редактирование';
    $this->subtitle = $this->channel['name'];
  }

  function update()
  {
    check_access('channel/edit');

    $this->channel = new Channel();
    $this->channel->load_attributes($_POST['channel'], $this->id);
    if ($this->channel->valid()) {
      $this->channel->update();
      flash_notice('Канал был обновлена');
      redirect_to(url_for('channels', 'show', $this->id));
    } else {
      $this->channel['type'] = get_field_value('channel', 'type');
      $channel_db = Channel::load($id);
      $this->channel['station'] = $channel_db['station'];
      $this->channel['station_id'] = $channel_db['station_id'];
      
      $this->title = 'Редактирование';
      $this->subtitle = $channel['name'];
      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('channel/edit');

    if ($_POST)
    {
      $channel = Channel::load($this->id);  
      $this->factory->deactivate('channels', $this->id);
      flash_notice('Канал был удален');
    }
    
    redirect_to(url_for('channels', 'index').'?station='.get_object_value($channel, 'station_id'));
  }

  function forget_known()
  {
    check_access('home/channel_notifications');
    Channel::forget_known($this->id);
    redirect_to(url_for('home', 'index'));
  }

  function forget_unknown()
  {
    check_access('home/channel_notifications');
    Channel::forget_unknown($this->id);
    redirect_to(url_for('home', 'index'));
  }
}
?>