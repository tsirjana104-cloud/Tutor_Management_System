// my_bookings.js - filter, confirmation popups and auto-enabled "Mark as completed" for tutor/my_bookings.php
document.addEventListener("DOMContentLoaded", function () {
  var filter = document.getElementById("statusFilter");
  var table = document.getElementById("bookingTable");
  if (!filter || !table) return; // no bookings yet, so there is nothing to do

  var rows = table.querySelectorAll("tbody tr");

  // ---------- "Nothing found" message, shown when the filter hides every row ----------
  var emptyMessage = document.createElement("p");
  emptyMessage.className = "empty-text";
  emptyMessage.hidden = true;
  table.parentNode.insertAdjacentElement("afterend", emptyMessage); // right after the .table-wrap box

  // ---------- Status filter ----------
  function applyFilter() {
    var selected = filter.value; // all, pending, accepted, completed or rejected
    var visible = 0;

    rows.forEach(function (row) {
      var show = selected === "all" || row.dataset.status === selected;
      row.style.display = show ? "" : "none";
      if (show) visible++;
    });

    table.parentNode.style.display = visible === 0 ? "none" : "";
    emptyMessage.hidden = visible !== 0;
    emptyMessage.textContent = "No " + selected + " requests found.";
  }

  filter.addEventListener("change", applyFilter);

  // ---------- Confirm before accepting, rejecting or completing ----------
  // One listener on the table handles every action link (event delegation),
  // so it also works for the "Mark as completed" link that is created later by the timer below.
  table.addEventListener("click", function (e) {
    var link = e.target.closest("a.btn-small");
    if (!link) return;

    e.preventDefault(); // we decide after the popup

    var row = link.closest("tr");
    var student = row.cells[0].textContent.trim(); // 1st column: student name
    var subject = row.cells[2].textContent.trim(); // 3rd column: subject
    var date = row.cells[3].textContent.trim();    // 4th column: date
    var time = row.cells[4].textContent.trim().slice(0, 5); // 5th column: time, "16:00:00" -> "16:00"

    var options;
    if (link.classList.contains("reject")) {
      options = {
        title: "Reject this request?",
        message: "The " + subject + " request from " + student + " will be rejected. The student will be notified.",
        confirmText: "Yes, reject",
        variant: "danger"
      };
    } else if (link.classList.contains("complete-link")) {
      options = {
        title: "Mark as completed?",
        message: "Confirm that your " + subject + " lesson with " + student + " has taken place. The student can then leave a review.",
        confirmText: "Yes, completed",
        variant: "primary"
      };
    } else {
      options = {
        title: "Accept this request?",
        message: student + " wants a " + subject + " lesson on " + date + " at " + time + ". The student will be notified.",
        confirmText: "Yes, accept",
        variant: "primary"
      };
    }

    // Use the nice popup when it is loaded, the browser confirm otherwise
    if (typeof confirmModal !== "function") {
      if (confirm(options.title + " " + options.message)) window.location.href = link.href;
      return;
    }

    confirmModal(options).then(function (ok) {
      if (ok) window.location.href = link.href; // go on to your PHP action
    });
  });

  // ---------- Enable "Mark as completed" when the lesson time arrives ----------
  // PHP shows a grey button for lessons that have not started yet. This checks every 15 seconds
  // and swaps it for the real blue button the moment the time comes, with no page refresh.
  // (PHP still checks the time again on the server, so this is only for convenience.)
  function enableReadyButtons() {
    var now = Math.floor(Date.now() / 1000); // current time in seconds, same unit PHP uses
    var waiting = table.querySelectorAll(".btn-small.disabled[data-href]");

    if (waiting.length === 0) {
      clearInterval(timer); // nothing left to wait for
      return;
    }

    waiting.forEach(function (greyButton) {
      var row = greyButton.closest("tr");
      var lessonTime = Number(row.dataset.lessonTime);

      if (now >= lessonTime) {
        // Build the real link
        var link = document.createElement("a");
        link.className = "btn-small approve complete-link";
        link.href = greyButton.dataset.href;
        link.textContent = "Mark as completed";

        // Remove the "Available after ..." note under the grey button
        var note = greyButton.parentNode.querySelector(".block-text");
        if (note) note.remove();

        greyButton.replaceWith(link);
      }
    });
  }

  var timer = setInterval(enableReadyButtons, 15000);
  enableReadyButtons(); // also run once straight away
});