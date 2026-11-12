// add_subject.js - validation for admin/add_subject.php
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("addSubjectForm");
  if (!form) return; // page without the form, do nothing

  var input = document.getElementById("name");
  var errorBox = document.getElementById("nameError");
  var list = document.getElementById("subjectList"); // does not exist when there are no subjects yet

  // ---------- Regular expression ----------
  // 2 to 50 characters, must start with a letter.
  // Allowed: letters, numbers, spaces and & . + ( ) ' -   (for names like "Computer Science", "C++", "Math & Stats")
  var subjectPattern = /^\p{L}[\p{L}\p{M}0-9\s&.+()'-]{1,49}$/u;

  // Trim the value and turn repeated spaces into one space: "  Computer   Science " -> "Computer Science"
  function clean(value) {
    return value.trim().replace(/\s+/g, " ");
  }

  // Read the subjects already on the page, in lower case, so we can warn about duplicates
  function existingSubjects() {
    var names = [];
    if (!list) return names;
    list.querySelectorAll(".item-name").forEach(function (item) {
      names.push(clean(item.textContent).toLowerCase());
    });
    return names;
  }

  // ---------- Validator: returns an error message, or "" if the value is fine ----------
  function validate(value) {
    value = clean(value);

    if (value === "") return "Subject name is required.";
    if (value.length < 2) return "Subject name must be at least 2 characters.";
    if (value.length > 50) return "Subject name must be 50 characters or less.";
    if (!subjectPattern.test(value)) {
      return "Start with a letter. Use only letters, numbers, spaces and & . + ( ) ' -";
    }
    if (existingSubjects().indexOf(value.toLowerCase()) !== -1) {
      return "This subject already exists.";
    }
    return "";
  }

  // ---------- Show or clear the error ----------
  function showError(message) {
    if (errorBox) errorBox.textContent = message;
    input.classList.toggle("invalid", message !== "");
  }

  function checkField() {
    var message = validate(input.value);
    showError(message);
    return message === "";
  }

  // ---------- Live feedback ----------
  // Check when the user leaves the field
  input.addEventListener("blur", function () {
    checkField();
  });

  // Once the field was marked wrong, re-check while typing so the error disappears as soon as it is fixed
  input.addEventListener("input", function () {
    if (input.classList.contains("invalid")) {
      checkField();
    }
  });

  // ---------- On submit ----------
  form.addEventListener("submit", function (e) {
    if (!checkField()) {
      e.preventDefault(); // stop the form from being sent
      input.focus();
      return;
    }

    // Valid: send the cleaned value and stop double clicks
    input.value = clean(input.value);

    var button = form.querySelector("button[type='submit']");
    if (button) {
      button.disabled = true;
      button.textContent = "Adding...";
    }
  });
});