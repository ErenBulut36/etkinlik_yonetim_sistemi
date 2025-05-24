<?php
session_start();
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Oturumda sepet yoksa oluştur
if (!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = [];
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'add') {
    // Sepete ekleme; beklenen alanlar: ad, kategori, tarih, konum, kontenjan
    $item = [
       'id' => time() . rand(1000,9999), // benzersiz id oluşturuyoruz
       'ad' => isset($_POST['ad']) ? $_POST['ad'] : '',
       'kategori' => isset($_POST['kategori']) ? $_POST['kategori'] : '',
       'tarih' => isset($_POST['tarih']) ? $_POST['tarih'] : '',
       'konum' => isset($_POST['konum']) ? $_POST['konum'] : '',
       'kontenjan' => isset($_POST['kontenjan']) ? intval($_POST['kontenjan']) : 0
    ];
    // Aynı etkinlik zaten eklenmişse eklemiyoruz
    $exists = false;
    foreach ($_SESSION['sepet'] as $existing) {
        if ($existing['ad'] == $item['ad']) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $_SESSION['sepet'][] = $item;
        echo json_encode(["status" => "success", "message" => "Ürün sepete eklendi.", "item" => $item]);
    } else {
        echo json_encode(["status" => "error", "message" => "Bu etkinlik zaten sepette"]);
    }
} elseif ($action == 'list') {
    echo json_encode(["status" => "success", "items" => $_SESSION['sepet']]);
} elseif ($action == 'remove') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $found = false;
    foreach ($_SESSION['sepet'] as $index => $item) {
       if ($item['id'] == $id) {
            unset($_SESSION['sepet'][$index]);
            $found = true;
            break;
       }
    }
    if ($found) {
       $_SESSION['sepet'] = array_values($_SESSION['sepet']);
       echo json_encode(["status" => "success", "message" => "Ürün sepetten çıkarıldı."]);
    } else {
       echo json_encode(["status" => "error", "message" => "Ürün bulunamadı."]);
    }
} elseif ($action == 'clear') {
    $_SESSION['sepet'] = [];
    echo json_encode(["status" => "success", "message" => "Sepet temizlendi."]);
} else {
    echo json_encode(["status" => "error", "message" => "Geçersiz istek."]);
}
?>
