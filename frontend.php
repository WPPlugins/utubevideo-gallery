<?php
/**
 * utvFrontend - Frontend section for uTubeVideo Gallery
 *
 * @package uTubeVideo Gallery
 * @author Dustin Scarberry
 *
 * @since 1.3
 */

if(!class_exists('utvFrontend'))
{

	class utvFrontend
	{

		private $_options, $_version;

		public function __construct($version)
		{

			//set version
			$this->_version = $version;

			//get plugin options
			$this->_options = get_option('utubevideo_main_opts');

			//add hooks
			add_shortcode('utubevideo', array($this, 'shortcode'));
			add_action('wp_enqueue_scripts', array($this, 'addJS'));
			add_action('wp_enqueue_scripts', array($this, 'addCSS'));

			//check for extra lightbox script inclusion
			if($this->_options['skipMagnificPopup'] == 'no')
				add_action('wp_enqueue_scripts', array($this, 'addLightboxScripts'));

		}

		//insert styles for galleries
		public function addCSS()
		{

			//load frontend styles
			wp_enqueue_style('utv_style', plugins_url('css/front_style.min.css', __FILE__), false, $this->_version);
			wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css');

			if($this->_options['thumbnailBorderRadius'] > 0){

				$css = '.utv-thumb a, .utv-thumb img{border-radius:' . $this->_options['thumbnailBorderRadius'] . 'px!important;-moz-border-radius:' . $this->_options['thumbnailBorderRadius'] . 'px!important;-webkit-border-radius:' . $this->_options['thumbnailBorderRadius'] . 'px!important}';
				wp_add_inline_style('utv_style', $css);
			}

		}

		//insert javascript for galleries
		public function addJS()
		{

			$jsdata = array(

				'thumbnailWidth' => $this->_options['thumbnailWidth'],
				'thumbnailPadding' => $this->_options['thumbnailPadding'],
				'playerWidth' => $this->_options['playerWidth'],
				'playerHeight' => $this->_options['playerHeight'],
				'lightboxOverlayColor' => $this->_options['fancyboxOverlayColor'],
				'lightboxOverlayOpacity' => $this->_options['fancyboxOverlayOpacity'],
				'playerControlTheme' => $this->_options['playerControlTheme'],
				'playerProgressColor' => $this->_options['playerProgressColor']

			);

			wp_enqueue_script('utv-frontend', plugins_url('js/frontend.min.js', __FILE__), array('jquery'), $this->_version, true);
			wp_localize_script('utv-frontend', 'utvJSData', $jsdata);

		}

		public function addLightboxScripts()
		{

			//load jquery and lightbox js / css
			wp_enqueue_script('jquery');
			wp_enqueue_script('codeclouds-mp-js', '//cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js', array('jquery'), null, true);
			wp_enqueue_style('codeclouds-mp-css', '//cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css', false, null);

		}

		public function shortcode($atts)
		{

			require_once 'class/utvVideoGen.class.php';

			//panel view
			if(isset($atts['view']) && $atts['view'] == 'panel'){

				$utvVideoGen = new utvVideoGen($atts, $this->_options);

				return $utvVideoGen->printPanel();

			//regular gallery view
			}else{

				if(get_query_var('albumid') != null)
					$utvVideoGen = new utvVideoGen($atts, $this->_options, 'permalink', get_query_var('albumid'));
				elseif(isset($_GET['aid']))
					$utvVideoGen = new utvVideoGen($atts, $this->_options, 'query', $_GET['aid']);
				else
					$utvVideoGen = new utvVideoGen($atts, $this->_options);

				return $utvVideoGen->printGallery();

			}

		}

	}

}
?>
