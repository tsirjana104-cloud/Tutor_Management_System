// login.js - validation for login.php
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("loginForm");
  if (!form) return; // page without the form, do nothing

  // ---------- Regular expression ----------
  // something@domain.tld  (no spaces, valid characters, TLD of 2+ letters)
  var emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/;

  // ---------- Fields ----------
  var fields = {
    email: document.getElementById("email"),
    password: document.getElementById("password")
  };

  // ---------- Validators: return an error message, or "" if the value is fine ----------
  var validators = {
    email: function (value) {
      value = value.trim();
      if (value === "") return "Email is required.";
      if (!emailPattern.test(value)) return "Enter a valid email, like name@example.com.";
      return "";
    },

    // On the login page we only check that something was typed.
    // The password rules are not repeated here, so they are not revealed to someone guessing.
    password: function (value) {
      if (value === "") return "Password is required.";
      return "";
    }
  };

  // ---------- Show or clear an error under a field ----------
  function showError(key, message) {
    var field = fields[key];
    var errorBox = document.getElementById(key + "Error");
    if (errorBox) errorBox.textContent = message;
    field.classList.toggle("invalid", message !== "");
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

    // Everything is valid: tidy the email and stop double clicks
    fields.email.value = fields.email.value.trim();

    var button = form.querySelector("button[type='submit']");
    if (button) {
      button.disabled = true;
      button.textContent = "Logging in...";
    }
  });
});