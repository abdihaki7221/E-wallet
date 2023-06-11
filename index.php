<?php
// Initialize the variables
$consumer_key = 'Lms5EIf2gK16o1sptYPaA3HsfbGUd7fv';
$consumer_secret = 'dgfk1IefQx1SnG1A';
$Business_Code = '6392494';
$Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$Type_of_Transaction = 'CustomerPayBillOnline';
$Token_URL = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
$OnlinePayment = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
$CallBackURL = '';
$Time_Stamp = date("Ymdhis");

// Database credentials
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "e-wallet";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        'PartyA' => '8583900',
        'PartyB' => $Business_Code,
        'PhoneNumber' => $phone_number,
        'CallBackURL' => $CallBackURL,
        'AccountReference' => 'Hillary',
        'TransactionDesc' => 'Test',
    ];

    $data2_string = json_encode($curl_Tranfer2_post_data);

    curl_setopt($curl_Tranfer2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_Tranfer2, CURLOPT_POST, true);
    curl_setopt($curl_Tranfer2, CURLOPT_POSTFIELDS, $data2_string);
    curl_setopt($curl_Tranfer2, CURLOPT_HEADER, false);
    curl_setopt($curl_Tranfer2, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl_Tranfer2, CURLOPT_SSL_VERIFYHOST, 0);
    $curl_Tranfer2_response = json_decode(curl_exec($curl_Tranfer2));

    // Check if the response is successful
    if ($curl_Tranfer2_response->Body->stkCallback->ResultCode == 0) {
        // Insert data into the database
        $mpesaReceiptNumber = $curl_Tranfer2_response->Body->stkCallback->CallbackMetadata->Item[1]->Value;
        $amount = $curl_Tranfer2_response->Body->stkCallback->CallbackMetadata->Item[0]->Value;

        $sql = "INSERT INTO transaction_Table (timestamp, MpesaReceiptNumber, amount, phonenumber) VALUES (NOW(), '$mpesaReceiptNumber', '$amount', '$phone_number')";

        if ($conn->query($sql) === true) {
            echo "Transaction recorded successfully.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Transaction failed.";
    }
}

$conn->close();
?>

<!-- HTML form for user input -->
<form method="POST" action="index.php">
    <input type="text" name="phone_number" placeholder="Phone Number" required>
    <input type="text" name="amount" placeholder="Amount" required>
    <button type="submit">Pay with M-Pesa</button>
</form>
