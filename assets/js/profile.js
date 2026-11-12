// profile.js - validation for tutor/profile.php
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("profileForm");
  if (!form) return; // page without the form, do nothing

  // ---------- Regular expressions ----------
  var patterns = {
    // Qualification: 2 to 100 characters, starts with a letter or number.
    // Allowed: letters, numbers, spaces and . , & ( ) / ' -   (for "BSc. Mathematics", "M.Ed (Science)")
    qualification: /^[\p{L}0-9][\p{L}\p{M}0-9\s.,&()\/'-]{1,99}$/u,

    // Experience: a whole number, 1 or 2 digits
    experience: /^\d{1,2}$/,

    // Hourly rate: a number with up to 2 decimals, like 500 or 499.50
    rate: /^\d{1,6}(\.\d{1,2})?$/
  };

  var BIO_MIN = 20;
  var BIO_MAX = 500;
  var EXPERIENCE_MAX = 60;
  var RATE_MAX = 100000;

  // ---------- Fields and the ids of their error spots ----------
  var fields = {
    bio: document.getElementById("bio"),
    qualification: document.getElementById("qualification"),
    experience: document.getElementById("experience_years"),
    rate: document.getElementById("hourly_rate")
  };

  var errorIds = {
    bio: "bioError",
    qualification: "qualificationError",
    experience: "experienceError",
    rate: "rateError"
  };

  // ---------- Live character counter under the bio ----------
  var counter = document.createElement("small");
  counter.className = "muted-text block-text";
  fields.bio.insertAdjacentElement("afterend", counter);

  function updateCounter() {
    counter.textContent = fields.bio.value.trim().length + " / " + BIO_MAX + " characters (minimum " + BIO_MIN + ")";
  }
  updateCounter();
  fields.bio.addEventListener("input", updateCounter);

  // ---------- Validators: return an error message, or "" if the value is fine ----------
  var validators = {
    bio: function (value) {
      value = value.trim();
      if (value === "") return "Bio is required.";
      if (value.length < BIO_MIN) return "Bio must be at least " + BIO_MIN + " characters.";
      if (value.length > BIO_MAX) return "Bio must be " + BIO_MAX + " characters or less.";
      if (!/\p{L}/u.test(value)) return "Bio must contain some letters.";
      return "";
    },

    qualification: function (value) {
      value = value.trim();
      if (value === "") return "Qualification is required.";
      if (!patterns.qualification.test(value)) {
        return "Enter 2 to 100 characters, like BSc. Mathematics. Special symbols are not allowed.";
      }
      return "";
    },

    experience: function (value) {
      value = value.trim();
      if (value === "") return "Experience is required. Enter 0 if you are new.";
      if (!patterns.experience.test(value)) return "Enter a whole number, like 3.";
      if (Number(value) > EXPERIENCE_MAX) return "Experience cannot be more than " + EXPERIENCE_MAX + " years.";
      return "";
    },

    rate: function (value) {
      value = value.trim();
      if (value === "") return "Hourly rate is required.";
      if (!patterns.rate.test(value)) return "Enter a valid amount, like 500 or 499.50.";
      if (Number(value) <= 0) return "Hourly rate must be greater than 0.";
      if (Number(value) > RATE_MAX) return "Hourly rate cannot be more than Rs. " + RATE_MAX + ".";
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

  // ---------- Number boxes: do not allow e, E, + and - ----------
  [fields.experience, fields.rate].forEach(function (box) {
    box.addEventListener("keydown", function (e) {
      if (["e", "E", "+", "-"].indexOf(e.key) !== -1) {
        e.preventDefault();
      }
    });
  });
  // Experience is a whole number, so a dot is not allowed there either
  fields.experience.addEventListener("keydown", function (e) {
    if (e.key === ".") e.preventDefault();
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

    // Everything is valid: tidy the text values and stop double clicks
    fields.bio.value = fields.bio.value.trim();
    fields.qualification.value = fields.qualification.value.trim();

    var button = form.querySelector("button[type='submit']");
    if (button) {
      button.disabled = true;
      button.textContent = "Saving...";
    }
  });
});