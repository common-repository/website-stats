<?php
/**

* Plugin Name: Website Stats

* Plugin URI: https://wordpress.org/plugins/website-stats/

* Description: A brief description about your plugin.

* Version: 1.0

* Author: vivan jakes
 
* License: A "Slug" license name e.g. GPL12

*/
register_activation_hook( __FILE__, 'ptcstscounter_website_stats_table' );



global $ws_db_version;

$ws_db_version = '1.0';

function ptcstscounter_website_stats_table() {

	global $wpdb;

	global $ws_db_version;

	$table_name = $wpdb->prefix . 'website_stats_counter';

	$charset_collate = $wpdb->get_charset_collate();

	if($wpdb->get_var("show tables like '$table_name'") != $table_name){

		$ws_sql = "CREATE TABLE $table_name (

			id INT(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,

			country_name VARCHAR(25) NOT NULL,

			view_count INT(25) NOT NULL,

			ws_lat DECIMAL(15,12) NOT NULL,

			ws_lng DECIMAL(15,12) NOT NULL,

			UNIQUE KEY id (id)

		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $ws_sql );

	}

	add_option( 'ws_db_version', $ws_db_version );

}







class ptc_statscounter{

    public $options;

    public function __construct() {

       

        $this->options = get_option('ptcstscounter_plugin_options');

        $this->statscounter_register_settings_and_fields();



   }



    public static function statscounter_tools_options_page(){



        add_options_page('Website Stats', 'Website Stats ', 'administrator', __FILE__, array('ptc_statscounter','statscounter_tools_options'));



    }



    



    public static function statscounter_tools_options(){



?>

<div class="wrap">
  <h2>Website Stats Configuration</h2>
  <form method="post" action="options.php" enctype="multipart/form-data">
    <?php settings_fields('ptcstscounter_plugin_options'); ?>
    <?php do_settings_sections(__FILE__); ?>
    <p class="submit">
      <input name="submit" type="submit" class="button-primary" value="Save Changes"/>
    </p>
  </form>
  <div class="ws_country_count">
    <?php 

		global $wpdb;

		$ws_items_per_page = 10;

		$ws_page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;

		$ws_table_name = $wpdb->prefix . 'website_stats_counter';

		$total = $wpdb->get_var("SELECT COUNT(country_name) FROM $ws_table_name");

		$ws_offset = ( $ws_page * $ws_items_per_page ) - $ws_items_per_page;

		$ws_country_count = $wpdb->get_results( "SELECT * FROM $ws_table_name LIMIT ${ws_offset},${ws_items_per_page}");

		$ws_country_map = $wpdb->get_results( "SELECT * FROM $ws_table_name");
		$ws_information = array();

		$ws = 1;

		foreach ($ws_country_map as $ws_map){

			$ws_info[0] = $ws_map->country_name;

			$ws_info[1] = $ws_map->ws_lat;

			$ws_info[2] = $ws_map->ws_lng;
			
			$ws_info[3] = $ws_map->view_count;

			$ws_info[4] = $ws;

			$ws_information[] = $ws_info;

		$ws++;}

		$ws_final_map = json_encode($ws_information);


		?>
       
    <script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
    
    <script type="text/javascript">
  
	function openCity(evt, cityName) {
			var i, tabcontent, tablinks;
			tabcontent = document.getElementsByClassName("tabcontent");
			for (i = 0; i < tabcontent.length; i++) {
				tabcontent[i].style.display = "none";
			}
			tablinks = document.getElementsByClassName("tablinks");
			for (i = 0; i < tablinks.length; i++) {
				tablinks[i].className = tablinks[i].className.replace(" ws_active", "");
			}
			document.getElementById(cityName).style.display = "block";
			evt.currentTarget.className += " ws_active";
			
	// for map	
			
	var locations = <?php echo $ws_final_map; ?>;
			
	
    var map = new google.maps.Map(document.getElementById('map'), {
      zoom: 3, 
      center: new google.maps.LatLng(34.5133, -94.1629),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    var infowindow = new google.maps.InfoWindow();

    var marker, i;

    for (i = 0; i < locations.length; i++) { 
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
        map: map
      });

      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {
          //infowindow.setContent(locations[i][0]);
		  infowindow.setContent('<div><strong>'+locations[i][0]+'</strong><br>Total Visitor ='+locations[i][3]+'</div>');
          infowindow.open(map, marker);
        }
      })(marker, i));
    }
	
		}
		
  </script>
  
      <div class="ws_heading_message">Visitors Across World</div>
      <ul class="tab">
     
      <li><a href="#" class="tablinks ws_active" onclick="openCity(event, 'ws_list')">List View</a></li>
      <li><a href="#" class="tablinks" onclick="openCity(event, 'ws_map')">Map View</a></li>
     </ul>
       
        
        <div id="ws_list" class="tabcontent ws_block">
          <div class="ws_list_view">
            <?php	
        
                echo '<table class="form-table ws">
        
                <tr>
        
                <th>Country Name</th>
        
                <th>Total View Counts</th>
        
                </tr>
        
                ';
        
                foreach ( $ws_country_count as $ws_stats_c )
        
                {
        
                   echo '<tr>';
        
                   echo '<td>'.$ws_stats_c->country_name.'</td>';
        
                   echo '<td>'.$ws_stats_c->view_count.'<td>';
        
                   echo '</tr>';
        
                }
        
                
        
                echo '</table>';
        
                echo paginate_links( array(
        
                    'base' => add_query_arg( 'cpage', '%#%' ),
        
                    'format' => '',
        
                    'prev_text' => __('&laquo;'),
        
                    'next_text' => __('&raquo;'),
        
                    'total' => ceil($total / $ws_items_per_page),
        
                    'current' => $ws_page
        
                ));
        
                 ?>
               </div>
        </div>
        
         <div id="ws_map" class="tabcontent">
             <div id="map" style="height: 500px; width: 100%;"></div>
        </div>
 
  
  </div>
