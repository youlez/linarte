<?php 

if ( ! defined( 'ABSPATH' ) )
	exit;
	
require_once 'wonderplugin-3dcarousel-functions.php';

class WonderPlugin_3DCarousel_Model {

	private $controller;
	
	function __construct($controller) {
		
		$this->controller = $controller;

		$this->multilingual = false;

		if ( get_option( 'wonderplugin_3dcarousel_supportmultilingual', 1 ) == 1 )
		{
			if (class_exists('SitePress'))
			{
				$defaultlang = apply_filters( 'wpml_default_language', NULL);
				if ( !empty($defaultlang) )
				{
					$this->multilingual = true;
					$this->multilingualsys = "wpml";
					$this->defaultlang = $defaultlang;
					$this->currentlang = apply_filters('wpml_current_language', NULL );
				}
			}
		}
	}
	
	function get_upload_path() {
		
		$uploads = wp_upload_dir();
		return $uploads['basedir'] . '/wonderplugin-3dcarousel/';
	}
	
	function get_upload_url() {
	
		$uploads = wp_upload_dir();
		return $uploads['baseurl'] . '/wonderplugin-3dcarousel/';
	}
	
	function xml_cdata( $str ) {
	
		if ( ! seems_utf8( $str ) ) {
			$str = utf8_encode( $str );
		}
	
		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';
	
		return $str;
	}
	
	function search_replace_items($post)
	{
		$allitems = sanitize_text_field($_POST['allitems']);
		$itemid = sanitize_text_field($_POST['itemid']);

		$replace_list = array();
		for ($i = 0; ; $i++)
		{
			if (empty($post['standalonesearch' . $i]) || empty($post['standalonereplace' . $i]))
				break;

			$replace_list[] = array(
					'search' => str_replace('/', '\\/', sanitize_text_field($post['standalonesearch' . $i])),
					'replace' => str_replace('/', '\\/', sanitize_text_field($post['standalonereplace' . $i]))
			);
		}

		global $wpdb;

		if (!$this->is_db_table_exists())
			$this->create_db_table();

		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";

		$total = 0;

		foreach($replace_list as $replace)
		{
			$search = $replace['search'];
			$replace = $replace['replace'];

			if ($allitems)
			{
				$ret = $wpdb->query( $wpdb->prepare(
						"UPDATE $table_name SET data = REPLACE(data, %s, %s) WHERE INSTR(data, %s) > 0",
						$search,
						$replace,
						$search
				));
			}
			else
			{
				$ret = $wpdb->query( $wpdb->prepare(
						"UPDATE $table_name SET data = REPLACE(data, %s, %s) WHERE INSTR(data, %s) > 0 AND id = %d",
						$search,
						$replace,
						$search,
						$itemid
				));
			}

			if ($ret > $total)
				$total = $ret;
		}

		if (!$total)
		{
			return array(
					'success' => false,
					'message' => 'No carousel modified' .  (isset($wpdb->lasterror) ? $wpdb->lasterror : '')
			);
		}

		return array(
				'success' => true,
				'message' => sprintf( _n( '%s carousel', '%s carousels', $total), $total) . ' modified'
		);
	}

	function replace_data($replace_list, $data)
	{
		foreach($replace_list as $replace)
		{
			$data = str_replace($replace['search'], $replace['replace'], $data);
		}
	
		return $data;
	}
	
	function eacape_html_quotes($str) {
	
		$result = str_replace("<", "&lt;", $str);
		$result = str_replace('>', '&gt;', $result);
		$result = str_replace("\'", "&#39;", $result);
		$result = str_replace('\"', '&quot;', $result);
		$result = str_replace("'", "&#39;", $result);
		$result = str_replace('"', '&quot;', $result);
		return $result;
	}
	
