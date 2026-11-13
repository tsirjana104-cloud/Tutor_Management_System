// dashboard.js - shared by the admin, tutor and student pages.
// 1. Closes the notification bell and the user menu when you click outside them or press Escape
// 2. Counts the dashboard numbers up from 0 when the page loads
//
// The three top bar files (admin_navbar.php, tutor_navbar.php, student_navbar.php) load this file,
// so it works on every page that has a top bar. The check below stops it from running twice
// on the dashboards, which also load it on their own.

(function () {
  if (window.dashboardJsLoaded) return;
  window.dashboardJsLoaded = true;

  document.addEventListener("DOMContentLoaded", function () {
    // ---------- Dropdowns: notification bell and user menu ----------
    // Both are HTML <details> elements, so they already open and close when you click them.
    // This adds the things <details> does not do by itself.
    var menus = [
      document.getElementById("notificationBell"),
      document.getElementById("userMenu")
    ].filter(Boolean); // keep only the ones that exist on this page

    // Click anywhere outside an open dropdown: close it
    document.addEventListener("click", function (e) {
      menus.forEach(function (menu) {
        if (menu.open && !menu.contains(e.target)) {
          menu.open = false;
        }
      });
    });

    // Escape key: close the open dropdown and give the focus back to its button
    document.addEventListener("keydown", function (e) {
      if (e.key !== "Escape") return;
      menus.forEach(function (menu) {
        if (menu.open) {
          menu.open = false;
          var button = menu.querySelector("summary");
          if (button) button.focus();
        }
      });
    });

    // Opening one dropdown closes the other, so they never overlap
    menus.forEach(function (menu) {
      menu.addEventListener("toggle", function () {
        if (!menu.open) return;
        menus.forEach(function (other) {
          if (other !== menu) other.open = false;
        });
      });
    });

    // ---------- Count-up animation for the stat cards ----------
    var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var numbers = document.querySelectorAll(".stat-number");

    numbers.forEach(function (box) {
      var text = box.textContent.trim();
      if (!/^\d+$/.test(text)) return; // only whole numbers (a rating like 4.5 or "-" stays as it is)

      var target = Number(text);
      if (reduceMotion || target === 0) return; // nothing to animate

      var duration = 600; // milliseconds
      var startTime = null;
      box.textContent = "0";

      function step(timestamp) {
        if (startTime === null) startTime = timestamp;
        var progress = Math.min((timestamp - startTime) / duration, 1);
        box.textContent = Math.round(progress * target);
        if (progress < 1) {
          requestAnimationFrame(step);
        } else {
          box.textContent = target; // make sure it ends on the exact number
        }
      }

      requestAnimationFrame(step);
    });
  });
})();