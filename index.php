<!DOCTYPE html>
<html>
<head>
    <title>Send Money</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <style>
        body {
            background-color: black;
            color: rgba(20, 130, 65, 0.9);
            font-family: Montserrat, sans-serif;
        }

        .form-container {
            margin-top: 100px;
        }

        .form-container h2 {
            margin-bottom: 20px;
        }

        .navbar-custom {
            background-color: rgba(20, 130, 65, 0.9);
            color: white;
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .navbar-nav .nav-link {
            color: white;
        }

        .navbar-custom .btn-logout {
            background-color: black;
            color: white;
        }

        .btn-send-money {
            background-color: green;
        }
    </style>
</head>
<body>
 <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <a class="navbar-brand" href="#">
            E-wallet Logo
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Send Money</a>
                </li>
            </ul>
            <span class="navbar-text mr-3">
                Balance: Ksh 600
            </span>
            <span class="navbar-text mr-3">
                Username: abdihakimomar@gmail.com
            </span>
            <a class="btn btn-logout" href="#">
                Logout
            </a>
        </div>
    </nav>


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar content goes here -->
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            
            <div class="col-md-6">
                 
                <div class="form-container">
                    <h2>Send Money</h2>
                    
                     <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        // Initialize the variables
                        $consumer_key = 'Lms5EIf2gK16o1sptYPaA3HsfbGUd7fv';
                        $consumer_secret = 'dgfk1IefQx1SnG1A';
                        $Business_Code = '174379';
                        $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
                        $Type_of_Transaction = 'CustomerPayBillOnline';
                        $Token_URL = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
                        $OnlinePayment = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
                        $CallBackURL = 'https://abdihakimomar.com/payment.php';
                        $Time_Stamp = date("Ymdhis");

                        $phone_number = $_POST['phone_number'];
                        $total_amount = $_POST['amount'];
                        $password = base64_encode($Business_Code . $Passkey . $Time_Stamp);

                        // Generate authentication token
                        $curl_Tranfer = curl_init();
                        curl_setopt($curl_Tranfer, CURLOPT_URL, $Token_URL);
                        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
                        curl_setopt($curl_Tranfer, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
                        curl_setopt($curl_Tranfer, CURLOPT_HEADER, false);
                        curl_setopt($curl_Tranfer, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl_Tranfer, CURLOPT_SSL_VERIFYPEER, false);
                        $curl_Tranfer_response = curl_exec($curl_Tranfer);

                        $token = json_decode($curl_Tranfer_response)->access_token;

                        // Initiate STK push
                        $curl_Tranfer2 = curl_init();
                        curl_setopt($curl_Tranfer2, CURLOPT_URL, $OnlinePayment);
                        curl_setopt($curl_Tranfer2, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token));

                        $curl_Tranfer2_post_data = [
                            'BusinessShortCode' => $Business_Code,
                            'Password' => $password,
                            'Timestamp' => $Time_Stamp,
                            'TransactionType' => $Type_of_Transaction,
                            'Amount' => $total_amount,
                            'PartyA' => $phone_number,
                            'PartyB' => $Business_Code,
                            'PhoneNumber' => $phone_number,
                            'CallBackURL' => $CallBackURL,
                            'AccountReference' => 'E-wallet',
                            'TransactionDesc' => 'Payment of X',
                        ];

                        $data2_string = json_encode($curl_Tranfer2_post_data);

                        curl_setopt($curl_Tranfer2, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl_Tranfer2, CURLOPT_POST, true);
                        curl_setopt($curl_Tranfer2, CURLOPT_POSTFIELDS, $data2_string);
                        curl_setopt($curl_Tranfer2, CURLOPT_HEADER, false);
                        curl_setopt($curl_Tranfer2, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($curl_Tranfer2, CURLOPT_SSL_VERIFYHOST, 0);
                        $curl_Tranfer2_response = json_decode(curl_exec($curl_Tranfer2));

                        if ($curl_Tranfer2_response->ResponseCode == "0") {
                            $message = "Success. Request accepted for processing";
                            $alertClass = "alert-success";
                        } else {
                            $message = "Error. Request not accepted";
                            $alertClass = "alert-danger";
                        }
                        ?>
                             <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show mt-4" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                     
                    <?php } ?>
                     
                    <form method="POST" action="payment.php">
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" id="phone_number" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="text" class="form-control" name="amount" id="amount" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-send-money">Pay with M-Pesa</button>
                    </form>
                   
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
