<?php
/*
Plugin Name: Medyum Burak Namaz Vakitleri
Plugin URI:  https://wordpress.org/plugins/medyum-burak-namaz-vakitleri/
Version: 1.0
Author: Medyum Burak
Author URI: https://www.medyumburak.com/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Contributors: medyumburak
Tags: namaz vakitleri
Description: Medyum Burak Namaz Vakitleri eklentisi ile dünya genelinde her il ve ilçede namaz vakitlerini ister sidebar üzerinde widget olarak, isterseniz sayfa içinde shortcode kullanarak sitenizde yayınlayabilirsiniz.

Medyum Burak Namaz Vakitleri is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Medyum Burak Namaz Vakitleri is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Medyum Burak Namaz Vakitleri If not, see {License URI}.
*/

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
      $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
      $str_end = "";
      if ($lower_str_end) {
        $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
      }
      else {
        $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
      }
      $str = $first_letter . $str_end;
      return $str;
    }
  }

require(dirname(__FILE__).'/inc/namaz-vakitleri-core.php');
if (class_exists('NamazVakitleriCore')) {
    global $namazVakitleriCore;
    $namazVakitleriCore = new NamazVakitleriCore(plugin_dir_path(__FILE__), plugin_dir_url(__FILE__));
    $namazVakitleriCore->load();
    $namazVakitleriCore->initAssets();
    $GLOBALS["namazVakitleriCore"] = $namazVakitleriCore;
}