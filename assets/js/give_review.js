// give_review.js - star rating and validation for student/give_review.php
document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("reviewForm");
  if (!form) return; // the form is not shown after a review was sent, so do nothing

  var ratingSelect = document.getElementById("rating");
  var comment = document.getElementById("comment");
  var ratingError = document.getElementById("ratingError");
  var commentError = document.getElementById("commentError");

  var COMMENT_MIN = 10;
  var COMMENT_MAX = 500;

  // ---------- Regular expressions ----------
  var ratingPattern = /^[1-5]$/;   // the rating must be exactly 1, 2, 3, 4 or 5
  var letterPattern = /\p{L}/u;    // the comment must contain at least one letter

  // ---------- Styles for the stars (added here so the page does not depend on style.css) ----------
  var style = document.createElement("style");
  style.textContent =
    ".star-rating { display: flex; align-items: center; gap: 2px; }" +
    ".star-btn { padding: 0 2px; font-size: 34px; line-height: 1; color: #cbd5e1; background: none; border: none; cursor: pointer; }" +
    ".star-btn.active { color: #f59e0b; }" +
    ".star-btn:focus-visible { outline: 3px solid rgba(37, 99, 235, 0.35); border-radius: 4px; }" +
    ".star-rating.invalid .star-btn:not(.active) { color: #fca5a5; }" +
    ".star-label { margin-left: 10px; font-size: 14px; color: #64748b; }";
  document.head.appendChild(style);

  // ---------- Turn the dropdown into stars ----------
  // The select stays in the form (hidden) so your PHP still receives "rating" exactly as before.
  // An empty first option is added so the student must choose a rating on purpose, instead of
  // sending the default "1" by accident.
  var placeholder = new Option("Select rating", "", true, true);
  ratingSelect.insertBefore(placeholder, ratingSelect.firstChild);
  ratingSelect.value = "";
  ratingSelect.style.display = "none";

  var starBox = document.createElement("div");
  starBox.className = "star-rating";
  starBox.setAttribute("role", "radiogroup");
  starBox.setAttribute("aria-label", "Rating");

  var stars = [];
  for (var i = 1; i <= 5; i++) {
    var star = document.createElement("button");
    star.type = "button"; // a star must never submit the form
    star.className = "star-btn";
    star.textContent = "\u2605"; // the star character
    star.dataset.value = String(i);
    star.setAttribute("aria-label", i + (i === 1 ? " star" : " stars"));
    starBox.appendChild(star);
    stars.push(star);
  }

  var starLabel = document.createElement("span");
  starLabel.className = "star-label";
  starBox.appendChild(starLabel);

  ratingSelect.insertAdjacentElement("afterend", starBox);

  // Colour the first n stars. Also write the text of the chosen option next to them ("4 - Very good")
  function paint(n) {
    stars.forEach(function (s, index) {
      s.classList.toggle("active", index < n);
    });
  }

  function showSelected() {
    var chosen = Number(ratingSelect.value) || 0;
    paint(chosen);
    starLabel.textContent = chosen ? ratingSelect.options[ratingSelect.selectedIndex].textContent : "Click a star to rate";
  }

  stars.forEach(function (s) {
    // Preview while hovering
    s.addEventListener("mouseenter", function () {
      paint(Number(s.dataset.value));
    });

    // Choose the rating
    s.addEventListener("click", function () {
      ratingSelect.value = s.dataset.value;
      showSelected();
      checkRating(); // clears the error straight away
    });
  });

  starBox.addEventListener("mouseleave", showSelected); // go back to the chosen rating
  showSelected();

  // ---------- Live character counter under the comment ----------
  var counter = document.createElement("small");
  counter.className = "muted-text block-text";
  comment.insertAdjacentElement("afterend", counter);

  function updateCounter() {
    counter.textContent = comment.value.trim().length + " / " + COMMENT_MAX + " characters (optional, minimum " + COMMENT_MIN + " if you write one)";
  }
  updateCounter();
  comment.addEventListener("input", updateCounter);

  // ---------- Validators: return an error message, or "" if the value is fine ----------
  function ratingMessage() {
    if (ratingSelect.value === "") return "Please select a rating from 1 to 5 stars.";
    if (!ratingPattern.test(ratingSelect.value)) return "Invalid rating.";
    return "";
  }

  function commentMessage() {
    var value = comment.value.trim();
    if (value === "") return ""; // the comment is optional
    if (value.length < COMMENT_MIN) return "A comment must be at least " + COMMENT_MIN + " characters, or leave it empty.";
    if (value.length > COMMENT_MAX) return "Comment must be " + COMMENT_MAX + " characters or less.";
    if (!letterPattern.test(value)) return "Comment must contain some letters.";
    return "";
  }

  function checkRating() {
    var message = ratingMessage();
    if (ratingError) ratingError.textContent = message;
    starBox.classList.toggle("invalid", message !== "");
    return message === "";
  }

  function checkComment() {
    var message = commentMessage();
    if (commentError) commentError.textContent = message;
    comment.classList.toggle("invalid", message !== "");
    return message === "";
  }

  // ---------- Live feedback for the comment ----------
  comment.addEventListener("blur", checkComment);
  comment.addEventListener("input", function () {
    if (comment.classList.contains("invalid")) checkComment();
  });

  // ---------- On submit ----------
  form.addEventListener("submit", function (e) {
    var ratingOk = checkRating();
    var commentOk = checkComment();

    if (!ratingOk || !commentOk) {
      e.preventDefault(); // stop the form from being sent
      if (!ratingOk) stars[0].focus(); else comment.focus();
      return;
    }

    // Valid: send the trimmed comment and stop double clicks
    comment.value = comment.value.trim();

    var button = form.querySelector("button[type='submit']");
    if (button) {
      button.disabled = true;
      button.textContent = "Submitting...";
    }
  });

  // ---------- Coming back with the browser's Back button ----------
  window.addEventListener("pageshow", showSelected);
});