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

<!-- This file has functionality of Admin menu page -->

<?php


/**
 * Create a new table class that will extend the WP_List_Table
 */
class SMM_List_Order_Table extends WP_List_Table {

   /**
     * Prepare the items for the table to process
     *
     * @return Void
     */

    function extra_tablenav( $which ) {
        if ( $which == "top" ){     
            echo "<span style='color: #23282d;font-size: 1.3em;font-weight: 600;'>Orders Data</span>";
        }
    }

    /**
     * Displays the search box.
     *
     * @since 3.1.0
     *
     * @param string $text     The 'submit' button label.
     * @param string $input_id ID attribute value for the search input field.
     */
    public function search_box( $text, $input_id ) {
      if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
        return;
      }

      $input_id = $input_id . '-search-input';

      if ( ! empty( $_REQUEST['orderby'] ) ) {
        echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
      }
      if ( ! empty( $_REQUEST['order'] ) ) {
        echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
      }
      if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
        echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
      }
      if ( ! empty( $_REQUEST['detached'] ) ) {
        echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
      }
      ?>
<p class="search-box">
  <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
  <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
  <?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
</p>
<?php
    }
    public function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'api_order_detail'; // do not forget about tables prefix
        $per_page = 10; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings

        //$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'created_at';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array


        $search = '';

        if ( ! empty( $_REQUEST['s'] ) ) { // WPCS: input var okay, CSRF ok.
          //$order_data= $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE order_id LIKE '%{$_REQUEST['s']}%' OR type LIKE '%{$_REQUEST['s']}%'ORDER BY $orderby $order"), ARRAY_A);
      
      $order_data= $wpdb->get_results("SELECT * FROM $table_name WHERE order_id LIKE '%{$_REQUEST['s']}%' OR link LIKE '%{$_REQUEST['s']}%' OR type LIKE '%{$_REQUEST['s']}%' ORDER BY $orderby $order LIMIT $per_page OFFSET $paged", ARRAY_A);
      
      $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE order_id LIKE '%{$_REQUEST['s']}%' OR link LIKE '%{$_REQUEST['s']}%' OR type LIKE '%{$_REQUEST['s']}%'");
      
        }else{
           $order_data= $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
       
       $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        }
        $data = array();

        foreach ($order_data as $value) {
          if($value['status']==1){ $status = '<span class="label label-success">Completed</span>';}if($value['status']==2){ $status = '<span class="label label-warning">Partial / Cancelled</span>';}
          if($value['status']==0){ $status = '<span class="label label-danger">Failed</span>';}if($value['status']==4){ $status = '<span class="label label-danger">Pending</span>';}
          $service = get_service_name_return($value['service_id'],$value['product_id']);
           $data[] = [
                      'order_id'      => $value['order_id'],
                      'created_at'    => date('d-m-Y',strtotime($value['created_at'])),
                      'type'          => $value['type'],
                      'service'       => $service,
                      'link'          => $value['link'],
                      'quantity'      => $value['quantity'],
                      'mesg'          => $value['mesg'],
                      'status'        => $status,
                  ];
        }

        $this->items = $data;

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            // 'cb'        => '<input type="checkbox" />',
            'order_id'      => 'Order#',
            'created_at'    => 'Date',
            'type'          => 'Type',
            'service'       => 'Service',
            'link'          => 'Link',
            'quantity'      => 'Quantity',
            'mesg'          => 'Message',
            'status'        => 'Status',
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }


    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return [
            'order_id'  => ['created_at', false],
            'created_at'=> ['created_at', false],
            'type'      => ['type', false],
            'service'   => ['service', false],
            'link'      => ['link', false],
            'quantity'  => ['quantity', false],
            'mesg'      => ['mesg', false],
            'status'    => ['status', false],
        ];
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'order_id':
            case 'created_at':
            case 'type':
            case 'date':
            case 'service':
            case 'link':
            case 'quantity':
            case 'mesg':
            case 'status':
              return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class SMM_List_Manual_Order_Table extends WP_List_Table {

   /**
     * Prepare the items for the table to process
     *
     * @return Void
     */

    function extra_tablenav( $which ) {
        if ( $which == "top" ){     
      echo '<div class="row">';
            echo "<div class='col-md-6'><span style='color: #23282d;font-size: 1.3em;font-weight: 600;'>Manual Orders Data</span></div>";
      echo '<div class="col-md-6"><a href="#" class="btn btn-primary" data-toggle="modal" data-target="#addNewModal">Add new Order</a></div>';
      echo '</div>';
        }
    }

    /**
     * Displays the search box.
     *
     * @since 3.1.0
     *
     * @param string $text     The 'submit' button label.
     * @param string $input_id ID attribute value for the search input field.
     */
    public function search_box( $text, $input_id ) {
      if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
        return;
      }

      $input_id = $input_id . '-search-input';

      if ( ! empty( $_REQUEST['orderby'] ) ) {
        echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
      }
      if ( ! empty( $_REQUEST['order'] ) ) {
        echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
      }
      if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
        echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
      }
      if ( ! empty( $_REQUEST['detached'] ) ) {
        echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
      }
      ?>
