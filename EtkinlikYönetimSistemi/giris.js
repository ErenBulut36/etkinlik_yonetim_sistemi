function girisKontrol() {
    const email = document.getElementById("giris-email").value;
    const sifre = document.getElementById("giris-sifre").value;
  
    if (email === "" || sifre === "") {
      alert("Lütfen tüm alanları doldurun.");
      return false;
    }

    return true;
}
