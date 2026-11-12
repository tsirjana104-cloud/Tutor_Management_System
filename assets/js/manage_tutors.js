// manage_tutors.js - status filter and confirmations for admin/manage_tutors.php
document.addEventListener("DOMContentLoaded", function () {
  var filter = document.getElementById("statusFilter");
  var table = document.getElementById("tutorTable");
  if (!filter || !table) return; // no tutors yet, so there is nothing to filter

  var rows = table.querySelectorAll("tbody tr");

  // ---------- "Nothing found" message, shown when the filter hides every row ----------
  var emptyMessage = document.createElement("p");
  emptyMessage.className = "empty-text";
  emptyMessage.hidden = true;
  table.parentNode.insertAdjacentElement("afterend", emptyMessage); // right after the .table-wrap box

  // ---------- Status filter ----------
  function applyFilter() {
    var selected = filter.value; // all, pending, approved or rejected
    var visible = 0;

    rows.forEach(function (row) {
      var show = selected === "all" || row.dataset.status === selected;
      row.style.display = show ? "" : "none";
      if (show) visible++;
    });

    // Hide the whole table when nothing matches, and say so
    table.parentNode.style.display = visible === 0 ? "none" : "";
    emptyMessage.hidden = visible !== 0;
    emptyMessage.textContent = "No " + selected + " tutors found.";
  }

  filter.addEventListener("change", applyFilter);

  // ---------- Confirm before approving or rejecting ----------
  // One listener on the table handles every Approve / Reject link (event delegation)
  table.addEventListener("click", function (e) {
    var link = e.target.closest("a.btn-small");
    if (!link) return;

    e.preventDefault(); // always stop the link first, we decide after the popup

    var row = link.closest("tr");
    var tutorName = row.cells[1].textContent.trim(); // 2nd column is the name
    var isReject = link.classList.contains("reject");

    var options = isReject
      ? {
          title: "Reject this tutor?",
          message: tutorName + " will not appear in student searches.",
          confirmText: "Yes, reject",
          variant: "danger"
        }
      : {
          title: "Approve this tutor?",
          message: tutorName + " will appear in student searches and can receive bookings.",
          confirmText: "Yes, approve",
          variant: "primary"
        };

    confirmModal(options).then(function (ok) {
      if (ok) {
        window.location.href = link.href; // go on to your PHP action
      }
    });
  });
});