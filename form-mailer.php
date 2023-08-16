<?php
require_once __DIR__ . "/PHPMailer-master/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer-master/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;

if (!function_exists("dd")) {
  function dd(...$args) {
    foreach ($args as $arg) {
      var_dump($arg);
    }
    exit();
  }
}

class FormMailer {
  protected $form;
  protected $reload_url;
  protected $gmail_user;
  protected $gmail_password;
  protected $recaptcha_sitekey;
  protected $recaptcha_secretkey;

  public function __construct() {
    require_once __DIR__ . "/config.php";
    $this->reload_url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $this->gmail_user = $gmail_user;
    $this->gmail_password = $gmail_password;
    $this->recaptcha_sitekey = $recaptcha_sitekey;
    $this->recaptcha_secretkey = $recaptcha_secretkey;
  }

  public function handleSubmit() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      return;
    }
    $this->initForm();
    $this->validateRecaptcha();
    $this->sendEmail();
    $this->reload();
  }

  protected function reload() {
    $this->reload_url = $this->reload_url . "?success=true";
    http_response_code(200);
    header("Location: " . $this->reload_url);
    exit();
  }

  protected function initForm() {
    $this->form = $_POST;
    unset($this->form["g-recaptcha-response"]);
  }

  protected function validateRecaptcha() {
    $recaptcha_response = $_POST["g-recaptcha-response"] ?? null;
    if (!$recaptcha_response) {
      throw new Exception("Please check recaptcha");
    }
    $recaptcha_verify = json_decode(
      file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret=" .
          $this->recaptcha_secretkey .
          "&response=" .
          $recaptcha_response
      )
    );
    if ($recaptcha_verify->success !== true) {
      throw new Exception("Invalid recaptcha response");
    }
  }

  protected function getEmailBody() {
    $rows = [];
    foreach ($this->form as $key => $value) {
      $key = ucwords($key);
      $value = strip_tags($value);
      $rows[] = "<tr><th><strong>{$key}</strong></th><td>{$value}</td></tr>";
    }
    if ($rows) {
      $body =
        '<table rules="all" style="border-color: #666;" cellpadding="10">' .
        implode("", $rows) .
        "</table>";
    } else {
      $body = "[no_fields]";
    }
    return "<html><body>{$body}</body></html>";
  }

  protected function getEmailAltBody() {
    return json_encode($this->form, JSON_PRETTY_PRINT);
  }

  protected function sendEmail() {
    $to = $this->gmail_user;
    $from = $this->gmail_user;
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Port = 587;
    $mail->Username = $this->gmail_user;
    $mail->Password = $this->gmail_password;
    $mail->Subject = "Contact Form";
    $mail->addAddress($to);
    $mail->setFrom($from);
    $mail->isHTML(true);
    $mail->Body = $this->getEmailBody();
    $mail->AltBody = $this->getEmailAltBody();
    $mail->send();
  }
}