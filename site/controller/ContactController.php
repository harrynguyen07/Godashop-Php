<?php


class ContactController
{
    //hiển thị form liên hệ
    function form()
    {
        require ABSPATH_SITE .  'view/contact/form.php';
    }

    function sendEmail()
    {
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $message = $_POST['content'];
        $emailService = new EmailService();
        $to = SHOP_OWNER;
        $subject = 'Godashop: Liên hệ';
        $website = get_domain();
        $content = "
        Chào chủ cửa hàng, <br>
        Dưới đây là thông tin khách hàng liên hệ: <br>
        Tên: $fullname <br>
        SDT:  $mobile <br>
        Email:$email <br>
        Nội dung:$message <br>
        Được gửi từ trang web: $website
        ";
        if ($emailService->send($to, $subject, $content)) {
            echo 'Đã gửi mail thành công';
        } else {
            echo $emailService->message;
        }
    }
}
