/**
 * Grafika South - Customer-Facing Prototype JavaScript
 * Handles tab switching, search suggestions demo, cart counter badge, and responsive behavior.
 */

document.addEventListener('DOMContentLoaded', () => {
  console.log('Grafika South Prototype Loaded.');

  // 1. Featured Products Category Tabs Filter
  const tabButtons = document.querySelectorAll('.category-tabs .tab-btn');
  const productCards = document.querySelectorAll('.featured-products-grid .product-card');

  if (tabButtons.length > 0 && productCards.length > 0) {
    tabButtons.forEach(button => {
      button.addEventListener('click', () => {
        // Remove active class from all tabs
        tabButtons.forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked tab
        button.classList.add('active');

        const categoryFilter = button.getAttribute('data-category');

        productCards.forEach(card => {
          const cardCategory = card.getAttribute('data-category');

          if (categoryFilter === 'all' || cardCategory === categoryFilter) {
            card.style.display = 'flex';
            card.style.animation = 'fadeIn 0.4s ease';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  // 2. Add to Cart Toast Simulation
  const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
  const cartBadge = document.querySelector('.cart-count');
  let currentCartCount = parseInt(cartBadge ? cartBadge.textContent : '0', 10);

  addToCartBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      currentCartCount++;
      if (cartBadge) {
        cartBadge.textContent = currentCartCount;
        
        // Bounce animation on cart badge
        cartBadge.style.transform = 'scale(1.3)';
        setTimeout(() => {
          cartBadge.style.transform = 'scale(1)';
        }, 200);
      }

      // Simple notification toast
      showToast('Item added to cart!');
    });
  });

  // Simple Toast Helper
  function showToast(message) {
    let toast = document.getElementById('prototype-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'prototype-toast';
      toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        background-color: #1E2022;
        color: #FFFFFF;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 9999;
        transition: opacity 0.3s ease, transform 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
      `;
      document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
    }, 2500);
  }

  // 3. Product Detail Dynamic Price Calculation & Interactive Controls
  const qtyInput = document.getElementById('calc-qty-input');
  const qtyMinusBtn = document.getElementById('qty-minus');
  const qtyPlusBtn = document.getElementById('qty-plus');
  const sizeSelect = document.getElementById('calc-size-select');
  const totalPriceElem = document.getElementById('dynamic-total-price');
  const unitPriceElem = document.getElementById('unit-price-display');
  const finishPills = document.querySelectorAll('.finish-pill-choice');

  function calculatePrice() {
    if (!totalPriceElem) return;

    let basePrice = 150; // Default base price for smallest size
    if (sizeSelect) {
      basePrice = parseFloat(sizeSelect.options[sizeSelect.selectedIndex].getAttribute('data-price') || '150');
    }

    let qty = 1;
    if (qtyInput) {
      qty = parseInt(qtyInput.value, 10) || 1;
    }

    // Material / Finish modifier
    let finishMultiplier = 1.0;
    const activePill = document.querySelector('.finish-pill-choice.active');
    if (activePill && activePill.getAttribute('data-multiplier')) {
      finishMultiplier = parseFloat(activePill.getAttribute('data-multiplier'));
    }

    const calculatedUnitPrice = basePrice * finishMultiplier;
    const calculatedTotal = calculatedUnitPrice * qty;

    if (totalPriceElem) {
      totalPriceElem.textContent = '₱' + calculatedTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (unitPriceElem) {
      unitPriceElem.textContent = '₱' + calculatedUnitPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  }

  if (qtyMinusBtn && qtyInput) {
    qtyMinusBtn.addEventListener('click', () => {
      let currentVal = parseInt(qtyInput.value, 10) || 1;
      if (currentVal > 1) {
        qtyInput.value = currentVal - 1;
        calculatePrice();
      }
    });
  }

  if (qtyPlusBtn && qtyInput) {
    qtyPlusBtn.addEventListener('click', () => {
      let currentVal = parseInt(qtyInput.value, 10) || 1;
      qtyInput.value = currentVal + 1;
      calculatePrice();
    });
  }

  if (sizeSelect) {
    sizeSelect.addEventListener('change', calculatePrice);
  }

  finishPills.forEach(pill => {
    pill.addEventListener('click', () => {
      finishPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      calculatePrice();
    });
  });

  // Initial calculation trigger
  calculatePrice();

  // 4. Drag & Drop File Upload Demo Simulation
  const fileDropzone = document.getElementById('artwork-dropzone');
  const fileInput = document.getElementById('artwork-file-input');
  const filePreviewPill = document.getElementById('file-preview-pill');
  const fileNameDisplay = document.getElementById('file-name-display');

  if (fileDropzone && fileInput) {
    fileDropzone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', (e) => {
      if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (fileNameDisplay) fileNameDisplay.textContent = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
        if (filePreviewPill) filePreviewPill.style.display = 'flex';
      }
    });

    fileDropzone.addEventListener('dragover', (e) => {
      e.preventDefault();
      fileDropzone.style.borderColor = 'var(--accent-color)';
      fileDropzone.style.backgroundColor = 'var(--accent-light)';
    });

    fileDropzone.addEventListener('dragleave', (e) => {
      e.preventDefault();
      fileDropzone.style.borderColor = 'var(--border-focus)';
      fileDropzone.style.backgroundColor = 'var(--bg-surface)';
    });

    fileDropzone.addEventListener('drop', (e) => {
      e.preventDefault();
      fileDropzone.style.borderColor = 'var(--border-focus)';
      fileDropzone.style.backgroundColor = 'var(--bg-surface)';
      if (e.dataTransfer.files.length > 0) {
        const file = e.dataTransfer.files[0];
        if (fileNameDisplay) fileNameDisplay.textContent = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
        if (filePreviewPill) filePreviewPill.style.display = 'flex';
      }
    });
  }

  // 4b. Mobile Nav Slide-in Panel Toggle
  const mobileMenuToggle = document.getElementById('mobileMenuToggle');
  const mobileNavPanel = document.getElementById('mobileNavPanel');
  const mobileNavOverlay = document.getElementById('mobileNavOverlay');
  const mobileNavClose = document.getElementById('mobileNavClose');

  function openMobileNav() {
    if (!mobileNavPanel || !mobileNavOverlay) return;
    mobileNavPanel.classList.add('active');
    mobileNavOverlay.classList.add('active');
    document.body.classList.add('mobile-nav-open');
    if (mobileMenuToggle) mobileMenuToggle.setAttribute('aria-expanded', 'true');
  }

  function closeMobileNav() {
    if (!mobileNavPanel || !mobileNavOverlay) return;
    mobileNavPanel.classList.remove('active');
    mobileNavOverlay.classList.remove('active');
    document.body.classList.remove('mobile-nav-open');
    if (mobileMenuToggle) mobileMenuToggle.setAttribute('aria-expanded', 'false');
  }

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', openMobileNav);
  }
  if (mobileNavClose) {
    mobileNavClose.addEventListener('click', closeMobileNav);
  }
  if (mobileNavOverlay) {
    mobileNavOverlay.addEventListener('click', closeMobileNav);
  }
  // Close panel on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMobileNav();
  });

  // 5. Product Detail Tabs Switcher
  const detailTabLinks = document.querySelectorAll('.detail-tab-link');
  const tabPanes = document.querySelectorAll('.tab-pane');

  detailTabLinks.forEach(link => {
    link.addEventListener('click', () => {
      detailTabLinks.forEach(l => l.classList.remove('active'));
      tabPanes.forEach(p => p.classList.remove('active'));

      link.classList.add('active');
      const targetId = link.getAttribute('data-tab');
      const targetPane = document.getElementById(targetId);
      if (targetPane) targetPane.classList.add('active');
    });
  });
});

