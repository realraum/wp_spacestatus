<?php
/*  Copyright (c) 2014  Michael Wendland
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License, version 2, as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *  Authors:
 *      Michael Wendland <michael@michiwend.com>
 */

/*
 *  Plugin Name: WP SpaceStatus
 *  Plugin URI: https://github.com/michiwend/wp_spacestatus
 *  Description: a WordPress plugin that displays your space status.
 *  Version: 1.0.0
 *  Author: Michael Wendland
 *  Author URI: http://blog.michiwend.com
 *  License: GPL2
 */

include('settings.php');


function get_spacestatus() {

    $options = get_option('wp_spacestatus_options');

    // FIXME set timeout, useragent, etc.
    $api_response = wp_remote_get( $options['api_url_string'] );

    $rsp_code = wp_remote_retrieve_response_code( &$api_response );
    $rsp_msg  = wp_remote_retrieve_response_message( &$api_response );
    $rsp_body = wp_remote_retrieve_body( &$api_response );

    if( $rsp_code != 200) {
        return new WP_ERROR(
            'api_call_failed',
            "Failed calling ".$options['api_url_string'],
            $rsp_msg );
    }

    return json_decode( $rsp_body )->open;
}


function icon_builder($status, $size, $class, $id) {

    // TODO get icons from options.
    $icon['open']['large']   = "open_large.png";
    $icon['open']['small']   = "open_small.png";
    $icon['closed']['large'] = "closed_large.png";
    $icon['closed']['small'] = "closed_small.png";

    $icon_baseurl = plugins_url()."/wp_spacestatus/icons";

    return "<img id=\"$id\" class=\"$class\" src=\"".$icon_baseurl."/".$icon[$status][$size]."\" alt=\"Space status $status icon\" />";
}


function spacestatus_shortcode( $atts ) {

    $options = get_option('wp_spacestatus_options');

    $a = shortcode_atts( array(
        'type'  => 'icon_large',
        'class' => '',
        'id'    => '',
    ), $atts );

    $status = get_spacestatus();

    if( is_wp_error( $status ) ) {
        // FIXME return the unknown icon
        return $status->get_error_message();
    }

    switch( $a['type'] ) {
    case 'icon_large':
        $out = icon_builder(
            $status ? 'open' : 'closed',
            'large',
            $a['class'],
            $a['id']);
        break;
    case 'icon_small':
        $out = icon_builder(
            $status ? 'open' : 'closed',
            'small',
            $a['class'],
            $a['id']);
        break;
    case 'text':
        $out = $options[$status ? 'textstatus_open_string' : 'textstatus_closed_string'];
        break;
    default:
        $out = "undefined shortcode param";
        break;
    }

    return $out;
}

add_shortcode('space_status', 'spacestatus_shortcode');

?>
