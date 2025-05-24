<?php
session_start();
if (!isset($_SESSION['yonetici'])) {
    header("Location: admin_giris.php");
    exit;
}

// MySQL bağlantısı
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "etkinlik_sistemi";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error){
    die("Bağlantı hatası: " . $conn->connect_error);
}

/* KULLANICI ONAY İŞLEMLERİ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_action'])) {
    $user_id = intval($_POST['user_id']);
    if ($_POST['user_action'] === "approve") {
        // Onaylananın yanında şifre değiştirme zorunluluğu da aktif olsun.
        $sql = "UPDATE kullanicilar SET onay = 1, sifre_degistirme_zorunlu = 1 WHERE id = ?";
    } elseif ($_POST['user_action'] === "reject") {
        $sql = "DELETE FROM kullanicilar WHERE id = ?";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

/* ETKİNLIK EKLEME İŞLEMLERİ (events.json) */
$eventsFile = "events.json";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_action']) && $_POST['event_action'] === "add") {
    $ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
    $kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : '';
    $tarih = isset($_POST['tarih']) ? trim($_POST['tarih']) : '';
    $konum = isset($_POST['konum']) ? trim($_POST['konum']) : '';
    $kontenjan = isset($_POST['kontenjan']) ? intval($_POST['kontenjan']) : 0;
    if ($ad && $kategori && $tarih && $konum) {
        $data = file_get_contents($eventsFile);
        $events = json_decode($data, true);
        if (!is_array($events)) {
            $events = [];
        }
        $newEvent = array(
            "ad" => $ad,
            "kategori" => $kategori,
            "tarih" => $tarih,
            "konum" => $konum,
            "kontenjan" => $kontenjan
        );
        $events[] = $newEvent;
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
    }
    header("Location: admin.php");
    exit;
}

/* ETKİNLIK SİLME İŞLEMLERİ (GET ile) */
if (isset($_GET['delete_event'])) {
    $index = intval($_GET['delete_event']);
    $data = file_get_contents($eventsFile);
    $events = json_decode($data, true);
    if (is_array($events) && array_key_exists($index, $events)) {
        array_splice($events, $index, 1);
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
    }
    header("Location: admin.php");
    exit;
}

/* DUYURU SİLME İŞLEMLERİ (GET ile) */
if (isset($_GET['delete_duyuru'])) {
    $duyuru_id = intval($_GET['delete_duyuru']);
    $sql = "DELETE FROM duyurular WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $duyuru_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

/* DUYURU EKLEME İŞLEMLERİ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['duyuru_metni'])) {
    $duyuruMetni = trim($_POST['duyuru_metni']);
    if (!empty($duyuruMetni)) {
        $sql = "INSERT INTO duyurular (metin) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $duyuruMetni);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin.php");
    exit;
}

/* DUYURULARI ÇEK */
$sql_duyurular = "SELECT id, metin, tarih FROM duyurular ORDER BY tarih DESC LIMIT 5";
$result_duyurular = $conn->query($sql_duyurular);
$duyurular = [];
if ($result_duyurular && $result_duyurular->num_rows > 0) {
    while ($row = $result_duyurular->fetch_assoc()) {
        $duyurular[] = $row;
    }
}

// Onay bekleyen kullanıcıları çek
$sql_users = "SELECT id, email FROM kullanicilar WHERE onay = 0";
$result_users = $conn->query($sql_users);

// Etkinlik listesini oku
$events = json_decode(file_get_contents($eventsFile), true);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-container">
    <h1>Admin Paneli</h1>
    
    <!-- Kullanıcı Onay Bölümü -->
    <section class="section">
      <h2>Onay Bekleyen Kullanıcılar</h2>
      <?php if ($result_users && $result_users->num_rows > 0): ?>
        <table>
          <tr>
            <th>ID</th>
            <th>E-posta</th>
            <th>İşlemler</th>
          </tr>
          <?php while ($row = $result_users->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <form method="POST" action="admin.php" style="display:inline;">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                <button type="submit" name="user_action" value="approve">Onayla</button>
              </form>
              <form method="POST" action="admin.php" style="display:inline;">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                <button type="submit" name="user_action" value="reject">Reddet</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </table>
      <?php else: ?>
        <p>Onay bekleyen kullanıcı bulunmamaktadır.</p>
      <?php endif; ?>
    </section>
    
    <!-- Etkinlik Yönetimi Bölümü -->
    <section class="section">
      <h2>Etkinlik Yönetimi</h2>
      <h3>Yeni Etkinlik Ekle</h3>
      <form id="event-form" method="POST" action="admin.php">
        <input type="hidden" name="event_action" value="add">
        <label>Etkinlik Adı: <input type="text" name="ad" required></label><br>
        <label>Kategori: <input type="text" name="kategori" required></label><br>
        <label>Tarih: <input type="date" name="tarih" required></label><br>
        <label>Konum: <input type="text" name="konum" required></label><br>
        <label>Kontenjan: <input type="number" name="kontenjan" required></label><br>
        <button type="submit">Ekle</button>
      </form>
      
      <h3>Var Olan Etkinlikler</h3>
      <?php if (is_array($events) && count($events) > 0): ?>
        <table>
          <tr>
            <th>#</th>
            <th>Ad</th>
            <th>Kategori</th>
            <th>Tarih</th>
            <th>Konum</th>
            <th>Kontenjan</th>
            <th>Aksiyon</th>
          </tr>
          <?php foreach ($events as $index => $event): ?>
          <tr>
            <td><?= $index; ?></td>
            <td><?= htmlspecialchars($event['ad']); ?></td>
            <td><?= htmlspecialchars($event['kategori']); ?></td>
            <td><?= htmlspecialchars($event['tarih']); ?></td>
            <td><?= htmlspecialchars($event['konum']); ?></td>
            <td><?= isset($event['kontenjan']) ? $event['kontenjan'] : '0'; ?></td>
            <td>
              <a href="edit_event.php?edit_event=<?= $index; ?>" class="edit-event">Düzenle</a>
              &nbsp;
              <a href="admin.php?delete_event=<?= $index; ?>" class="delete-event" onclick="return confirm('Bu etkinliği silmek istediğinize emin misiniz?');">Sil</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p>Henüz etkinlik eklenmemiş.</p>
      <?php endif; ?>
    </section>
    
    <!-- Duyuru Ekleme Bölümü -->
    <section class="section">
        <h2>Duyuru Ekle</h2>
        <form method="POST" action="admin.php">
            <textarea name="duyuru_metni" rows="4" required placeholder="Duyuru metnini buraya yazın..."></textarea><br>
            <button type="submit">Duyuru Ekle</button>
        </form>
    </section>
    
    <!-- Mevcut Duyurular Bölümü -->
    <section class="section">
        <h2>Mevcut Duyurular</h2>
        <ul>
            <?php if(count($duyurular) > 0): ?>
                <?php foreach ($duyurular as $duyuru): ?>
                    <li>
                      <strong><?= date("d-m-Y", strtotime($duyuru['tarih'])); ?>:</strong>
                      <?= htmlspecialchars($duyuru['metin']); ?>
                      &nbsp; <a href="admin.php?delete_duyuru=<?= $duyuru['id']; ?>" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');">Sil</a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Henüz duyuru eklenmemiş.</li>
            <?php endif; ?>
        </ul>
    </section>
    <p>Giriş ekranına geri dön <a href="giris.html"><b>Giriş Ekranı</b></a></p>
  </div>
  <script src="admin.js"></script>
</body>
</html>
<?php
$conn->close();
?>
