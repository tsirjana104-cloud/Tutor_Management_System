<?php
// User menu in the top bar: profile icon + name, opens a dropdown with "Change password".
// Included by admin_navbar.php, tutor_navbar.php and student_navbar.php.
// Its CSS is inside this file on purpose, so the menu always looks right together with its markup.
$activePage = $activePage ?? '';
?>
<style>
  .user-menu { position: relative; }

  .user-menu summary {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 10px 4px 4px;
    list-style: none;
    color: #fff;
    cursor: pointer;
    border-radius: 24px;
  }

  .user-menu summary::-webkit-details-marker { display: none; }

  .user-menu summary:hover,
  .user-menu[open] summary { background: rgba(255, 255, 255, 0.15); }

  .user-menu .user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    color: #fff;
    background: var(--blue, #2563eb);
    border: 1px solid #93c5fd;
    border-radius: 50%;
  }

  .user-menu .user-name { font-size: 14px; }

  .user-menu .user-caret { transition: transform 0.15s ease; }
  .user-menu[open] .user-caret { transform: rotate(180deg); }

  .user-menu .user-panel {
    position: absolute;
    top: 46px;
    right: 0;
    z-index: 10;
    min-width: 210px;
    overflow: hidden;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(30, 58, 138, 0.25);
  }

  .user-menu .user-panel a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    font-size: 14px;
    color: #1e293b;
    text-decoration: none;
  }

  .user-menu .user-panel a:hover { background: #eff6ff; }
  .user-menu .user-panel a.current { font-weight: bold; color: #2563eb; }

  /* On phones only the icon and the arrow stay */
  @media (max-width: 700px) {
    .user-menu .user-name { display: none; }
  }
</style>

<details class="user-menu" id="userMenu">
  <summary aria-label="Account menu">
    <span class="user-avatar">
      <!-- profile icon -->
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
    </span>
    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
    <svg class="user-caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <polyline points="6 9 12 15 18 9"></polyline>
    </svg>
  </summary>

  <div class="user-panel">
    <a href="change_password.php" class="<?php echo $activePage == 'password' ? 'current' : ''; ?>">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="11" width="18" height="11" rx="2"></rect>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
      </svg>
      Change password
    </a>
  </div>
</details>