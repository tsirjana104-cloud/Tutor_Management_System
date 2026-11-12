// student_bookings.js - status filter for student/my_bookings.php
document.addEventListener("DOMContentLoaded", function () {
  var filter = document.getElementById("statusFilter");
  var table = document.getElementById("bookingTable");
  if (!filter || !table) return; // the student has no bookings yet, so there is nothing to filter

  var rows = table.querySelectorAll("tbody tr");
  var total = rows.length;

  // ---------- "Showing X of Y" text, placed at the right end of the filter bar ----------
  var counter = document.createElement("span");
  counter.className = "muted-text";
  counter.style.marginLeft = "auto";
  filter.parentNode.appendChild(counter);

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

    // Hide the whole table when nothing matches, and say so
    table.parentNode.style.display = visible === 0 ? "none" : "";
    emptyMessage.hidden = visible !== 0;
    emptyMessage.textContent = "You have no " + selected + " bookings.";

    counter.textContent = "Showing " + visible + " of " + total + (total === 1 ? " booking" : " bookings");
  }

  filter.addEventListener("change", applyFilter);
  applyFilter(); // set the counter text when the page loads

  // ---------- Coming back with the browser's Back button ----------
  // Browsers can restore the dropdown value without firing "change", so apply it again
  window.addEventListener("pageshow", applyFilter);
});