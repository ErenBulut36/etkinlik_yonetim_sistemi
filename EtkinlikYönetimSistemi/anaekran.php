<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// Veritabanı ayarları:
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "etkinlik_sistemi";

// Bağlantıyı oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die(json_encode(["hata" => "Veritabanı bağlantı hatası: " . $conn->connect_error]));
}

// Etkinlikleri çek (sütun isimleri; "isim as ad", "kategori", "tarih", "konum")
$sql = "SELECT isim as ad, kategori, tarih, konum FROM etkinlikler ORDER BY tarih ASC";
$result = $conn->query($sql);

$events = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $events[] = $row;
    }
}

// Duyuruları çek
$sql_duyurular = "SELECT metin, tarih FROM duyurular ORDER BY tarih DESC LIMIT 5";
$result_duyurular = $conn->query($sql_duyurular);
$duyurular = [];
if ($result_duyurular && $result_duyurular->num_rows > 0) {
    while($row = $result_duyurular->fetch_assoc()){
        $duyurular[] = $row;
    }
}

// JSON çıktısı hem etkinlikler hem de duyurular
echo json_encode(["events" => $events, "duyurular" => $duyurular], JSON_PRETTY_PRINT);
$conn->close();
?>
