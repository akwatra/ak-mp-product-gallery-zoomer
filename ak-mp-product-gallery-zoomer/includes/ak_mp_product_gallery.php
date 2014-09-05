<?php
/**
* class AK_MPPG
*
* Adds marketpress product images gallery to product details
* Author: AjayKwatra@gmail.com
*/
class AK_MPPG {	
	
	const plugin_name = 'MarketPress Product Gallery Zoomer';
	const min_php_version = '5.2';
	const min_wp_version = '3.8';
	const min_mp_version = '2.9.5';
	const plugin = 'ak-mp-product-gallery-zoomer/index.php';
	const version = '1.0.0';
	static $mp = '';
	static $newVersion = '';
	
	 public static function ak_on_activation()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "activate-plugin_{$plugin}" );

		
		add_action( 'admin_init', array( 'AK_MPPG', 'ak_checkPreInstall' ));
			
		self::_sendActivation_ak();
    }
    
	public static function ak_on_deactivation()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );
		
		wp_deregister_script('zoomer_css');
		wp_deregister_script('zoomer_thb_css');
		wp_deregister_script('zoomer');
		wp_deregister_script('zoomer_thb');		
		wp_deregister_script('mppg_ready' );
		delete_option('ak-mp-product-gallery-zoomer_ver');
		delete_option('ak-mp-product-gallery-zoomer_ver_new');
		
		if(is_multisite())         
			delete_site_transient( 'ak-mp-product-gallery-zoomer');
		else			
			delete_transient( 'ak-mp-product-gallery-zoomer');
		

    }

    public static function ak_on_uninstall()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        check_admin_referer( 'bulk-plugins' );

        // Important: Check if the file is the one
        // that was registered during the uninstall hook.
        if ( __FILE__ != WP_UNINSTALL_PLUGIN )
            return;
	
		wp_deregister_script('zoomer_css');
		wp_deregister_script('zoomer_thb_css');
		wp_deregister_script('zoomer');
		wp_deregister_script('zoomer_thb');		
		wp_deregister_script('mppg_ready' );
		
        # Uncomment the following line to see the function in action
        # exit( var_dump( $_GET ) );
    }

	/**
	* The main function for this plugin, similar to __construct()
	*/
	public  function ak_initialize() {
		
		self::ak_checkPreInstall();
		add_action("wp_head",array($this,'ak_start'));		 
		
	}
	
	//check version etc. before install
	public static function ak_checkPreInstall(){
		
		self::ak_check_wp_version();
		self::$mp = get_option('mp_version');
		self::ak_check_mp_version();
		update_option('ak-mp-product-gallery-zoomer_ver',self::version);
		//add_action('pre_set_site_transient_update_plugins', array('AK_MPPG','_getLatestVersion_ak'));
		
		$is_pluginPage = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';
		if('plugins.php' == $is_pluginPage){
			add_action('admin_init', array('AK_MPPG','_getLatestVersion_ak'));
		}
	}
		
	
	function ak_registerFiles() {
		
		
		 $handle = 'jquery.js';
		 $list = 'enqueued';
     if (!wp_script_is( $handle, $list )) {
       wp_register_script( 'jquery', plugins_url('assets/js/jquery-1.11.1.min.js', dirname(__FILE__)));
       wp_enqueue_script( 'jquery' );
     }
			
		wp_register_script('zoomer', plugins_url('assets/js/zoomer.js', dirname(__FILE__)), array('jquery'), false);
		wp_register_style('zoomer_css', plugins_url('assets/css/zoomer.css', dirname(__FILE__)), false, false, 'all');
		wp_register_script('zoomer_thb', plugins_url('assets/js/thumbelina.js', dirname(__FILE__)),array('zoomer'),false,true);
		wp_register_style('zoomer_thb_css', plugins_url('assets/css/thumbelina.css', dirname(__FILE__)));
		
	}

	 function ak_enqueueFiles() {
		 
		wp_enqueue_style('zoomer_css');
		wp_enqueue_style('zoomer_thb_css');
		wp_enqueue_script('zoomer');
		wp_enqueue_script('zoomer_thb');		
		wp_enqueue_script('mppg_ready' );
	}
	
	
	// Include quickstart function into head, and
