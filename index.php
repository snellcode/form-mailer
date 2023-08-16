<?php
require_once __DIR__ . "/form-mailer.php";
$error_message = null;
$success = $_GET["success"] ?? null === "true";
try {
  $fm = new FormMailer();
  $fm->handleSubmit();
} catch (Exception $e) {
  $error_message = $e->getMessage();
}
?>
<html>
  <head>
    <title>Form to Mail Example</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  </head>
  <body>

    <?php if ($error_message): ?>
    <div class="error-message">
      <?= $error_message ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="success-message">
      Thanks, your form has been sent.
    </div>
    <?php endif; ?>

    <form method="POST">

      <div class="field field-name">
        <label>Name:
          <input type="text" name="name"
          value="<?= $_POST["name"] ?? "" ?>" />
        </label>
      </div>

      <div class="field field-email">
        <label>Email:
          <input type="email" name="email"
          value="<?= $_POST["email"] ?? "" ?>" />
        </label>
      </div>

      <div class="field field-message">
        <label>Message:
          <textarea name="message"><?= $_POST["message"] ?? "" ?></textarea>
        </label>
      </div>

      <div class="g-recaptcha" data-sitekey="<?= $fm->recaptcha_sitekey ?>"></div>

      <button type="submit">Send</button>
    </form>
  </body>
</html>
