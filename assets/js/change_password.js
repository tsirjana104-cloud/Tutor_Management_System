// change_password.js - validation and show/hide buttons for change_password.php (admin, tutor and student)
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("changePasswordForm");
  if (!form) return; // page without the form, do nothing

  // ---------- Regular expression ----------
  // 6 to 64 characters with at least one letter and one number (same rule as the register page)
  var passwordPattern = /^(?=.*[A-Za-z])(?=.*\d).{6,64}$/;

  // ---------- Fields and the ids of their error spots ----------
  var fields = {
    current: document.getElementById("current_password"),
    newPass: document.getElementById("new_password"),
    confirm: document.getElementById("confirm_password")
  };

  var errorIds = {
    current: "currentError",
    newPass: "newError",
    confirm: "confirmError"
  };

  // ---------- Show / Hide button inside each password box ----------
  // Styles are added here so this does not depend on style.css
  var style = document.createElement("style");
  style.textContent =
    ".password-wrap { position: relative; }" +
    ".password-wrap input { padding-right: 64px; }" +
    ".toggle-pass { position: absolute; top: 50%; right: 8px; transform: translateY(-50%);" +
    " padding: 4px 8px; font-size: 12px; font-weight: bold; color: #2563eb; background: none; border: none; cursor: pointer; }" +
    ".toggle-pass:hover { text-decoration: underline; }";
  document.head.appendChild(style);

  Object.keys(fields).forEach(function (key) {
    var input = fields[key];

    var wrap = document.createElement("div");
    wrap.className = "password-wrap";
    input.parentNode.insertBefore(wrap, input); // put the wrapper where the input is
    wrap.appendChild(input);                    // then move the input inside it

    var toggle = document.createElement("button");
    toggle.type = "button"; // must never submit the form
    toggle.className = "toggle-pass";
    toggle.textContent = "Show";
    wrap.appendChild(toggle);

    toggle.addEventListener("click", function () {
      var hidden = input.type === "password";
      input.type = hidden ? "text" : "password";
      toggle.textContent = hidden ? "Hide" : "Show";
    });
  });

  // ---------- Validators: return an error message, or "" if the value is fine ----------
  var validators = {
    current: function (value) {
      if (value === "") return "Enter your current password.";
      return "";
    },

    newPass: function (value) {
      if (value === "") return "Enter a new password.";
      if (value.length < 6) return "Password must be at least 6 characters.";
      if (value.length > 64) return "Password must be 64 characters or less.";
      if (!passwordPattern.test(value)) return "Password must contain at least one letter and one number.";
      if (value === fields.current.value) return "The new password must be different from your current password.";
      return "";
    },

    confirm: function (value) {
      if (value === "") return "Type the new password again.";
      if (value !== fields.newPass.value) return "The two passwords do not match.";
      return "";
    }
  };

  // ---------- Show or clear an error under a field ----------
  function showError(key, message) {
    var errorBox = document.getElementById(errorIds[key]);
    if (errorBox) errorBox.textContent = message;
    fields[key].classList.toggle("invalid", message !== "");
  }

  // Validate one field. Returns true if it is valid.
  function checkField(key) {
    var message = validators[key](fields[key].value);
    showError(key, message);
    return message === "";
  }

  // ---------- Live feedback ----------
  Object.keys(fields).forEach(function (key) {
    // Check when the user leaves the field
    fields[key].addEventListener("blur", function () {
      checkField(key);
    });

    // Once a field was marked wrong, re-check while typing so the error disappears as soon as it is fixed
    fields[key].addEventListener("input", function () {
      if (fields[key].classList.contains("invalid")) {
        checkField(key);
      }
    });
  });

  // The confirmation and the "different from current" rules depend on other boxes, so re-check them too
  fields.newPass.addEventListener("input", function () {
    if (fields.confirm.value !== "") checkField("confirm");
  });
  fields.current.addEventListener("input", function () {
    if (fields.newPass.value !== "") checkField("newPass");
  });

  // ---------- On submit ----------
  form.addEventListener("submit", function (e) {
    var firstInvalid = null;

    Object.keys(fields).forEach(function (key) {
      var ok = checkField(key);
      if (!ok && firstInvalid === null) firstInvalid = fields[key];
    });

    if (firstInvalid) {
      e.preventDefault(); // stop the form from being sent
      firstInvalid.focus();
      return;
    }

    // Everything is valid: stop double clicks
    var button = form.querySelector("button[type='submit']");
    if (button) {
      button.disabled = true;
      button.textContent = "Changing...";
    }
  });
});