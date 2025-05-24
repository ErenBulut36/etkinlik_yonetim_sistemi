<?php
session_start();
if (!isset($_SESSION['yonetici'])) {
    header("Location: admin_giris.php");
    exit;
}

$eventsFile = "events.json";
$data = file_get_contents($eventsFile);
$events = json_decode($data, true);

// Düzenlenecek etkinliğin indeksini GET parametresi ile alıyoruz
if (!isset($_GET['edit_event'])) {
    header("Location: admin.php");
    exit;
}
$index = intval($_GET['edit_event']);
if (!is_array($events) || !isset($events[$index])) {
    echo "Geçersiz veya bulunamayan etkinlik.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Formdan gelen değerleri alıyoruz
    $ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
    $kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : '';
    $kontenjan = isset($_POST['kontenjan']) ? intval($_POST['kontenjan']) : 0;
    
    // JSON'daki ilgili etkinliği güncelliyoruz (sadece ad, kategori ve kontenjan)
    $events[$index]['ad'] = $ad;
    $events[$index]['kategori'] = $kategori;
    $events[$index]['kontenjan'] = $kontenjan;
    
    // Güncellenmiş verileri JSON dosyasına kaydediyoruz
    file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
    
    header("Location: admin.php");
    exit;
}

$currentEvent = $events[$index];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Etkinlik Düzenle</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-container">
    <h1>Etkinlik Düzenle</h1>
    <form method="POST" action="edit_event.php?edit_event=<?= $index ?>">
        <label>
            Etkinlik Adı:
            <input type="text" name="ad" value="<?= htmlspecialchars($currentEvent['ad']) ?>" required>
        </label><br>
        <label>
            Kategori:
            <input type="text" name="kategori" value="<?= htmlspecialchars($currentEvent['kategori']) ?>" required>
        </label><br>
        <label>
            Kontenjan:
            <input type="number" name="kontenjan" value="<?= htmlspecialchars($currentEvent['kontenjan']) ?>" required>
        </label><br>
        <button type="submit">Güncelle</button>
    </form>
    <p><a href="admin.php">Admin Paneline Geri Dön</a></p>
</div>
</body>
</html>
