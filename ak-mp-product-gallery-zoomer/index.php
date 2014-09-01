<?php
/*
Plugin Name: AK MarketPress Product Gallery Zoomer
Plugin URI: https://github.com/akwatra/ak-mp-product-gallery-zoomer/
Description: MarketPress Product Multi Image gallery management
Author: Ajay Kwatra
Version: 1.0
Author URI: https://github.com/akwatra/
License: GPLv2 or later
*/

/*  
	Copyright 2014  Ajay Kwatra  (email : AjayKwatra@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) OR die("No script kiddies please!");

include_once('includes/ak_mp_product_gallery.php');
  
register_activation_hook(   __FILE__, 'AK_MPPG::ak_on_activation' );
register_deactivation_hook( __FILE__, 'AK_MPPG::ak_on_deactivation' );
register_uninstall_hook(    __FILE__, 'AK_MPPG::ak_on_uninstall' );


$objMP_ProductGallery = new AK_MPPG;
$objMP_ProductGallery->ak_initialize();

$plugin = plugin_basename(__FILE__); 
$prefix = is_network_admin() ? 'network_admin_' : '';
add_filter("{$prefix}plugin_action_links_$plugin", 'AK_MPPG::ak_action_links' );

