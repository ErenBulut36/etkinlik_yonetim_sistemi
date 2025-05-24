<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$eventsFile = "events.json";
$action = isset($_POST['action']) ? $_POST['action'] : '';
$ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';

if ($action === 'reduce' && $ad) {
    // Dosyayı kilitlemek için açıyoruz
    $fp = fopen($eventsFile, "c+");
    if (!$fp) {
        echo json_encode(["status" => "error", "message" => "Dosya açılamadı."]);
        exit;
    }
    
    // Kilit al (LOCK_EX: exclusive lock)
    if (flock($fp, LOCK_EX)) {
        // Dosyanın mevcut içeriğini oku
        $filesize = filesize($eventsFile);
        $filesize = ($filesize > 0) ? $filesize : 0;
        $contents = ($filesize > 0) ? fread($fp, $filesize) : '';
        $events = json_decode($contents, true);
        if (!is_array($events)) {
            $events = [];
        }
        $found = false;
        foreach ($events as &$event) {
            if ($event['ad'] === $ad) {
                $found = true;
                if (isset($event['kontenjan']) && $event['kontenjan'] > 0) {
                    // Eğer kontenjan 0'dan büyükse azalt, ama negatif olmamasına dikkat edelim
                    $event['kontenjan'] = max(0, $event['kontenjan'] - 1);
                } else {
                    // Kilidi bırakmadan hata döndürelim
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    echo json_encode(["status" => "error", "message" => "Bu etkinlik için kontenjan kalmadı."]);
                    exit;
                }
                break;
            }
        }
        if (!$found) {
            flock($fp, LOCK_UN);
            fclose($fp);
            echo json_encode(["status" => "error", "message" => "Etkinlik bulunamadı."]);
            exit;
        }
        // Dosyanın içeriğini güncellemeden önce sıfırla
        ftruncate($fp, 0);
        rewind($fp);
        $result = fwrite($fp, json_encode($events, JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        if ($result !== false) {
            echo json_encode(["status" => "success", "message" => "Kontenjan güncellendi."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Veriler kaydedilemedi."]);
        }
    } else {
        fclose($fp);
        echo json_encode(["status" => "error", "message" => "Dosya kilidi alınamadı."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Geçersiz istek."]);
}
?>
