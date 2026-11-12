// book_tutor.js - validation for student/book_tutor.php
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("bookingForm");
  if (!form) return; // the form is not shown when the tutor has no subjects, so do nothing

  // ---------- Rules ----------
  var OPEN_TIME = "10:00";   // earliest time a lesson can be booked
  var CLOSE_TIME = "17:00";  // latest time a lesson can be booked
  var MESSAGE_MIN = 10;
  var MESSAGE_MAX = 300;

  // ---------- Regular expressions ----------
  var patterns = {
    // Date from the date box: YYYY-MM-DD
    date: /^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/,

    // Time from the time box: HH:MM in 24 hour format (00:00 to 23:59)
    time: /^([01]\d|2[0-3]):[0-5]\d$/
  };

  // ---------- Fields and the ids of their error spots ----------
  var fields = {
    subject: document.getElementById("subject_id"),
    date: document.getElementById("preferred_date"),
    time: document.getElementById("preferred_time"),
    message: document.getElementById("message")
  };

  var errorIds = {
    subject: "subjectError",
    date: "dateError",
    time: "timeError",
    message: "messageError"
  };

  // ---------- Helpers ----------
  // Today's date as YYYY-MM-DD, using the computer's local date
  function todayString() {
    var now = new Date();
    var month = String(now.getMonth() + 1).padStart(2, "0");
    var day = String(now.getDate()).padStart(2, "0");
    return now.getFullYear() + "-" + month + "-" + day;
  }

  // Current time as HH:MM, using the computer's local time
  function nowTimeString() {
    var now = new Date();
    return String(now.getHours()).padStart(2, "0") + ":" + String(now.getMinutes()).padStart(2, "0");
  }

  // The regex accepts 2026-02-31, so also make sure the date really exists
  function isRealDate(value) {
    var parts = value.split("-");
    var d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    return d.getFullYear() === Number(parts[0]) &&
           d.getMonth() === Number(parts[1]) - 1 &&
           d.getDate() === Number(parts[2]);
  }

  // ---------- Live character counter under the message ----------
  var counter = document.createElement("small");
  counter.className = "muted-text block-text";
  fields.message.insertAdjacentElement("afterend", counter);

  function updateCounter() {
    counter.textContent = fields.message.value.trim().length + " / " + MESSAGE_MAX + " characters (minimum " + MESSAGE_MIN + ")";
  }
  updateCounter();
  fields.message.addEventListener("input", updateCounter);

  // ---------- Validators: return an error message, or "" if the value is fine ----------
  var validators = {
    subject: function (value) {
      if (value === "") return "Please select a subject.";
      if (!/^\d+$/.test(value)) return "Invalid subject selected."; // option values are database ids (numbers)
      return "";
    },

    date: function (value) {
      if (value === "") return "Please choose a date.";
      if (!patterns.date.test(value) || !isRealDate(value)) return "Enter a valid date.";
      if (value < todayString()) return "You cannot select a past date. Choose today or a later date.";
      return "";
    },

    time: function (value) {
      if (value === "") return "Please choose a time.";
      if (!patterns.time.test(value)) return "Enter a valid time, like 14:30.";
      if (value < OPEN_TIME || value > CLOSE_TIME) return "Please choose a time between 10:00 AM and 5:00 PM.";

      // If the lesson is today, the time must still be in the future
      if (fields.date.value === todayString() && value <= nowTimeString()) {
        return "That time has already passed today. Please choose a later time.";
      }
      return "";
    },

    message: function (value) {
      value = value.trim();
      if (value === "") return "Please write a short message for the tutor.";
      if (value.length < MESSAGE_MIN) return "Message must be at least " + MESSAGE_MIN + " characters.";
      if (value.length > MESSAGE_MAX) return "Message must be " + MESSAGE_MAX + " characters or less.";
      if (!/\p{L}/u.test(value)) return "Message must contain some letters.";
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
    var eventName = (key === "subject" || key === "date" || key === "time") ? "change" : "input";
    fields[key].addEventListener(eventName, function () {
      if (fields[key].classList.contains("invalid")) {
        checkField(key);
      }
    });
  });

  // The "time already passed" rule depends on the date, so check the time again when the date changes
  fields.date.addEventListener("change", function () {
    if (fields.time.value !== "") checkField("time");
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

    // Everything is valid: tidy the message and stop double clicks
    fields.message.value = fields.message.value.trim();

    var button = form.querySelector("button[type='submit']");
    if (button) {
      button.disabled = true;
      button.textContent = "Sending...";
    }
  });
});