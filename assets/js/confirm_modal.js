// confirm_modal.js - a nicer replacement for the browser's confirm() popup.
// Include this file BEFORE the page's own js file, then use it like this:
//
//   confirmModal({
//     title: "Approve tutor?",
//     message: "Ram Sharma will appear in student searches.",
//     confirmText: "Yes, approve",
//     cancelText: "Cancel",          // optional
//     variant: "primary"             // "primary" (blue) or "danger" (red)
//   }).then(function (ok) {
//     if (ok) { /* admin pressed the confirm button */ }
//   });

(function () {
  var icons = {
    primary:
      '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<circle cx="12" cy="12" r="10"></circle><path d="M8 12.5l2.7 2.7L16 9.5"></path></svg>',
    danger:
      '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path>' +
      '<line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>'
  };

  // ---------- Styles ----------
  // The popup carries its own CSS, so it looks right on every page even if style.css is old or cached.
  function addStyles() {
    if (document.getElementById("confirmModalStyles")) return;
    var style = document.createElement("style");
    style.id = "confirmModalStyles";
    style.textContent = `
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: rgba(15, 23, 42, 0.45);
  -webkit-backdrop-filter: blur(5px);
  backdrop-filter: blur(5px);
  opacity: 0;
  transition: opacity 0.18s ease;
  font-family: Arial, Helvetica, sans-serif;
}
.modal-overlay.show { opacity: 1; }
.modal-box {
  width: 100%;
  max-width: 400px;
  padding: 28px 26px 24px;
  text-align: center;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3);
  transform: scale(0.92);
  transition: transform 0.18s ease;
}
.modal-overlay.show .modal-box { transform: scale(1); }
.modal-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  margin: 0 auto 14px;
  border-radius: 50%;
}
.modal-icon.primary { color: var(--blue, #2563eb); background: #dbeafe; }
.modal-icon.danger { color: var(--error, #dc2626); background: #fee2e2; }
.modal-title { margin: 0 0 8px; font-size: 20px; color: var(--blue-dark, #1e3a8a); }
.modal-text { margin: 0 0 22px; font-size: 14px; line-height: 1.5; color: var(--muted, #64748b); }
.modal-actions { display: flex; gap: 10px; }
.modal-btn {
  flex: 1;
  padding: 11px 14px;
  font-family: inherit;
  font-size: 15px;
  font-weight: bold;
  border-radius: 8px;
  cursor: pointer;
}
.modal-btn.cancel { color: var(--text, #1e293b); background: #fff; border: 1px solid var(--border, #cbd5e1); }
.modal-btn.cancel:hover { background: #f1f5f9; }
.modal-btn.confirm { color: #fff; background: var(--blue, #2563eb); border: 1px solid var(--blue, #2563eb); }
.modal-btn.confirm:hover { background: var(--blue-hover, #1d4ed8); }
.modal-btn.confirm.danger { background: var(--error, #dc2626); border-color: var(--error, #dc2626); }
.modal-btn.confirm.danger:hover { background: #b91c1c; }
.modal-btn:focus-visible { outline: 3px solid rgba(37, 99, 235, 0.35); outline-offset: 2px; }
body.modal-open { overflow: hidden; }
`;
    document.head.appendChild(style);
  }

  window.confirmModal = function (options) {
    addStyles();
    options = options || {};
    var variant = options.variant === "danger" ? "danger" : "primary";

    return new Promise(function (resolve) {
      var previousFocus = document.activeElement;

      // ----- Build the popup -----
      var overlay = document.createElement("div");
      overlay.className = "modal-overlay";
      overlay.innerHTML =
        '<div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalText">' +
          '<div class="modal-icon ' + variant + '">' + icons[variant] + '</div>' +
          '<h3 class="modal-title" id="modalTitle"></h3>' +
          '<p class="modal-text" id="modalText"></p>' +
          '<div class="modal-actions">' +
            '<button type="button" class="modal-btn cancel"></button>' +
            '<button type="button" class="modal-btn confirm ' + variant + '"></button>' +
          '</div>' +
        '</div>';

      // Text is set with textContent, so names from the database can never inject HTML
      overlay.querySelector(".modal-title").textContent = options.title || "Are you sure?";
      overlay.querySelector(".modal-text").textContent = options.message || "";
      var cancelBtn = overlay.querySelector(".modal-btn.cancel");
      var confirmBtn = overlay.querySelector(".modal-btn.confirm");
      cancelBtn.textContent = options.cancelText || "Cancel";
      confirmBtn.textContent = options.confirmText || "Confirm";

      document.body.appendChild(overlay);
      document.body.classList.add("modal-open"); // stops the page behind from scrolling
      requestAnimationFrame(function () {
        overlay.classList.add("show"); // triggers the fade-in animation
      });
      confirmBtn.focus();

      // ----- Closing -----
      var closed = false;
      function close(result) {
        if (closed) return; // protects against double clicks
        closed = true;
        document.removeEventListener("keydown", onKey);
        overlay.classList.remove("show");
        document.body.classList.remove("modal-open");
        setTimeout(function () {
          overlay.remove();
        }, 180);
        if (previousFocus && previousFocus.focus) previousFocus.focus();
        resolve(result);
      }

      function onKey(e) {
        if (e.key === "Escape") {
          close(false);
        } else if (e.key === "Tab") {
          // Keep keyboard focus inside the popup
          if (e.shiftKey && document.activeElement === cancelBtn) {
            e.preventDefault();
            confirmBtn.focus();
          } else if (!e.shiftKey && document.activeElement === confirmBtn) {
            e.preventDefault();
            cancelBtn.focus();
          }
        }
      }

      cancelBtn.addEventListener("click", function () { close(false); });
      confirmBtn.addEventListener("click", function () { close(true); });
      overlay.addEventListener("click", function (e) {
        if (e.target === overlay) close(false); // click on the blurred background
      });
      document.addEventListener("keydown", onKey);
    });
  };
})();