<p class="search-box">
  <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
  <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
  <?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
</p>
<?php
    }
    public function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'api_manual_order_detail'; // do not forget about tables prefix
        $per_page = 10; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings

        //$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'created_at';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array


        $search = '';

        if ( ! empty( $_REQUEST['s'] ) ) { // WPCS: input var okay, CSRF ok.
          //$order_data= $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE order_id LIKE '%{$_REQUEST['s']}%' OR type LIKE '%{$_REQUEST['s']}%'ORDER BY $orderby $order"), ARRAY_A);
      
      $order_data= $wpdb->get_results("SELECT * FROM $table_name WHERE order_id LIKE '%{$_REQUEST['s']}%' OR link LIKE '%{$_REQUEST['s']}%' OR type LIKE '%{$_REQUEST['s']}%' ORDER BY $orderby $order LIMIT $per_page OFFSET $paged", ARRAY_A);
      
      $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE order_id LIKE '%{$_REQUEST['s']}%' OR link LIKE '%{$_REQUEST['s']}%' OR type LIKE '%{$_REQUEST['s']}%'");
      
        }else{
           $order_data= $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
       
       $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        }
    
        $data = array();

        foreach ($order_data as $value) {
          if($value['status']==1){ $status = '<span class="label label-success">Completed</span>';}if($value['status']==2){ $status = '<span class="label label-warning">Partial / Cancelled</span>';}
          if($value['status']==0){ $status = '<span class="label label-danger">Failed</span>';}if($value['status']==4){ $status = '<span class="label label-danger">Pending</span>';}
          $service = get_service_name_return_manual($value['service_id'],$value['product_id']);
      $user_id = $value['placed_by'];
      $placed_by = '';
      if(!empty($user_id)){
        $user = get_userdata($user_id);
        $placed_by = ($user)?$user->display_name:"";  
      }
           $data[] = [
                      'order_id'      => $value['order_id'],
                      'created_at'    => date('d-m-Y',strtotime($value['created_at'])),
                      'type'          => $value['type'],
                      'service'       => $service,
                      'link'          => $value['link'],
                      'quantity'      => $value['quantity'],
                      'mesg'          => $value['mesg'],
                      'status'        => $status,
            'placed_by'     => $placed_by,
                  ];
        }

        $this->items = $data;

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            // 'cb'        => '<input type="checkbox" />',
            'order_id'      => 'Order#',
            'created_at'    => 'Date',
            'type'          => 'Type',
            'service'       => 'Service',
            'link'          => 'Link',
            'quantity'      => 'Quantity',
            'mesg'          => 'Message',
            'status'        => 'Status',
      'placed_by'     =>  'Placed By'
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }


    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return [
            'order_id'  => ['created_at', false],
            'created_at'=> ['created_at', false],
            'type'      => ['type', false],
            'service'   => ['service', false],
            'link'      => ['link', false],
            'quantity'  => ['quantity', false],
            'mesg'      => ['mesg', false],
            'status'    => ['status', false],
      'placed_by'    => ['placed_by', false],
        ];
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'order_id':
            case 'created_at':
            case 'type':
            case 'date':
            case 'service':
            case 'link':
            case 'quantity':
            case 'mesg':
            case 'status':
      case 'placed_by':
              return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}


  $myplugin_link = plugins_url( '', __FILE__ );
   $adminURL = admin_url().'admin.php?page=ftb';
   global $wpdb; //geting the acces to a wp tables
   $tablename=$wpdb->prefix . "api_credentials"; 
   $tablename2=$wpdb->prefix . "api_order_detail";  //the name of a table with it's prefix
   
   ?>
