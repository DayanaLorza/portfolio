<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function respond(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['message' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, 'Method not allowed.');
}

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$origin = strtolower((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
if ($origin !== '' && $host !== '' && parse_url($origin, PHP_URL_HOST) !== preg_replace('/:\d+$/', '', $host)) {
    respond(403, 'Request could not be verified.');
}

if (!empty($_POST['website'])) {
    respond(200, 'Thanks—your message has been received.');
}

$lastSubmission = (int) ($_SESSION['last_contact_submission'] ?? 0);
if (time() - $lastSubmission < 30) {
    respond(429, 'Please wait a moment before sending another message.');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$inquiryType = trim((string) ($_POST['subject'] ?? 'General inquiry'));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) > 100) {
    respond(422, 'Please enter your name.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 160) {
    respond(422, 'Please enter a valid email address.');
}
if (mb_strlen($phone) > 40 || mb_strlen($company) > 120 || mb_strlen($message) < 20 || mb_strlen($message) > 4000) {
    respond(422, 'Please enter a message between 20 and 4,000 characters.');
}

$allowedSubjects = ['Contract opportunity', 'Project review', 'Consulting inquiry', 'General inquiry'];
if (!in_array($inquiryType, $allowedSubjects, true)) {
    respond(422, 'Please select a valid inquiry type.');
}

$cleanName = preg_replace('/[\r\n]+/', ' ', $name);
$cleanPhone = preg_replace('/[^0-9+(). x-]/', '', $phone);
$cleanCompany = preg_replace('/[\r\n]+/', ' ', $company);
$subject = $inquiryType . ' from ' . $cleanName;
$body = "New portfolio inquiry\n\n"
    . "Name: {$cleanName}\n"
    . "Email: {$email}\n"
    . "Phone: " . ($cleanPhone !== '' ? $cleanPhone : 'Not provided') . "\n"
    . "Company: " . ($cleanCompany !== '' ? $cleanCompany : 'Not provided') . "\n\n"
    . "Message:\n{$message}\n";

$recipient = trim((string) getenv('PORTFOLIO_CONTACT_RECIPIENT'));
$fromAddress = trim((string) getenv('PORTFOLIO_CONTACT_FROM'));
if (!filter_var($recipient, FILTER_VALIDATE_EMAIL) || !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
    respond(503, 'The contact form is not configured yet.');
}

$headers = [
    'From: Portfolio Contact <' . $fromAddress . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
];

if (!mail($recipient, $subject, $body, implode("\r\n", $headers))) {
    respond(500, 'The message could not be sent right now. Please try again later.');
}

$_SESSION['last_contact_submission'] = time();
respond(200, 'Thanks—your message has been sent.');
