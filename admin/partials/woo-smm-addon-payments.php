<?php

/*add_action('wp_head', function(){
if(isset($_GET['kkiudsfdsgfbds'])){
	so_payment_complete_new(71317);	
}
});*/

add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', function($statuses, $order){
    $statuses[]='blockchainpending';
    return $statuses;
}, 10, 2);

add_action( 'woocommerce_payment_complete', 'so_payment_complete_new' );
function so_payment_complete_new($order_id){		
	$order = wc_get_order($order_id);
	if(!empty($order)){
		$user = $order->get_user();
		$order_data = array();
		foreach ($order->get_items() as $key => $lineItem) {
			$lineitem_data = json_decode($lineItem,true);
			$product_id = $lineitem_data['product_id'];
			$qty = $lineitem_data['quantity'];
			$quantity = 1;
			$url = '';
			if(isset($lineitem_data['meta_data'])){
				foreach($lineitem_data['meta_data'] as $k=>$v){
					if($v['key'] == 'custom_option'){
						$url = $v['value'];	
					}
					if(strpos($v['key'], 'quantity') !== false){
						$quantity = $v['value'];
					}
				}
			}
			$key = md5($product_id.'-'.$url);
			if(!isset($order_data[$key])){
				$_qty = $qty*$quantity;
				$order_data[$key] = array('data' => $lineitem_data, 'url' => $url, 'qty' => $_qty);	
			}else{
				$_qty = $qty*$quantity;
				$_qty = $order_data[$key]['qty']+$_qty;
				$order_data[$key]['qty'] = $_qty;	
			}
			
		}
		$order_det=array();
			foreach ($order->get_items() as $key => $lineItem) {
				 array_push($order_det,json_decode($lineItem,true));	  
			}
		 $sex=array();
		 $product_id=array();
		 for($i=0;$i<count($order_det);$i++){
			array_push($sex,$order_det[$i]['meta_data']);
		 }
	
		$result = call_user_func_array('array_merge', $sex);
	  //  print_r($result);
		// FOR GETTING QUANTITY AND LINK 
		$link = array(); 
		$quantity =array();
		$the_word="quantity";
		$newArray = array();
		for($i=0;$i<count($result);$i++){
			if($result[$i]['key'] == 'custom_option'){
			   array_push($link,$result[$i]['value']);
			}
			if(strpos($result[$i]['key'], $the_word) !== false) {
			array_push($quantity,$result[$i]['value']);
			}
		}
		
		global $wpdb;
		if(!empty($order_data)){
			foreach($order_data as $data){
				$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$data['data']['product_id']."", OBJECT );
				$service_val = json_decode(json_encode($results),True);
				$original_link=$data['url'];
				$real_quantity = $data['qty'];
				
				$results1 = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$data['data']['product_id']." and meta_key = '_Service'", OBJECT );    
				$service_val = json_decode(json_encode($results1),True);
				$service_id = $service_val[0]['meta_value'];  
				
				$result2 = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$data['data']['product_id']." and meta_key like '%_service_type%'", OBJECT );    
				$service_type = json_decode(json_encode($result2),True);
				$service_type = $service_type[0]['meta_value'];
				
				$results3 = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$data['data']['product_id']." and meta_key like '%_service_parent%'", OBJECT );
				$api_arr = json_decode(json_encode($results3),True);
				$api_id = $api_arr[0]['meta_value']; 
				
				$tablename=$wpdb->prefix . "api_credentials";
				$api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=".$api_id."");
				$api_data = json_decode(json_encode($api_data),true);
				
				$api = new Api();
				$api->api_url=$api_data['api_url'];
				$api->api_key= $api_data['api_key'];
				
				$arr = $data['data']['meta_data'];
		 		$product_id = $data['data']['product_id'];  
				switch ($service_type) {
					case 'default':
					$order = $api->order(array('service' =>$service_id, 'link' => $original_link, 'quantity' => $real_quantity));
					$type="Default";
					$status = $api->status($order);
					$status = $status->status;	
					submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
			
					break;
                                    
                                        case 'auto_post':
                                            $newposts = 0;
                                            $min_qty = 0;
                                            $max_qty = 0;
                                            $delay = 0;
                                            foreach($arr as $v){
                                                if($v['key'] == 'pa_aantal-posts'){
                                                    $newposts = $v['value'];
                                                }
                                                if($v['key'] == 'pa_aantal-likes-per-post'){
                                                    $min_qty = $v['value'];
                                                }
                                                if($v['key'] == 'pa_aantal-views-per-post'){
                                                    $min_qty = $v['value'];
                                                }
                                            }

                                            if(!empty($min_qty)){
                                                $min_qty = $min_qty*$qty;
                                                $max_qty = $min_qty + round(($min_qty*0.03));
                                            }

                                            $real_quantity = $min_qty;
                                            $order = $api->order(array('service' =>$service_id, 'username' => $original_link, 'min' => $min_qty, 'max' => $max_qty, 'posts' => $newposts, 'delay' => 0, 'expiry' => ''));
                                            $type="Auto Post";
                                            $status = $api->status($order);
                                            $status = $status->status;	
                                            submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
                                        break;
			
					case 'custom_comments':
					$comment = get_meta_type('custom_comment',$arr);
					$order = $api->order(array('service' => $service_id, 'link' => $original_link,'comments' =>$comment, 'quantity'=>$real_quantity));
					$type="Custom Comment";
					$status = $api->status($order);
					$status = $status->status;	
			
					submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
			
					break;
			
					case 'mention_custom_list':
					// No quantity required
					$value = get_meta_type('mention_custom_list',$arr);
					$order = $api->order(array('service' => $service_id, 'link' => $original_link, 'usernames' => $value, 'quantity'=>$real_quantity));
					$type="Mention Custom List";
					$status = $api->status($order);
					$status = $status->status;	
			
					submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
			
					break;
			
					case 'mention_user_follower':
					$value = get_meta_type('mention_user_follower',$arr);
					$order = $api->order(array('service' => $service_id, 'link' => $original_link, 'quantity' => $real_quantity, 'username' => $value));
					$type="Mention User Follower";
					$status = $api->status($order);
					$status = $status->status;	
			
					submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
			
					break;
			
			
					case 'comment_likes':
					$value = get_meta_type('comment_likes',$arr);
					$order = $api->order(array('service' => $service_id, 'link' => $original_link, 'quantity' => $real_quantity, 'username' => $value));
					$type="Comment Likes";
					submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id);
					break;
			
					case 'drip_feed':
					 $runs = get_meta_type('runs',$arr);
					 $interval = get_meta_type('interval',$arr);
					 $order = $api->order(array('service' => $service_id, 'link' => $original_link, 'quantity' => $real_quantity, 'runs' => $runs, 'interval' => $interval));
					 $type="Drip-Feed";
					 $status = $api->status($order);
					$status = $status->status;	
			
					submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
			
					break;
			
					case 'subscription':
					//No Link And Quantity Required
					$username = get_meta_type('username',$arr);
					$min = get_meta_type('min',$arr);
					$max = get_meta_type('max',$arr);
					$posts = get_meta_type('posts',$arr);
					$delay = get_meta_type('delay',$arr);
					$expiry = get_meta_type('expiry',$arr);
					$order = $api->order(array('service' => $service_id, 'username' => $username, 'link' => $original_link	, 'min' => $min, 'max' => $max, 'posts' => $posts,'delay' => $delay, 'expiry' => $expiry));
					$type="Subscription";
					$status = $api->status($order);
					$status = $status->status;	
			
					submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
			
					break;	
				}
				
			}
		}
		
	 }
}



