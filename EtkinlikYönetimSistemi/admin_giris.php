<?php
session_start();
if (isset($_SESSION['yonetici'])) {
    header("Location: admin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbname = "etkinlik_sistemi";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
    if ($conn->connect_error) {
        die("Bağlantı hatası: " . $conn->connect_error);
    }

    $email = trim($_POST["email"]);
    $sifre = trim($_POST["sifre"]);

    $sql = "SELECT id, email, sifre FROM yoneticiler WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_email, $db_sifre);
        $stmt->fetch();
        if (password_verify($sifre, $db_sifre)) {
            $_SESSION['yonetici'] = $db_email;
            header("Location: admin.php");
            exit;
        } else {
            echo "<script>alert('Şifre yanlış!'); window.location.href = 'admin_giris.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Yönetici bulunamadı!'); window.location.href = 'admin_giris.php';</script>";
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Admin Giriş</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-login-container">
    <h2>Admin Panel Girişi</h2>
    <form action="admin_giris.php" method="POST">
      <input type="email" name="email" placeholder="Yönetici E-posta" required>
      <input type="password" name="sifre" placeholder="Şifre" required>
      <button type="submit">Giriş Yap</button>
      <p>Giriş ekranına geri dön <a href="giris.html"><b>Giriş Ekranı</b></a></p>
    </form>
  </div>
  <script src="admin.js"></script>
</body>
</html>
