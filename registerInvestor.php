<?php
//connecting to database 
$host = 'localhost';
$dbname = 'vukainvestment_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Required field validation
    if (
        empty($_POST['full_name']) ||
        empty($_POST['email']) ||
        empty($_FILES['id_copy']) ||
        empty($_FILES['passport_photo'])
    ) {
        echo 'Missing required fields.';
        exit;
    }

    // Upload handling
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $idCopyName = uniqid() . '_' . basename($_FILES['id_copy']['name']);
    $photoName = uniqid() . '_' . basename($_FILES['passport_photo']['name']);

    $idCopyPath = $uploadDir . $idCopyName;
    $photoPath = $uploadDir . $photoName;

    move_uploaded_file($_FILES['id_copy']['tmp_name'], $idCopyPath);
    move_uploaded_file($_FILES['passport_photo']['tmp_name'], $photoPath);

    // Prepare & execute insert statement
    $stmt = $pdo->prepare("
        INSERT INTO `investors`(`fullname`, `email`, `nationality`, `dob`, `state`, 
        `address`, `mobile`, `passport_number`, `investment_exp_amount`, `investment_plan`, 
        `source_of_funds`, `payment_method`, `payout_bank`, `payout_account_name`, 
        `payout_account_number`, `bank_branch`, `payout_mpesa`, `kyc_upload_passport`, `kyc_upload_photo`)
         VALUES (
            :full_name, :id_number, :dob, :nationality, :state, :phone, :email, :address,
            :investment_amount, :investment_plan, :source_of_funds, :payment_method,
            :bank_name, :account_name, :account_number, :branch, :mpesa_payout,
            :id_copy_path, :passport_photo_path
        )
    ");

    $res = $stmt->execute([
        ':full_name' => $_POST['full_name'],
        ':id_number' => $_POST['id_number'],
        ':dob' => $_POST['dob'],
        ':nationality' => $_POST['nationality'],
        ':state' => $_POST['state'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':address' => $_POST['address'],
        ':investment_amount' => $_POST['investment_amount'],
        ':investment_plan' => $_POST['investment_plan'],
        ':source_of_funds' => $_POST['source_of_funds'],
        ':payment_method' => $_POST['payment_method'],
        ':bank_name' => $_POST['bank_name'],
        ':account_name' => $_POST['account_name'],
        ':account_number' => $_POST['account_number'],
        ':branch' => $_POST['branch'],
        ':mpesa_payout' => $_POST['mpesa_payout'],
        ':id_copy_path' => $idCopyPath,
        ':passport_photo_path' => $photoPath
    ]);
    if($res) {
        echo "success";
    }

} else {
    echo 'Invalid request';
}
?>
