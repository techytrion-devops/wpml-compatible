<?php
add_action('wp_ajax_my_action', 'my_action_callback');

function my_action_callback() {
    global $wpdb; // this is how you get access to the database
    $tablename=$wpdb->prefix."api_credentials";
    $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id='".$_POST['panel_id']."'");
    $api_data = json_decode(json_encode($api_data),true);
    $api = new Api();
    $api->api_url=$api_data['api_url'];
    $api->api_key= $api_data['api_key'];
    // FOR SERVICES     
    $services = $api->services();
    $service_data = json_decode(json_encode($services),True);
    $option_arr = [];
    foreach($service_data as $row){$option_arr[] = array($row['service']=>$row['name'].",".$row['min'].",".$row['max'].",".$row['rate']);}
    $newArray = array();
    foreach($option_arr as $array) {foreach($array as $k=>$v) {$newArray[$k] = $v; }}
    $abc="<option>Select Service</option>";
    foreach($newArray as $key =>$row){
    $service_data = explode(",",$row);    
    $service_name = $key.' - '.$service_data[0];    
    $service_min = $service_data[1];    
    $service_max = $service_data[2];
    $service_rate = $service_data[3];    
    $abc.= "<option value=".$key."  data-servicemin=".$service_min." data-servicemax=".$service_max." data-servicerate=".$service_rate.">".$service_name."</option>";   
    }
    echo $abc;
    exit();
    }
    
    add_action('wp_ajax_load_smm_categories_services', 'get_smm_categories_services');

function get_smm_categories_services() {
    global $wpdb; // this is how you get access to the database
    $tablename=$wpdb->prefix."api_credentials";
    $panel_id = $_POST['panel_id'];
    $category = $_POST['category_id'];
    $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id='".$panel_id."'");
    $api_data = json_decode(json_encode($api_data),true);
    $api = new Api();
    $api->api_url=$api_data['api_url'];
    $api->api_key= $api_data['api_key'];
    // FOR SERVICES     
    $services = $api->services();
    $service_data = json_decode(json_encode($services),True);
    $option_arr = [];
    foreach($service_data as $row){
        $v = sanitize_title($row['category']);
        if($v == $category){
            $option_arr[] = array($row['service']=>$row['name'].",".$row['min'].",".$row['max'].",".$row['rate']);
        }
        
    }
    //print_r($option_arr);
    $newArray = array();
    foreach($option_arr as $array) {foreach($array as $k=>$v) {$newArray[$k] = $v; }}
    $abc="<option>Select Service</option>";
    foreach($newArray as $key =>$row){
    $service_data = explode(",",$row);    
    $service_name = $key.' - '.$service_data[0];    
    $service_min = $service_data[1];    
    $service_max = $service_data[2];
    $service_rate = $service_data[3];    
    $abc.= "<option value=".$key."  data-servicemin=".$service_min." data-servicemax=".$service_max." data-servicerate=".$service_rate.">".$service_name."</option>";   
    }
    echo $abc;
    exit();
    }
    
    add_action('wp_ajax_load_smm_categories', 'get_smm_categories');
    function get_smm_categories(){
            $panel_id = $_POST['panel_id'];
            global $wpdb; // this is how you get access to the database
            $tablename=$wpdb->prefix."api_credentials";
            $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id='".$panel_id."'");
            $api_data = json_decode(json_encode($api_data),true);
            $api = new Api();
            $api->api_url=$api_data['api_url'];
            $api->api_key= $api_data['api_key'];
            // FOR SERVICES     
            $services = $api->services();
            $service_data = json_decode(json_encode($services),True);
            $option_arr = [];
            foreach($service_data as $row){ $v = sanitize_title($row['category']); $option_arr[$v] = $row['category'];}
            //$option_arr = array_unique($option_arr);
            $abc="<option>Select Service Category</option>";
            foreach($option_arr as $key =>$name){
            $abc.= "<option value='".$key."'>".$name."</option>";   
            }
            echo $abc;
            die();
    }
    
    


