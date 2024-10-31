<?php
/*
Plugin Name: PageRank
Plugin URI: https://wordpress.org/plugins/pagerank/
Description: Displays Google PageRank in the sidebar of your blog via widget or anywhere else. <a href="options-general.php?page=pagerank/pagerank.php">Configure here</a>.
Version: 0.4
Author: tomknows
Author URI: https://profiles.wordpress.org/tomknows/
*/

/**
 * v0.4 2014-10-15 updated to wp 4.x
 * v0.3 2010-04-27 minor xhtml fix
 * v0.2 2009-09-07 small url fix
 * v0.1 2009-07-07 initial release
 */

if(!class_exists('PageRank')):
class PageRank {
  var $id;
  var $version;
  var $title;
  var $name;
  var $options;
  var $path;
  var $cache_file;
  var $locale;
  var $url;
  var $layouts;
  
  function PageRank() {
    $this->id         = 'pagerank';
    $this->title      = 'Pagerank';
    $this->version    = '0.4';
    $this->name       = $this->title. ' v'. $this->version;
    $this->http_cache = array();
    $this->path       = dirname(__FILE__);
    $this->url        = get_bloginfo('wpurl'). '/wp-content/plugins/' . $this->id; 

    $this->layouts = array(
      array(
        'icon'    => '0.gif',
        'width'   => 80,
        'height'  => 15,
        'text'  => null,
        'image' => array(
          'x'     => 31,
          'y'     => 6,
          'w'     => 40,
          'h'     => 3,
          'color' => '5eaa5e'
        )
      ),
      array(
        'icon'    => '1.gif',
        'width'   => 80,
        'height'  => 30,
        'text'  => array(
          'x'     => 11,
          'y'     => 7,
          'font'  => 5,
          'color' => 'ffffff'
        ),
        'image' => array(
          'x'     => 34,
          'y'     => 20,
          'w'     => 4,
          'h'     => 3,
          'color' => '5eaa5e'
        )
      ),
      array(
        'icon'    => '2.gif',
        'width'   => 80,
        'height'  => 15,
        'text'  => array(
          'x'     => 68,
          'y'     => 1,
          'font'  => 3,
          'color' => '5eaa5e'
        ),
        'image' => null
      )
    );

	  $this->locale = get_locale();

	  if(empty($this->locale)) {
		  $this->locale = 'en_US';
    }

    load_textdomain($this->id, sprintf('%s/%s.mo', $this->path, $this->locale));

    $this->loadOptions();
    
    $this->cache_file = $this->path. '/cache/layout'. $this->options['layout']. '.gif';

    if(!@isset($_GET['image'])) {
      if(is_admin()) {
        add_action('admin_menu', array(&$this, 'optionMenu'));
      }
      else {
        add_action('wp_head', array(&$this, 'blogHead'));
      }

      add_action('widgets_init', array(&$this, 'initWidgets'));
    }
  }
  
  function optionMenu() {
    add_options_page($this->title, $this->title, 8, __FILE__, array(&$this, 'optionMenuPage'));
  }

  function optionMenuPage() {

    if(@$_REQUEST[$this->id]) {
      @unlink($this->cache_file);
    
      $this->updateOptions($_REQUEST[$this->id]);
    
      echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved!', $this->id) . '</strong></p></div>'; 
    }
?>
<div class="wrap">

<h2><?php _e('Settings', $this->id); ?></h2>
<form method="post" action="">
<table class="form-table">
<?php if(!file_exists($this->path. '/cache/') || !is_writeable($this->path. '/cache/')): ?>
<tr valign="top"><th scope="row" colspan="3"><span style="color:red;"><?php _e('Warning! The cachedirectory is missing or not writeable!', $this->id); ?></span><br /><em><?php echo $this->path; ?>/cache</em></th></tr>
<?php endif; ?>
</tr>
<tr valign="top">
  <th scope="row"><?php _e('Title', $this->id); ?></th>
  <td><input name="<?=$this->id?>[title]" type="text" class="code" value="<?=$this->options['title']?>" /><br /><?php _e('The title is displayed above the badge in widget mode only!', $this->id); ?></td></tr>
  <th scope="row"><?php _e('Layout', $this->id); ?></th>
  <td colspan="3">
  <input name="<?=$this->id?>[layout]" type="radio" class="code" value="0"<?php echo intval($this->options['layout']) == 0 ? ' checked="checked"' : ''; ?> />
  <img src="<?=$this->url?>/screenshot-1.gif" style="vertical-align:middle;" /><br /><br />
  <input name="<?=$this->id?>[layout]" type="radio" class="code" value="1"<?php echo intval($this->options['layout']) == 1 ? ' checked="checked"' : ''; ?> />
  <img src="<?=$this->url?>/screenshot-2.gif" style="vertical-align:middle;" /><br /><br />
  <input name="<?=$this->id?>[layout]" type="radio" class="code" value="2"<?php echo intval($this->options['layout']) == 2 ? ' checked="checked"' : ''; ?> />
  <img src="<?=$this->url?>/screenshot-3.gif" style="vertical-align:middle;" />
  </td>
</tr>

</table>

<p class="submit">
  <input type="submit" value="<?php _e('save', $this->id); ?>" name="submit" />
</p>

</form>

</div>
<?php
}
  function loadOptions() {
    if(!($this->options = get_option($this->id))) {
      $this->options = array(
        'title' => 'PageRank',
        'layout' => 0
			);

      add_option($this->id, $this->options, $this->name, 'yes');
    }
  }
  
