function kontrolEt() {
    const email = document.getElementById("email").value;
    const sifre = document.getElementById("sifre").value;
  
    if (email === "" || sifre === "") {
      alert("Lütfen tüm alanları doldurun.");
      return false;
    }
    alert("Kayıt başarılı! Giriş yapabilirsiniz.");
    return true;
}
