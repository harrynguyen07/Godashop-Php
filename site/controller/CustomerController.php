<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CustomerController
{
    public $customer;
    public $customerRepository;
    function __construct()
    {
        $email = $_SESSION['email'] ?? null;
        $this->customerRepository = new CustomerRepository();
        if ($email) {

            $this->customer = $this->customerRepository->findEmail($email);
        }
    }

    function checkLogin()
    {
        // Điều hướng về trang chủ nếu chưa Login
        if (empty($_SESSION['email'])) {
            header('location:/');
            exit;
        }
    }

    // tạo tài khoản người dùng
    function register()
    {
        // Check google recapcha
        $gRecaptchaResponse = $_POST['g-recaptcha-response'];
        $secret = GOOGLE_RECAPTCHA_SECRET;
        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $resp = $recaptcha->setExpectedHostname('godashop.com')
            ->verify($gRecaptchaResponse, '127.0.0.1');
        if (!$resp->isSuccess()) {
            // !Verified!
            $errors = $resp->getErrorCodes();
            // implode nối các phần tử trong array lại thành chuỗi
            $error = implode('br', $errors);
            $_SESSION['error'] = 'Error: ' . $error;
            header('location:/');
        }



        $data["name"] = $_POST['fullname'];
        $data["password"] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $data["mobile"] = $_POST['mobile'];
        $data["email"] = $_POST['email'];
        $data["login_by"] = 'form';
        $data["shipping_name"] = $_POST['fullname'];
        $data["shipping_mobile"] = $_POST['mobile'];
        $data["ward_id"] = NULL;
        $data["is_active"] = 0;
        $data["housenumber_street"] = '';


        $customerRepository = new CustomerRepository();
        $customerRepository->save($data);
        // Gởi mail active account
        $emailService = new Emailservice();
        $to = $_POST['email'];
        $subject = 'Gadashop -Verify your mail';
        $payload = [
            'email' => $to
        ];
        $token = JWT::encode($payload, JWT_KEY, 'HS256');
        $linkActive = get_domain_site() . '?c=customer&a=active&token=' . $token;
        $name = $data["name"];
        $website = get_domain();
        $content = "
        Dear $name, <br>
        Vui lòng click vào link bên dưới để active account<br>
        <a href='$linkActive'>Active Account</a><br>
        ------------<br>
        Được gửi từ $website
        ";
        $emailService->send($to, $subject, $content);



        $_SESSION['success'] = 'đã đăng ký thành công. Vui lòng kích hoạt tài khoản';
        header('location:/');
    }

    function notExistingEmail()
    {
        // nếu email tồn tại trong hệ thống thì echo false;
        // ngược lại echo true
        $email = $_GET['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        if (!empty($customer)) {
            echo 'false';
            return;
        }
        echo 'true';
    }

    function encode()
    {
        $key = 'example_key';
        $payload = [
            'email' => 'abc@gmail.com'
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');
        echo $jwt;
    }

    function decode()
    {
        $key = 'example_key';
        $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImFiY0BnbWFpbC5jb20ifQ.Kov91WXH8LQp07YNnxGAs5dZVXYDjwND25akWZG_yTQ';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        var_dump($decoded);
    }


    function active()
    {
        $token = $_GET['token'];
        $decoded = JWT::decode($token, new Key(JWT_KEY, 'HS256'));
        $email = $decoded->email;
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        $customer->setIsActive(1);
        $customerRepository->update($customer);
        $_SESSION['success'] = 'Đã kích hoạt tài khoản thành công';
        header('location:/');
    }

    // Hiển thị thông tin tài khoản
    function show()
    {
        $this->checkLogin();
        $customer = $this->customer;
        require ABSPATH_SITE .  'view/customer/show.php';
    }

    // Hiển thị thông tin giao hàng mặc định
    function shippingDefault()
    {
        $this->checkLogin();
        $customer = $this->customer;
        require ABSPATH_SITE .  'layout/variable_address.php';
        require ABSPATH_SITE .  'view/customer/shippingDefault.php';
    }

    function updateShippingDefault()
    {
        // Cập nhật giá trị mới vào object customer
        $customer = $this->customer;
        $customer->setShippingName($_POST['fullname']);
        $customer->setShippingMobile($_POST['moblie']);
        $customer->setWardId($_POST['ward']);
        $customer->setHousenumberStreet($_POST['address']);
        //Lưu xuống database
        if ($this->customerRepository->update($customer)) {
            // update session
            $_SESSION['success'] = 'Đã cập nhật địa chỉ giao hàng mặc định thành công';
            header('location: ?c=customer&a=shippingDefault');
            exit;
        }
        $_SESSION['error'] = $this->customerRepository->getError();
        header('location: ?c=customer&a=shippingDefault');
    }

    // Hiển thị đơn hàng của người đăng nhập
    function orders()
    {
        $this->checkLogin();
        $customer_Id = $this->customer->getId();
        $orderRepository = new OrderRepository();
        $orders = $orderRepository->getByCustomerId($customer_Id);
        require ABSPATH_SITE .  'view/customer/orders.php';
    }

    // Hiển thị chi tiết đơn hàng
    function orderDetail()
    {
        $id = $_GET['id'];
        $orderRepository = new OrderRepository();
        $order = $orderRepository->find($id);
        $this->checkLogin();
        require ABSPATH_SITE .  'view/customer/orderDetail.php';
    }


    function updateInfo()
    {
        $this->checkLogin();
        $name = $_POST['fullname'];
        $mobile = $_POST['mobile'];
        $email = $_SESSION['email'];

        $customer = $this->customer;

        $customer->setName($name);
        $customer->setMobile($mobile);
        // Nếu người dùng nhập mật khẩu hiện tại và mật khẩu mới
        $current_password = $_POST['current_password'];
        $password = $_POST['password'];
        if ($current_password && $password) {
            // Kiểm tra mật khẩu hiện tại đúng không
            if (!password_verify($current_password, $customer->getPassword())) {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng';
                header('location: ?c=customer&a=show');
                exit;
            }
            // Cập nhật mật khẩu mới
            $encode_password = password_hash($password, PASSWORD_BCRYPT);
            $customer->setPassword($encode_password);
        }

        if ($this->customerRepository->update($customer)) {
            // update session
            $_SESSION['name'] = $name;
            $_SESSION['success'] = 'Đã cập nhật thông tin thành công';
            header('location: ?c=customer&a=show');
            exit;
        }
        $_SESSION['error'] = $this->customerRepository->getError();
        header('location: ?c=customer&a=show');
    }

    function forgotPassword()
    {
        $email = $_POST['email'];
        $customer = $this->customerRepository->findEmail($email);
        if (empty($customer)) {
            $_SESSION['error'] = "email không tồn tại";
            header('location: /');
            exit;
        }

        $emailService = new EmailService();
        $name = $customer->getName();
        $to = $email;
        $payload = [
            'email' => $to
        ];
        $token = JWT::encode($payload, JWT_KEY, 'HS256');

        $url_reset_password = get_domain_site() . '?c=customer&a=resetPassword&token=' . $token;
        $link_reset_password = "<a href = '$url_reset_password'> Reset Password </a>";
        $subject = "Godashop: Reset password";
        $content = "
        Xin chào $email, <br>
        Vui long click vào link bên dưới để reset password <br>
        $link_reset_password
        ";
        if ($emailService->send($to, $subject, $content)) {
            $_SESSION['success'] = "Vui lòng check mail để reset password";
            header('location: /');
            exit;
        }

        $_SESSION['error'] = $emailService->message;
        header('location: /');
    }


    function resetPassword()
    {
        $token = $_GET['token'];
        require ABSPATH_SITE .  'view/customer/resetPassword.php';
    }

    function updatePassword()
    {
        $token = $_POST['token'];
        $password = $_POST['password'];
        $decoded = JWT::decode($token, new Key(JWT_KEY, 'HS256'));
        $email = $decoded->email;
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        $encodePassword = password_hash($password, PASSWORD_BCRYPT);
        $customer->setPassword($encodePassword);

        if ($this->customerRepository->update($customer)) {
            // update session
            $_SESSION['success'] = 'Đã reset password thành công';
            header('location: /');
            exit;
        }
        $_SESSION['error'] = $this->customerRepository->getError();
        header('location: /');
    }
}
