<?php

$plugin_info = array(
	'pi_name' => 'Vidio',
	'pi_version' => '1.0',
	'pi_author' => 'Matthew Callis, based on work by <a href="http://iain.co.nz/">Iain Urquhart</a>',
	'pi_author_url' => 'http://paramoreredd.com/',
	'pi_description' => 'Vidio - Using YouTube, Vimeo and hopefully others at some point',
	'pi_usage' => Vidio::usage()
);

class Vidio{
	var $return_data;
	function Vidio($str = ''){
		global $TMPL;

		$the_content = '';
		$width = (is_numeric($TMPL->fetch_param('width')) ? $TMPL->fetch_param('width') : '480');
		$height = (is_numeric($TMPL->fetch_param('height')) ? $TMPL->fetch_param('height') : '320');
		$replace = (($TMPL->fetch_param('replace') != '') ? $TMPL->fetch_param('replace') : 'myContainer');
		$id = (($TMPL->fetch_param('id') != '') ? $TMPL->fetch_param('id') : 'myContent');

		$link = FALSE;
		$host = '';
		$matches = array();
		if($str == ''){
			$the_content = $TMPL->tagdata;
			if(strrpos($the_content, 'youtube')){
				$host = 'youtube';
				if(strrpos($the_content, '?v=')){
					$link = TRUE;
					preg_match('/v=([^&]*)/', $the_content, $matches);
				}
				else{
					preg_match('#(?<=youtube\.com/v/)\w+#', $the_content, $matches);
				}
			}
			elseif(strrpos($the_content, 'vimeo')){
				$host = 'vimeo';
				if(strrpos($the_content, 'object')){
					preg_match('#(?<=clip_id=)\w+#', $the_content, $matches);
				}
				else{
					$matches[0] = substr($the_content, 29);
				}
			}
		}

		$output = $TMPL->fetch_param('format');
		switch ($output){
			case "object":
				if($host == 'youtube'){
					$code = '<object type="application/x-shockwave-flash" data="http://www.youtube.com/v/'.$matches[0].'&amp;hl=en" width="'.$width.'" height="'.$height.'">'."\n";
					$code .= '	<param name="movie" value="http://www.youtube.com/v/'.$matches[0].'"/>'."\n";
					$code .= '	<param name="FlashVars" value="playerMode=embedded"/>'."\n";
					$code .= '	<param name="wmode" value="transparent"/>'."\n";
					$code .= '	<param name="allowfullscreen" value="true"/>'."\n";
					$code .= '	<param name="allowscriptaccess" value="always"/>'."\n";
					$code .= '</object>'."\n";
					$this->return_data = $code;
				}
				elseif($host == 'vimeo'){
					$code = '<object width="'.$width.'" height="'.$height.'">'."\n";
					$code .= '	<param name="allowfullscreen" value="true"/>'."\n";
					$code .= '	<param name="allowscriptaccess" value="always"/>'."\n";
					$code .= '	<param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id='.$matches[0].'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1"/>'."\n";
					$code .= '	<embed src="http://vimeo.com/moogaloop.swf?clip_id=1395756&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="'.$width.'" height="'.$height.'"></embed>'."\n";
					$code .= '</object>'."\n";
					$this->return_data = $code;
				}
				break;
			case "id":
				if($link && ($host == 'youtube')){
					$this->return_data = substr($matches[0], 2);
				}
				else{
					$this->return_data = $matches[0];
				}
				break;
			case "link":
				if($host == 'youtube'){
					$this->return_data = 'http://www.youtube.com/watch?v='.$matches[0].'&feature=player_embedded';
				}
				elseif($host == 'vimeo'){
					$this->return_data = 'http://vimeo.com/'.$matches[0];
				}
				break;
			case 'so':
				if($host == 'youtube'){
					$code = 'var flashvars = {"playerMode":"embedded"};'."\n";
					$code .= 'var params = {"movie":"http://www.youtube.com/v/'.$matches[0].'","allowscriptaccess":"always","allowfullscreen":true,"wmode":"transparent"};'."\n";
					$code .= 'var attributes = {"id":"'.$container.'"};'."\n";
					$code .= 'swfobject.embedSWF("http://www.youtube.com/v/'.$matches[0].'", "'.$replace.'", "'.$width.'", "'.$height.'", "9", false, flashvars, params, attributes);'."\n";
					$this->return_data = $code;
				}			
				elseif($host == 'vimeo'){
					$code = 'var flashvars = {"clip_id": "'.$matches[0].'", "server":"vimeo.com","show_title":1,"show_byline":1,"show_portrait":0,"fullscreen": 1,"js_api":1};'."\n";
					$code .= 'var params = {"swliveconnect":true,"fullscreen": 1,"allowscriptaccess":"always","allowfullscreen":true,"wmode":"transparent"};'."\n";
					$code .= 'var attributes = {"id":"'.$container.'"};'."\n";
					$code .= 'swfobject.embedSWF("http://www.vimeo.com/moogaloop.swf", "'.$replace.'", "'.$width.'", "'.$height.'", "9", false, flashvars, params , attributes);'."\n";
					$this->return_data = $code;
				}
				break;
			default:
				$this->return_data = ($matches[0] ? $matches[0] : '');
				break;
		}
	}

	// ----------------------------------------
	//  Plugin Usage
	// ----------------------------------------
	function usage(){
		ob_start();
?>
		{exp:vidio format="object" width="480" height="340"}http://www.youtube.com/watch?v=xxxxx{/exp:vidio}
		Will output the player from just the url
		
		{exp:vidio format="id"}http://www.youtube.com/watch?v=xxxxx{/exp:vidio}
		Will output the video ID from a URL or embed code

		{exp:vidio format="link"}<object ... />{/exp:vidio}
		Will output the link from a embed code

		{exp:vidio format="so" id="myContent" replace="myContainer"}<object ... />{/exp:vidio}
		Will output the video using SWFObject 2.x format
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
	// END
}
?>