// SERVICES DROPDOWN CODE
add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_general_fields' );
function woo_add_custom_general_fields() {
    global $wpdb;
	global $post;
	$post_id = $post->ID;
    $newArray = array();
    $abc="";
        if(isset($_GET['post'])){
        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = ".$_GET['post']." and meta_key like '%_service_parent%'", OBJECT );
        if(sizeof($results)>0){
        $service_val = json_decode(json_encode($results),True);
        $parent_id = $service_val[0]['meta_value']; 
        // FOR API KEY
        global $wpdb;
        $tablename=$wpdb->prefix . "api_credentials";
        $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id='".$parent_id."'");
        $api_data = json_decode(json_encode($api_data),true);
        $api = new Api();
        $api->api_url=$api_data['api_url'];
        $api->api_key= $api_data['api_key'];
        // FOR SERVICES     
        $services = $api->services();
        $service_data = json_decode(json_encode($services),True);
        //$option_arr=[];
        
        
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = " . $_GET['post'] . "", OBJECT);
        $product_data = json_decode(json_encode($results), True);
        
        foreach ($product_data as $row => $values):
            $product_details[$values['meta_key']] = $values['meta_value'];
        endforeach;
         $service_id = $product_details['_Service'];
         
        $_service_categories = array();
        $service_categories = "<option value=''>Select Category</option>";
        $selected_cat = '';
        foreach($service_data as $key=>$row){
            $cat = sanitize_title($row['category']);
            $_service_categories[$cat] = $row['category'];
            if($service_id == $row['service']){
                $selected_cat = $cat;
            }
        }
        
        if(!empty($_service_categories)){
            foreach($_service_categories as $k=>$v){
                $service_categories.= "<option value=".$k." ".($selected_cat == $k?'selected':'')."  >".$v."</option>"; 
            }
        }
        
        
        
        foreach($service_data as $row){
            $cat = sanitize_title($row['category']);
            if($cat == $selected_cat){
                $option_arr[] = array($row['service']=>$row['name'].",".$row['min'].",".$row['max'].",".$row['rate']);
            }
            
        }
        foreach($option_arr as $array) {foreach($array as $k=>$v) {$newArray[$k] = $v; }}
        }
        
        

       
        
        $abc="<option value=''>Select Service</option>";
        foreach($newArray as $key =>$row){
        $service_data = explode(",",$row);    
        $service_name = $service_data[0];    
        $service_min = $service_data[1];    
        $service_max = $service_data[2];
        $service_rate = $service_data[3];    
        
        $abc.= "<option value=".$key." ".($service_id == $key?'selected':'')."  data-servicemin=".$service_min." data-servicemax=".$service_max." data-servicerate=".$service_rate.">".$key.' - '.$service_name."</option>";   
        }
    }
  echo "<p>For using below service dropdown product type will be variable product</p>";
     //SERVICE TYPE
     $service_type_arr =array('default'=>'Default',
      'custom_comments'=>'Custom Comments',
      //'mention_custom_list'=>'Mentions Custom List',
      //'mention_user_follower'=>'Mentions User Followers',
      //'mention_package'=>'Package',
      'drip_feed'=>'Drip-Feed',
         'auto_post'=>'Auto Post'
      //'subscription'=>'Subscriptions',
      //'comment_likes'=>'Comment-Likes'
    );
    echo '<div class="options_group">';
    
        $tablename=$wpdb->prefix . "api_credentials";
    $api_data = $wpdb->get_results("SELECT * FROM $tablename");
    $api_data = json_decode(json_encode($api_data),true);
    
    $service_parent = [];
    foreach($api_data as $row => $value)
    { $service_parent[$value['api_id']] = $value['panel_name'];}
    
    array_unshift($service_parent, 'Select Panel');
    
    
    
    
    
    //SERVICES
    echo '<div class="options_group">';
         // Text Field
     
       $enable_smm = [0=>'Enable SMM API',1=>'Yes',2=>'No'];
       woocommerce_wp_select( array( // Text Field type
        'id'          => '_enable_smm',
        'label'       => __( 'Enable SMM API', 'woocommerce' ),
        'description' => __( 'To Enable SMM Plugin', 'woocommerce' ),
        'desc_tip'    => true,
        'options'     => $enable_smm
        ) );


       $enable_smm2 = ['no_value'=>'Choose Link Type',
       'https://instagram.com/'=>'https://instagram.com/',
       'https://www.tiktok.com/@'=>'https://www.tiktok.com/@',
       'https://www.youtube.com/'=>'https://www.youtube.com/',
       'https://www.facebook.com/'=>'https://www.facebook.com/',
       'https://www.twitter.com/'=>'https://www.twitter.com/',
       'https://www.snapchat.com/add/'=>'https://www.snapchat.com/add/',
       'https://www.spotify.com/'=>'https://www.spotify.com/',
       'https://www.twitch.tv/'=>'https://www.twitch.tv/',
       'https://www.linkedin.com/'=>'https://www.linkedin.com/',
       'https://www.soundcloud.com/'=>'https://www.soundcloud.com/',
       'https://www.pinterest.com/'=>'https://www.pinterest.com/',
       'https://www.quora.com/'=>'https://www.quora.com/'];
       
       woocommerce_wp_select( array( // Text Field type
        'id'          => '_link_type',
        'label'       => __( 'Choose Link Type', 'woocommerce' ),
        'description' => __( 'To Choose Link Type', 'woocommerce' ),
        'desc_tip'    => true,
        'options'     => $enable_smm2
        ) );
         
      
      woocommerce_wp_text_input( 
        array( 
          'id'          => '_link_label', 
          'label'       => __( 'Link Label', 'woocommerce' ), 
          'placeholder' => 'Link Label',
          'desc_tip'    => 'true',
          'description' => __( 'The Link Label of Product', 'woocommerce' ) 
        )
      );
      
       woocommerce_wp_text_input( 
        array( 
          'id'          => '_service_holder', 
          'label'       => __( 'Place Holder', 'woocommerce' ), 
          'placeholder' => 'Link Label',
          'desc_tip'    => 'true',
          'description' => __( 'The Link Place Holder of Product', 'woocommerce' ) 
        )
      );
       
       woocommerce_wp_text_input( 
        array( 
          'id'          => '_service_help_text', 
          'label'       => __( 'Help Text', 'woocommerce' ), 
          'placeholder' => 'Help Text',
          'desc_tip'    => 'true',
          'description' => __( 'Input field help text.', 'woocommerce' ) 
        )
      );
      
    woocommerce_wp_checkbox( 
        array( 
          'id'          => '_service_with', 
          'label'       => __( 'With', 'woocommerce' ), 
          'placeholder' => 'With',
          'desc_tip'    => 'true',
          'description' => __( 'Checkbox of the with validation', 'woocommerce' ) 
        )
      );

    woocommerce_wp_text_input( 
        array( 
          'id'          => '_service_with_holder', 
          'label'       => __( 'With Holder', 'woocommerce' ), 
          'placeholder' => 'With Label',
          'desc_tip'    => 'true',
          'description' => __( 'The Link Place Holder of With Comma Seprated', 'woocommerce' ) 
        )
      );

    woocommerce_wp_checkbox( 
        array( 
          'id'          => '_service_without', 
          'label'       => __( 'Without', 'woocommerce' ), 
          'placeholder' => 'Without',
          'desc_tip'    => 'true',
          'description' => __( 'Checkbox of the without validation', 'woocommerce' ) 
        )
      );

     woocommerce_wp_text_input( 
        array( 
          'id'          => '_service_without_holder', 
          'label'       => __( 'Without Holder', 'woocommerce' ), 
          'placeholder' => 'Without Label',
          'desc_tip'    => 'true',
          'description' => __( 'The Link Place Holder of Without Comma Seprated', 'woocommerce' ) 
        )
      );
    woocommerce_wp_select( array( // Text Field type
        'id'          => '_service_parent',
        'label'       => __( 'Panel', 'woocommerce' ),
        'description' => __( 'Choose Panel.', 'woocommerce' ),
        'desc_tip'    => true,
        'options'     => $service_parent
    ) );

    echo '</div>';
    
    woocommerce_wp_select( array( // Text Field type
        'id'          => '_service_type',
        'label'       => __( 'Service Type', 'woocommerce' ),
        'description' => __( 'Choose Service Type.', 'woocommerce' ),
        'desc_tip'    => true,
        'options'     => $service_type_arr
    ) );
  echo '</div>';

    
    echo '<div class="options_group"> <p class=" form-field _Service_field">
    <label for="_Service_category">Service</label>
          <span class="woocommerce-help-tip"></span><select style="" id="_Service_category" name="_Service_category" class="select short">'.$service_categories.'
          </select>
      </p>
    </div>';
    
    echo '<div class="options_group"> <p class=" form-field _Service_field">
    <label for="_Service">Service</label>
          <span class="woocommerce-help-tip"></span><select style="" id="_Service" name="_Service" class="select short">'.$abc.'
          </select>
      </p>
    </div>';
      
      
      woocommerce_wp_text_input( 
        array( 
          'id'          => '_service_lbl_id', 
          'label'       => __( 'Service Id', 'woocommerce' ), 
          'placeholder' => 'Service Id',
          'desc_tip'    => 'true',
          'description' => __( 'The Service Id', 'woocommerce' ) 
        )
      );
      
            // Text Field
      woocommerce_wp_text_input( 
        array( 
          'id'          => '_service_min', 
          'label'       => __( 'Minimum Quantity', 'woocommerce' ), 
          'placeholder' => 'Minimum Quantity',
          'desc_tip'    => 'true',
          'description' => __( 'The minimum quantity of service', 'woocommerce' ) 
        )
      );

      woocommerce_wp_text_input( 
        array( 
          'id'          => '_service_max', 
          'label'       => __( 'Maximum Quantity', 'woocommerce' ), 
          'placeholder' => 'Maximum Quantity',
          'desc_tip'    => 'true',
          'description' => __( 'The maximum quantity of service', 'woocommerce' ) 
        )
      );

      woocommerce_wp_text_input( 
        array( 
          'id'          => '_service_rate', 
          'label'       => __( 'Rate', 'woocommerce' ), 
          'placeholder' => 'Rate',
          'desc_tip'    => 'true',
          'description' => __( 'The rate of service', 'woocommerce' ) 
        )
      );
	  
	  $args = array(
			'type' => 'variable',
			'limit' => -1,
			'exclude' => array( $post_id ),
	  );
		$products = wc_get_products( $args );
	  
	  ?>
        <p class="form-field _smm_upsell_product_field">
            <label for="smm_upsell_product"><?php esc_html_e( 'Select Upsell product', 'woocommerce' ); ?></label>
            <select style="width: 50%;" id="_smm_upsell_product" name="_smm_upsell_product">
            	<option value="">Select Product</option>
                <?php
                $upsell_id = get_post_meta($post_id, '_smm_upsell_product', true);
				if($products){
					foreach($products as $p){
						?>
						<option <?php echo ($upsell_id == $p->get_id())?"selected=\"selected\"":""?> value="<?php echo esc_attr( $p->get_id() ); ?>"><?php echo  esc_html( wp_strip_all_tags( $p->get_formatted_name() ) );?></option>	
                        <?php
					}
				}

                ?>
            </select>
        </p>
    <?php	
	
	woocommerce_wp_text_input( 
        array( 
          'id'          => '_smm_upsell_text', 
          'label'       => __( 'Upsell Text', 'woocommerce' ), 
          'desc_tip'    => 'true',
          'description' => __( 'Upsell Text', 'woocommerce' ) 
        )
      );
	  
	  woocommerce_wp_text_input( 
        array( 
          'id'          => '_smm_upsell_help_text', 
          'label'       => __( 'Upsell Help Text', 'woocommerce' ), 
          'desc_tip'    => 'true',
          'description' => __( 'Upsell help text', 'woocommerce' ) 
        )
      );
          
          $args = array(
			'type' => 'variable',
			'limit' => -1,
			'exclude' => array( $post_id ),
	  );
		$products = wc_get_products( $args );
	  
	  ?>
        <p class="form-field _smm_upsell_product2_field">
            <label for="smm_upsell_product2"><?php esc_html_e( 'Select Second Upsell', 'woocommerce' ); ?></label>
            <select style="width: 50%;" id="_smm_upsell_product2" name="_smm_upsell_product2">
            	<option value="">Select Product</option>
                <?php
                $upsell_id2 = get_post_meta($post_id, '_smm_upsell_product2', true);
				if($products){
					foreach($products as $p){
						?>
						<option <?php echo ($upsell_id2 == $p->get_id())?"selected=\"selected\"":""?> value="<?php echo esc_attr( $p->get_id() ); ?>"><?php echo  esc_html( wp_strip_all_tags( $p->get_formatted_name() ) );?></option>	
                        <?php
					}
				}

                ?>
            </select>
        </p>
    <?php
    
    woocommerce_wp_text_input( 
        array( 
          'id'          => '_smm_upsell2_text', 
          'label'       => __( 'Second Upsell Text', 'woocommerce' ), 
          'desc_tip'    => 'true',
          'description' => __( 'Second Upsell Text', 'woocommerce' ) 
        )
      );
	  
	  woocommerce_wp_text_input( 
        array( 
          'id'          => '_smm_upsell2_help_text', 
          'label'       => __( 'Second Upsell Help Text', 'woocommerce' ), 
          'desc_tip'    => 'true',
          'description' => __( 'Second Upsell help text', 'woocommerce' ) 
        )
      );


}

