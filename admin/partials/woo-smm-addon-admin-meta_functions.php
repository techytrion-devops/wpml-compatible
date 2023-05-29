<?php
function get_meta_type($data,$arr){
 $key = array_search($data, array_column($arr, 'key'));
 if($key!=""){return $arr[$key]['value'];}
}


/*function submit_api_order($order,$service_id,$original_link,$quantity,$type,$product_id){
	global $wpdb;
	 if(!empty($order)){
		 $order_mesg =""; $status ="";	
		 $order_arr = json_decode(json_encode($order),true);
		 if(!empty($order_arr['error'])){$order_id="0";$order_mesg	=$order_arr['error'];$status=1;} 
		 if(!empty($order_arr['order'])){$order_id=$order_arr['order'];$order_mesg ="Success";$status=2;}
		 $tablename2=$wpdb->prefix . "api_order_detail";
		 $wpdb->query("INSERT INTO $tablename2 (`service_id`, `order_id`, `link`, `status`, `quantity`, `type`,`mesg`,`product_id`)
		 VALUES ('".$service_id."','".$order_id."','".$original_link."','".$status."','".$quantity."','".$type."','".$order_mesg."','".$product_id."')");
		}
	}*/
	
	
function submit_api_order($order,$service_id,$original_link,$quantity,$type,$product_id,$woo_order_id,$status){


  global $wpdb;


   if(!empty($order)){


     $order_mesg =""; 

     $order_arr = json_decode(json_encode($order),true);


     if(!empty($order_arr['error'])){$order_id="0";$order_mesg  =$order_arr['error'];$status=0;


        $tablename1=$wpdb->prefix."api_credentials";


        $api_data = $wpdb->get_row("SELECT * FROM $tablename1 where api_id=1");


        $api_data = json_decode(json_encode($api_data),true);


        $email = $api_data['email'];


        wp_mail($email, "SMM Order","The order has been failed and the reason is: $order_mesg and the woocommerce order id is: $woo_order_id");


     } 


     if(!empty($order_arr['order'])){$order_id=$order_arr['order'];$order_mesg =$status; $status=4;}


     $tablename2=$wpdb->prefix . "api_order_detail";


     $wpdb->query("INSERT INTO $tablename2 (`service_id`, `order_id`, `link`, `status`, `quantity`, `type`,`mesg`,`product_id`,`woo_order_id`)


     VALUES ('".$service_id."','".$order_id."','".$original_link."','".$status."','".$quantity."','".$type."','".$order_mesg."','".$product_id."','".$woo_order_id."')");


    }


  }

add_action('wp_ajax_add_api_manual_order', 'add_api_manual_order');
function add_api_manual_order(){
	$api_id = $_POST['_service_parent_id'];
	$service_type = $_POST['_service_type'];
	$service_id = $_POST['_service'];
	$original_link = $_POST['_link'];
	$real_quantity = $_POST['_qty'];
        
        $new_posts = $_POST['_new_posts'];
        
        $custom_comments = $_POST['_custom_comments'];
	
	// print_r($custom_comments);
        
        //wp_send_json_success(array('status' => 'faild', 'message' => $custom_comments));
        
        //die();
	
	global $wpdb;
	$tablename=$wpdb->prefix . "api_credentials";
	$api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=".$api_id."");
	$api_data = json_decode(json_encode($api_data),true);
	
	$api = new Api();
	$api->api_url=$api_data['api_url'];
	$api->api_key= $api_data['api_key'];
	$_status = 0;
	switch ($service_type) {
		case 'default':
			$order = $api->order(array('service' =>$service_id, 'link' => $original_link, 'quantity' => $real_quantity));
			$type="Default";
			$status = $api->status($order);
			$status = $status->status;	
			$_status = submit_api_manual_order($order,$service_id,$original_link,$real_quantity,$type,$api_id,'manual-order',$status);
			
		break;
            case 'custom_comments':
			$order = $api->order(array('service' =>$service_id, 'link' => $original_link, 'comments' => $custom_comments));
			$type="custom_comments";
			$status = $api->status($order);
			$status = $status->status;	
                        $real_quantity = $custom_comments;
			$_status = submit_api_manual_order($order,$service_id,$original_link,$real_quantity,$type,$api_id,'manual-order',$status);
			
		break;
                case 'auto_post':
                    $min_qty = $real_quantity;
                    $max_qty = $min_qty + round(($min_qty*0.03));
                    $order = $api->order(array('service' =>$service_id, 'username' => $original_link, 'min' => $min_qty, 'max' => $max_qty, 'posts' => $new_posts, 'delay' => 0, 'expiry' => ''));
                    $type="Auto Post";
                    $status = $api->status($order);
                    $status = $status->status;	
                    $_status = submit_api_manual_order($order,$service_id,$original_link,$real_quantity,$type,$api_id,'manual-order',$status);
                break;
	}
	
	if(!empty($_status)){
		if(isset($_status['status']) && $_status['status'] == 'success'){
			wp_send_json_success(array('status' => 'success', 'insert_id' => $_status['insert_id'], 'message' => 'Order added'));			
		}else{
			wp_send_json_success(array('status' => 'faild', 'message' => "Faild : ".$_status['message']));			
		}
	}
	wp_send_json_success(array('status' => 'faild', 'message' => 'Something went wrong. Please try again'));
}

function submit_api_manual_order($order,$service_id,$original_link,$quantity,$type,$product_id,$woo_order_id,$status){
	global $wpdb;
   if(!empty($order)){
     $order_mesg =""; 
     $order_arr = json_decode(json_encode($order),true);
     if(!empty($order_arr['error'])){
		$order_id="0";
		$order_mesg  =$order_arr['error'];
		$status=0;
        $tablename1=$wpdb->prefix."api_credentials";
        $api_data = $wpdb->get_row("SELECT * FROM $tablename1 where api_id=1");
        $api_data = json_decode(json_encode($api_data),true);
        $email = $api_data['email'];
        wp_mail($email, "SMM Order","The order has been failed and the reason is: $order_mesg and the woocommerce order id is: $woo_order_id");
		return array('status' => 'fails', 'message' => $order_mesg);
     } 
     if(!empty($order_arr['order'])){
		 $order_id=$order_arr['order'];
		 $order_mesg =$status; 
		 $status=4;
	 }
	 $user_id = get_current_user_id();
     $tablename2=$wpdb->prefix . "api_manual_order_detail";
     $wpdb->query("INSERT INTO $tablename2 (`service_id`, `order_id`, `link`, `status`, `quantity`, `type`, `mesg`, `product_id`, `woo_order_id`, `placed_by`)
     VALUES ('".$service_id."','".$order_id."','".$original_link."','".$status."','".$quantity."','".$type."','".$order_mesg."','".$product_id."','".$woo_order_id."','".$user_id."')");
    	$insert_id = $wpdb->insert_id;
		return array('status' => 'success', 'insert_id' => $insert_id);
	}
	return '';
}  

	
function get_service_name($service_id,$product_id){
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$product_id." and meta_key like '%_service_parent%'", OBJECT );
    $service_val = json_decode(json_encode($results),True);
    $parent_id = $service_val[0]['meta_value']; 
    
    $tablename=$wpdb->prefix."api_credentials";
    $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=".$parent_id."");
    $api_data = json_decode(json_encode($api_data),true);
    $api = new Api();
    $api->api_url=$api_data['api_url'];
    $api->api_key= $api_data['api_key'];
    // FOR SERVICES     
    $services = $api->services();
	    if(!empty($services)){
	    $service_data = json_decode(json_encode($services),True);
	    $service_name ="";
	    foreach($service_data as $row){if($row['service']==$service_id){ $service_name = $row['name'];}} 
	    if(!empty($service_name)){ echo $service_name;}else{echo "<span style='color:#FF0000'>Api Missing</span>";}
	}
}


function get_service_name_return_manual($service_id,$parent_id){
    global $wpdb;
    $tablename=$wpdb->prefix."api_credentials";
    $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=".$parent_id."");
    $api_data = json_decode(json_encode($api_data),true);
    $api = new Api();
    $api->api_url=$api_data['api_url'];
    $api->api_key= $api_data['api_key'];
    // FOR SERVICES     
    $services = $api->services();
        if(!empty($services)){
        $service_data = json_decode(json_encode($services),True);
        $service_name ="";
        foreach($service_data as $row){if($row['service']==$service_id){ $service_name = $row['name'];}} 
        if(!empty($service_name)){ return $service_name;}else{return "Api Missing";}
    }
}

function get_service_name_return($service_id,$product_id){
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$product_id." and meta_key like '%_service_parent%'", OBJECT );
    $service_val = json_decode(json_encode($results),True);
    $parent_id = $service_val[0]['meta_value']; 
    
    $tablename=$wpdb->prefix."api_credentials";
    $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=".$parent_id."");
    $api_data = json_decode(json_encode($api_data),true);
    $api = new Api();
    $api->api_url=$api_data['api_url'];
    $api->api_key= $api_data['api_key'];
    // FOR SERVICES     
    $services = $api->services();
        if(!empty($services)){
        $service_data = json_decode(json_encode($services),True);
        $service_name ="";
        foreach($service_data as $row){if($row['service']==$service_id){ $service_name = $row['name'];}} 
        if(!empty($service_name)){ return $service_name;}else{return "Api Missing";}
    }
}

function get_service_type($service_type){
global $wpdb;
$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$service_type." and meta_key like '%_service_type%'", OBJECT );    
if(!empty($results)){
$service_val = json_decode(json_encode($results),True);
return $service_val[0]['meta_value'];}
}