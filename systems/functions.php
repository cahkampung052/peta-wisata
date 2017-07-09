<?php

function validate($data, $validasi, $custom = [])
{
    if (!empty($custom)) {
        $validasiData = array_merge($validasi, $custom);
    } else {
        $validasiData = $validasi;
    }

    $validate = GUMP::is_valid($data, $validasiData);

    if ($validate === true) {
        return true;
    } else {
        return $validate;
    }
}

function cuplikan($str, $panjang)
{
    return substr(strip_tags($str), 0, $panjang) . '...';
}

function get_first_image($html)
{
    if (empty($class)) {
        $class = 'img img-responsive';
    } else {
        $class = $class;
    }

    $post_html = str_get_html($html);

    $first_img = $post_html->find('img', 0);

    if ($first_img !== null) {

        $first_img->src = $first_img->src;

        return $first_img->src;
    } else {
        return site_url() . "public/images/ noimage.jpg";
    }
}

function parseToXML($htmlStr)
{
    $xmlStr = str_replace('<', '&lt;', $htmlStr);
    $xmlStr = str_replace('>', '&gt;', $xmlStr);
    $xmlStr = str_replace('"', '&quot;', $xmlStr);
    $xmlStr = str_replace("'", '&#39;', $xmlStr);
    $xmlStr = str_replace("&", '&amp;', $xmlStr);
    return $xmlStr;
}