<?php
if(isset($_POST['api_submit']))
{ 
  $panel_name = $_POST['panel_name'];
  $api_url = $_POST['api_url'];
  $api_key = $_POST['api_key'];
  
  $panel_name2 = $_POST['panel_name2'];
  $api_url2 = $_POST['api_url2'];
  $api_key2 = $_POST['api_key2'];
  
  $panel_name3 = $_POST['panel_name3'];
  $api_url3 = $_POST['api_url3'];
  $api_key3 = $_POST['api_key3'];
  
  $panel_name4 = $_POST['panel_name4'];
  $api_url4 = $_POST['api_url4'];
  $api_key4 = $_POST['api_key4'];

  $wpdb->query("INSERT INTO $tablename(`api_url`, `api_key`, `panel_name`) VALUES ('".$api_url."','".$api_key."','".$panel_name."')");
  $wpdb->query("INSERT INTO $tablename(`api_url`, `api_key`, `panel_name`) VALUES ('".$api_url2."','".$api_key2."','".$panel_name2."')");
  $wpdb->query("INSERT INTO $tablename(`api_url`, `api_key`, `panel_name`) VALUES ('".$api_url3."','".$api_key3."','".$panel_name3."')");
  $wpdb->query("INSERT INTO $tablename(`api_url`, `api_key`, `panel_name`) VALUES ('".$api_url4."','".$api_key4."','".$panel_name4."')");
  echo "<script>alert('Api has been inserted successfully!!');</script>";
}
else if(isset($_POST['api_update'])){
  $panel_name = $_POST['panel_name'];
  $api_url = $_POST['api_url'];
  $api_key = $_POST['api_key'];
  
  $panel_name2 = $_POST['panel_name2'];
  $api_url2 = $_POST['api_url2'];
  $api_key2 = $_POST['api_key2'];
  
  $panel_name3 = $_POST['panel_name3'];
  $api_url3 = $_POST['api_url3'];
  $api_key3 = $_POST['api_key3'];
  
  $panel_name4 = $_POST['panel_name4'];
  $api_url4 = $_POST['api_url4'];
  $api_key4 = $_POST['api_key4'];

  $wpdb->query("UPDATE $tablename SET `api_url` ='".$api_url."', `api_key` ='".$api_key."', `panel_name` ='".$panel_name."'  where api_id=1");
  $wpdb->query("UPDATE $tablename SET `api_url` ='".$api_url2."', `api_key` ='".$api_key2."', `panel_name` ='".$panel_name2."'  where api_id=2");
  $wpdb->query("UPDATE $tablename SET `api_url` ='".$api_url3."', `api_key` ='".$api_key3."', `panel_name` ='".$panel_name3."'  where api_id=3");
  $wpdb->query("UPDATE $tablename SET `api_url` ='".$api_url4."', `api_key` ='".$api_key4."', `panel_name` ='".$panel_name4."'  where api_id=4");
  echo "<script>alert('Api has been updated successfully!!');</script>";
}
  $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=1");
  $api_data = json_decode(json_encode($api_data),true);
  $panel_name = $api_data['panel_name'];
  $api_url = $api_data['api_url']; 
  $api_key = $api_data['api_key'];
  
  $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=2");
  $api_data = json_decode(json_encode($api_data),true);
  $panel_name2 = $api_data['panel_name'];
  $api_url2 = $api_data['api_url']; 
  $api_key2 = $api_data['api_key'];
  
  $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=3");
  $api_data = json_decode(json_encode($api_data),true);
  $panel_name3 = $api_data['panel_name'];
  $api_url3 = $api_data['api_url']; 
  $api_key3 = $api_data['api_key'];
  
  $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=4");
  $api_data = json_decode(json_encode($api_data),true);
  $panel_name4 = $api_data['panel_name'];
  $api_url4 = $api_data['api_url']; 
  $api_key4 = $api_data['api_key'];

  
?>

</head><body>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> 
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> 
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script> 
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
<h1 class="smm-api-addon-heading">Woo SMM API Panel</h1>
<p>Click on the buttons inside the tabbed menu:</p>
<?php
  $active_tab = 'orders';
  if(isset($_GET['tab'])){
    if('manual_orders' == $_GET['tab']){
      $active_tab = 'manual_orders';  
    }
    if('api' == $_GET['tab']){
      $active_tab = 'api';  
    }
  }else{
    $active_tab = 'orders'; 
  }
