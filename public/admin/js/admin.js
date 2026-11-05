/**
 * Grafika South - Admin Panel JavaScript
 * Handles status updates, search filters, and dashboard interactions.
 */

document.addEventListener('DOMContentLoaded', () => {
  console.log('Grafika South Admin Panel Loaded.');

  // 1. Order Status Select Handler Simulation
  const statusSelects = document.querySelectorAll('.admin-status-select');
  statusSelects.forEach(select => {
    select.addEventListener('change', (e) => {
      const selectedValue = e.target.value;
      const orderRow = e.target.closest('tr');
      const orderId = orderRow ? orderRow.querySelector('.order-id-link')?.textContent : 'Order';

      showAdminToast(`${orderId} status changed to "${selectedValue.toUpperCase()}"`);
    });
  });

  // Admin Toast Helper
  function showAdminToast(message) {
    let toast = document.getElementById('admin-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'admin-toast';
      toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        background-color: #0F172A;
        color: #FFFFFF;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        z-index: 9999;
        transition: opacity 0.3s ease, transform 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
        display: flex;
        align-items: center;
        gap: 10px;
      `;
      document.body.appendChild(toast);
    }

    toast.innerHTML = `<i class="fa-solid fa-circle-check" style="color:#10B981;"></i> ${message}`;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
    }, 3000);
  }

  // 2. Global Admin Modal Toggle Handlers
  window.openAdminModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('active');
    }
  };

  window.closeAdminModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('active');
    }
  };
});

