<?php
session_start(); // Oturumu başlat

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "etkinlik_sistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$email = $_POST['email'];
$sifre = $_POST['sifre'];

// Prepared statementsorgu kısmını güncelliyoruz sifre_degistirme_zorunlu sütunu da çekiliyor
$sql = "SELECT id, email, sifre, onay, sifre_degistirme_zorunlu FROM kullanicilar WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $db_email, $db_sifre, $onay, $sifre_degistirme_zorunlu);
    $stmt->fetch();
  
    if (password_verify($sifre, $db_sifre)) {
        if ($onay == 1) {
            if ($sifre_degistirme_zorunlu == 1) {
                $_SESSION['sifre_degistirme_id'] = $id;
                header("Location: sifre_degistir.php");
                exit;
            } else {
                $_SESSION['user_email'] = $db_email;
                echo "<script>
                        alert('Giriş başarılı! Ana ekrana yönlendiriliyorsunuz.');
                        window.location.href = 'anaekran.html';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Hesabınız henüz yönetici tarafından onaylanmadı. Lütfen bekleyin.');
                    window.location.href = 'giris.html';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Şifre yanlış! Lütfen tekrar deneyin.');
                window.location.href = 'giris.html';
              </script>";
    }
} else {
    echo "<script>
            alert('Bu e-posta ile kayıtlı bir kullanıcı bulunamadı!');
            window.location.href = 'giris.html';
          </script>";
}

$stmt->close();
$conn->close();
?>
