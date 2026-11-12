// dashboard.js - shared by the admin, tutor and student pages.
// 1. Closes the notification bell when you click outside it or press Escape
// 2. Counts the dashboard numbers up from 0 when the page loads
//
// The three top bar files (admin_navbar.php, tutor_navbar.php, student_navbar.php) load this file,
// so it works on every page that has a top bar. The check below stops it from running twice
// on the dashboards, which also load it on their own.

(function () {
  if (window.dashboardJsLoaded) return;
  window.dashboardJsLoaded = true;

  document.addEventListener("DOMContentLoaded", function () {
    // ---------- Notification bell ----------
    // The bell is an HTML <details> element, so it already opens and closes when you click it.
    // This adds the two things <details> does not do by itself.
    var bell = document.getElementById("notificationBell");

    if (bell) {
      // Click anywhere outside the bell: close it
      document.addEventListener("click", function (e) {
        if (bell.open && !bell.contains(e.target)) {
          bell.open = false;
        }
      });

      // Escape key: close it and give the focus back to the bell icon
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && bell.open) {
          bell.open = false;
          var icon = bell.querySelector("summary");
          if (icon) icon.focus();
        }
      });
    }

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