// search_tutors.js - search form helpers for student/search_tutors.php
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("searchForm");
  if (!form) return; // page without the form, do nothing

  var subject = document.getElementById("subject_id");
  var locationInput = document.getElementById("location");
  var button = form.querySelector("button[type='submit']");
  var buttonText = button ? button.textContent : "Search";

  // ---------- Regular expression ----------
  // Location search: 1 to 60 characters. Letters, numbers, spaces and , . ' -
  var locationPattern = /^[\p{L}\p{M}0-9][\p{L}\p{M}0-9\s,.'-]{0,59}$/u;

  // The location box has no error spot in the HTML, so one is created right under it
  var errorBox = document.createElement("small");
  errorBox.className = "error-text";
  locationInput.insertAdjacentElement("afterend", errorBox);

  // Trim the value and turn repeated spaces into one space
  function clean(value) {
    return value.trim().replace(/\s+/g, " ");
  }

  // ---------- Validator: returns an error message, or "" if the value is fine ----------
  function validateLocation() {
    var value = clean(locationInput.value);
    if (value === "") return ""; // location is optional, an empty search is allowed
    if (!locationPattern.test(value)) {
      return "Use only letters, numbers and , . ' - (up to 60 characters).";
    }
    return "";
  }

  function checkLocation() {
    var message = validateLocation();
    errorBox.textContent = message;
    locationInput.classList.toggle("invalid", message !== "");
    return message === "";
  }

  // ---------- Live feedback ----------
  locationInput.addEventListener("blur", checkLocation);

  // Once the box was marked wrong, re-check while typing so the error disappears as soon as it is fixed
  locationInput.addEventListener("input", function () {
    if (locationInput.classList.contains("invalid")) {
      checkLocation();
    }
  });

  // ---------- Search again straight away when the subject changes ----------
  subject.addEventListener("change", function () {
    if (typeof form.requestSubmit === "function") {
      form.requestSubmit(); // goes through the submit check below
    } else {
      form.submit();
    }
  });

  // ---------- On submit ----------
  form.addEventListener("submit", function (e) {
    if (!checkLocation()) {
      e.preventDefault(); // stop the form from being sent
      locationInput.focus();
      return;
    }

    // Valid: send the cleaned value and show that something is happening
    locationInput.value = clean(locationInput.value);

    if (button) {
      button.disabled = true;
      button.textContent = "Searching...";
    }
  });

  // ---------- Coming back with the browser's Back button ----------
  // The page can be restored from memory with the button still disabled, so reset it
  window.addEventListener("pageshow", function () {
    if (button) {
      button.disabled = false;
      button.textContent = buttonText;
    }
  });
});