// Save Fields values to database when submitted (Backend)
add_action( 'woocommerce_process_product_meta', 'woo_save_custom_general_fields');
function woo_save_custom_general_fields( $post_id ){


    // Saving "Conditions" field key/value
    $posted_field_value = @$_POST['_Service'];
    if( ! empty( $posted_field_value ) )
        update_post_meta( $post_id, '_Service', esc_attr( $posted_field_value ) );

    $posted_field_value = @$_POST['_service_type'];
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_service_type', esc_attr( $posted_field_value ) );
         
    $posted_field_value = @$_POST['_service_parent'];
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_service_parent', esc_attr( $posted_field_value ) );
    
    $posted_field_value = @$_POST['_link_label'];
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_link_label', __( $posted_field_value ) );
         
    $posted_field_value = @$_POST['_service_holder'];
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_service_holder', esc_attr( $posted_field_value ) );
    
    if(isset($_POST['_service_help_text'])){
        $_service_help_text = $_POST['_service_help_text'];
        update_post_meta( $post_id, '_service_help_text', esc_attr( $_service_help_text ) );
    }

    $posted_field_value = isset( $_POST['_service_with'] ) ? 'yes' : 'no';

    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_service_with', esc_attr( $posted_field_value ) );

    $posted_field_value = @$_POST['_service_with_holder'];
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_service_with_holder', esc_attr( $posted_field_value ) );

    $posted_field_value = isset( $_POST['_service_without'] ) ? 'yes' : 'no';
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_service_without', esc_attr( $posted_field_value ) );

    $posted_field_value = @$_POST['_service_without_holder'];
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_service_without_holder', esc_attr( $posted_field_value ) );     
             $posted_field_value = @$_POST['_enable_smm'];
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_enable_smm', esc_attr( $posted_field_value ) );

           $posted_field_value = @$_POST['_link_type'];
    if( ! empty( $posted_field_value ) )
         update_post_meta( $post_id, '_link_type', esc_attr( $posted_field_value ) );
		 
    
    
    if($_POST['_smm_upsell_text']){
	$posted_field_value = @$_POST['_smm_upsell_text'];
         update_post_meta( $post_id, '_smm_upsell_text', esc_attr( $posted_field_value ) );
    }
		 
    if($_POST['_smm_upsell_help_text']){
	$posted_field_value = @$_POST['_smm_upsell_help_text'];
         update_post_meta( $post_id, '_smm_upsell_help_text', esc_attr( $posted_field_value ) );
    }
    
    if(isset($_POST['_smm_upsell_product'])){
	$posted_field_value = intval(@$_POST['_smm_upsell_product'] );
         update_post_meta( $post_id, '_smm_upsell_product', esc_attr( $posted_field_value ) );	
         if(empty($posted_field_value)){
             update_post_meta( $post_id, '_smm_upsell_text', '' );
             update_post_meta( $post_id, '_smm_upsell_help_text', '' );
         }
    }
    
    
    
    
    if(isset($_POST['_smm_upsell2_text'])){
        $posted_field_value = $_POST['_smm_upsell2_text'];
        update_post_meta( $post_id, '_smm_upsell2_text', esc_attr( $posted_field_value ) );
        
    }
    
    if(isset($_POST['_smm_upsell2_help_text'])){
        $posted_field_value = $_POST['_smm_upsell2_help_text'];
        update_post_meta( $post_id, '_smm_upsell2_help_text', esc_attr( $posted_field_value ) );
    }
    
    if(isset($_POST['_smm_upsell_product2'])){
        $posted_field_value = $_POST['_smm_upsell_product2'];
        update_post_meta( $post_id, '_smm_upsell_product2', esc_attr( $posted_field_value ) );
        if(empty($posted_field_value)){
             update_post_meta( $post_id, '_smm_upsell2_text', '' );
             update_post_meta( $post_id, '_smm_upsell2_help_text', '' );
        }
        
    }
}
//END SERVICES DROPDOWN CODE