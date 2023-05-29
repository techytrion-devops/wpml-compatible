<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://http://likes-kopen.nl/
 * @since      1.0.0
 *
 * @package    Woo_Smm_Addon
 * @subpackage Woo_Smm_Addon/admin/partials
 */
?>

<!-- This file has functionality of Admin API -->

<?php


if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}



/*error_reporting(E_ALL); // Error engine - always ON!

ini_set('ignore_repeated_errors', TRUE); // always ON

ini_set('display_errors', TRUE); // Error display - OFF in production env or real server

ini_set('log_errors', TRUE); // Error logging

ini_set('error_log', plugin_dir_path(__FILE__).'debug.log'); // Logging file

ini_set('log_errors_max_len', 1024); // Logging file size
*/

//create custom text table when plugin activate
custom_order_tbl();

function custom_order_tbl() {

global $wpdb; //geting the acces to a wp tables
$tablename=$wpdb->prefix . "api_credentials"; //the name of a table with it's prefix
$table2 = $wpdb->prefix . "api_order_detail";
$table3 = $wpdb->prefix . "api_manual_order_detail";
//checking if the table with the name we created a line above exists
if($wpdb->get_var("SHOW TABLES LIKE '$tablename'") != $tablename) {
    //if it does not exists we create the table
    $sql="CREATE TABLE `$tablename`(
    `api_id` int(11) NOT NULL AUTO_INCREMENT,
    `api_url` varchar(500) DEFAULT NULL,
    `api_key` varchar(500) DEFAULT NULL,
    `panel_name` varchar(500) DEFAULT NULL,
    PRIMARY KEY (api_id)
    );";
    //wordpress function for updating the table
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
 }
 if($wpdb->get_var("SHOW TABLES LIKE '$table2'") != $table2) {
    //if it does not exists we create the table
    $sql="CREATE TABLE `$table2` ( `id` INT NOT NULL AUTO_INCREMENT, 
  `service_id` INT NULL , 
  `order_id` VARCHAR(20) NULL ,
  `link` TEXT NULL ,
  `status` TINYINT(2) NOT NULL DEFAULT '0' ,
  `quantity` VARCHAR(20) NULL ,
  `type` VARCHAR(20) NULL ,
  `mesg` VARCHAR(250) NULL ,
  `woo_order_id` VARCHAR(20) NULL,
  `product_id` INT NULL ,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`))";
   //wordpress function for updating the table
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
 }
 
 if($wpdb->get_var("SHOW TABLES LIKE '$table3'") != $table3) {
    //if it does not exists we create the table
    $sql="CREATE TABLE `$table3` ( `id` INT NOT NULL AUTO_INCREMENT, 
  `service_id` INT NULL , 
  `order_id` VARCHAR(20) NULL ,
  `link` TEXT NULL ,
  `status` TINYINT(2) NOT NULL DEFAULT '0' ,
  `quantity` VARCHAR(20) NULL ,
  `type` VARCHAR(20) NULL ,
  `mesg` VARCHAR(250) NULL ,
  `woo_order_id` VARCHAR(20) NULL,
  `product_id` INT NULL ,
  `placed_by` INT NULL ,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`))";
   //wordpress function for updating the table
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
 }
  
}


add_filter( 'cron_schedules', 'isa_add_every_three_minutes' );
function isa_add_every_three_minutes( $schedules ) {
    $schedules['every_three_minutes'] = array(
            'interval'  => 180,
            'display'   => __( 'Every 3 Minutes', 'textdomain' )
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'isa_add_every_three_minutes' ) ) {
    wp_schedule_event( time(), 'every_three_minutes', 'isa_add_every_three_minutes' );
}