</div>
<?php

}



    public function statscounter_register_settings_and_fields(){



        register_setting('ptcstscounter_plugin_options', 'ptcstscounter_plugin_options',array($this,'statscounter_validate_settings'));



        add_settings_section('ptcstscounter_main_section', 'Settings', array($this,'ptcstscounter_main_section_cb'), __FILE__);



        //Start Creating Fields and Options



        //marginTop



        add_settings_field('visitor', 'Visitor Message', array($this,'visitor_settings'), __FILE__,'ptcstscounter_main_section');



		 //total count



        add_settings_field('total_count', 'Set hit counter', array($this,'tcount_settings'), __FILE__,'ptcstscounter_main_section');



      

    }



    public function statscounter_validate_settings($plugin_options){



        return($plugin_options);



    }



    public function ptcstscounter_main_section_cb(){



        //optional



    }

	 //visitor_settings



    public function visitor_settings() {



        if(empty($this->options['visitor'])) $this->options['visitor'] = "You Are The Visitor No.";



        echo "<input name='ptcstscounter_plugin_options[visitor]' type='text' value='{$this->options['visitor']}' />";



    }



   

		// tcount_settings

	

	 public function tcount_settings() {



        if(empty($this->options['total_count'])) 

		

		$this->options['total_count'];

		//$totalview = get_option('total_count');



       echo "<input name='ptcstscounter_plugin_options[total_count]' type='text' value='{$this->options['total_count']}' />";

	  

    }

}



add_action('admin_menu', 'ptcstscounter_trigger_options_function');



function ptcstscounter_trigger_options_function(){



    ptc_statscounter::statscounter_tools_options_page();



}


add_action('admin_init','ptcstscounter_trigger_create_object');



function ptcstscounter_trigger_create_object(){
  new ptc_statscounter();
}

add_action('wp_footer','ptcstscounter_add_content_in_footer');