//add_action( 'woocommerce_payment_complete', 'so_payment_complete' );
function so_payment_complete($order_id){
		
	$order = wc_get_order($order_id);
	 if(!empty($order)){
	 $user = $order->get_user();
				  $order_det=array();
				  foreach ($order->get_items() as $key => $lineItem) {
				  //$order_det = json_decode($lineItem,true);
				  
				  array_push($order_det,json_decode($lineItem,true));
				  
				  }
		 $sex=[];
		 $product_id=[];
		 for($i=0;$i<count($order_det);$i++)
		 {
			array_push($sex,$order_det[$i]['meta_data']);
		 }
	
		$result = call_user_func_array('array_merge', $sex);
	  //  print_r($result);
		// FOR GETTING QUANTITY AND LINK 
		$link = []; 
		$quantity =[];
		$the_word="quantity";
		$newArray = array();
		for($i=0;$i<count($result);$i++){
			if($result[$i]['key'] == 'custom_option'){
			   array_push($link,$result[$i]['value']);
			}
			if(strpos($result[$i]['key'], $the_word) !== false) {
			array_push($quantity,$result[$i]['value']);
			}
		}
		
		global $wpdb;
		 for($i=0;$i<count($order_det);$i++){	
		 $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$order_det[$i]['product_id']."", OBJECT );    
		 $service_val = json_decode(json_encode($results),True);
		
		 $original_link=$link[$i];
		 $myvalue = (!empty($quantity[$i]) ? $quantity[$i] : 0 );
		 $arr = explode(' ',trim($myvalue));
		 $real_quantity = $arr[0]*$order_det[$i]['quantity'];
		 
		 $results1 = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$order_det[$i]['product_id']." and meta_key = '_Service'", OBJECT );    
		 $service_val = json_decode(json_encode($results1),True);
		 $service_id = $service_val[0]['meta_value'];  
		 
		 $result2 = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$order_det[$i]['product_id']." and meta_key like '%_service_type%'", OBJECT );    
		 $service_type = json_decode(json_encode($result2),True);
		 $service_type = $service_type[0]['meta_value'];
	
		 $results3 = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$order_det[$i]['product_id']." and meta_key like '%_service_parent%'", OBJECT );
		 $api_arr = json_decode(json_encode($results3),True);
		 $api_id = $api_arr[0]['meta_value']; 
	
		 $tablename=$wpdb->prefix . "api_credentials";
		 $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=".$api_id."");
		 $api_data = json_decode(json_encode($api_data),true);
	
		 $api = new Api();
		 $api->api_url=$api_data['api_url'];
		 $api->api_key= $api_data['api_key'];
	
		 
		 $arr = $order_det[$i]['meta_data'];
		 $product_id =  $order_det[$i]['product_id'];  
		 switch ($service_type) {
			case 'default':
			$order = $api->order(array('service' =>$service_id, 'link' => $original_link, 'quantity' => $real_quantity));
			$type="Default";
			$status = $api->status($order);
			$status = $status->status;	
			submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
	
			break;
	
			case 'custom_comments':
			$comment = get_meta_type('custom_comment',$arr);
			$order = $api->order(array('service' => $service_id, 'link' => $original_link,'comments' =>$comment, 'quantity'=>$real_quantity));
			$type="Custom Comment";
			$status = $api->status($order);
			$status = $status->status;	
	
			submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
	
			break;
	
			case 'mention_custom_list':
			// No quantity required
			$value = get_meta_type('mention_custom_list',$arr);
			$order = $api->order(array('service' => $service_id, 'link' => $original_link, 'usernames' => $value, 'quantity'=>$real_quantity));
			$type="Mention Custom List";
			$status = $api->status($order);
			$status = $status->status;	
	
			submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
	
			break;
	
			case 'mention_user_follower':
			$value = get_meta_type('mention_user_follower',$arr);
			$order = $api->order(array('service' => $service_id, 'link' => $original_link, 'quantity' => $real_quantity, 'username' => $value));
			$type="Mention User Follower";
			$status = $api->status($order);
			$status = $status->status;	
	
			submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
	
			break;
	
	
			case 'comment_likes':
			$value = get_meta_type('comment_likes',$arr);
			$order = $api->order(array('service' => $service_id, 'link' => $original_link, 'quantity' => $real_quantity, 'username' => $value));
			$type="Comment Likes";
			submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id);
			break;
	
			case 'drip_feed':
			 $runs = get_meta_type('runs',$arr);
			 $interval = get_meta_type('interval',$arr);
			 $order = $api->order(array('service' => $service_id, 'link' => $original_link, 'quantity' => $real_quantity, 'runs' => $runs, 'interval' => $interval));
			 $type="Drip-Feed";
			 $status = $api->status($order);
			$status = $status->status;	
	
			submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
	
			break;
	
			case 'subscription':
			//No Link And Quantity Required
			$username = get_meta_type('username',$arr);
			$min = get_meta_type('min',$arr);
			$max = get_meta_type('max',$arr);
			$posts = get_meta_type('posts',$arr);
			$delay = get_meta_type('delay',$arr);
			$expiry = get_meta_type('expiry',$arr);
			$order = $api->order(array('service' => $service_id, 'username' => $username, 'link' => $original_link	, 'min' => $min, 'max' => $max, 'posts' => $posts,'delay' => $delay, 'expiry' => $expiry));
			$type="Subscription";
			$status = $api->status($order);
			$status = $status->status;	
	
			submit_api_order($order,$service_id,$original_link,$real_quantity,$type,$product_id,$order_id,$status);
	
			break;
	
		 }
		   
		 }
	}
}

