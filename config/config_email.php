<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendBookingEmail($data)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nisrinafirdaus02@gmail.com'; 
        $mail->Password = 'lurk svtg ihli uyhr';   // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email pengirim
        $mail->setFrom('nisrinafirdaus02@gmail.com', 'Studio Musik');

        // Email admin
       $mail->addAddress('nisrinafirdaus02@gmail.com');


        $mail->isHTML(true);
        $mail->Subject = 'Booking Baru Masuk';

        $mail->Body = "
            <h2>Ada Booking Baru!</h2>
            <p><b>ID Order:</b> {$data['id_order']}</p>
            <p><b>Nama:</b> {$data['nama']}</p>
            <p><b>Studio:</b> {$data['id_studio']}</p>
            <p><b>Tanggal:</b> {$data['tanggal']}</p>
            <p><b>Jam:</b> {$data['jam_booking']}</p>
            <p><b>Paket:</b> {$data['paket']}</p>
            <p><b>Total Tagihan:</b> Rp " . number_format($data['total'], 0, ',', '.') . "</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
