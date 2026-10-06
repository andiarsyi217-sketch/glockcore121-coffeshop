/* =========================================================
   GLOCKCORE 121 - JavaScript Interaction (jQuery)
   ========================================================= */

$(document).ready(function () {
  // 1. Mobile Menu Toggle
  const $mobileToggle = $('.mobile-toggle');
  const $navLinks = $('.nav-links');

  if ($mobileToggle.length && $navLinks.length) {
    $mobileToggle.on('click', function (e) {
      e.stopPropagation();
      $navLinks.toggleClass('open');
      const isOpen = $navLinks.hasClass('open');
      $mobileToggle.html(isOpen ? '✕' : '☰');
      $mobileToggle.attr('aria-expanded', isOpen);
    });

    // Close menu when clicking outside
    $(document).on('click', function (e) {
      if (!$(e.target).closest('.mobile-toggle, .nav-links').length) {
        $navLinks.removeClass('open');
        $mobileToggle.html('☰');
        $mobileToggle.attr('aria-expanded', 'false');
      }
    });

    // Close menu when a navigation link is clicked
    $navLinks.find('a').on('click', function () {
      $navLinks.removeClass('open');
      $mobileToggle.html('☰');
      $mobileToggle.attr('aria-expanded', 'false');
    });
  }

  // 2. Menu Filter Logic (for menu.html)
  const $filterBtns = $('.filter-btn');
  const $menuItems = $('.menu-frame');

  if ($filterBtns.length && $menuItems.length) {
    $filterBtns.on('click', function () {
      // Active state
      $filterBtns.removeClass('active');
      $(this).addClass('active');

      const filterValue = $(this).attr('data-filter');

      $menuItems.each(function () {
        const $item = $(this);
        const itemCategory = $item.attr('data-category');

        if (filterValue === 'all' || itemCategory === filterValue) {
          $item.stop(true, true).css('display', 'flex').animate(
            { opacity: 1 },
            200
          );
        } else {
          $item.stop(true, true).animate(
            { opacity: 0 },
            200,
            function () {
              $(this).css('display', 'none');
            }
          );
        }
      });
    });
  }

  // 3. Smooth Scroll for Anchor Links
  $('a[href^="#"]').on('click', function (e) {
    const targetId = $(this).attr('href');
    if (targetId && targetId !== '#') {
      const $target = $(targetId);
      if ($target.length) {
        e.preventDefault();
        $('html, body').stop().animate(
          {
            scrollTop: $target.offset().top - 70
          },
          400
        );
      }
    }
  });
});

// 4. WhatsApp Configuration & Order Function
const GLOCK_WA_NUMBER = '6282298663371';

function formatRupiah(num) {
  return 'Rp ' + Number(num).toLocaleString('id-ID');
}

function orderWhatsAppSingle(itemName, price) {
  const text = `Halo Glockcore 121, saya ingin memesan:\n- *${itemName}* (${formatRupiah(price)})\n\nApakah stok tersedia? Mohon info total dan pembayarannya. Terima kasih!`;
  const url = `https://wa.me/${GLOCK_WA_NUMBER}?text=${encodeURIComponent(text)}`;
  window.open(url, '_blank');
}

