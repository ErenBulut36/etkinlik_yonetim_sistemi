document.addEventListener("DOMContentLoaded", function() {
  let sepet = [];
  const sepetListesi = document.getElementById('sepet-listesi');
  const toplamFiyatElement = document.getElementById('toplam-fiyat');
  const biletTuruSelect = document.getElementById('bilet-turu-select');
  const odemeButon = document.getElementById('odeme-buton');

  const biletFiyatlari = {
    normal: 50,
    ogrenci: 30,
    vip: 100
  };

  // Backend'den sepeti listeleme
  function fetchSepet() {
    return fetch('sepet_backend.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'list' })
    })
    .then(response => response.json())
    .then(data => {
      if(data.status === 'success'){
         return data.items;
      } else {
         console.error('Sepet listeleme hatası:', data.message);
         return [];
      }
    });
  }

  function sepetiGoster() {
    if (sepet.length === 0) {
      sepetListesi.innerHTML = '<p>Sepetiniz boş</p>';
      fiyatHesapla();
      return;
    }
    sepetListesi.innerHTML = '';
    sepet.forEach(item => {
      const etkinlikItem = document.createElement('div');
      etkinlikItem.className = 'etkinlik-item';
      etkinlikItem.innerHTML = `
        <h3>${item.ad}</h3>
        <p><strong>Tarih:</strong> ${item.tarih}</p>
        <p><strong>Yer:</strong> ${item.konum}</p>
        <p><strong>Kalan Kontenjan:</strong> ${item.kontenjan !== undefined ? item.kontenjan : 'Bilinmiyor'}</p>
        <button class="remove-btn" data-id="${item.id}">Sil</button>
      `;
      sepetListesi.appendChild(etkinlikItem);
    });
    fiyatHesapla();
  }

  function fiyatHesapla() {
    const biletTuru = biletTuruSelect.value;
    const toplamFiyat = sepet.length * biletFiyatlari[biletTuru];
    toplamFiyatElement.textContent = `Toplam: ${toplamFiyat} TL`;
  }

  // Sepetten öğe silme
  function removeItem(id) {
    fetch('sepet_backend.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'remove', id: id })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        fetchSepet().then(items => {
          sepet = items;
          sepetiGoster();
        });
      } else {
        alert('Silme hatası: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Remove error:', error);
    });
  }

  // Sepeti temizleme
  function temizleSepet() {
    return fetch('sepet_backend.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'clear' })
    })
    .then(response => response.json())
    .then(data => {
      if(data.status === 'success'){
         sepet = [];
         sepetiGoster();
      } else {
         console.error('Sepet temizleme hatası:', data.message);
      }
    });
  }

  // Gerçek kontenjan güncelleme her sepet öğesi için guncelle_kontenjan.php çağrılır
  function gercekKontenjanAzalt(etkinlikler) {
  let promises = etkinlikler.map(event => {
    return fetch('guncelle_kontenjan.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'reduce', ad: event.ad })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status !== 'success') {
        console.error(`Kontenjan güncellemesi başarısız: ${event.ad} → ${data.message}`);
        throw new Error(`Kontenjan güncellemesi başarısız: ${event.ad}`);
      }
    })
    .catch(error => {
      console.error(`Kontenjan güncelleme hatası: ${error.message}`);
      throw error;
    });
  });

  return Promise.all(promises)
    .then(() => console.log("Tüm kontenjan güncellemeleri başarıyla tamamlandı"))
    .catch(error => {
      console.error("Bazı kontenjan güncellemeleri başarısız oldu, işlem iptal edildi!");
      throw error;
    });
}


  odemeButon.addEventListener('click', function() {
    if (sepet.length === 0) {
      alert('Sepetiniz boş!');
      return;
    }
    const odemeYontemi = document.getElementById('odeme-yontemi-select').value;
    const biletTuru = biletTuruSelect.value;
    const toplamFiyat = sepet.length * biletFiyatlari[biletTuru];

    gercekKontenjanAzalt(sepet)
      .then(() => {
        alert(`Ödeme başarılı!\nÖdeme Yöntemi: ${odemeYontemi}\nToplam: ${toplamFiyat} TL`);
        temizleSepet().then(() => {
          window.location.href = 'anaekran.html';
        });
      })
      .catch(error => {
        alert(`Hata: ${error.message}`);
      });
  });

  // Sepetteki etkinliği kaldırır
  sepetListesi.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('remove-btn')) {
      const itemId = e.target.getAttribute('data-id');
      removeItem(itemId);
    }
  });

  biletTuruSelect.addEventListener('change', fiyatHesapla);

  // Sayfa yüklendiğinde backend'den sepeti getir ve göster
  fetchSepet().then(items => {
    sepet = items;
    sepetiGoster();
  });
});
