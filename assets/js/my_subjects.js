// my_subjects.js - validation and remove-confirmation for tutor/my_subjects.php
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("mySubjectForm");
  if (!form) return; // page without the form, do nothing

  var select = document.getElementById("subject_id");
  var errorBox = document.getElementById("subjectError");
  var list = document.getElementById("mySubjectList"); // does not exist when the tutor has no subjects yet
  var submitting = false;

  // Tidy a text so "  Computer   Science " and "computer science" compare as equal
  function clean(text) {
    return text.trim().replace(/\s+/g, " ").toLowerCase();
  }

  // Names of the subjects this tutor already teaches (read from the list on the page)
  function taughtSubjects() {
    var names = [];
    if (!list) return names;
    list.querySelectorAll(".item-name").forEach(function (item) {
      names.push(clean(item.textContent));
    });
    return names;
  }

  // ---------- Mark the subjects already added, so they cannot be picked again ----------
  var taught = taughtSubjects();
  Array.prototype.forEach.call(select.options, function (option) {
    if (option.value !== "" && taught.indexOf(clean(option.textContent)) !== -1) {
      option.disabled = true;
      option.textContent = option.textContent.trim() + " (already added)";
    }
  });

  // ---------- Validator: returns an error message, or "" if the value is fine ----------
  function validate() {
    var value = select.value;

    if (value === "") return "Please select a subject.";
    if (!/^\d+$/.test(value)) return "Invalid subject selected."; // option values are database ids (numbers)

    var chosen = select.options[select.selectedIndex];
    if (chosen.disabled) return "You already teach this subject.";

    return "";
  }

  function showError(message) {
    if (errorBox) errorBox.textContent = message;
    select.classList.toggle("invalid", message !== "");
  }

  function checkField() {
    var message = validate();
    showError(message);
    return message === "";
  }

  // ---------- Live feedback ----------
  select.addEventListener("blur", checkField);
  select.addEventListener("change", checkField);

  // ---------- On submit ----------
  form.addEventListener("submit", function (e) {
    if (submitting || !checkField()) {
      e.preventDefault(); // stop the form from being sent (invalid, or already sent once)
      select.focus();
      return;
    }

    submitting = true; // blocks a second click

    // IMPORTANT: the button is NOT disabled here. Your PHP looks for $_POST['add_subject'],
    // which only arrives if the button is still enabled when the form is sent.
    var button = form.querySelector("button[type='submit']");
    if (button) button.textContent = "Adding...";
  });

  // ---------- Confirm before removing a subject ----------
  if (list) {
    list.addEventListener("click", function (e) {
      var link = e.target.closest("a.remove-link");
      if (!link) return;

      e.preventDefault(); // we decide after the popup

      var subjectName = link.closest("li").querySelector(".item-name").textContent.trim();

      // Use the nice popup when it is loaded, the browser confirm otherwise
      if (typeof confirmModal !== "function") {
        if (confirm("Remove " + subjectName + " from your subjects?")) window.location.href = link.href;
        return;
      }

      confirmModal({
        title: "Remove this subject?",
        message: subjectName + " will be removed from the subjects you teach. Students will no longer find you under it.",
        confirmText: "Yes, remove",
        variant: "danger"
      }).then(function (ok) {
        if (ok) window.location.href = link.href; // go on to your PHP action
      });
    });
  }
});