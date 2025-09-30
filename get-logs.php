<html>

<head>
    <title>ZK Test</title>
</head>

<body>
    <?php
    $filename = __DIR__ . '/ips.txt';
    if (file_exists($filename)) {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        $savedIp = isset($lines[0]) ? trim($lines[0]) : '';
        $savedCompany = isset($lines[1]) ? trim($lines[1]) : '';
        $savedBranchCode = isset($lines[2]) ? trim($lines[2]) : '';
    }else{
        $savedIp = '';
        $savedCompany = '';
        $savedBranchCode = '';
    }
   

    if (empty($savedIp) && empty($savedCompany) && empty($savedBranchCode)) {
        echo "Please set the IP Address, Company, and Branch Code in <a href='index.php'>the home page</a> first.";
        exit;
    }
  
    include("zklib/zklib.php");
    $zk = new ZKLib($savedIp, 4370);
    $ret = $zk->connect();
    sleep(1);
    // $attendance = $zk->getAttendance();
    // sleep(1);
    // var_dump('<pre>', json_encode($attendance));
    // die;

    // sleep(1);
    // if ( $ret ): 
    //     $zk->disableDevice();
    //     sleep(1);
    ?>

    <table border="1" cellpadding="5" cellspacing="2">
        <tr>
            <td><b>Status</b></td>
            <td>Connected</td>
            <td><b>Version</b></td>
            <td><?php echo $zk->version() ?></td>
            <td><b>OS Version</b></td>
            <td><?php echo $zk->osversion() ?></td>
            <td><b>Platform</b></td>
            <td><?php echo $zk->platform() ?></td>
        </tr>
        <tr>
            <td><b>Firmware Version</b></td>
            <td><?php echo $zk->fmVersion() ?></td>
            <td><b>WorkCode</b></td>
            <td><?php echo $zk->workCode() ?></td>
            <td><b>SSR</b></td>
            <td><?php echo $zk->ssr() ?></td>
            <td><b>Pin Width</b></td>
            <td><?php echo $zk->pinWidth() ?></td>
        </tr>
        <tr>
            <td><b>Face Function On</b></td>
            <td><?php echo $zk->faceFunctionOn() ?></td>
            <td><b>Serial Number</b></td>
            <td><?php echo $zk->serialNumber() ?></td>
            <td><b>Device Name</b></td>
            <td><?php echo $zk->deviceName(); ?></td>
            <td><b>Get Time</b></td>
            <td><?php echo $zk->getTime() ?></td>
        </tr>
    </table>
    <hr />

    <?php
    try {
        //$zk->setUser(1, '1', 'Admin', '', LEVEL_ADMIN);
        $user = $zk->getUser();
        sleep(1);
    } catch (Exception $e) {
        header("HTTP/1.0 404 Not Found");
        header('HTTP', true, 500); // 500 internal server error                
    }
    //$zk->clearAdmin();
    ?>
    <?php
    $date_minus_one = date('Y-m-d', strtotime('-1 day'));
    $attendance = $zk->getAttendance();
    sleep(1);

    if (empty($attendance)) {
        die("❌ No attendance data found.");
    }
    $name = "attendance_".$savedCompany."_". $savedBranchCode."_" . date("Ymd_His") . ".json";
    // storeLogs( $name . ".json", $attendance);
    $json_data = json_encode($attendance, JSON_PRETTY_PRINT);
    $b64 = base64_decode("dGhlYmVzdDEyMzQ1");
    // 🔑 Your password
    $password = $b64;

    // Encryption settings
    $cipher = "AES-256-CBC";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);

    // Encrypt JSON with password
    $encrypted_data = openssl_encrypt($json_data, $cipher, $password, 0, $iv);

    // Store IV + encrypted data (needed for decryption later)
    $final_data = base64_encode($iv . $encrypted_data);

    // Folder and file path
    $folder = "files/";

    $filename = $folder . $name;

    // Create folder if not exists
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
    chmod($folder, 0777);
    chmod($filename, 0777);


    // Save encrypted file
    if (file_put_contents($filename, $final_data) !== false) {
        echo "✅ Encrypted file saved: " . $filename . "<br>";
        echo "Total Records: " . count($attendance);
        echo '<br><a href="get_file_data.php?file=' . pathinfo($filename, PATHINFO_FILENAME) . '" target="_blank">View Decrypted Data</a><br><br>';
        include("Controller.php");
        $db = new Controller("db.txt");

        $count_inserted = 0;
        foreach ($attendance as $idx => $att) {

            $result = $db->insert($att['userid'], $att['state'], $att['timestamp']);
            if (!$result['error']) $count_inserted++;
            echo $result['message'] . "<br>";
        }
        $res =   $db->sendMail($filename,$savedCompany,$savedBranchCode);
        $count_inserted =  isset($_GET['send_email']) ? 1 : 0;
        if ($res == 1 && $count_inserted > 0) {
            echo "✅ Email sent successfully.";
        } else {
            echo "❌ Failed to send email.";
        }
    } else {
        echo "❌ Failed to save file.";
    }


    ?>
    <br>
    <br>
    <br>
    <br>
    <br>
    <a href="index.php" type="button">Back to HOME</a>
</body>

</html>