<?php
require 'vendor/autoload.php';

/** load .env file */
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

require 'systems/database.php';
require 'systems/functions.php';
require 'systems/systems.php';

$db = new Cahkampung\Landadb(Db());

$db->select("wisata.*, kategori_wisata.icon")
    ->from("wisata")
    ->leftJoin("kategori_wisata", "kategori_wisata.id = wisata.kategori_wisata_id");

if (!empty($_GET['keyword'])) {
    $db->customWhere("
        wisata.nama like '%" . $_GET['keyword'] . "%' or
        wisata.informasi like '%" . $_GET['keyword'] . "%' or
        wisata.alamat like '%" . $_GET['keyword'] . "%'",
        "AND");
}

if (!empty($_GET['kategori'])) {
    $db->customWhere("kategori_wisata_id = '" . $_GET['kategori'] . "'", "AND");
}

$wisata = $db->findAll();

header("Content-type: text/xml");

echo '<markers>';
foreach ($wisata as $key => $value) {

    $harga1 = empty($value->harga_weekday) ? 'Gratis' : rp($value->harga_weekday);
    $harga2 = empty($value->harga_weekend) ? 'Gratis' : rp($value->harga_weekend);

    echo '<marker ';
    echo 'id="' . parseToXML($value->id) . '" ';
    echo 'alias="' . parseToXML(str_replace(" ", "-", $value->nama)) . '" ';
    echo 'name="' . parseToXML($value->nama) . '" ';
    echo 'address="' . parseToXML($value->alamat) . '" ';
    echo 'lat="' . parseToXML($value->lattitude) . '" ';
    echo 'lng="' . parseToXML($value->longitude) . '" ';
    echo 'img="' . parseToXML($value->foto) . '" ';
    echo 'jam_operasional="' . parseToXML($value->jam_operasional) . '" ';
    echo 'harga_weekday="' . parseToXML($harga1) . '" ';
    echo 'harga_weekend="' . parseToXML($harga2) . '" ';
    echo 'icon="' . parseToXML(getenv('ICON_BASE') . $value->icon) . '" ';
    echo 'type="R"';
    echo '/>';
}

echo '</markers>';
