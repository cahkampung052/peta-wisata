<?php
require 'vendor/autoload.php';

/** load .env file */
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

require 'systems/database.php';
require 'systems/functions.php';

$db = new Cahkampung\Landadb(Db());

$db->select("wisata.*, kategori_wisata.icon")
    ->from("wisata")
    ->leftJoin("kategori_wisata", "kategori_wisata.id = wisata.kategori_wisata_id");

$wisata = $db->findAll();

header("Content-type: text/xml");

echo '<markers>';

foreach ($wisata as $key => $value) {
    echo '<marker ';
    echo 'name="' . parseToXML($value->nama) . '" ';
    echo 'address="' . parseToXML($value->alamat) . '" ';
    echo 'lat="' . parseToXML($value->lattitude) . '" ';
    echo 'lng="' . parseToXML($value->longitude) . '" ';
    echo 'icon="' . parseToXML(getenv('ICON_BASE') . $value->icon) . '" ';
    echo 'type="R"';
    echo '/>';
}

echo '</markers>';
