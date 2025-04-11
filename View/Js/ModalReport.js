// Function to show the modal with animation
function showModal() {
  const modalElement = document.getElementById("notificationModal");
  const modalBackdrop = document.createElement('div');
  
  // Configure backdrop
  modalBackdrop.classList.add('modal-backdrop', 'fade');
  document.body.appendChild(modalBackdrop);
  
  // Add animation classes
  modalElement.classList.add('fade');
  modalElement.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
  modalElement.style.transform = 'scale(0.95) translateY(-30px)';
  modalElement.style.opacity = '0';
  
  const modal = new bootstrap.Modal(modalElement, {
    backdrop: false // We'll handle the backdrop manually
  });
  
  // Add event listener for when modal starts showing
  modalElement.addEventListener('show.bs.modal', function() {
    requestAnimationFrame(() => {
      modalBackdrop.style.opacity = '0.5';
      this.style.transform = 'scale(1) translateY(0)';
      this.style.opacity = '1';
    });
  });
  
  // Add event listener for when modal is closing
  modalElement.addEventListener('hide.bs.modal', function() {
    this.style.transform = 'scale(0.95) translateY(-30px)';
    this.style.opacity = '0';
    modalBackdrop.style.opacity = '0';
    
    // Remove backdrop after animation
    setTimeout(() => {
      modalBackdrop.remove();
    }, 400);
  });
  
  modal.show();
}

// Function to update notification badge count
function updateNotificationBadge(count) {
  const badge = document.querySelector('.notification-badge');
  if (badge) {
    badge.textContent = count;
    badge.style.display = count > 0 ? 'block' : 'none';
    
    // Add a pulse animation if there are new notifications
    if (count > 0) {
      const bellButton = document.querySelector('.bell-button');
      if (bellButton && !bellButton.classList.contains('bell-hover')) {
        bellButton.classList.add('bell-hover');
        setTimeout(() => {
          bellButton.classList.remove('bell-hover');
        }, 1000);
      }
    }
  }
}

// Initialize bell container functionality
document.addEventListener('DOMContentLoaded', function() {
  const bellButton = document.querySelector('.bell-button');
  const bellContainer = document.querySelector('.bell-container');
  
  if (bellButton) {
    bellButton.addEventListener('click', function(e) {
      e.preventDefault();
      showModal();
      
      // Reset counter when viewing notifications
      updateNotificationBadge(0);
      localStorage.setItem('lastNotificationTimestamp', Date.now());
    });
  }
  
  // Add hover effects to bell container
  if (bellContainer) {
    bellContainer.addEventListener('mouseenter', function() {
      this.querySelector('.bell-button').classList.add('bell-hover');
    });
    
    bellContainer.addEventListener('mouseleave', function() {
      this.querySelector('.bell-button').classList.remove('bell-hover');
    });
  }
  
  // Fetch initial notification count
  fetchNotificationCount();
  
  // Set up periodic checking for new notifications (every 30 seconds)
  setInterval(fetchNotificationCount, 30000);
});

// Function to fetch notification count from server
function fetchNotificationCount() {
  // Get the last time we checked for notifications
  const lastCheck = localStorage.getItem('lastNotificationTimestamp') || 0;
  
  // Call the notification count endpoint
  fetch('./Model/get_notification_count.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ lastCheck: lastCheck })
  })
    .then(response => response.json())
    .then(data => {
      updateNotificationBadge(data.count);
      
      // If there are new notifications, play a subtle sound (optional)
      if (data.count > 0 && lastCheck > 0) {
        notifyUser();
      }
    })
    .catch(error => {
      console.error('Error fetching notification count:', error);
    });
}

// Function to notify user of new notifications (optional)
function notifyUser() {
  const bellButton = document.querySelector('.bell-button');
  if (bellButton) {
    bellButton.classList.add('bell-hover');
    setTimeout(() => {
      bellButton.classList.remove('bell-hover');
    }, 1000);
    
    // You could also play a sound here if desired
    // const notificationSound = new Audio('path/to/notification-sound.mp3');
    // notificationSound.play();
  }
}

function clearLogs() {
  if (confirm("Are you sure you want to delete all records?")) {
    fetch("./Model/clear_logs.php", {
      method: "POST",
    })
      .then((response) => response.text())
      .then((data) => {
        alert(data); // Show server response
        
        // Reset notification counter after clearing logs
        updateNotificationBadge(0);
        localStorage.setItem('lastNotificationTimestamp', Date.now());
        
        location.reload(); // Reload the page to see changes
      })
      .catch((error) => console.error("Error:", error));
  }
}