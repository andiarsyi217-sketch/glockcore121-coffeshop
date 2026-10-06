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
// WhatsApp Configuration
const GLOCK_WA_NUMBER = '6282298663371';