// Hook into that action that'll fire every three minutes
add_action( 'isa_add_every_three_minutes', 'every_three_minutes_event_func' );
function every_three_minutes_event_func() {
    global $wpdb;
    $order_tbl = $wpdb->prefix . "api_order_detail";
    $api_tbl = $wpdb->prefix . "api_credentials";
    $order_data = $wpdb->get_results("select order_id,product_id,woo_order_id from $order_tbl where status = 4 and order_id != 0");
    
    $order_data=json_decode(json_encode($order_data),true);
    
    foreach ($order_data as $row => $value) {
        
        $order_id = $value['order_id'];
        $woo_order_id = $value['woo_order_id'];
        $order = wc_get_order($woo_order_id);
        $order_data = $order->get_data(); // The Order data
        $email = $order_data['billing']['email'];
        
        $results2 = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$value['product_id']." and meta_key like '%_service_parent%'", OBJECT );
        $api_arr = json_decode(json_encode($results2),True);
        $api_id = $api_arr[0]['meta_value'];

        $api_data = $wpdb->get_row("SELECT * FROM $api_tbl where api_id=$api_id");
        $api_data = json_decode(json_encode($api_data),true);
      
        $api = new Api();
        $api->api_url=$api_data['api_url'];
        $api->api_key= $api_data['api_key'];
        $status = $api->status($order_id);
        
        if($status->status=="Completed"){
            $wpdb->query("UPDATE $order_tbl SET `status` = 1  where order_id=$order_id");
            $order = wc_get_order($woo_order_id);
            $checkallStatus =  $wpdb->get_var( "SELECT COUNT(id) FROM $order_tbl where woo_order_id=$woo_order_id AND status =1" );
            $total_orders = $wpdb->get_var("SELECT COUNT(id) FROM $order_tbl where woo_order_id=$woo_order_id");
            if ($total_orders == $checkallStatus) {
                $order->update_status('completed');
                //wp_mail($email, "SMM Order Status","Your SMM order has been completed successfully");   
            }
        }

        if($status->status=="Partial" || $status->status=="Canceled"){
            $email = "marek@gebruikersnamen.nl";
            $status = $status->status; 
            $wpdb->query("UPDATE $order_tbl SET `status` = 2  where woo_order_id=$woo_order_id");  
            wp_mail($email, "SMM Order Status","The order has been $status and the woocommerce order id is: $woo_order_id smm order id is: $order_id");     
        }       
    }
}

// Hook into that action that'll fire every three minutes
add_action( 'isa_add_every_three_minutes', 'every_three_minutes_event_manual_orders_func' );

function every_three_minutes_event_manual_orders_func() {
    global $wpdb;
    $order_tbl = $wpdb->prefix . "api_manual_order_detail";
    $api_tbl = $wpdb->prefix . "api_credentials";
    $order_data = $wpdb->get_results("select order_id,product_id,woo_order_id from $order_tbl where status = 4 and order_id != 0");
	
    
    $order_data=json_decode(json_encode($order_data),true);
	
    foreach ($order_data as $row => $value) {
        
        $order_id = $value['order_id'];
		
        /*$woo_order_id = $value['woo_order_id'];
        $order = wc_get_order($woo_order_id);
        $order_data = $order->get_data(); // The Order data
        $email = $order_data['billing']['email'];*/
        
        
		$api_id = $value['product_id'];
		
		if(empty($api_id)){
			$wpdb->query("UPDATE $order_tbl SET `status` = 0  where order_id=$order_id");  	
		}

        $api_data = $wpdb->get_row("SELECT * FROM $api_tbl where api_id=$api_id");
        $api_data = json_decode(json_encode($api_data),true);
      
        $api = new Api();
        $api->api_url=$api_data['api_url'];
        $api->api_key= $api_data['api_key'];
        $status = $api->status($order_id);

        if($status->status=="Completed"){
            $wpdb->query("UPDATE $order_tbl SET `status` = 1  where order_id=$order_id");
        }

        if($status->status=="Partial" || $status->status=="Canceled"){
            $email = "marek@gebruikersnamen.nl";
            $status = $status->status; 
            $wpdb->query("UPDATE $order_tbl SET `status` = 2  where order_id=$order_id");  
            wp_mail($email, "SMM Order Status","The order has been $status and the woocommerce order id is: $woo_order_id smm order id is: $order_id");     
        }       
    }
}

//woocommerce_quantity_input(array('input_value' => @$_POST['quantity']));
// HITTING API AFTER PAYMENT