?>
<div class="tabs smm-addon-admin"> <a class="button <?php echo ($active_tab == 'orders')?"active":"";?>" href="<?php echo admin_url("admin.php?page=ftb");?>" id="order_btn">Orders</a> <a class="button <?php echo ($active_tab == 'manual_orders')?"active":"";?>" href="<?php echo admin_url("admin.php?page=ftb&tab=manual_orders");?>"  id="manual_order_btn">Manual Orders</a> <a class="button <?php echo ($active_tab == 'api')?"active":"";?>" href="<?php echo admin_url("admin.php?page=ftb&tab=api");?>" >API Credentials</a> </div>
<?php if(isset($_GET['tab']) && 'api' == $_GET['tab']){ ?>
<div id="api" class="tabcontent tabs-content-area">
  <div class="row">
    <div class="col-sm-12">
      <center>
        <h2>API CREDENTIALS</h2>
      </center>
      <hr>
    </div>
    <hr>
  </div>
  <div class="row">
    <div class="col-sm-12">
      <form method="POST" action="#">
        <h2>API 1</h2>
        <div class="form-group">
          <label for="exampleInputEmail1">PANEL</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="panel_name" placeholder="Enter PANEL NAME" value="<?php if(isset($panel_name)){ echo $panel_name; }?>" required>
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">API URL</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="api_url" placeholder="Enter API URL" value="<?php if(isset($api_url)){ echo $api_url; }?>" required>
        </div>
        <div class="form-group">
          <label for="exampleInputPassword1">API KEY</label>
          <input type="text" class="form-control" name="api_key" id="exampleInputPassword1" placeholder="Enter API KEY" value="<?php if(isset($api_key)){ echo $api_key; }?>" required>
          <small id="emailHelp" class="form-text text-muted" >We'll never share your Api key with anyone else.</small> </div>
        <h2>API 2</h2>
        <div class="form-group">
          <label for="exampleInputEmail1">PANEL</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="panel_name2" placeholder="Enter PANEL NAME" value="<?php if(isset($panel_name2)){ echo $panel_name2; }?>" required>
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">API URL</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="api_url2" placeholder="Enter API URL" value="<?php if(isset($api_url2)){ echo $api_url2; }?>" required>
        </div>
        <div class="form-group">
          <label for="exampleInputPassword1">API KEY</label>
          <input type="text" class="form-control" name="api_key2" id="exampleInputPassword1" placeholder="Enter API KEY" value="<?php if(isset($api_key2)){ echo $api_key2; }?>" required>
          <small id="emailHelp" class="form-text text-muted" >We'll never share your Api key with anyone else.</small> </div>
        <h2>API 3</h2>
        <div class="form-group">
          <label for="exampleInputEmail1">PANEL</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="panel_name3" placeholder="Enter PANEL NAME" value="<?php if(isset($panel_name3)){ echo $panel_name3; }?>" required>
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">API URL</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="api_url3" placeholder="Enter API URL" value="<?php if(isset($api_url3)){ echo $api_url3; }?>" required>
        </div>
        <div class="form-group">
          <label for="exampleInputPassword1">API KEY</label>
          <input type="text" class="form-control" name="api_key3" id="exampleInputPassword1" placeholder="Enter API KEY" value="<?php if(isset($api_key3)){ echo $api_key3; }?>" required>
          <small id="emailHelp" class="form-text text-muted" >We'll never share your Api key with anyone else.</small> </div>
        <h2>API 4</h2>
        <div class="form-group">
          <label for="exampleInputEmail1">PANEL</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="panel_name4" placeholder="Enter PANEL NAME" value="<?php if(isset($panel_name4)){ echo $panel_name4; }?>" required>
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">API URL</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="api_url4" placeholder="Enter API URL" value="<?php if(isset($api_url4)){ echo $api_url4; }?>" required>
        </div>
        <div class="form-group">
          <label for="exampleInputPassword1">API KEY</label>
          <input type="text" class="form-control" name="api_key4" id="exampleInputPassword1" placeholder="Enter API KEY" value="<?php if(isset($api_key4)){ echo $api_key4; }?>" required>
          <small id="emailHelp" class="form-text text-muted" >We'll never share your Api key with anyone else.</small> </div>
        <?php if(empty($api_data)){?>
        <button type="submit" class="btn btn-primary" name="api_submit">Submit</button>
        <?php } else{?>
        <button type="submit" class="btn btn-primary" name="api_update">Update</button>
        <?php
                   }
                  ?>
      </form>
    </div>
  </div>
</div>
<?php } ?>
<?php if(isset($_GET['tab']) && 'manual_orders' == $_GET['tab']){
  wp_enqueue_script('jquery-blockui');
   ?>
<div id="manual_orders" class="tabcontent tabs-content-area">
  <form method="post">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
    <?php 
    $smmListOrderTable = new SMM_List_Manual_Order_Table();
    $smmListOrderTable->prepare_items();
    $smmListOrderTable->search_box('search', 'search_id');
    $smmListOrderTable->display(); ?>
  </form>
</div>
<div id="addNewModal" class="modal fade" role="dialog">
  <div class="modal-dialog"> 
    
    <!-- Modal content-->
    <div class="modal-content" id="manual-orders-modal-body">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add manual order</h4>
      </div>
      <div class="modal-body">
      <?php
    $tablename=$wpdb->prefix . "api_credentials";
    $api_data = $wpdb->get_results("SELECT * FROM $tablename");
    $api_data = json_decode(json_encode($api_data),true);
    
    $service_parent = array();
    foreach($api_data as $row => $value)
    { $service_parent[$value['api_id']] = $value['panel_name'];}
    
    array_unshift($service_parent, 'Select Panel');
    
    $service_type_arr =array(
      'default'=>'Default',
                        'auto_post'=>'Auto Post',
        'custom_comments'=>'Custom Comments',
        /*'drip_feed'=>'Drip-Feed'*/
    );
    
    ?>
        <form method="POST" action="#" id="manual-orders-modal-form">
          <div class="form-group">
            <?php
        woocommerce_wp_select( array( // Text Field type
          'id'          => '_service_parent_id',
          'label'       => __( 'Panel', 'woocommerce' ),
          'description' => __( 'Choose Panel.', 'woocommerce' ),
          'desc_tip'    => false,
          'options'     => $service_parent
        ) );
      ?>
          </div>
          <div class="form-group">
            <?php
        woocommerce_wp_select( array( // Text Field type
          'id'          => '_service_type',
          'label'       => __( 'Service Type', 'woocommerce' ),
          'description' => __( 'Choose Service Type.', 'woocommerce' ),
          'desc_tip'    => false,
          'options'     => $service_type_arr
        ) );
      ?>
          </div>
          <div class="form-group">
          <?php
        woocommerce_wp_select( array( // Text Field type
          'id'          => '_service_category',
          'label'       => __( 'Service Category', 'woocommerce' ),
          'description' => __( 'Choose Service Category.', 'woocommerce' ),
          'desc_tip'    => false,
          'options'     => array()
        ) );
      ?>
          </div>
            <div class="form-group">
          <?php
        woocommerce_wp_select( array( // Text Field type
          'id'          => '_service',
          'label'       => __( 'Service', 'woocommerce' ),
          'description' => __( 'Choose Service.', 'woocommerce' ),
          'desc_tip'    => false,
          'options'     => array()
        ) );
      ?>
          </div>
          <div class="form-group">
          <?php
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
      ?>
          </div>
          
          <div class="form-group">
          <?php
        woocommerce_wp_text_input( array( // Text Field type
          'id'          => '_link',
          'label'       => __( 'Link', 'woocommerce' ),
          'description' => __( 'Link URL.', 'woocommerce' ),
          'desc_tip'    => false,
          'data_type'   => 'url',
        ) );
      ?>
          </div>
          
          <div class="form-group _qty_field_input">
          <?php
        woocommerce_wp_text_input( array( // Text Field type
          'id'          => '_qty',
          'label'       => __( 'Quantity', 'woocommerce' ),
          'desc_tip'    => false,
          'type'        => 'number',
        ) );
      ?>
          </div>
            <div class="form-group new_posts_input" style="display:none;">
          <?php
        woocommerce_wp_text_input( array( // Text Field type
          'id'          => '_new_posts',
          'label'       => __( 'New Posts', 'woocommerce' ),
          'desc_tip'    => false,
          'type'        => 'number',
        ) );
      ?>
          </div>
            <div class="form-group custom_comments" style="display:none;">
                
                <p class="form-field _custom_comments_field ">
                    <label for="_custom_comments">Comments (1 per line)</label><textarea rows="10" name="_custom_comments" id="_custom_comments"></textarea> </p>
                
         
          </div>
          <input type="hidden" name="action" value="add_api_manual_order" />
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="manual-orders-modal-add-btn" class="btn btn-default">Save</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
  
  $(document).on('click', '#manual-orders-modal-add-btn', function(event){
    event.preventDefault(); 
    $('#manual-orders-modal-form').submit();
  });
        
        $(document).on('change', '#_service_type', function(){
            if($(this).val() == 'auto_post'){
                $('.new_posts_input').show();
                $('.custom_comments').hide();
            }else{
                $('.new_posts_input').hide();
            }
            
            if($(this).val() == 'custom_comments'){
                $('.new_posts_input').hide();
                $('.custom_comments').show();
                $("._qty_field_input").hide();
            }else{
                $('.custom_comments').hide();
                $("._qty_field_input").show();
            }
            
            
            
        });
  
  $(document).on('submit', '#manual-orders-modal-form', function(event){
    event.preventDefault(); 
    
    var $err = 0;
    if(_validate_midal_input_field($("#_service_parent_id"))){
      $err++; 
    }
    if(_validate_midal_input_field($("#_service"))){
      $err++; 
    }
    if(_validate_midal_input_field($("#_link"))){
      $err++; 
    }
                
                if($('#_service_type').val() != 'custom_comments'){
                    if(_validate_midal_input_field($("#_qty"))){
                            $err++; 
                    }
                }
    
    if($err == 0){
      var $data = $(this).serialize();
      $('#manual-orders-modal-body').block();
      $.post(ajaxurl, $data, function(response) {
        console.log(response);
        if(response.success == true){
          alert(response.data.message);
          if(response.data.status == 'success'){
            location.reload(true);  
          }
        }
        $('#manual-orders-modal-body').unblock();
      });
      
    }
  });
  
  function _validate_midal_input_field($field){
    if($field.val() == '' || $field.val() == 0){
      $field.addClass('error-field'); 
      return true;
    }else{
      $field.removeClass('error-field');  
      return false;
    }
  }
  
  
  
    $("#_service_parent_id").on('change', function() {
    $('#manual-orders-modal-body').block();
    var data = {
      action: 'load_smm_categories',
      panel_id: $(this).val()
    };
    $.post(ajaxurl, data, function(response) {
      $("#_service_category").html(response);
      $('#manual-orders-modal-body').unblock();
    });
  });
        
         $("#_service_category").on('change', function() {
    $('#manual-orders-modal-body').block();
    var data = {
      action: 'load_smm_categories_services',
      category_id: $(this).val(),
                        panel_id:$("#_service_parent_id").val(),
    };
    $.post(ajaxurl, data, function(response) {
      $("#_service").html(response);
      $('#manual-orders-modal-body').unblock();
    });
  });
  
  $(document).on('change','#_service', function () {
    if($(this).val() != ""){
      $("#_service_min").val($("#_service [value="+$(this).val()+"]").attr('data-servicemin'));
      $("#_service_max").val($("#_service [value="+$(this).val()+"]").attr('data-servicemax'));
      $("#_service_rate").val($("#_service [value="+$(this).val()+"]").attr('data-servicerate'));
      $("#_service_lbl_id").val($(this).val());
    }else{
      $("#_service_min").val("");
      $("#_service_max").val("");
      $("#_service_rate").val("");
      $("#_service_lbl_id").val("");
    }
  });
            
  $("#_service_min").val($("#_service [value="+$("#_service").val()+"]").attr('data-servicemin'));
  $("#_service_max").val($("#_service [value="+$("#_service").val()+"]").attr('data-servicemax'));
  $("#_service_rate").val($("#_service [value="+$("#_service").val()+"]").attr('data-servicerate'));    
   $("#_service_lbl_id").val($("#_service").val());
  
});
</script>

<?php } ?>
<?php if( !isset($_GET['tab']) || ( isset($_GET['tab']) && 'orders' == $_GET['tab'] ) || ( isset($_GET['tab']) && '' == $_GET['tab'] ) ){ ?>
<div id="orders" class="tabcontent tabs-content-area">
  <form method="post">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
    <?php 
    $smmListOrderTable = new SMM_List_Order_Table();
    $smmListOrderTable->prepare_items();
    $smmListOrderTable->search_box('search', 'search_id');
    $smmListOrderTable->display(); ?>
  </form>
</div>
<?php } ?>
<script>

</script>
<?php

// include 'woo-smm-addon-admin-api.php';
// include 'woo-smm-addon-admin_api_callback.php';
// include 'woo-smm-addon-payments.php';
// include 'woo-smm-addon-admin-meta_functions.php';
// include 'woo-smm-addon-admin_custom_field.php';
// include 'woo-smm-addon-global-cart.php';