	function import_3dcarousel($post, $files)
	{
		if (!isset($files['importxml']))
		{
			return array(
					'success' => false,
					'message' => 'No file or invalid file sent.'
			);
		}

		if (!empty($files['importxml']['error']))
		{
			$message = 'XML file error.';

			switch ($files['importxml']['error']) {
				case UPLOAD_ERR_NO_FILE:
					$message = 'No file sent.';
					break;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$message = 'Exceeded filesize limit.';
					break;
			}

			return array(
					'success' => false,
					'message' => $message
			);
		}

		if ($files['importxml']['type'] != 'text/xml')
		{
			return array(
					'success' => false,
					'message' => 'Not an xml file'
			);
		}

		add_filter( 'wp_check_filetype_and_ext', 'wonderplugin_3dcarousel_wp_check_filetype_and_ext', 10, 4);
		
		$xmlfile = wp_handle_upload($files['importxml'], array(
				'test_form' => false,
				'mimes' => array('xml' => 'text/xml')
		));

		remove_filter( 'wp_check_filetype_and_ext', 'wonderplugin_3dcarousel_wp_check_filetype_and_ext');
		
		if ( empty($xmlfile) || !empty( $xmlfile['error'] ) ) {
			return array(
					'success' => false,
					'message' => (!empty($xmlfile) && !empty( $xmlfile['error'] )) ? $xmlfile['error']: 'Invalid xml file'
			);
		}

		$content = file_get_contents($xmlfile['file']);

		$xmlparser = xml_parser_create();
		xml_parse_into_struct($xmlparser, $content, $values, $index);
		xml_parser_free($xmlparser);

		if (empty($index) || empty($index['WONDERPLUGIN3DCAROUSEL']) || empty($index['ID']))
		{
			return array(
					'success' => false,
					'message' => 'Not an exported xml file'
			);
		}

		$keepid = (!empty($post['keepid'])) ? true : false;
		$authorid = sanitize_text_field($post['authorid']);

		$replace_list = array();
		for ($i = 0; ; $i++)
		{
			if (empty($post['olddomain' . $i]) || empty($post['newdomain' . $i]))
				break;

			$replace_list[] = array(
					'search' => str_replace('/', '\\/', sanitize_text_field($post['olddomain' . $i])),
					'replace' => str_replace('/', '\\/', sanitize_text_field($post['newdomain' . $i]))
			);
		}

		$items = Array();
		foreach($index['ID'] as $key => $val)
		{
			$items[] = Array(
					'id' => ($keepid ? $values[$index['ID'][$key]]['value'] : 0),
					'name' => $values[$index['NAME'][$key]]['value'],
					'data' => $this->replace_data($replace_list, $values[$index['DATA'][$key]]['value']),
					'time' => $values[$index['TIME'][$key]]['value'],
					'authorid' => $authorid
			);
		}

		if (empty($items))
		{
			return array(
					'success' => false,
					'message' => 'No 3dcarousel found'
			);
		}

		global $wpdb;

		if (!$this->is_db_table_exists())
			$this->create_db_table();

		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";

		$total = 0;
		foreach($items as $item)
		{
			$ret = $wpdb->query($wpdb->prepare(
					"
					INSERT INTO $table_name (id, name, data, time, authorid)
					VALUES (%d, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE
					name=%s, data=%s, time=%s, authorid=%s
					",
					$item['id'], $item['name'], $item['data'], $item['time'], $item['authorid'],
					$item['name'], $item['data'], $item['time'], $item['authorid']
			));

			if ($ret)
				$total++;
		}

		if (!$total)
		{
			return array(
					'success' => false,
					'message' => 'No 3dcarousel imported' .  (isset($wpdb->lasterror) ? $wpdb->lasterror : '')
			);
		}

		return array(
				'success' => true,
				'message' => sprintf( _n( '%s 3dcarousel', '%s 3dcarousels', $total), $total) . ' imported'
		);

	}

	function export_3dcarousel()
	{
		if ( !check_admin_referer('wonderplugin-3dcarousel', 'wonderplugin-3dcarousel-export') || !isset($_POST['all3dcarousel']) || !isset($_POST['carouselid']) || !is_numeric($_POST['carouselid']) )
			exit;

		$all3dcarousel = sanitize_text_field($_POST['all3dcarousel']);
		$carouselid = sanitize_text_field($_POST['carouselid']);

		if ($all3dcarousel)
			$data = $this->get_list_data(true);
		else
			$data = array($this->get_list_item_data($carouselid));

		header('Content-Description: File Transfer');
		header("Content-Disposition: attachment; filename=wonderplugin_3dcarousel_export.xml");
		header('Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true);
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: 0");
		$output = fopen("php://output", "w");

		echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";
		echo "<WONDERPLUGIN3DCAROUSEL>\r\n";
		foreach($data as $row)
		{
			if (empty($row))
				continue;

			echo "<ID>" . intval($row["id"]) . "</ID>\r\n";
			echo "<NAME>" . $this->xml_cdata($row["name"]) . "</NAME>\r\n";
			echo "<DATA>" . $this->xml_cdata($row["data"]) . "</DATA>\r\n";
			echo "<TIME>" . $this->xml_cdata($row["time"]) . "</TIME>\r\n";
			echo "<AUTHORID>" . $this->xml_cdata($row["authorid"]) . "</AUTHORID>\r\n";
		}
		echo '</WONDERPLUGIN3DCAROUSEL>';

		fclose($output);
		exit;
	}

	function get_list_item_data($id)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";

		return $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) , ARRAY_A);
	}
		
	function find_id_by_name($itemname)
	{
		$list = $this->get_list_data();
	
		$id = null;
	
		foreach($list as $item)
		{
			if (strcasecmp($item['name'], $itemname) == 0)
			{
				$id = $item['id'];
				break;
			}
		}
	
		return $id;
	}
	
	function get_multilingual_slide_text($slide, $attr, $currentlang) {

		$result = !empty($slide->{$attr}) ? $slide->{$attr} : '';

		if ($this->multilingual && !empty($slide->langs) )		
		{
			$langs = json_decode($slide->langs, true);
			if ( !empty($langs) && array_key_exists($currentlang, $langs) && array_key_exists($attr, $langs[$currentlang]))
			{
				$result = html_entity_decode($langs[$currentlang][$attr]);
			}
		}

		return $result;
	}

	function generate_lightbox_code($id, $data, $slide) {

		$image_code = '<a';
		if (!empty($data->aextraprops))
			$image_code .= ' ' . $data->aextraprops;
		
		if ($slide->type == 10)
		{
			$image_code .= ' data-href="';
		}
		else
		{
			$image_code .= ' href="';
		}
				
		if ($slide->type == 0)
		{
			$image_code .= $slide->image;
		}
		else if ($slide->type == 1)
		{
			$image_code .= $slide->mp4;
			if ($slide->webm)
				$image_code .= '" data-webm="' . $slide->webm;
		}
		else if ($slide->type == 2 || $slide->type == 3 || $slide->type == 10)
		{
			$image_code .= $slide->video;
		}
		
		$image_code .= '"';

		if ($slide->title && strlen($slide->title) > 0 && ( !isset($data->usedatatitle) || (strtolower($data->usedatatitle) === 'false')))
		{
			$image_code .= ' title="';
			$image_code .= $this->eacape_html_quotes($slide->title);
			$image_code .= '"';
		}
		
		if ($slide->lightboxsize)
			$image_code .= ' data-width="' .  $slide->lightboxwidth . '" data-height="' .  $slide->lightboxheight . '"';
		
		$image_code .= ' data-thumbnail="' . $slide->thumbnail . '"';
		
		$image_code .= ' class="wp3dcarousellightbox wp3dcarousellightbox-' . $id . '"';
		if ( !isset($data->lightboxnogroup) || strtolower($data->lightboxnogroup) !== 'true' )
			$image_code .= ' data-group="wp3dcarousellightbox-' . $id . '"';
		
		if ($slide->type == 10)
			$image_code .= ' data-ytplaylist=1';

		return $image_code;
	}

	function generate_button_code($id, $data, $slide) {

		$button_code = '';

		if (isset($slide->button) && strlen($slide->button) > 0)
		{
			if (isset($slide->buttonlightbox) && strtolower($slide->buttonlightbox) === 'true')
			{
				$button_code .= $this->generate_lightbox_code($id, $data, $slide);
			}
			else if ($slide->buttonlink && strlen($slide->buttonlink) > 0)
			{
				$button_code .= '<a href="' . $slide->buttonlink . '"';
				if ($slide->buttonlinktarget && strlen($slide->buttonlinktarget) > 0)
					$button_code .= ' target="' . $slide->buttonlinktarget . '"';
			}

			if ( (isset($slide->buttonlightbox) && strtolower($slide->buttonlightbox) === 'true')  || ($slide->buttonlink && strlen($slide->buttonlink) > 0) )
			{
				$button_code .= '>';
			}

			$button_code .= '<button class="' . $slide->buttoncss . '">' . $slide->button . '</button>';

			if ( (isset($slide->buttonlightbox) && strtolower($slide->buttonlightbox) === 'true')  || ($slide->buttonlink && strlen($slide->buttonlink) > 0) )
			{
				$button_code .= '</a>';
			}
		}

		return $button_code;
	}

	function escape_css_for_js($str) {

		$str = str_replace('\\', '\\\\', $str);
		return str_replace('"', '\"', $str);
	}
	
	function generate_body_code($id, $itemname, $has_wrapper) {
		
		if ( !isset($id) )
		{
			if ( isset($itemname) )
			{
				$id = $this->find_id_by_name($itemname);
			}
		}
		
		if ( !isset($id) )
		{
			return '<p>Please specify a valid 3dcarousel id or name.</p>';
		}
		
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
		
		if ( !$this->is_db_table_exists() )
		{
			return '<p>The specified 3dcarousel does not exist.</p>';
		}
		
		$ret = "";
		$item_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		if ($item_row != null)
		{			
			
			try {
				$data = str_replace("\\\\", "\\", $item_row->data);
				$data = json_decode(trim($data));
				if (is_null($data))
				{
					throw new Exception("JSON Decode Error"); 
				}
			} catch (Exception $e) {
				$data = str_replace('\\\"', '"', $item_row->data);
				$data = str_replace("\\\'", "'", $data);
				$data = str_replace("\\\\", "\\", $data);
				$data = json_decode(trim($data));
			}
						
			if ( isset($data->publish_status) && ($data->publish_status === 0) )
			{
				return '<p>The specified 3dcarousel is trashed.</p>';
			}
			
			$cssjs = '';
			$removeinlinecss = !(isset($data->removeinlinecss) && strtolower($data->removeinlinecss) === 'false');

			add_filter('safe_style_css', 'wonderplugin_3dcarousel_css_allow');
			add_filter('wp_kses_allowed_html', 'wonderplugin_3dcarousel_tags_allow', 'post');
			foreach($data as &$value)
			{
				if ( is_string($value) )
					$value = wp_kses_post($value);
			}
			remove_filter('wp_kses_allowed_html', 'wonderplugin_3dcarousel_tags_allow', 'post');
			remove_filter('safe_style_css', 'wonderplugin_3dcarousel_css_allow');
			
			if (isset($data->customcss) && strlen($data->customcss) > 0)
			{
				$customcss = str_replace("\r", " ", $data->customcss);
				$customcss = str_replace("\n", " ", $customcss);
				$customcss = str_replace("3DCAROUSELID", $id, $customcss);
				if ($removeinlinecss)
				{
					$cssjs .= 'wp3dcarousel_' . $id . '_appendcss("' . $this->escape_css_for_js($customcss) . '");';
				}
				else
				{
					$ret .= '<style>' . $customcss . '</style>';
				}
			}
			
			if (isset($data->skincss) && strlen($data->skincss) > 0)
			{
				$skincss = str_replace("\r", " ", $data->skincss);
				$skincss = str_replace("\n", " ", $skincss);
				$skincss = str_replace('#wonderplugin3dcarousel-3DCAROUSELID',  '#wonderplugin3dcarousel-' . $id, $skincss);
				if ($removeinlinecss)
				{
					$cssjs .= 'wp3dcarousel_' . $id . '_appendcss("' . $this->escape_css_for_js($skincss) . '");';
				}
				else
				{
					$ret .= '<style>' . $skincss . '</style>';
				}
			}
						
			if (isset($data->lightboxadvancedoptions) && strlen($data->lightboxadvancedoptions) > 0)
			{
				$ret .= '<div id="wp3dcarousellightbox_advanced_options_' . $id . '" ' . stripslashes($data->lightboxadvancedoptions) . ' ></div>';
			}
			
			$ret .= '<div class="wonderplugin3dcarousel-container" id="wonderplugin3dcarousel-container-' . $id . '" style="display:block;position:relative;margin:0 auto;padding:0;">';

			$has_woocommerce = false;
			if (class_exists('WooCommerce') && isset($data->addwoocommerceclass) && (strtolower($data->addwoocommerceclass) === 'true'))
			{
				$has_custom = false;
				if (isset($data->slides) && count($data->slides) > 0)
				{
					foreach ($data->slides as $index => $slide)
					{
						if ($slide->type == 7)
						{
							$has_custom = true;
							break;
						}
					}
				}
				if ($has_custom)
					$has_woocommerce = true;
			}
			
			// div data tag
			$ret .= '<div class="wonderplugin3dcarousel' . ($has_woocommerce ? ' woocommerce' : '') . '" id="wonderplugin3dcarousel-' . $id . '" data-carouselid="' . $id . '" data-width="' . $data->width . '" data-height="' . $data->height . '" data-skin="' . $data->skin . '"';
			
			if (isset($data->dataoptions) && strlen($data->dataoptions) > 0)
			{
				$ret .= ' ' . stripslashes($data->dataoptions);
			}
			
			$boolOptions = array('donotzoomin', 'addimgoverlay', 'applylinktohoveroverlay',
					'showtitle', 'showdescription', 'showbutton',
					'arrowsinsidelist',
					'showplayvideo', 'lightboxinityoutube', 'lightboxinitvimeo', 
					'showimgtitle', 'addwoocommerceclass', 'usedatatitle',
					'autoslide', 'autoslidewhenscrollinview', 'pauseonmouseover', 'onlyenablelightboxoncenter', 'loop', 'loopslide', 'random', 'onlyenableweblinkoncenter',
					'donotinit', 'addinitscript', 'doshortcodeontext', 'triggerresize', 'removeinlinecss',
					'lightboxresponsive', 'lightboxshownavigation', 'lightboxnogroup', 'lightboxshowtitle', 'lightboxshowdescription', 'lightboxshownavcontrol', 'lightboxhidenavdefault',
					'lightboximagekeepratio', 'lightboxshowsocial', 'lightboxshowfacebook', 'lightboxshowtwitter', 'lightboxshowpinterest', 'lightboxsocialrotateeffect', 'lightboxfullscreenmode', 'lightboxcloseonoverlay', 'lightboxvideohidecontrols', 'lightboxautoslide', 'lightboxshowtimer', 'lightboxshowplaybutton', 'lightboxalwaysshownavarrows', 'lightboxshowtitleprefix');
			foreach ( $boolOptions as $key )
			{
				if (isset($data->{$key}) )
					$ret .= ' data-' . $key . '="' . ((strtolower($data->{$key}) === 'true') ? 'true': 'false') .'"';
			}
			
			$valOptions = array('template', 'scalemode', 'imghovereffect', 'itembgcolor', 'itemborder', 'carouselmargin', 'medium_carouselmargin', 'small_carouselmargin', 'textstyle', 'texteffect', 
					'arrowstyle', 'arrowpos', 'arrowimage', 'arrowwidth', 'arrowheight', 'arrowanimation',
					'navstyle', 'navimage', 'navwidth', 'navheight', 'navspacing',
					'playvideoimage', 'playvideoposition', 'ga4account', 'googleanalyticsaccount',
					'imgtitle',
					'autoslidedir', 'slideinterval', 'firstitem',
					'visibleitems', 'perspective', 'xdis', 'zdis', 'yrotate', 'transition', 
					'medium_screenwidth', 'medium_width', 'medium_height', 'medium_visibleitems', 'medium_perspective', 'medium_xdis', 'medium_zdis', 'medium_yrotate', 'medium_transition',
					'small_screenwidth', 'small_width', 'small_height', 'small_visibleitems', 'small_perspective', 'small_xdis', 'small_zdis', 'small_yrotate', 'small_transition',
					'facemode', 'itemspace', 'rotatex', 'scaleratio',
					'triggerresizedelay',
					'lightboxthumbwidth', 'lightboxthumbheight', 'lightboxthumbtopmargin', 'lightboxthumbbottommargin', 'lightboxbarheight', 'lightboxtitlebottomcss', 'lightboxdescriptionbottomcss', 'lightboxnavbgcolor',
					'lightboxtitlestyle', 'lightboximagepercentage', 'lightboxdefaultvideovolume', 'lightboxoverlaybgcolor', 'lightboxoverlayopacity', 'lightboxbgcolor', 'lightboxtitleprefix', 'lightboxtitleinsidecss', 'lightboxdescriptioninsidecss',
					'lightboxsocialposition', 'lightboxsocialpositionsmallscreen', 'lightboxsocialdirection', 'lightboxsocialbuttonsize', 'lightboxsocialbuttonfontsize',
					'lightboxslideinterval', 'lightboxtimerposition', 'lightboxtimerheight:', 'lightboxtimercolor', 'lightboxtimeropacity', 'lightboxbordersize', 'lightboxborderradius');
			foreach ( $valOptions as $key )
			{
				if (isset($data->{$key}) )
					$ret .= ' data-' . $key . '="' . $data->{$key} . '"';
			}
			
			$ret .= ' data-jsfolder="' . WONDERPLUGIN_3DCAROUSEL_URL . 'engine/"'; 
				
			$ret .= ' style="display:none;position:relative;margin:0 auto;width:100%;max-width:100%;"';
			
			$ret .= ' >';
			
			$currentlang = $this->multilingual ? (!empty($data->lang) ? $data->lang : $this->currentlang) : null;

			if (isset($data->slides) && count($data->slides) > 0)
			{
				$ret .= '<div class="wonderplugin3dcarousel-list-container">';
				$ret .= '<ul class="wonderplugin3dcarousel-list">';
				
				$count = 0;
				
				foreach ($data->slides as $index => $slide)
				{		
					add_filter('safe_style_css', 'wonderplugin_3dcarousel_css_allow');
					add_filter('wp_kses_allowed_html', 'wonderplugin_3dcarousel_tags_allow', 'post');
					foreach($slide as &$value)
					{
						if ( is_string($value) )
							$value = wp_kses_post($value);
					}
					remove_filter('wp_kses_allowed_html', 'wonderplugin_3dcarousel_tags_allow', 'post');
					remove_filter('safe_style_css', 'wonderplugin_3dcarousel_css_allow');
					
					if ($this->multilingual && $currentlang != $this->defaultlang)
					{
						$slide->title = $this->get_multilingual_slide_text($slide, 'title', $currentlang);
						$slide->description = $this->get_multilingual_slide_text($slide, 'description', $currentlang);
						$slide->alt = $this->get_multilingual_slide_text($slide, 'alt', $currentlang);
					}

					if ( isset($data->doshortcodeontext) && (strtolower($data->doshortcodeontext) === 'true') )
					{
						if ($slide->title && strlen($slide->title) > 0)
							$slide->title = do_shortcode($slide->title);
						
						if ($slide->description && strlen($slide->description) > 0)
							$slide->description = do_shortcode($slide->description);
					}
					
					$boolOptions = array('lightbox', 'displaythumbnail', 'lightboxsize', 'weblinklightbox');
					foreach ( $boolOptions as $key )
					{
						if (isset($slide->{$key}) )
							$slide->{$key} = ((strtolower($slide->{$key}) === 'true') ? true: false);
					}
					
					if ($slide->type == 6)
					{
						$items = $this->get_post_items($slide, $data);
						$ret .= $this->create_post_code($items, $data, $id);
					}
					else if ($slide->type == 7)
					{
						$items = $this->get_custom_post_items($slide);
						$ret .= $this->create_custom_post_code($items, $data, $id);
					}
					else
					{
						if ($slide->type == 10)
						{
							if ($count > 0)
								$ret .= '</li>';
							
							$ret .= '<li class="wonderplugin3dcarousel-item lightboxcontainer"';
							$slide->image = '__IMAGE__';
							$slide->thumbnail = '__THUMBNAIL__';
							$slide->video = '__VIDEO__';
							$slide->title = '__TITLE__';
							$slide->description = '__DESCRIPTION__';
							$ret .= ' data-youtubeapikey="' . $slide->youtubeapikey . '" data-youtubeplaylistid="' . $slide->youtubeplaylistid . '" data-youtubeplaylistmaxresults="' . $slide->youtubeplaylistmaxresults . '"';
							$ret .= '>';
						}
						else
						{
							if ($count > 0)
								$ret .= '</li>';
							
							$ret .= '<li class="wonderplugin3dcarousel-item lightboxcontainer">';

							if (!empty($slide->button))
								$ret .= '<div class="wonderplugin3dcarousel-item-button">' . $this->generate_button_code($id, $data, $slide) . '</div>';
						}
						
						$count++;
						
						$ret .= '<div class="wonderplugin3dcarousel-item-extrainfo lightboxextrainfo">';

						if ($slide->title && strlen($slide->title) > 0)
							$ret .= '<div class="wonderplugin3dcarousel-item-text-title lightboxtitle">' . $this->eacape_html_quotes($slide->title) . '</div>';
						
						if ($slide->description && strlen($slide->description) > 0)
							$ret .= '<div class="wonderplugin3dcarousel-item-text-description lightboxdescription">' . $this->eacape_html_quotes($slide->description) . '</div>';
						
						$ret .= '</div>';

						$ret .= '<div class="wonderplugin3dcarousel-item-container">';
						
						$image_code = '';
						if ( isset($slide->lightbox) && $slide->lightbox )
						{
							$image_code .= $this->generate_lightbox_code($id, $data, $slide);
							$image_code .= '>';
						}
						else if ($slide->weblink && strlen($slide->weblink) > 0)
						{
							$image_code .= '<a';
							if (!empty($data->aextraprops))
								$image_code .= ' ' . $data->aextraprops;
							$image_code .= ' href="' . $slide->weblink . '"';
							
							if ($slide->clickhandler && strlen($slide->clickhandler) > 0)
								$image_code .= ' onclick="' . str_replace('"', '&quot;', $slide->clickhandler) . '"';
							if ($slide->linktarget && strlen($slide->linktarget) > 0)
								$image_code .= ' target="' . $slide->linktarget . '"';
							if ( isset($slide->weblinklightbox) && $slide->weblinklightbox )
							{
								$image_code .= ' class="wp3dcarousellightbox wp3dcarousellightbox-' . $id . '"';
								if ( !isset($data->lightboxnogroup) || strtolower($data->lightboxnogroup) !== 'true' )
									$image_code .= ' data-group="wp3dcarousellightbox-' . $id . '"';
								if ($slide->lightboxsize)
									$image_code .= ' data-width="' .  $slide->lightboxwidth . '" data-height="' .  $slide->lightboxheight . '"';
							}
							else
							{
								$image_code .= ' class="wonderplugin3dcarousel-item-weblink"';
							}
							$image_code .= '>';
						}
							
						$image_code .= '<div class="wonderplugin3dcarousel-img-container"><img class="wonderplugin3dcarousel-img"';
						
						if (!empty($data->imgextraprops))
							$image_code .= ' ' . $data->imgextraprops;
						
						if ($slide->type == 10)
							$image_code .= ' data-srcyt="';
						else
							$image_code .= ' src="';
						
						if ( isset($slide->displaythumbnail) && $slide->displaythumbnail )
							$image_code .= $slide->thumbnail . '"';
						else
							$image_code .= $slide->image . '"';
						
						if ( isset($slide->altusetitle) && (strtolower($slide->altusetitle) === 'false') && isset($slide->alt) )
							$image_code .= ' alt="' . $this->eacape_html_quotes(strip_tags($slide->alt)) . '"';
						else
							$image_code .= ' alt="' . $this->eacape_html_quotes(strip_tags($slide->title)) . '"';
						
						if ( isset($data->showimgtitle) && (strtolower($data->showimgtitle) === 'true') && isset($data->imgtitle) )
						{
							if ($data->imgtitle == 'title' && isset($slide->title))
								$image_code .= ' title="' . $this->eacape_html_quotes($slide->title) . '"';
							else if ($data->imgtitle == 'description' && isset($slide->description))
								$image_code .= ' title="' . $this->eacape_html_quotes($slide->description) . '"';
							else if ($data->imgtitle == 'alt' && isset($slide->alt))
								$image_code .= ' title="' . $this->eacape_html_quotes($slide->alt) . '"';
						}

						if (!$slide->lightbox)
						{
							if ($slide->type == 1)
							{
								$image_code .= ' data-video="' . $slide->mp4 . '"';
								if ($slide->webm)
									$image_code .= ' data-videowebm="' . $slide->webm . '"';
							}
							else if ($slide->type == 2 || $slide->type == 3 || $slide->type == 10)
							{
								$image_code .= ' data-video="' . $slide->video . '"';
							}
						}
						$image_code .= ' /></div>';
						
						if ($slide->lightbox || (!$slide->lightbox && $slide->weblink && strlen($slide->weblink) > 0))
						{
							$image_code .= '</a>';
						}
						
						$title_code = '';
						if ($slide->title && strlen($slide->title) > 0)
							$title_code = $slide->title;
						 
						$description_code = '';
						if ($slide->description && strlen($slide->description) > 0)
							$description_code = $slide->description;
						
						$skin_template = str_replace('&amp;',  '&', $data->skintemplate);
						$skin_template = str_replace('&lt;',  '<', $skin_template);
						$skin_template = str_replace('&gt;',  '>', $skin_template);
						
						$skin_template = str_replace('__IMAGE__',  $image_code, $skin_template);
						$skin_template = str_replace('__TITLE__',  $title_code, $skin_template);
						$skin_template = str_replace('__DESCRIPTION__',  $description_code, $skin_template);
						$skin_template = str_replace('__BUTTON__',  $this->generate_button_code($id, $data, $slide), $skin_template);

						$skin_template = str_replace('__HREF__',  empty($slide->weblink) ? '' : $slide->weblink, $skin_template);
						$skin_template = str_replace('__CLICKHANDLER__',  empty($slide->clickhandler) ? '' : $slide->clickhandler, $skin_template);
						$skin_template = str_replace('__TARGET__',  empty($slide->linktarget) ? '' : $slide->linktarget, $skin_template);

						$ret .= $skin_template;	
					
						$ret .= '</div>';
					}
				}
				
				if ($count > 0)
					$ret .= '</li>';
				
				$ret .= '</ul>';
				$ret .= '</div>';		
			}
			if ('F' == 'F')
				$ret .= '<div class="wonderplugin-3dcarousel-engine"><a href="https://www.wonderplugin.com/wordpress-3dcarousel/" title="'. get_option('wonderplugin-3dcarousel-engine')  .'">' . get_option('wonderplugin-3dcarousel-engine') . '</a></div>';
			$ret .= '</div>';
			
			$ret .= '</div>';
			
			if (!empty($cssjs))
			{
				$ret .= '<script>function wp3dcarousel_' . $id . '_appendcss(csscode) {var head=document.head || document.getElementsByTagName("head")[0];var style=document.createElement("style");head.appendChild(style);style.type="text/css";if (style.styleSheet){style.styleSheet.cssText=csscode;} else {style.appendChild(document.createTextNode(csscode));}};' . $cssjs . '</script>';
			}

			if (isset($data->addinitscript) && strtolower($data->addinitscript) === 'true')
			{
				$ret .= '<script>jQuery(document).ready(function(){jQuery(".wonderplugin-3dcarousel-engine").css({display:"none"});jQuery(".wonderplugin3dcarousel").wonderplugin3dcarousel({forceinit:true});});</script>';
			}
			
			if (isset($data->triggerresize) && strtolower($data->triggerresize) === 'true')
			{
				$ret .= '<script>jQuery(document).ready(function(){';
				if ($data->triggerresizedelay > 0)
					$ret .= 'setTimeout(function(){jQuery(window).trigger("resize");},' . $data->triggerresizedelay . ');';
				else
					$ret .= 'jQuery(window).trigger("resize");'; 
				$ret .= '});</script>';
			}
			
			if (isset($data->customjs) && strlen($data->customjs) > 0)
			{
				$customjs = str_replace("\r", " ", $data->customjs);
				$customjs = str_replace("\n", " ", $customjs);
				$customjs = str_replace('&lt;',  '<', $customjs);
				$customjs = str_replace('&gt;',  '>', $customjs);
				$customjs = str_replace("3DCAROUSELID", $id, $customjs);
				$ret .= '<script>' . $customjs . '</script>';
			}
		}
		else
		{
			$ret = '<p>The specified 3dcarousel id does not exist.</p>';
		}
		return $ret;
	}
	
	function replace_custom_field($postdata, $postmeta, $field, $textlength) {
		
		$postdata = array_merge($postdata, $postmeta);
		
		$postdata = apply_filters( 'wonderplugin_3dcarousel_custom_post_field_content', $postdata );
		
		$result = $field;
		
		preg_match_all('/\\%(.*?)\\%/s', $field, $matches);
		
		if (!empty($matches) && count($matches) > 1)
		{
			foreach($matches[1] as $match)
			{
				$replace = '';
				if (array_key_exists($match, $postdata))
				{	
					if (is_array($postdata[$match]))
					{
						$replace = implode(' ', $postdata[$match]);
					}
					else
					{
						$replace = $postdata[$match];
					}
					
					if ($match == 'post_content' || $match == 'post_excerpt')
						$replace = wonderplugin_3dcarousel_wp_trim_words($replace, $textlength);

					if ($match == 'post_author' && is_numeric($replace))
					{
						$replace = get_the_author_meta( 'display_name', $replace );
					}
				}
				$result = str_replace('%' . $match . '%', $replace, $result);
			}
		}
		
		return $result;
	}
	
	function get_custom_post_items($options) {
					
		global $post;
		
		$items = array();
		
		$args = array(
					'post_type' 		=> $options->customposttype,
					'posts_per_page'	=> $options->postnumber,
					'post_status' 	=> 'publish'
				);
		
		if (isset($options->postdaterange) && (strtolower($options->postdaterange) === 'true') && isset($options->postdaterangeafter) )
		{
			$args['date_query'] = array(
					'after' => date('Y-m-d', strtotime('-' . $options->postdaterangeafter . ' days'))
				);
		}
		
		$taxonomytotal = 0;
		
		$tax_query = array();
		
		for ($i = 0; ; $i++)
		{
			if (isset($options->{'taxonomy' . $i}) && isset($options->{'term' . $i}) && ($options->{'taxonomy' . $i} != '-1') && ($options->{'term' . $i} != '-1') )
			{
				$taxonomytotal++;
				$tax_query[] = array(
						'taxonomy' => $options->{'taxonomy' . $i},
						'field'    => 'slug',
						'terms'    => $options->{'term' . $i}
				);
			}
			else
			{
				break;
			}
		}
		
		if ($taxonomytotal > 1)
		{
			$tax_query['relation'] = $options->taxonomyrelation;
		}
		
		if ($taxonomytotal > 0)
		{
			$args['tax_query'] = $tax_query;
		}

		// meta _featured only works for WooCommerce 1 and 2
		if ( class_exists('WooCommerce') )
		{
			global $woocommerce;
			if ( version_compare( $woocommerce->version, '3.0', ">=") )
				$options->metafeatured = 'false';	
		}
		
		// woocommerce meta query
		if ( class_exists('WooCommerce') && ((isset($options->metatotalsales) && (strtolower($options->metatotalsales) === 'true')) || (isset($options->metafeatured) && (strtolower($options->metafeatured) === 'true'))) )
		{
			$meta_query = array();
			
			if (isset($options->metatotalsales) && (strtolower($options->metatotalsales) === 'true'))
			{
				$meta_query[] = array(
						'key'       => 'total_sales',
						'value'     => '0',
						'compare'   => '>='
					);
				
				$args['orderby'] = 'total_sales';
			}
			
			if (isset($options->metafeatured) && (strtolower($options->metafeatured) === 'true'))
			{
				$meta_query[] = array(
						'key'       => '_featured',
            			'value'     => 'yes',
            			'compare'   => '='
					);
			}
			
			if ( (isset($options->metatotalsales) && (strtolower($options->metatotalsales) === 'true')) && (isset($options->metafeatured) && (strtolower($options->metafeatured) === 'true')) )
			{
				$meta_query['relation'] = $options->metarelation;
			}
			
			$args['meta_query'] = $meta_query;
		}
		
		if ( class_exists('WooCommerce') && isset($options->metaonsale) && (strtolower($options->metaonsale) === 'true') )
		{
			$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		}
		
		$query = new WP_Query($args);
		if ($query->have_posts())
		{
			while ( $query->have_posts() ) 
			{
				$query->the_post();
				
				if ($post)
				{
					$postdata = get_object_vars($post);
						
					$featured_image = '';
					if (has_post_thumbnail($postdata['ID']))
					{
						$attachment_image = wp_get_attachment_image_src(get_post_thumbnail_id($postdata['ID']), 'full');
						$featured_image = $attachment_image[0];
					}
					$postdata['featured_image'] = $featured_image;
					
					$postmeta = get_post_meta($postdata['ID']);
					
					if (class_exists('WooCommerce') && isset($postdata['ID']))
					{
						global $woocommerce;
						$woocommerce_v3 = version_compare($woocommerce->version, 3, ">=");

						$product = wc_get_product($postdata['ID']);
						if ($product)
						{
							$postmeta['wc_price_html'] = $product->get_price_html();
							$postmeta['wc_price'] = wc_price( $product->get_price() );
							$postmeta['wc_regular_price'] = wc_price( $product->get_regular_price() );
							$postmeta['wc_sale_price'] = wc_price( $product->get_sale_price() );
							$postmeta['wc_review_count'] = $product->get_review_count();
							$postmeta['wc_rating_count'] = $product->get_rating_count();
							$postmeta['wc_average_rating'] = $product->get_average_rating();
							$postmeta['wc_total_sales'] = (int) get_post_meta( $postdata['ID'], 'total_sales', true );
							
							if ($woocommerce_v3)
								$postmeta['wc_rating_html'] = wc_get_rating_html($product->get_average_rating(), $product->get_rating_count());
							else
								$postmeta['wc_rating_html'] = $product->get_rating_html();

							if (method_exists($product,'get_category_ids'))
							{
								$cat_ids = $product->get_category_ids();
								$cat_index = 0;
									
								foreach($cat_ids as $cat_id)
								{
									if( $term = get_term_by( 'id', $cat_id, 'product_cat' ) ){
											
										if ($cat_index == 0)
										{
											$postmeta['wc_product_cat_id'] = $cat_id;
											$postmeta['wc_product_cat_name'] = $term->name;
											$postmeta['wc_product_cat_slug'] = $term->slug;
											$postmeta['wc_product_cat_link'] = get_term_link( $term );
										}
											
										$postmeta['wc_product_cat_id' . $cat_index] = $cat_id;
										$postmeta['wc_product_cat_name' . $cat_index] = $term->name;
										$postmeta['wc_product_cat_slug' . $cat_index] = $term->slug;
										$postmeta['wc_product_cat_link' . $cat_index] = get_term_link( $term );
											
										$cat_index++;
									}
								}
							}
						}	
					}
					
					$title = $this->replace_custom_field($postdata, $postmeta, $options->titlefield, $options->textlength);					
					$description = $this->replace_custom_field($postdata, $postmeta, $options->descriptionfield, $options->textlength);
					$image = $this->replace_custom_field($postdata, $postmeta, $options->imagefield, $options->textlength);
					$postlink = get_permalink($postdata['ID']);
					
					$post_item = array(
							'type'					=> 7,
							'image'					=> $image,
							'thumbnail'				=> $image,
							'title'					=> $title,
							'description'			=> $description,
							'postlink'				=> $postlink,
							'datetime'				=> $postdata['post_date'],
							'date'					=> date('Y-m-d', strtotime($postdata['post_date'])),
							'titlelink'				=> (strtolower($options->titlelink) === 'true'),
							'imageaction'			=> (strtolower($options->imageaction) === 'true'),
							'imageactionlightbox'	=> (strtolower($options->imageactionlightbox) === 'true'),
							'openpostinlightbox'		=> (strtolower($options->openpostinlightbox) === 'true'),
							'postlightboxsize'		=> (strtolower($options->postlightboxsize) === 'true'),
							'postlightboxwidth'		=> $options->postlightboxwidth,
							'postlightboxheight'	=> $options->postlightboxheight,
							'postlinktarget'		=> $options->postlinktarget
						);
					
					$items[] = (object) $post_item;
				}
				
			}
			wp_reset_postdata();
		}
		
		if (isset($options->postorder) && ($options->postorder == 'ASC'))
			$items = array_reverse($items);
				
		return $items;
	}
	
	function create_custom_post_code($slides, $data, $id) {
		
		$ret = '';
		
		$count = 0;
		
		foreach($slides as $slide)
		{	
			if ($count > 0)
				$ret .= '</li>';

			$ret .= '<li class="wonderplugin3dcarousel-item lightboxcontainer">';
			
			$count++;
			
			$ret .= '<div class="wonderplugin3dcarousel-item-extrainfo lightboxextrainfo">';

			if ($slide->title && strlen($slide->title) > 0)
				$ret .= '<div class="wonderplugin3dcarousel-item-text-title lightboxtitle">' . $this->eacape_html_quotes($slide->title) . '</div>';
			
			if ($slide->description && strlen($slide->description) > 0)
				$ret .= '<div class="wonderplugin3dcarousel-item-text-description lightboxdescription">' . $this->eacape_html_quotes($slide->description) . '</div>';
			
			$ret .= '</div>';

			$ret .= '<div class="wonderplugin3dcarousel-item-container">';
			
			$skin_template = str_replace('&amp;',  '&', $data->skintemplate);
			$skin_template = str_replace('&lt;',  '<', $skin_template);
			$skin_template = str_replace('&gt;',  '>', $skin_template);
			
			$image_code = '';
			
			if ($slide->thumbnail && strlen($slide->thumbnail) > 0)
			{
				if ( $slide->imageaction )
				{					
					if ( $slide->imageactionlightbox )
					{
						$image_code .= '<a';
						if (!empty($data->aextraprops))
							$image_code .= ' ' . $data->aextraprops;
						$image_code .= ' href="';
						
						$image_code .= $slide->image;
						
						$image_code .= '"';

						if ($slide->title && strlen($slide->title) > 0 && (!isset($data->usedatatitle) || (strtolower($data->usedatatitle) === 'false')))
						{
							$image_code .= ' title="';
							$image_code .= $this->eacape_html_quotes($slide->title);
							$image_code .= '"';
						}
	
						if ( $slide->postlightboxsize )
							$image_code .= ' data-width="' .  $slide->postlightboxwidth . '" data-height="' .  $slide->postlightboxheight . '"';
							
						$image_code .= ' data-thumbnail="' . $slide->thumbnail . '"';
							
						$image_code .= ' class="wp3dcarousellightbox wp3dcarousellightbox-' . $id . '"';
						if ( !isset($data->lightboxnogroup) || strtolower($data->lightboxnogroup) !== 'true' )
							$image_code .= ' data-group="wp3dcarousellightbox-' . $id . '"';
						
						$image_code .= '>';
					}
					else if ($slide->postlink && strlen($slide->postlink) > 0)
					{
						$image_code .= '<a';
						if (!empty($data->aextraprops))
							$image_code .= ' ' . $data->aextraprops;
						$image_code .= ' href="' . $slide->postlink . '"';
						
						if ( $slide->openpostinlightbox )
						{
							if ( $slide->postlightboxsize )
								$image_code .= ' data-width="' .  $slide->postlightboxwidth . '" data-height="' .  $slide->postlightboxheight . '"';
								
							$image_code .= ' data-thumbnail="' . $slide->thumbnail . '"';

							$image_code .= ' class="wp3dcarousellightbox wp3dcarousellightbox-' . $id . '"';
							if ( !isset($data->lightboxnogroup) || strtolower($data->lightboxnogroup) !== 'true' )
								$image_code .= ' data-group="wp3dcarousellightbox-' . $id . '"';
						}
						else
						{
							$image_code .= ' class="wonderplugin3dcarousel-item-weblink"';
							if ($slide->postlinktarget && strlen($slide->postlinktarget) > 0)
								$image_code .= ' target="' . $slide->postlinktarget . '"';
						}
						
						$image_code .= '>';
					}
				}
				
				$image_code .= '<div class="wonderplugin3dcarousel-img-container"><img class="wonderplugin3dcarousel-img"';
				
				if (!empty($data->imgextraprops))
					$image_code .= ' ' . $data->imgextraprops;
				
				$image_code .= ' src="' . $slide->thumbnail . '"';
				$image_code .= ' alt="' . $this->eacape_html_quotes(strip_tags($slide->title)) . '"';
				$image_code .= ' /></div>';
				
				if ($slide->imageaction && ($slide->imageactionlightbox || ($slide->postlink && strlen($slide->postlink) > 0)))
				{
					$image_code .= '</a>';
				}
			}
			
			$posttitle = $slide->title;
			if ($slide->titlelink && $slide->postlink && strlen($slide->postlink) > 0 ) 
			{
				$posttitle = '<a class="wonderplugin3dcarousel-posttitle-link" href="' . $slide->postlink . '"';
				if ($slide->postlinktarget && strlen($slide->postlinktarget) > 0)
					$posttitle .= ' target="' . $slide->postlinktarget . '"';
				$posttitle .= '>' . $slide->title . '</a>';
			}
			
			$skin_template = str_replace('__IMAGESRC__',  $slide->image, $skin_template);
			$skin_template = str_replace('__IMAGE__',  $image_code, $skin_template);
			$skin_template = str_replace('__TITLE__',  $posttitle, $skin_template);
			$skin_template = str_replace('__DESCRIPTION__',  $slide->description, $skin_template);
			$skin_template = str_replace('__HREF__',  $slide->postlink, $skin_template);
			$skin_template = str_replace('__TARGET__',  $slide->postlinktarget, $skin_template);
			$skin_template = str_replace('__DATETIME__',  $slide->datetime, $skin_template);
			$skin_template = str_replace('__DATE__',  $slide->date, $skin_template);
			$skin_template = str_replace('__BUTTON__',  '', $skin_template);

			$ret .= $skin_template;
			$ret .= '</div>';
		}
		
		if ($count > 0)
			$ret .= '</li>';
		
		return $ret;
	}
	
	function get_post_items($options, $data) {

		$posts = array();

		$args = array(
				'numberposts' 	=> $options->postnumber,
				'post_status' 	=> 'publish'
		);

		if (isset($options->selectpostbytags) && !empty($options->posttags))
		{
			$args['tag'] = $options->posttags;
		}

		if (isset($options->postdaterange) && isset($options->postdaterangeafter) && (strtolower($options->postdaterange) === 'true'))
		{
			$args['date_query'] = array(
					'after' => date('Y-m-d', strtotime('-' . $options->postdaterangeafter . ' days'))
			);
		}
		else if (isset($options->postdatefromto) && (strtolower($options->postdatefromto) === 'true'))
		{
			$args['date_query'] = array(
					'after' => array('year' => $options->postdatefromyear, 'month' => $options->postdatefrommonth, 'day' => $options->postdatefromday),
					'before' => array('year' => $options->postdatetoyear, 'month' => $options->postdatetomonth, 'day' => $options->postdatetoday),
					'inclusive' => true
			);
		}

		if ($options->postcategory == -1)
		{
			$posts = wp_get_recent_posts($args);
		}
		else
		{
			if ($options->postcategory != -2)
			{
				$args['category'] = $options->postcategory;
			}

			if (!empty($options->postorderby))
			{
				$args['orderby'] = $options->postorderby;
			}

			$posts = get_posts($args);
		}

		$items = array();

		$currentlang = $this->multilingual ? (!empty($data->lang) ? $data->lang : $this->currentlang) : null;

		foreach($posts as $post)
		{
			if (is_object($post))
				$post = get_object_vars($post);

			// WPML
			if ($this->multilingual && defined('ICL_SITEPRESS_VERSION') && version_compare( ICL_SITEPRESS_VERSION, '4.2.8', '<' ) )
			{
				$post_lang_details = apply_filters( 'wpml_post_language_details', NULL, $post['ID'] ) ;
				if ( !empty($post_lang_details) && $post_lang_details['language_code'] != $this->defaultlang)
					continue;

				if ($currentlang != $this->defaultlang)
				{
					$lang_id = apply_filters( 'wpml_object_id', $post['ID'], 'post', FALSE, $currentlang );
					if ( !empty( $lang_id ) )
					{
						$post = get_post( $lang_id, 'ARRAY_A');
					}
				}
			}

			$thumbnail = '';
			$image = '';
			if ( has_post_thumbnail($post['ID']) )
			{
				$featured_thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post['ID']), $options->featuredimagesize);
				$thumbnail = $featured_thumb[0];

				$featured_image = wp_get_attachment_image_src(get_post_thumbnail_id($post['ID']), 'full');
				$image = $featured_image[0];
			}

			$excerpt = $post['post_excerpt'];
			if (empty($excerpt))
			{
				$excerpts = explode( '<!--more-->', $post['post_content'] );
				$excerpt = $excerpts[0];
				$excerpt = strip_tags( str_replace(']]>', ']]&gt;', strip_shortcodes($excerpt)) );
			}
			$excerpt = wonderplugin_3dcarousel_wp_trim_words($excerpt, $options->excerptlength);

			$post_categories = wp_get_post_categories( $post['ID'] );
			$cats = array();
			foreach($post_categories as $cat_id){
					
				$cat = get_category( $cat_id );
				$cats[] = array(
						'id'   => $cat_id,
						'name' => $cat->name,
						'slug' => $cat->slug,
						'link' => get_category_link( $cat_id )
				);
			}
								
			$post_tags = wp_get_post_tags( $post['ID'] );
			$tags = array();
			foreach($post_tags as $tag)
			{
				$tags[] = array(
						'id' 	=> $tag->term_id,
						'name'	=> $tag->name,
						'slug'	=> $tag->slug,
						'link'	=> get_tag_link( $tag->term_id )
				);
			}

			$post_link = get_permalink($post['ID']);
			
			if (!isset($options->posttitlefield))
				$options->posttitlefield = '%post_title%';
				
			if (!isset($options->postdescriptionfield))
				$options->postdescriptionfield = '%post_excerpt%';
			
			
			$postdata = array_merge($post, array(
				'post_excerpt' => $excerpt,
				'post_link' => $post_link
			));
						
			foreach($cats as $catindex => $cat)
			{
				if ($catindex == 0)
				{
					$postdata['%categoryid%'] = $cat['id'];
					$postdata['%categoryname%'] = $cat['name'];
					$postdata['%categoryslug%'] = $cat['slug'];
					$postdata['%categorylink%'] = $cat['link'];
				}
				
				$postdata['%categoryid' . $catindex . '%'] = $cat['id'];
				$postdata['%categoryname' . $catindex . '%'] = $cat['name'];
				$postdata['%categoryslug' . $catindex . '%'] = $cat['slug'];
				$postdata['%categorylink' . $catindex . '%'] = $cat['link'];
			}
			
			foreach($tags as $tagindex => $tag)
			{
				if ($tagindex == 0)
				{
					$postdata['%tagid%'] = $tag['id'];
					$postdata['%tagname%'] = $tag['name'];
					$postdata['%tagslug%'] = $tag['slug'];
					$postdata['%taglink%'] = $tag['link'];
				}
				
				$postdata['%tagid' . $tagindex . '%'] = $tag['id'];
				$postdata['%tagname' . $tagindex . '%'] = $tag['name'];
				$postdata['%tagslug' . $tagindex . '%'] = $tag['slug'];
				$postdata['%taglink' . $tagindex . '%'] = $tag['link'];
			}
			
			$postmeta = get_post_meta($postdata['ID']);

			$title = $this->replace_custom_field($postdata, $postmeta, $options->posttitlefield, $options->excerptlength);
			$description = $this->replace_custom_field($postdata, $postmeta, $options->postdescriptionfield, $options->excerptlength);

			$post_item = array(
					'type'			=> 0,
					'image'			=> $image,
					'thumbnail'		=> $thumbnail,
					'title'			=> $title,
					'posttitle'		=> $title,
					'description'	=> $description,
					'weblink'		=> $post_link,
					'linktarget'	=> $options->postlinktarget,
					'datetime'		=> $post['post_date'],
					'date'			=> date('Y-m-d', strtotime($post['post_date']))
			);

			$post_categories = wp_get_post_categories( $post['ID'] );
			$cats = array();
			foreach($post_categories as $cat_id){

				$cat = get_category( $cat_id );
				$cats[] = array(
						'id'   => $cat_id,
						'name' => $cat->name,
						'slug' => $cat->slug,
						'link' => get_category_link( $cat_id )
					);
			}

			$post_item['categories'] = $cats;

			$post_tags = wp_get_post_tags( $post['ID'] );

			$tags = array();
			foreach($post_tags as $tag)
			{
				$tags[] = array(
						'id' 	=> $tag->term_id,
						'name'	=> $tag->name,
						'slug'	=> $tag->slug,
						'link'	=> get_tag_link( $tag->term_id )
					);
			}

			$post_item['tags'] = $tags;

			if (isset($options->postlightbox))
			{
				$post_item['lightbox'] = $options->postlightbox;
				$post_item['lightboxsize'] = $options->postlightboxsize;
				$post_item['lightboxwidth'] = $options->postlightboxwidth;
				$post_item['lightboxheight'] = $options->postlightboxheight;

				if (isset($options->posttitlelink) && strtolower($options->posttitlelink) === 'true')
				{
					$post_item['posttitle'] = '<a class="wonderplugin3dcarousel-posttitle-link" href="' . $post_item['weblink'] . '"';
					if (isset($post_item['linktarget']) && strlen($post_item['linktarget']) > 0)
						$post_item['posttitle'] .= ' target="' . $post_item['linktarget'] . '"';
					$post_item['posttitle'] .= '>' . $title . '</a>';
				}
			}

			if (isset($options->imageaction) && strtolower($options->imageaction) == 'false')
			{
				$post_item['imageaction'] = false;
			}
			else
			{
				$post_item['imageaction'] = true;
			}

			$items[] = (object) $post_item;
		}

		if (isset($options->postorder) && ($options->postorder == 'ASC'))
			$items = array_reverse($items);

		$items = apply_filters( 'wonderplugin_3dcarousel_modify_post_items', $items );

		return $items;
	}
	
	function create_post_code($slides, $data, $id) {
		
		$ret = '';
		
		$count = 0;
		
		foreach($slides as $slide)
		{	
			if ($count > 0)
				$ret .= '</li>';
					
			$ret .= '<li class="wonderplugin3dcarousel-item lightboxcontainer">';
			
			$count++;
			
			$ret .= '<div class="wonderplugin3dcarousel-item-extrainfo lightboxextrainfo">';

			if ($slide->posttitle && strlen($slide->posttitle) > 0)
				$ret .= '<div class="wonderplugin3dcarousel-item-text-title lightboxtitle">' . $this->eacape_html_quotes($slide->posttitle) . '</div>';
			
			if ($slide->description && strlen($slide->description) > 0)
				$ret .= '<div class="wonderplugin3dcarousel-item-text-description lightboxdescription">' . $this->eacape_html_quotes($slide->description) . '</div>';
			
			$ret .= '</div>';

			$ret .= '<div class="wonderplugin3dcarousel-item-container">';
			
			$skin_template = str_replace('&amp;',  '&', $data->skintemplate);
			$skin_template = str_replace('&lt;',  '<', $skin_template);
			$skin_template = str_replace('&gt;',  '>', $skin_template);
			
			$image_code = '';
			
			if ($slide->thumbnail && strlen($slide->thumbnail) > 0)
			{
				if ( isset($slide->lightbox) && strtolower($slide->lightbox) === 'true' )
				{
					$image_code .= '<a';
					if (!empty($data->aextraprops))
						$image_code .= ' ' . $data->aextraprops;
					$image_code .= ' href="';
					
					$image_code .= $slide->image;
					
					$image_code .= '"';

					if ($slide->title && strlen($slide->title) > 0 && ( !isset($data->usedatatitle) || (strtolower($data->usedatatitle) === 'false') ))
					{
						$image_code .= ' title="';
						$image_code .= $this->eacape_html_quotes($slide->title);
						$image_code .= '"';
					}

					if ( isset($slide->lightboxsize) && strtolower($slide->lightboxsize) === 'true' )
						$image_code .= ' data-width="' .  $slide->lightboxwidth . '" data-height="' .  $slide->lightboxheight . '"';
						
					$image_code .= ' data-thumbnail="' . $slide->thumbnail . '"';
						
					$image_code .= ' class="wp3dcarousellightbox wp3dcarousellightbox-' . $id . '"';
					if ( !isset($data->lightboxnogroup) || strtolower($data->lightboxnogroup) !== 'true' )
						$image_code .= ' data-group="wp3dcarousellightbox-' . $id . '"';
					
					$image_code .= '>';
				}
				else if ($slide->weblink && strlen($slide->weblink) > 0)
				{
					$image_code .= '<a';
					if (!empty($data->aextraprops))
						$image_code .= ' ' . $data->aextraprops;
					$image_code .= ' href="' . $slide->weblink . '"';
					if ($slide->linktarget && strlen($slide->linktarget) > 0)
						$image_code .= ' target="' . $slide->linktarget . '"';
					
					$image_code .= ' class="wonderplugin3dcarousel-item-weblink"';
					$image_code .= '>';
				}
				
				$image_code .= '<div class="wonderplugin3dcarousel-img-container"><img class="wonderplugin3dcarousel-img"';
				
				if (!empty($data->imgextraprops))
					$image_code .= ' ' . $data->imgextraprops;
				
				$image_code .= ' src="' . $slide->thumbnail . '"';
				$image_code .= ' alt="' . $this->eacape_html_quotes(strip_tags($slide->title)) . '"';
				$image_code .= ' /></div>';
				
				if ((isset($slide->lightbox) && strtolower($slide->lightbox) === 'true') || ($slide->weblink && strlen($slide->weblink) > 0))
				{
					$image_code .= '</a>';
				}
			}
			
			$skin_template = str_replace('__IMAGE__',  $image_code, $skin_template);
			$skin_template = str_replace('__TITLE__',  $slide->posttitle, $skin_template);
			$skin_template = str_replace('__DESCRIPTION__',  $slide->description, $skin_template);
			$skin_template = str_replace('__HREF__',  $slide->weblink, $skin_template);
			$skin_template = str_replace('__TARGET__',  $slide->linktarget, $skin_template);
			$skin_template = str_replace('__DATETIME__',  $slide->datetime, $skin_template);
			$skin_template = str_replace('__DATE__',  $slide->date, $skin_template);
			$skin_template = str_replace('__BUTTON__',  '', $skin_template);

			if ( isset($slide->categoryid) )
			{
				$skin_template = str_replace('__CATEGORYID__',  $slide->categoryid, $skin_template);
				$skin_template = str_replace('__CATEGORYNAME__',  $slide->categoryname, $skin_template);
				$skin_template = str_replace('__CATEGORYSLUG__',  $slide->categoryslug, $skin_template);
				$skin_template = str_replace('__CATEGORYLINK__',  $slide->categorylink, $skin_template);
			}
				
			$ret .= $skin_template;
			$ret .= '</div>';
		}
		
		if ($count > 0)
			$ret .= '</li>';
		
		return $ret;
	}
	
	function delete_item($id) {
		
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
		
		$ret = $wpdb->query( $wpdb->prepare(
				"
				DELETE FROM $table_name WHERE id=%s
				",
				$id
		) );
		
		return $ret;
	}
	
	function trash_item($id) {
	
		return $this->set_item_status($id, 0);
	}
	
	function restore_item($id) {
	
		return $this->set_item_status($id, 1);
	}
	
	function set_item_status($id, $status) {
	
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
	
		$ret = false;
		$item_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		if ($item_row != null)
		{
			$data = json_decode($item_row->data, true);
			$data['publish_status'] = $status;
			$data = json_encode($data);
	
			$update_ret = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET data=%s WHERE id=%d", $data, $id ) );
			if ( $update_ret )
				$ret = true;
		}
	
		return $ret;
	}
	
	function clone_item($id) {
	
		global $wpdb, $user_ID;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
		
		$cloned_id = -1;
		
		$item_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		if ($item_row != null)
		{
			$time = current_time('mysql');
			$authorid = $user_ID;
			
			$ret = $wpdb->query( $wpdb->prepare(
					"
					INSERT INTO $table_name (name, data, time, authorid)
					VALUES (%s, %s, %s, %s)
					",
					$item_row->name . " Copy",
					$item_row->data,
					$time,
					$authorid
			) );
				
			if ($ret)
				$cloned_id = $wpdb->insert_id;
		}
	
		return $cloned_id;
	}
	
	function is_db_table_exists() {
	
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
	
		return ( strtolower($wpdb->get_var("SHOW TABLES LIKE '$table_name'")) == strtolower($table_name) );
	}
	
	function is_id_exist($id)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
	
		$carousel_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		return ($carousel_row != null);
	}
	
	function create_db_table() {
	
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
		
		$charset = '';
		if ( !empty($wpdb -> charset) )
			$charset = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( !empty($wpdb -> collate) )
			$charset .= " COLLATE $wpdb->collate";
	
		$sql = "CREATE TABLE $table_name (
		id INT(11) NOT NULL AUTO_INCREMENT,
		name tinytext DEFAULT '' NOT NULL,
		data MEDIUMTEXT DEFAULT '' NOT NULL,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		authorid tinytext NOT NULL,
		PRIMARY KEY  (id)
		) $charset;";
			
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	function save_item($item) {
		
		global $wpdb, $user_ID;
		
		if ( !$this->is_db_table_exists() )
		{
			$this->create_db_table();
				
			$create_error = "CREATE DB TABLE - ". $wpdb->last_error;
			if ( !$this->is_db_table_exists() )
			{				
				return array(
						"success" => false,
						"id" => -1,
						"message" => $create_error
				);
			}
		}	
		
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
		
		$id = $item["id"];
		$name = $item["name"];
		
		unset($item["id"]);
		$data = json_encode($item);
		
		if ( empty($data) )
		{
			$json_error = "json_encode error";
			if ( function_exists('json_last_error_msg') )
				$json_error .= ' - ' . json_last_error_msg();
			else if ( function_exists('json_last_error') )
				$json_error .= 'code - ' . json_last_error();
		
			return array(
					"success" => false,
					"id" => -1,
					"message" => $json_error
			);
		}
		
		$time = current_time('mysql');
		$authorid = $user_ID;
		
		if ( ($id > 0) && $this->is_id_exist($id) )
		{
			$ret = $wpdb->query( $wpdb->prepare(
					"
					UPDATE $table_name
					SET name=%s, data=%s, time=%s, authorid=%s
					WHERE id=%d
					",
					$name,
					$data,
					$time,
					$authorid,
					$id
			) );
			
			if (!$ret)
			{
				return array(
						"success" => false,
						"id" => $id, 
						"message" => "UPDATE - ". $wpdb->last_error
					);
			}
		}
		else
		{
			$ret = $wpdb->query( $wpdb->prepare(
					"
					INSERT INTO $table_name (name, data, time, authorid)
					VALUES (%s, %s, %s, %s)
					",
					$name,
					$data,
					$time,
					$authorid
			) );
			
			if (!$ret)
			{
				return array(
						"success" => false,
						"id" => -1,
						"message" => "INSERT - " . $wpdb->last_error
				);
			}
			
			$id = $wpdb->insert_id;
		}
		
		return array(
				"success" => true,
				"id" => intval($id),
				"message" => "Carousel published!"
		);
	}
	
	function get_list_data() {
		
		if ( !$this->is_db_table_exists() )
			$this->create_db_table();
		
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
		
		$rows = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A);
		
		$ret = array();
		
		if ( $rows )
		{
			foreach ( $rows as $row )
			{
				$ret[] = array(
							"id" => $row['id'],
							'name' => $row['name'],
							'data' => $row['data'],
							'time' => $row['time'],
							'authorid' => $row['authorid']
						);
			}
		}
	
		return $ret;
	}
	
	function get_item_data($id)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_3dcarousel";
	
		$ret = "";
		$item_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		if ($item_row != null)
		{
			$ret = $item_row->data;
		}

		return $ret;
	}
	
	
	function get_settings() {
	
		$userrole = get_option( 'wonderplugin_3dcarousel_userrole' );
		if ( $userrole == false )
		{
			update_option( 'wonderplugin_3dcarousel_userrole', 'manage_options' );
			$userrole = 'manage_options';
		}
		
		$thumbnailsize = get_option( 'wonderplugin_3dcarousel_thumbnailsize' );
		if ( $thumbnailsize == false )
		{
			update_option( 'wonderplugin_3dcarousel_thumbnailsize', 'large' );
			$thumbnailsize = 'large';
		}
		
		$keepdata = get_option( 'wonderplugin_3dcarousel_keepdata', 1 );
		
		$disableupdate = get_option( 'wonderplugin_3dcarousel_disableupdate', 0 );
		
		$supportwidget = get_option( 'wonderplugin_3dcarousel_supportwidget', 1 );
		
		$addjstofooter = get_option( 'wonderplugin_3dcarousel_addjstofooter', 0 );
		
		$jsonstripcslash = get_option( 'wonderplugin_3dcarousel_jsonstripcslash', 1 );
		
		$jetpackdisablelazyload = get_option( 'wonderplugin_3dcarousel_jetpackdisablelazyload', 1 );
		
		$supportmultilingual = get_option( 'wonderplugin_3dcarousel_supportmultilingual', 1 );

		$settings = array(
			"userrole" => $userrole,
			"thumbnailsize" => $thumbnailsize,
			"keepdata" => $keepdata,
			"disableupdate" => $disableupdate,
			"supportwidget" => $supportwidget,
			"addjstofooter" => $addjstofooter,
			"jsonstripcslash" => $jsonstripcslash,
			"jetpackdisablelazyload" => $jetpackdisablelazyload,
			"supportmultilingual" => $supportmultilingual
		);
		
		return $settings;
		
	}
	
	function save_settings($options) {
	
		if (!isset($options) || !isset($options['userrole']))
			$userrole = 'manage_options';
		else if ( $options['userrole'] == "Editor")
			$userrole = 'moderate_comments';
		else if ( $options['userrole'] == "Author")
			$userrole = 'upload_files';
		else
			$userrole = 'manage_options';
		update_option( 'wonderplugin_3dcarousel_userrole', $userrole );
		
		if (isset($options) && isset($options['thumbnailsize']))
			$thumbnailsize = $options['thumbnailsize'];
		else
			$thumbnailsize = 'large';
		update_option( 'wonderplugin_3dcarousel_thumbnailsize', $thumbnailsize );
		
		if (!isset($options) || !isset($options['keepdata']))
			$keepdata = 0;
		else
			$keepdata = 1;
		update_option( 'wonderplugin_3dcarousel_keepdata', $keepdata );
		
		if (!isset($options) || !isset($options['disableupdate']))
			$disableupdate = 0;
		else
			$disableupdate = 1;
		update_option( 'wonderplugin_3dcarousel_disableupdate', $disableupdate );
		
		if (!isset($options) || !isset($options['supportwidget']))
			$supportwidget = 0;
		else
			$supportwidget = 1;
		update_option( 'wonderplugin_3dcarousel_supportwidget', $supportwidget );
		
		if (!isset($options) || !isset($options['jetpackdisablelazyload']))
			$jetpackdisablelazyload = 0;
		else
			$jetpackdisablelazyload = 1;
		update_option( 'wonderplugin_3dcarousel_jetpackdisablelazyload', $jetpackdisablelazyload );
		
		if (!isset($options) || !isset($options['addjstofooter']))
			$addjstofooter = 0;
		else
			$addjstofooter = 1;
		update_option( 'wonderplugin_3dcarousel_addjstofooter', $addjstofooter );
		
		if (!isset($options) || !isset($options['jsonstripcslash']))
			$jsonstripcslash = 0;
		else
			$jsonstripcslash = 1;
		update_option( 'wonderplugin_3dcarousel_jsonstripcslash', $jsonstripcslash );

		if (!isset($options) || !isset($options['supportmultilingual']))
			$supportmultilingual = 0;
		else
			$supportmultilingual = 1;
		update_option( 'wonderplugin_3dcarousel_supportmultilingual', $supportmultilingual );
	}
	
	function get_plugin_info() {
	
		$info = get_option('wonderplugin_3dcarousel_information');
		if ($info === false)
			return false;
	
		return unserialize($info);
	}
	
	function save_plugin_info($info) {
	
		update_option( 'wonderplugin_3dcarousel_information', serialize($info) );
	}
	
	function check_license($options) {
	
		$ret = array(
				"status" => "empty"
		);
	
		if ( !isset($options) || empty($options['wonderplugin-3dcarousel-key']) )
		{
			return $ret;
		}
	
		$key = sanitize_text_field( $options['wonderplugin-3dcarousel-key'] );
		if ( empty($key) )
			return $ret;
	
		$update_data = $this->controller->get_update_data('register', $key);
		if( $update_data === false )
		{
			$ret['status'] = 'timeout';
			return $ret;
		}
	
		if ( isset($update_data->key_status) )
			$ret['status'] = $update_data->key_status;
	
		return $ret;
	}
	
	function deregister_license($options) {
	
		$ret = array(
				"status" => "empty"
		);
	
		if ( !isset($options) || empty($options['wonderplugin-3dcarousel-key']) )
			return $ret;
	
		$key = sanitize_text_field( $options['wonderplugin-3dcarousel-key'] );
		if ( empty($key) )
			return $ret;
	
		$info = $this->get_plugin_info();
		$info->key = '';
		$info->key_status = 'empty';
		$info->key_expire = 0;
		$this->save_plugin_info($info);
	
		$update_data = $this->controller->get_update_data('deregister', $key);
		if ($update_data === false)
		{
			$ret['status'] = 'timeout';
			return $ret;
		}
	
		$ret['status'] = 'success';
	
		return $ret;
	}
}