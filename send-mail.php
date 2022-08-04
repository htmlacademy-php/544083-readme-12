<?php
require_once ('config.php');
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

function send_mail(string $to, string $subject, string $body)
{
  $transport = Transport::fromDsn(SMTP);
  $mailer = new Mailer($transport);

  $email = (new Email())
    ->from(Address::create(MAIL_FROM_CAPTION))
    ->to(IS_TEST_MAIL ? TEST_MAIL : $to)
    ->subject($subject)
    ->html($body);

  $mailer->send($email);
}