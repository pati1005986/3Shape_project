// Function to show the modal
function showModal() {
  const modal = new bootstrap.Modal(
    document.getElementById("notificationModal")
  );
  modal.show();
}

function clearLogs() {
  if (confirm("Are you sure you want to delete all records?")) {
    fetch("/3Shape_project/Model/clear_logs.php", {
      method: "POST",
    })
      .then((response) => response.text())
      .then((data) => {
        alert(data); // Mostrar respuesta del servidor
        location.reload(); // Recargar la página para ver cambios
      })
      .catch((error) => console.error("Error:", error));
  }
}
