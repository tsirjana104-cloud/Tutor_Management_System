<?php
// Change-password card (form only), shared by the admin, tutor and student pages.
// The page must include change_password_logic.php at the top first, which sets $message and $messageType.
$message = $message ?? '';
$messageType = $messageType ?? '';
?><div class="auth-card wide">
  <h1>Change password</h1>
  <p class="subtitle">Choose a password you do not use anywhere else.</p>

  <?php if ($message != ''): ?>
    <div class="message <?php echo $messageType; ?>">
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <form method="POST" id="changePasswordForm" novalidate>

    <div class="form-group">
      <label for="current_password">Current password</label>
      <input type="password" id="current_password" name="current_password"
             autocomplete="current-password" placeholder="Your current password" required>
      <small class="error-text" id="currentError"></small>
    </div>

    <div class="form-group">
      <label for="new_password">New password</label>
      <input type="password" id="new_password" name="new_password"
             autocomplete="new-password" placeholder="6+ characters, with a letter and a number" required>
      <small class="error-text" id="newError"></small>
    </div>

    <div class="form-group">
      <label for="confirm_password">Confirm new password</label>
      <input type="password" id="confirm_password" name="confirm_password"
             autocomplete="new-password" placeholder="Type the new password again" required>
      <small class="error-text" id="confirmError"></small>
    </div>

    <button type="submit" class="btn">Change password</button>
  </form>
</div>