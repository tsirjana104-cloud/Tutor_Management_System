// register.js - validation for register.php
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("registerForm");
  if (!form) return; // page without the form, do nothing

  // ---------- Regular expressions ----------
  var patterns = {
    // Letters from any language, spaces, dot, apostrophe, hyphen. 2 to 50 characters, must start with a letter.
    name: /^\p{L}[\p{L}\p{M}\s.'-]{1,49}$/u,

    // something@domain.tld  (no spaces, valid characters, TLD of 2+ letters)
    email: /^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/,

    // At least 6 characters with at least one letter and one number
    password: /^(?=.*[A-Za-z])(?=.*\d).{6,64}$/,

    // Nepali mobile number: 98XXXXXXXX or 97XXXXXXXX, optionally with +977 in front
    phone: /^(\+977)?9[78]\d{8}$/,

    // Letters, numbers, spaces, comma, dot, apostrophe, hyphen. 2 to 60 characters
    location: /^[\p{L}0-9][\p{L}\p{M}0-9\s,.'-]{1,59}$/u
  };

  // ---------- Fields ----------
  var fields = {
    name: document.getElementById("name"),
    email: document.getElementById("email"),
    password: document.getElementById("password"),
    phone: document.getElementById("phone"),
    location: document.getElementById("location"),
    role: document.getElementById("role")
  };

  // ---------- One validator per field. Each returns an error message, or "" if the value is fine ----------
  var validators = {
    name: function (value) {
      value = value.trim();
      if (value === "") return "Name is required.";
      if (!patterns.name.test(value)) return "Use 2 to 50 letters. Only spaces, . ' - are allowed besides letters.";
      return "";
    },

    email: function (value) {
      value = value.trim();
      if (value === "") return "Email is required.";
      if (!patterns.email.test(value)) return "Enter a valid email, like name@example.com.";
      return "";
    },

    password: function (value) {
      if (value === "") return "Password is required.";
      if (value.length < 6) return "Password must be at least 6 characters.";
      if (!patterns.password.test(value)) return "Password must contain at least one letter and one number.";
      return "";
    },

    phone: function (value) {
      value = value.trim();
      if (value === "") return ""; // phone is optional
      var cleaned = value.replace(/[\s-]/g, ""); // allow spaces and dashes while typing
      if (!patterns.phone.test(cleaned)) return "Enter a valid mobile number, like 98XXXXXXXX.";
      return "";
    },

    location: function (value) {
      value = value.trim();
      if (value === "") return ""; // location is optional
      if (!patterns.location.test(value)) return "Enter a valid location (2 to 60 characters).";
      return "";
    },

    role: function (value) {
      if (value !== "student" && value !== "tutor") return "Please select your role.";
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
    var eventName = key === "role" ? "change" : "input";
    fields[key].addEventListener(eventName, function () {
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

    // Everything is valid: tidy the values and stop double clicks
    fields.name.value = fields.name.value.trim();
    fields.email.value = fields.email.value.trim();
    fields.phone.value = fields.phone.value.replace(/[\s-]/g, "");
    fields.location.value = fields.location.value.trim();

    var button = form.querySelector("button[type='submit']");
    if (button) {
      button.disabled = true;
      button.textContent = "Registering...";
    }
  });
});