<?php
session_start();

// Veritabanı bağlantısı ayarları
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "etkinlik_sistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Formdan gelen veriler
$email = trim($_POST["email"]);
$sifre = password_hash(trim($_POST["sifre"]), PASSWORD_DEFAULT);

// Kullanıcının zaten kayıtlı olup olmadığını kontrol et
$sql_check = "SELECT id FROM kullanicilar WHERE email = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // Kullanıcı zaten kayıtlı, giriş sayfasına yönlendir
    echo "<script>
            alert('Zaten kayıt olmuşsun! Giriş yapabilirsiniz.');
            window.location.href = 'giris.html';
          </script>";
    exit;
}

$stmt_check->close();

// Kullanıcıyı veritabanına ekle (onay durumu başlangıçta 0 olarak)
$sql_insert = "INSERT INTO kullanicilar (email, sifre, onay) VALUES (?, ?, 0)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("ss", $email, $sifre);

if ($stmt_insert->execute()) {
    echo "<script>
            alert('Kayıt başarılı! Yönetici onayını bekleyiniz.');
            window.location.href = 'giris.html';
          </script>";
} else {
    echo "<script>
            alert('Kayıt sırasında hata oluştu! Lütfen tekrar deneyin.');
            window.location.href = 'kayit.html';
          </script>";
}

$stmt_insert->close();
$conn->close();
?>