// 5. Cart System (Local Storage + Drawer Management)
const Cart = {
  storageKey: 'glockcore_cart_v1',

  getItems() {
    try {
      return JSON.parse(localStorage.getItem(this.storageKey)) || [];
    } catch (e) {
      return [];
    }
  },

  saveItems(items) {
    localStorage.setItem(this.storageKey, JSON.stringify(items));
    this.updateUI();
  },

  addItem(name, price, img) {
    const items = this.getItems();
    const existing = items.find(i => i.name === name);
    if (existing) {
      existing.qty += 1;
    } else {
      items.push({
        name: name,
        price: Number(price),
        img: img || 'logo.jpeg',
        qty: 1
      });
    }
    this.saveItems(items);
    this.showToast(`"${name}" ditambahkan ke keranjang!`);
  },

  updateQty(name, delta) {
    let items = this.getItems();
    const item = items.find(i => i.name === name);
    if (item) {
      item.qty += delta;
      if (item.qty <= 0) {
        items = items.filter(i => i.name !== name);
      }
    }
    this.saveItems(items);
  },

  removeItem(name) {
    let items = this.getItems();
    items = items.filter(i => i.name !== name);
    this.saveItems(items);
    this.showToast(`Item dihapus dari keranjang`);
  },

  clearCart() {
    this.saveItems([]);
  },

  getTotal() {
    return this.getItems().reduce((sum, item) => sum + (item.price * item.qty), 0);
  },

  getTotalCount() {
    return this.getItems().reduce((sum, item) => sum + item.qty, 0);
  },

  showToast(message) {
    let $toast = $('#toastMsg');
    if (!$toast.length) {
      $toast = $('<div id="toastMsg" class="toast-msg"></div>').appendTo('body');
    }
    $toast.html(`<span>🛒</span> <span>${message}</span>`).addClass('show');
    clearTimeout(window._toastTimeout);
    window._toastTimeout = setTimeout(() => {
      $toast.removeClass('show');
    }, 2400);
  },

  updateUI() {
    const items = this.getItems();
    const totalCount = this.getTotalCount();
    const totalPrice = this.getTotal();

    // Update badges
    $('.cart-badge').text(totalCount);
    if (totalCount > 0) {
      $('.cart-badge').show();
      $('#checkoutWaBtn').prop('disabled', false);
    } else {
      $('#checkoutWaBtn').prop('disabled', true);
    }

    // Render Items in drawer
    const $container = $('#cartItemsBody');
    if ($container.length) {
      if (items.length === 0) {
        $container.html(`
          <div class="cart-empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <p style="font-weight: 600; color: #fff; margin-bottom: 0.35rem;">Keranjang Masih Kosong</p>
            <p style="font-size: 0.85rem;">Pilih menu racikan Glock dan klik "+ Keranjang" untuk memesan.</p>
          </div>
        `);
      } else {
        let html = '';
        items.forEach(item => {
          html += `
            <div class="cart-item">
              <img src="${item.img}" alt="${item.name}" class="cart-item-img" onerror="this.src='logo.jpeg'">
              <div class="cart-item-info">
                <div class="cart-item-name" title="${item.name}">${item.name}</div>
                <div class="cart-item-price">${formatRupiah(item.price)} × ${item.qty} = <strong>${formatRupiah(item.price * item.qty)}</strong></div>
                <div style="margin-top: 0.4rem; display: flex; align-items: center; gap: 0.6rem;">
                  <div class="cart-qty-ctrl">
                    <button type="button" class="cart-qty-btn" onclick="Cart.updateQty('${item.name.replace(/'/g, "\\'")}', -1)">-</button>
                    <span class="cart-qty-val">${item.qty}</span>
                    <button type="button" class="cart-qty-btn" onclick="Cart.updateQty('${item.name.replace(/'/g, "\\'")}', 1)">+</button>
                  </div>
                </div>
              </div>
              <button type="button" class="cart-item-remove" onclick="Cart.removeItem('${item.name.replace(/'/g, "\\'")}')" title="Hapus">✕</button>
            </div>
          `;
        });
        $container.html(html);
      }
    }

    // Update total price display
    $('#cartTotalPrice').text(formatRupiah(totalPrice));
  },

  openDrawer() {
    this.updateUI();
    $('#cartOverlay').addClass('active');
    $('#cartDrawer').addClass('active');
    $('body').css('overflow', 'hidden');
  },

  closeDrawer() {
    $('#cartOverlay').removeClass('active');
    $('#cartDrawer').removeClass('active');
    $('body').css('overflow', '');
  },

  checkoutWhatsApp() {
    const items = this.getItems();
    if (items.length === 0) {
      alert('Keranjang belanja Anda masih kosong!');
      return;
    }

    const notes = $('#cartNotes').val().trim();
    const totalPrice = this.getTotal();

    let message = `*PESANAN BARU - GLOCKCORE 121*\n`;
    message += `──────────────────────\n`;
    items.forEach((item, idx) => {
      message += `${idx + 1}. *${item.name}*\n   ${item.qty}x @ ${formatRupiah(item.price)} = ${formatRupiah(item.price * item.qty)}\n`;
    });
    message += `──────────────────────\n`;
    message += `*Total Pembayaran: ${formatRupiah(totalPrice)}*\n`;
    if (notes) {
      message += `*Catatan Khusus:* ${notes}\n`;
    }
    message += `\nMohon konfirmasi pesanan dan ketersediaan menu. Terima kasih!`;

    const waUrl = `https://wa.me/${GLOCK_WA_NUMBER}?text=${encodeURIComponent(message)}`;
    window.open(waUrl, '_blank');
  }
};

// Initialize Cart on Document Ready
$(document).ready(function () {
  Cart.updateUI();

  // Open drawer buttons
  $(document).on('click', '.open-cart-btn', function (e) {
    e.preventDefault();
    Cart.openDrawer();
  });

  // Close drawer
  $(document).on('click', '#cartCloseBtn, #cartOverlay', function () {
    Cart.closeDrawer();
  });

  // Add to cart click
  $(document).on('click', '.btn-add-cart', function () {
    const name = $(this).attr('data-name');
    const price = $(this).attr('data-price');
    const img = $(this).attr('data-img');
    Cart.addItem(name, price, img);
  });

  // Checkout button
  $(document).on('click', '#checkoutWaBtn', function () {
    Cart.checkoutWhatsApp();
  });
});