add_action( 'wp_head', 'check_gateway' );
function check_gateway(){
    
?>
<style>
input[type=text], input[type=password], input[type=radio], #comment, input[type=email], textarea[name=your-message] {
    border: 1px solid #ccc;
    background: #fafafa;
    border-radius: 0;
    color: #888;
    font: inherit;
    font-size: 16px;
    padding: 6px;
}
input[type='email'], input[type='date'], input[type='search'], input[type='number'], input[type='text'], input[type='tel'], input[type='url'], input[type='password'], textarea, select, .select-resize-ghost, .select2-container .select2-choice, .select2-container .select2-selection {
    box-sizing: border-box;
    border: 1px solid #ddd;
    padding: 0 .75em;
    height: 2.507em;
    font-size: .97em;
    border-radius: 0;
    max-width: 100%;
    width: 100%;
    vertical-align: middle;
    background-color: #fff;
    color: #333;
    box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
    transition: color .3s,border .3s,background .3s,opacity .3s;
}

#wccf_product_field_master_container {
    padding: 5px 15px;
    background: #f6f5f2;
    margin-top: 15px;
    margin-bottom: 10px;
}
.link_txtbox{
    width:100% !important;
    border: 1px solid #ccc;
    background: #fafafa;
    border-radius: 0;
    color: #888;
    font: inherit;
    font-size: 16px;
    padding: 6px;
    box-sizing: border-box;
    border: 1px solid #ddd;
    padding: 0 .75em;
    height: 2.507em;
    font-size: .97em;
    border-radius: 0;
    max-width: 100%;
    width: 100%;
    vertical-align: middle;
    background-color: #fff;
    color: #333;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    transition: color .3s, border .3s, background .3s, opacity .3s;

}
.link_lbl{
    vertical-align: inherit;
}

</style>
<script>
jQuery(function($) {
    
$('.link_bc:gt(0)').remove();$('.quantity').hide();
/*$('.link_img_div:gt(0)').remove();
$(".variations").append("<td class='label'><label style='font-weight=700'>"+$(".link_lbl").html()+"</label></td>");
$(".variations").append("<td class='value'>"+$(".link_txtbox2").html()+"</td>");
$(".link_txtbox").css("width",'100%');
$(".link_bc").remove();*/



});
</script>
<?php
}

add_action('admin_footer', 'load_custom_script' ); 
function load_custom_script() {
  ?>
<script>
(function($) {
  $(document).ready(function(){
    $("#_service_type").change(function(){
    if($(this).val()==1){alert("Custom Comments");}   
});

});
$(".variations").css('margin-left','-104px');


/*var data = {
        action: 'my_action',
        panel_id: $('#_service_parent').val()
    };
    $.post(ajaxurl, data, function(response) {
        $("#_Service").html(response);
    });*/


$("#_service_parent").on('change', function() {
    var data = {
        action: 'load_smm_categories',
        panel_id: $(this).val()
    };
    $.post(ajaxurl, data, function(response) {
        $("#_Service_category").html(response);
    });
});

$("#_Service_category").on('change', function() {
    var data = {
        action: 'load_smm_categories_services',
        category_id: $(this).val(),
        panel_id:$("#_service_parent").val(),
    };
    $.post(ajaxurl, data, function(response) {
        $("#_Service").html(response);
    });
});


                $("#_service_min").attr('readonly',true);
                $("#_service_max").attr('readonly',true);
                $("#_service_rate").attr('readonly',true);
                $("#_service_lbl_id").attr('readonly',true);
                
                 $(document).on('change','#_Service', function () {
                if($(this).val() != ""){
                $("#_service_min").val($("#_Service [value="+$(this).val()+"]").attr('data-servicemin'));
                $("#_service_max").val($("#_Service [value="+$(this).val()+"]").attr('data-servicemax'));
                $("#_service_rate").val($("#_Service [value="+$(this).val()+"]").attr('data-servicerate'));
                 $("#_service_lbl_id").val($(this).val());
                }else{
                $("#_service_min").val("");
                $("#_service_max").val("");
                $("#_service_rate").val("");
                 $("#_service_lbl_id").val("");
                }
            });
            
            $("#_service_min").val($("#_Service [value="+$("#_Service").val()+"]").attr('data-servicemin'));
                $("#_service_max").val($("#_Service [value="+$("#_Service").val()+"]").attr('data-servicemax'));
                $("#_service_rate").val($("#_Service [value="+$("#_Service").val()+"]").attr('data-servicerate'));  
                
                 $("#_service_lbl_id").val($("#_Service").val());  
            

}(jQuery));


</script>
<?php
}
