<html>

<head>
    <title>ZK Test</title>
</head>

<body>
    <?php
    include("zklib/zklib.php");

    $zk = new ZKLib("172.0.0.199", 4370);
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
    <table border="1" cellpadding="5" cellspacing="2" style="float: left; margin-right: 10px;">
        <tr>
            <th colspan="5">Data User</th>
        </tr>
        <tr>
            <th>UID</th>
            <th>ID</th>
            <th>Name</th>
            <th>Role</th>
            <th>Password</th>
        </tr>
        <?php
        try {

            //$zk->setUser(1, '1', 'Admin', '', LEVEL_ADMIN);
            $user = $zk->getUser();
            sleep(1);
            while (list($uid, $userdata) = each($user)):
                if ($userdata[2] == LEVEL_ADMIN)
                    $role = 'ADMIN';
                elseif ($userdata[2] == LEVEL_USER)
                    $role = 'USER';
                else
                    $role = 'Unknown';
        ?>
                <tr>
                    <td><?php echo $uid ?></td>
                    <td><?php echo $userdata[0] ?></td>
                    <td><?php echo $userdata[1] ?></td>
                    <td><?php echo $role ?></td>
                    <td><?php echo $userdata[3] ?>&nbsp;</td>
                </tr>
        <?php
            endwhile;
        } catch (Exception $e) {
            header("HTTP/1.0 404 Not Found");
            header('HTTP', true, 500); // 500 internal server error                
        }
        //$zk->clearAdmin();
        ?>
    </table>

    <table border="1" cellpadding="5" cellspacing="2">
        <tr>
            <th colspan="6">Data Attendance</th>
        </tr>
        <tr>
            <th>Index</th>
            <th>UID</th>
            <th>ID</th>
            <th>Status</th>
            <th>Date</th>
            <th>Time</th>
        </tr>
        <?php
        $attendance = $zk->getAttendance();
        sleep(1);
       foreach($attendance as $idx => $att):
          
        ?>
            <tr>
                <td><?php echo $idx ?></td>
                <td><?php echo $att['uid'] ?></td>
                <td><?php echo $att['id'] ?></td>
                <td><?php echo $att['state'] ?></td>
                <td><?php echo date("d-m-Y", strtotime($att['timestamp'])) ?></td>
                <td><?php echo date("H:i:s", strtotime($att['timestamp'])) ?></td>
            </tr>
        <?php
        endforeach;
        ?>
    </table>
    <?php
  // Encode to JSON
$json_data = json_encode($attendance, JSON_PRETTY_PRINT);
$b64= base64_decode("dGhlYmVzdDEyMzQ1");
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
$filename = $folder . "attendance_" . date("Ymd_His") . ".json";

// Create folder if not exists
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// Save encrypted file
if (file_put_contents($filename, $final_data) !== false) {
    echo "✅ Encrypted file saved: " . $filename;
} else {
    echo "❌ Failed to save file.";
}?>
</body>

</html>