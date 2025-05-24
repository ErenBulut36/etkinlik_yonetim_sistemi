document.addEventListener("DOMContentLoaded", function() {
  const deleteLinks = document.querySelectorAll("a.delete-event");
  deleteLinks.forEach(link => {
    link.addEventListener("click", function(e) {
      if (!confirm("Bu etkinliği silmek istediğinize emin misiniz?")) {
        e.preventDefault();
      }
    });
  });

  const toggleBtn = document.getElementById("toggle-event-form");
  const eventForm = document.getElementById("event-form");
  if (toggleBtn && eventForm) {
    eventForm.style.display = "none";
    toggleBtn.addEventListener("click", function() {
      eventForm.style.display = eventForm.style.display === "none" ? "block" : "none";
    });
  }
});
