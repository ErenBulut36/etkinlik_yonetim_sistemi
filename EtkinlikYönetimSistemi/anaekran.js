let tumEtkinlikler = [];
let havaDurumuBilgisi = null;

function havaDurumuGetir(lat, lon) {
  const apiKey = "7ecbd54b5c31fafa67b6117adaa5e927";
  const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric&lang=tr`;
  fetch(url)
    .then(res => res.json())
    .then(data => {
      havaDurumuBilgisi = data;
      document.getElementById("hava-durumu").innerHTML = `
         <p><strong>${data.name}:</strong> ${data.main.temp}°C, ${data.weather[0].description}</p>
      `;
      etkinlikleriFiltrele();
    })
    .catch(err => {
      document.getElementById("hava-durumu").innerHTML = "<p>Hava durumu alınamadı.</p>";
      etkinlikleriFiltrele();
    });
}

function havaDurumuGetirKonumaGore() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      konum => {
        havaDurumuGetir(konum.coords.latitude, konum.coords.longitude);
      },
      () => {
        document.getElementById("hava-durumu").innerHTML = "<p>Konum alınamadı.</p>";
        etkinlikleriFiltrele();
      }
    );
  } else {
    document.getElementById("hava-durumu").innerHTML = "<p>Konum desteklenmiyor.</p>";
    etkinlikleriFiltrele();
  }
}

function etkinlikleriGetir() {
  fetch('events.json')
    .then(res => res.json())
    .then(data => {
      tumEtkinlikler = data.sort((a, b) => new Date(a.tarih) - new Date(b.tarih));
      etkinlikleriFiltrele();
    })
    .catch(() => {
      document.getElementById('etkinlikler').innerHTML = "<p>Etkinlikler yüklenemedi.</p>";
    });
}

function etkinlikleriFiltrele() {
  const secilenler = Array.from(document.querySelectorAll('.kategori-checkbox:checked')).map(cb => cb.value);
  const uygunlar = tumEtkinlikler.filter(e => secilenler.includes(e.kategori));
  const kutu = document.getElementById("etkinlikler");
  kutu.innerHTML = "";
  if (uygunlar.length === 0) {
    kutu.innerHTML = "<p>Uygun etkinlik bulunamadı.</p>";
    return;
  }
  const havaUygunMu = havaDurumuBilgisi ?
         havaEtkinligeUygunMu(havaDurumuBilgisi.weather[0].description.toLowerCase()) : true;
  uygunlar.forEach(etkinlik => {
    const div = document.createElement("div");
    div.className = "etkinlik-karti";
    div.innerHTML = `
      <h3>${etkinlik.ad}</h3>
      <p><strong>Tarih:</strong> ${etkinlik.tarih}</p>
      <p><strong>Yer:</strong> ${etkinlik.konum}</p>
      <p><strong>Kontenjan:</strong> ${etkinlik.kontenjan !== undefined ? etkinlik.kontenjan : 'Bilinmiyor'}</p>
      <p class="${havaUygunMu ? 'uygun' : 'uygun-degil'}">
         ${havaUygunMu ? '✅ Uygun Hava' : '❌ Uygun Değil'}
      </p>
      <button class="sepet-buton" data-etkinlik='${JSON.stringify(etkinlik)}'>
         Sepete Ekle
      </button>
    `;
    kutu.appendChild(div);
  });

  
  document.querySelectorAll('.sepet-buton').forEach(buton => {
    buton.onclick = function () {
      const etkinlik = JSON.parse(this.getAttribute('data-etkinlik'));
      
      fetch('sepet_backend.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'add',
          ad: etkinlik.ad,
          kategori: etkinlik.kategori,
          tarih: etkinlik.tarih,
          konum: etkinlik.konum,
          kontenjan: etkinlik.kontenjan
        })
      })
      .then(response => response.json())
      .then(data => {
         if(data.status === 'success'){
            this.classList.add('eklendi');
            this.textContent = '✔ Eklendi';
            updateCartCount();
         } else {
            alert('Sepete ekleme hatası: ' + data.message);
         }
      })
      .catch(err => console.error(err));
    };
  });
}

function havaEtkinligeUygunMu(durum) {
  const olumsuzKelimeler = ["yağmur", "fırtına", "kar", "sağanak", "sis", "şiddetli"];
  return !olumsuzKelimeler.some(kelime => durum.includes(kelime));
}

function updateCartCount() {
  fetch('sepet_backend.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ action: 'list' })
  })
  .then(response => response.json())
  .then(data => {
    if(data.status === 'success'){
      document.getElementById('sepet-sayaci').textContent = data.items.length;
    }
  })
  .catch(err => console.error(err));
}

function duyurulariGetir() {
    fetch('anaekran.php')
        .then(response => response.json())
        .then(data => {
            const duyuruListesi = document.getElementById("duyuru-listesi");
            duyuruListesi.innerHTML = "";

            if (data.duyurular.length === 0) {
                duyuruListesi.innerHTML = "<li>Henüz duyuru eklenmedi.</li>";
                return;
            }

            data.duyurular.forEach(duyuru => {
                const li = document.createElement("li");
                li.textContent = `${duyuru.tarih.split(" ")[0]} - ${duyuru.metin}`;
                duyuruListesi.appendChild(li);
            });
        })
        .catch(error => {
            console.error("Duyurular yüklenemedi:", error);
            document.getElementById("duyuru-listesi").innerHTML = "<li>Bağlantı hatası.</li>";
        });
}

document.addEventListener("DOMContentLoaded", () => {
  etkinlikleriGetir();
  havaDurumuGetirKonumaGore();
  document.querySelectorAll('.kategori-checkbox').forEach(cb => {
    cb.addEventListener('change', etkinlikleriFiltrele);
  });
  duyurulariGetir();
});