function ptcstscounter_add_content_in_footer(){

    $option_vlaue = get_option('ptcstscounter_plugin_options');

	$my_options = get_option('ptcstscounter_plugin_options');

	$total = $my_options['total_count'];

	extract($my_options);

	print_r($option_vlaue);

	if(!isset($_SESSION['scounter'])){

		 $_SESSION['scounter'] = 1;

		 $total_count123 = $total + 1;

		 $my_options['total_count'] = $total_count123;

		 update_option( 'ptcstscounter_plugin_options', $my_options);

	} else {

		 $_SESSION['scounter']++;

	}

	

	/*Get user ip address*/

	$ip_address = $_SERVER['REMOTE_ADDR'];
	
	//162.254.149.198 171.79.23.180

	/*Get user ip address details with geoplugin.net*/

	$geopluginURL='http://www.geoplugin.net/php.gp?ip='.$ip_address;

	$addrDetailsArr = unserialize(file_get_contents($geopluginURL)); 

	/*Get Country name by return array*/

	$wcountry = $addrDetailsArr['geoplugin_countryName'];
	

	

	$geocode_stats = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.$wcountry.'&sensor=false');

	$output_deals = json_decode($geocode_stats);

	$latLng = $output_deals->results[0]->geometry->location;

	$ws_lat = $latLng->lat;

	$ws_lng = $latLng->lng;



	global $wpdb;

	$ws_table_name = $wpdb->prefix . 'website_stats_counter';

	if($_SESSION['scounter'] == 1){

		   $website_stats_country = $wpdb->get_results( "SELECT * FROM $ws_table_name WHERE country_name = '$wcountry'");

	       $ws_country = $wpdb->num_rows;

		   $ws_viewcount = $wpdb->get_var( "SELECT view_count FROM $ws_table_name WHERE country_name = '$wcountry'" );

		  

		   if($ws_country == 0){

			    $result = $wpdb->insert($ws_table_name, array(

								'country_name' => $wcountry,

								'view_count' => 1,

								'ws_lat'     => $ws_lat,

								'ws_lng'     => $ws_lng

								

					)); 

		   } else { 

		   	   $ws_viewcount = $ws_viewcount + 1;

			   $wpdb->update( $ws_table_name, array( 'view_count' => $ws_viewcount),array('country_name'=>$wcountry));

		   }

	}

	

	

		  	

	

	 //$my_options['total_count'];

	

	

	

	$total_array  = array_map('intval', str_split($my_options['total_count']));

	

	

	$wstats_counter = '';

	

	$wstats_counter .= '<div style="text-align: center; border:none;overflow:hidden; padding: 10px;"><div class="wstats_total_result"><div class="wstats_message">'.$visitor.'</div> <div class="wstats_counter_result">';

	for($i=0; $i< count($total_array); $i++){

	$wstats_counter .= '<span>'.$total_array[$i].'</span>';

	}

	$wstats_counter .= '</div></div></div>';

	 

	$imgURL = plugins_url( 'assets/icon46.png' , __FILE__ );



?>
<div id="real_instagram_display">
  <div id="ibox1" style="bottom:-78px; z-index: 10000; left: 50%;">
    <div id="ibox2" style="text-align: left;"> <a class="open" id="ilink" href="#"></a><img style="top:-50px; left: -5px;"  src="<?php echo $imgURL;?>" alt=""> <?php echo $wstats_counter; ?> </div>
    <div style="font-size: 9px; color: #808080; font-weight: normal; font-family: tahoma,verdana,arial,sans-serif; line-height: 1.28; text-align: right; direction: ltr;padding:3px 0 0;"><a href="https://www.nationalcprassociation.com/faqs/" target="_blank" style="color: #808080;">Resources</a></div>
  </div>
</div>
<script type="text/javascript">

    

    jQuery(document).ready(function()
		
    {

    jQuery(function (){

    

    jQuery("#ibox1").hover(function(){ 

    

    jQuery('#ibox1').css('z-index',101009);

    

    jQuery(this).stop(true,false).animate({bottom:  0}, 500); },

    

    function(){ 

    

        jQuery('#ibox1').css('z-index',10000);

    

        jQuery("#ibox1").stop(true,false).animate({bottom: -78}, 500); });

    

    });}); 


</script>
<?php



 }





add_action( 'wp_enqueue_scripts', 'register_ptcstscounter_slider_styles' );
add_action( 'admin_enqueue_scripts', 'register_ptcstscounter_slider_styles' ); 


 function register_ptcstscounter_slider_styles() {


    wp_register_style( 'ptcstscounter_slider_styles', plugins_url( 'assets/ptcstscounter.css' , __FILE__ ) );

    wp_enqueue_style( 'ptcstscounter_slider_styles' );



    wp_enqueue_script('jquery');

	



 }
  function register_ptcstscounter_slider_styles_jquery() {



    wp_register_style( 'ptcstscounter_slider_styles', plugins_url( 'assets/ptcstscounter.css' , __FILE__ ) );



    wp_enqueue_style( 'ptcstscounter_slider_styles' );

	wp_enqueue_script('ws_tabs', plugins_url( 'assets/tabs.js' , __FILE__ ));

    wp_enqueue_script('jquery');

	



 }
 

$ptcstscounter_default_values = array(
     'visitor' => 'You Are The Visitor No.',
	 'total_count' => '0',
 );
add_option('ptcstscounter_plugin_options', $ptcstscounter_default_values);