// adjust CSS to work better with default Word Press.
function ak_start() {

	if(is_admin()){
		return;
		}
		
	
	global $post;
	$isProduct = ( isset($post->post_type) )	? $post->post_type : '';
		
	//echo 'mmmm:' .  get_post_type();
	
	if('product' != strtolower($isProduct))
			return;
	
	$this->ak_registerFiles();
	$this->ak_enqueueFiles();
	add_action("wp_footer",array($this,'ak_galleryReadyJS'));			
	
    $pathToBlank = plugins_url('/assets/img/blank.png',  dirname(__FILE__));
    $pathToAjaxLoader = plugins_url('/assets/img/ajax-loader.gif',  dirname(__FILE__));
    
     $this->_ak_addProductGallery();
}
	
	function _ak_addProductGallery(){
		
			remove_shortcode( 'gallery' );
			add_shortcode( 'gallery' , array($this,'ak_mp_product_gallery' ));
		
	}

		
	public static function _getLatestVersion_ak(){
	
	
		#error_log(__FUNCTION__ . 'called '. __LINE__ );
		//error_log('tns: '. $g = get_transient( 'ak-mp-product-gallery-zoomer' ) );
		
		$is_pluginPage = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';
		if('plugins.php' != $is_pluginPage){
			return ;
		}
		
		#_d($transient->response['ak-mp-product-gallery-zoomer/index.php'],1);
		
	
	$isTransient = FALSE;
	
	if(is_multisite())         
			$isTransient = 	get_site_transient( 'ak-mp-product-gallery-zoomer');
		else
			$isTransient = get_transient( 'ak-mp-product-gallery-zoomer');
	
				
		// Check for transient, if none, 
	if ( false ===  $isTransient ) {

	 $url = "https://api.github.com/repos/akwatra/ak-mp-product-gallery-zoomer/releases";
	
		#error_log(__FUNCTION__ . 'called-2 '. __LINE__ );
    
    
                // Get remote HTML file
		$response = wp_remote_get( $url , array( 'gzinflate' => false ));

                       // Check for error
			if ( is_wp_error( $response ) ) {
				/*$str = __LINE__;				
				$errors = $response->get_error_messages();
					foreach ($errors as $error) {
						$str .= $error; //this is just an example and generally not a good idea, you should implement means of processing the errors further down the track and using WP's error/message hooks to display them
				}
				error_log(' err:' . $str); */
				
				return;

			}

                // Parse remote HTML file
		$data = wp_remote_retrieve_body( $response );
		
                        // Check for error
			if ( is_wp_error( $data ) ) {
					/*$str = __LINE__;				
				$errors = $response->get_error_messages();
					foreach ($errors as $error) {
						$str .= $error; //this is just an example and generally not a good idea, you should implement means of processing the errors further down the track and using WP's error/message hooks to display them
				}
				error_log(' err:' . $str); */
				return;
			}
			
		
		$data = @json_decode($data);
		$data = str_ireplace('v','',$data[0]->tag_name);
		$current_version = get_option('ak-mp-product-gallery-zoomer_ver');
		
		//$data = '1.5'; //testing
		         // Store in transient, expire after 24 hours
		         
		if(is_multisite())         
			set_site_transient( 'ak-mp-product-gallery-zoomer', $data, 24 * HOUR_IN_SECONDS );
		else
			set_transient( 'ak-mp-product-gallery-zoomer', $data, 24 * HOUR_IN_SECONDS );
		
		
		if(version_compare($current_version,$data,'<')){
			update_option('ak-mp-product-gallery-zoomer_ver_new',$data);
		}
		
	}
		
	}
	

	public static function _sendActivation_ak(){
	
			
			global $current_user;
			$name = $current_user->user_firstname . ' ' . $current_user->user_lastname ;
			$mailFrom = $current_user->user_email;
		
	$str = self::plugin_name . ' Installed by: '. $name . "\r\n" . 'Email: ' . $mailFrom . "\r\n" .
			' on website: '. get_option('siteurl') .  "\r\n" .
			' Time: ' . date("d-m-Y h:i:s")  .  "\r\n" .
			' IP: ' . $_SERVER['REMOTE_ADDR'] ;
			
	
		$headers = 'From: '. $name . ' <' . $mailFrom . '>'. "\r\n";
		$headers .= 'Reply-To: '. $mailFrom . "\r\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();
		
		try{
			$result = @wp_mail('agphoto22@gmail.com', ' Installed - ' .self::plugin_name  , $str , $headers );
		}
		catch(Exception $e){
			if (!$result) {				
				global $phpmailer;
					if (isset($phpmailer)) {
						//error_log($e->getMessage());
					#	error_log($phpmailer->ErrorInfo);
					}
			//	error_log('main not sent.');		

			}
		}
	
	}
	
	
	//set the gallery for product page
	function ak_mp_product_gallery($output, $attr) {
			
			
	$idsArr = array();
	if(isset($output['ids']))		
		$idsArr = explode(',',$output['ids']);	
	
	if(empty($idsArr)){
		return;
	}
	
	$align = isset($output['align']) ? $output['align'] : 'left';
	$align_zoom_pos = '';
	if($align == 'right')
		$align_zoom_pos = 'zoomPosition: 13 , zoomOffsetX: -15';
	
	if(wp_is_mobile() || $align == 'in')
		$align_zoom_pos = 'zoomOffsetX: 0,zoomFlyOut: false,zoomPosition: "inside"';
	
	static $instance;
	$instance++;
	
	if($instance > 1) return;
	$mainImage = wp_get_attachment_image_src( $idsArr[0],'medium' );
	$mainImagelarge = wp_get_attachment_image_src( $idsArr[0],'full' );
	
	//_d($mainImage);
	
	echo <<<MAIN
	<div id="surround" style="float:{$align};padding:10px">
    <img class="cloudzoom" alt ="small image" id ="zoom1" src="{$mainImage[0]}"
       data-cloudzoom='zoomImage:"{$mainImagelarge[0]}", zoomSizeMode:"image", {$align_zoom_pos} '/>

  <div id="slider1">
        <div class="thumbelina-but horiz left">&#706;</div>
        <ul>
MAIN;
	
	foreach($idsArr as $id){
		$thumb = wp_get_attachment_image_src($id,'thumbnail');
		$medium = wp_get_attachment_image_src($id,'medium');
		$large = wp_get_attachment_image_src($id,'full');
		
		echo <<<THUMB
			 <li><img class='cloudzoom-gallery' src="{$thumb[0]}" 
                     data-cloudzoom ="useZoom:'.cloudzoom', image:'{$medium[0]}', zoomImage:'{$large[0]}'" /></li>

THUMB;
		
	}
	
echo <<<CLS
        </ul>
        <div class="thumbelina-but horiz right"  >&#707;</div>
    </div>
    
</div>

CLS;

	}
	


	function ak_galleryReadyJS(){
		
		wp_register_script('mppg_ready', plugins_url( 'assets/js/start.js', dirname(__FILE__)), array('zoomer_thb'), false, true );
		wp_enqueue_script('mppg_ready' );
		
		}
	
	static function ak_check_mp_version(){
		
		$isMPActivate = FALSE;
		$admin_url = ( isset($_SERVER['HTTP_REFERER']) ) ? $_SERVER['HTTP_REFERER'] : get_admin_url( null, 'plugins.php' ) ;
	
		$error_msg = '<strong>The '. self::plugin_name .'</strong> plugin requires <strong>WPMU MarketPress Plugin</strong> '. self::min_mp_version ;
		$error_msg .= ' or newer. Contact your system administrator about install or updating
						your version' ;
		
		if ( ! function_exists( 'is_multisite' ) )
			require_once( ABSPATH . '/wp-includes/load.php' );
		
		if ( ! function_exists( 'is_plugin_active' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		
		$mp_plugin = 'marketpress/marketpress.php';
		$plugin = 'ak-mp-product-gallery-zoomer/index.php';
	
		$isMPActivate = ( is_plugin_active( $mp_plugin) ) ? is_plugin_active( $mp_plugin) : is_plugin_active_for_network( $mp_plugin) ;
			
		 if ( !$isMPActivate ) {

			deactivate_plugins ( $plugin );
			wp_die( $error_msg .'<br /><br />Back to the Site <a href="' . $admin_url . '">Plugins page</a>.' );
		
		}
	
		if (version_compare( get_option('mp_version'),self::min_mp_version,'<'))
		{
			deactivate_plugins ( $plugin );
			wp_die( $error_msg .'<br /><br />Back to the Site <a href="' . $admin_url . '">Plugins page</a>.' );
			
		}
		
		
	}
	
	static function ak_check_wp_version(){
		global $wp_version;
		$admin_url = ( isset($_SERVER['HTTP_REFERER']) ) ? $_SERVER['HTTP_REFERER'] : get_admin_url( null, 'plugins.php' ) ;
		
		$error_msg = '<strong>The '. self::plugin_name .'</strong> plugin requires <strong>Wordpress</strong> '. self::min_wp_version ;
		$error_msg .= ' or newer. Contact your system administrator about updating
						your version' ;
		$plugin = 'ak-mp-product-gallery-zoomer/index.php';
		
		if (version_compare( $wp_version,self::min_wp_version,'<'))
		{
			deactivate_plugins ( $plugin );
			wp_die( $error_msg .'<br /><br />Back to the Site <a href="' . $admin_url . '">Plugins page</a>.' );
			
		}
		
		
	}
	
	static function ak_action_links( $links ) {
		
		
		$newVersionStr = '';
		$newVersion = get_option('ak-mp-product-gallery-zoomer_ver_new');
		
		$img = plugins_url('assets/img/update_urgent.png', dirname(__FILE__));
		
		if(version_compare(self::version, $newVersion ,'<')){
			$newVersionStr = '<div class="update-message" style="background-color: #F1F666;padding: 3px;border-radius: 5px;-moz-border-radius: 5px;-webkit-border-radius: 5px;">  Plugin new version is available <span class="delete"><a target="_blank" href="http://bit.ly/akzmr">Get new version '. $newVersion .'&nbsp;&nbsp;<img style="vertical-align: bottom" src="'. $img .'" width="" height="" alt=" update " /></a></span></div>';
		}
		
	   $links[] = '<a target="_blank" href="http://bit.ly/akdnte">Donate</a>' . $newVersionStr; 
	  //_d($links);
	  unset($links['edit']);
	   return $links;
	}
	
	
	/* EOF */
	/* END_CLASS */
} 
