<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//include the utils
require_once( plugin_dir_path( __FILE__ ) . '../utils/utils.php' );

//include the API class
require_once( plugin_dir_path( __FILE__ ) . '../external-api/heroframe-dashboard-api.php');

//include the admin actions
require_once 'actions/admin-actions.php';

//include the gui-actions
require_once 'actions/gui-actions.php';
