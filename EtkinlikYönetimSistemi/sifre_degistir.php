<?php
session_start();
if (!isset($_SESSION['sifre_degistirme_id'])) {
    header("Location: giris.html");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "etkinlik_sistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error){
    die("Bağlantı hatası: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $yeni_sifre = trim($_POST["yeni_sifre"]);
    $hashed_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
    $user_id = $_SESSION['sifre_degistirme_id'];

    $sql = "UPDATE kullanicilar SET sifre = ?, sifre_degistirme_zorunlu = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_sifre, $user_id);
    $stmt->execute();
    $stmt->close();

    // Kullanıcının e-posta bilgisini çekelim
    $sql = "SELECT email FROM kullanicilar WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_email);
    $stmt->fetch();
    $stmt->close();

    unset($_SESSION['sifre_degistirme_id']);
    $_SESSION['user_email'] = $user_email;
    header("Location: anaekran.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifre Değiştir</title>
    <style>
       body {
         background: linear-gradient(to bottom, #f5f7fa, #c3cfe2);
         font-family: Arial, sans-serif;
         display: flex;
         align-items: center;
         justify-content: center;
         height: 100vh;
         margin: 0;
       }
       .container {
         background: white;
         padding: 30px;
         border-radius: 10px;
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
         width: 300px;
         text-align: center;
       }
       h2 {
          color: #005faa;
          margin-bottom: 20px;
       }
       form {
          display: flex;
          flex-direction: column;
       }
       input[type="password"] {
          padding: 10px;
          border: 1px solid #ccc;
          border-radius: 5px;
          margin-bottom: 20px;
          font-size: 16px;
       }
       button {
          padding: 10px;
          background: #005faa;
          color: white;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          font-size: 16px;
       }
       button:hover {
          background: #003f7f;
       }
    </style>
</head>
<body>
  <div class="container">
    <h2>Şifrenizi Güncelleyin</h2>
    <form method="POST" action="sifre_degistir.php">
      <input type="password" name="yeni_sifre" placeholder="Yeni Şifre" required>
      <button type="submit">Şifreyi Güncelle</button>
    </form>
  </div>
</body>
</html>