add_action('wp_footer', 'wp_footer_smm_script');
function wp_footer_smm_script(){
?>
<script type="text/javascript">
	jQuery(".xoo-wsc-icon-cross").click(function(){ 
		jQuery(".single_add_to_cart_button").removeClass("loading");
	});
	jQuery(document).click(function(){ 
		jQuery(".single_add_to_cart_button").removeClass("loading");
	});
		
	function checkMustInclude(custom_option, service_with_holderarray){
		var matchvalue = service_with_holderarray.find(function (service_holderarray) {
		var reg = new RegExp(service_holderarray , 'i');
			return custom_option.match(reg) !== null;
		});
	  
		if (matchvalue) {
			return  true;
		} else {
			return  false;
		}
	}
	jQuery(".single_add_to_cart_button").click(function(){
		var service_with = jQuery('#service_with').val();
		var service_with_holder = jQuery('#service_with_holder').val();
		var service_with_holderarray = service_with_holder.split(',');
		var service_without = jQuery('#service_without').val();
		var service_without_holder = jQuery('#service_without_holder').val();
		var service_without_holderarray = service_without_holder.split(',');
		var custom_option = jQuery('.link_txtbox').val();
		if(service_without == 'yes'){
			var mustInlcude = checkMustInclude(custom_option, service_without_holderarray);
			if(mustInlcude == true){
				alert('Mag het volgende niet bevatten: '+service_without_holderarray);
				return false;
			}
		}
		if(service_with == 'yes'){
			var mustNotInc = checkMustInclude(custom_option, service_with_holderarray);
			if(mustNotInc == false){
				alert('Geef alsjeblieft een geldige url op: '+service_with_holderarray);
				return false;
			}
		}
	});
</script>
<?php	
}


