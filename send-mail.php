<?php
require_once ('enums.php');
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

function send_mail(string $to, string $subject, string $body)
{
  $transport = Transport::fromDsn('smtp://d8ba60dccbb8fa:796aa6c769aca6@smtp.mailtrap.io:2525?encryption=tls&auth_mode=login');
  $mailer = new Mailer($transport);

  $email = (new Email())
    ->from(Address::create('Readme <437fb34c1e-9339cf+1@inbox.mailtrap.io>'))
    ->to(IS_TEST_MAIL ? '437fb34c1e-9339cf+1@inbox.mailtrap.io' : $to)
    ->subject($subject)
    ->html($body);

  $mailer->send($email);
}