  function updateOption($name, $value) {
    $this->updateOptions(array($name => $value));
  }

  function updateOptions($options) {
    foreach($this->options as $k => $v) {
      if(array_key_exists($k, $options)) {
        $this->options[$k] = $options[ $k ];
      }
    }

		update_option($this->id, $this->options);
	}
  
  function blogHead() {
    printf('<meta name="%s" content="%s/%s" />' . "\n", $this->id, $this->id, $this->version);
    print( '<style type="text/css">
#pagerank, #pagerank small {padding: 0;margin: 0;color: #aaa;font-family: Arial, sans-serif;font-size: 10px;font-style: normal;font-weight: normal;letter-spacing: 0px;text-transform: none; width: 80px;text-align:center;}
#pagerank small a:hover, #pagerank small a:link, #pagerank small a:visited, #pagerank small a:active {color: #aaa;text-decoration:none;cursor: pointer;text-transform: none;}
</style>');
  }
  
  function getPageRank() {
    include_once($this->path. '/lib/pagerankapi.class.php');

    $pr = PagerankApi::Fetch(get_bloginfo('wpurl'));

    return(is_null($pr) ? 0 : $pr);
  }

  function rgbColor(&$img, $rgb) {
    if( $rgb[ 0 ] == '#' ) {
      $rgb = substr( $rgb, 1 );
    }
    
    $a = substr($rgb, 0, 2);
    $b = substr($rgb, 2, 2);
    $c = substr($rgb, 4, 2);

    return imagecolorallocate($img, hexdec($a), hexdec($b), hexdec($c));
  }

  
  function getCode() {
    return sprintf( '<div id="%s"><a class="snap_noshots" href="https://profiles.wordpress.org/tomknows/" target="_blank"><img src="%s/%s/%s/%s.php?image=1" border="0" alt="%s" title="%s" /></a><br /><small>Plugin by <a href="https://profiles.wordpress.org/tomknows/" class="snap_noshots" target="_blank">Tom</a></small></div>', $this->id, get_bloginfo('wpurl'), PLUGINDIR, $this->id, $this->id, $this->title, $this->title);
  }

  function draw() {
    clearstatcache();
    
    $create = false;
    
    if(!file_exists($this->cache_file)) {
      $create = true;
    }
    elseif(time() - filemtime($this->cache_file) > (3600 * 3)) {
      $create = true;
    }
    
    if($create) {

      $numeric = $this->getPageRank();

      $layout = $this->layouts[intval($this->options['layout'])];

      $img = @imagecreatefromgif($this->path. '/img/'. $this->options['layout']. '.gif');
      
      if(!is_null($layout['text'])) {
        $color1 = $this->rgbColor($img, $layout['text']['color']);
        imagestring($img, $layout['text']['font'], $layout['text']['x'], $layout['text']['y'], $numeric, $color1);
      }
            
      if(!is_null($layout['image']) && $numeric > 0) {
        $color2 = $this->rgbColor($img, $layout['image']['color']);
        imagefilledrectangle($img, $layout['image']['x'], $layout['image']['y'], $layout['image']['x'] + ($numeric*4)-1, $layout['image']['y'] + $layout['image']['h']-1, $color2);
      }

      if(is_writeable($this->path. '/cache')) {
        imagegif($img, $this->cache_file);
      }
    }
    else {
      $img = @imagecreatefromgif($this->cache_file);
    }
    
    header( 'Content-Type: image/gif' );

    @imagegif($img);
  }

  function initWidgets() {
    if(function_exists('register_sidebar_widget')) {
      register_sidebar_widget($this->title. ' Widget', array(&$this, 'Widget'), null, 'widget_'. $this->id);
    }
  }
  
  function widget($args) {
    extract($args);

    printf('%s%s%s%s%s%s', $before_widget, $before_title, $this->options['title'], $after_title, $this->getCode(), $after_widget);
  }
}

function pagerank_display() {
  global $PageRank;

  if(!isset($PageRank)) {
    $PageRank = new PageRank();
  }

  if($PageRank) {
    print($PageRank->getCode());
  }
}
endif;

if(@isset($_GET['image'])) {
  include_once(dirname(__FILE__). '/../../../wp-config.php');

  if(!isset($PageRank)) {
    $PageRank = new PageRank();
  }

  $PageRank->draw();
}
else {
  add_action('plugins_loaded', create_function('$PageRank_sd2121c', 'global $PageRank; $PageRank = new PageRank();')); 
}

?>
