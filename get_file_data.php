<?php
if (isset($_GET['file']) && !empty($_GET['file'])) {
    $file_name = $_GET['file'];
    $filename = "files/{$file_name}.json";

    # pasword decoded is  thebest12345
    $b64 = base64_decode("dGhlYmVzdDEyMzQ1");
    $password = $b64;

    $cipher = "AES-256-CBC";
    $final_data = file_get_contents($filename);
    $raw_data = base64_decode($final_data);


    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($raw_data, 0, $ivlen);
    $encrypted_data = substr($raw_data, $ivlen);

    $decrypted_json = openssl_decrypt($encrypted_data, $cipher, $password, 0, $iv);
    $data = json_decode($decrypted_json, true);
?>
    </table>
    <a href="get_file_data.php">Back to list</a>
    <h3>📄 Decrypted Data from: <?php echo htmlspecialchars($file_name) ?>.json</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>UID</th>
                <th>AccessNo</th>
                <th>Status</th>
                <th>Date</th>
                <th>Millitary Time</th>
                <th>Standard Time</th>
            </tr>
        </thead>
        <?php
        foreach ($data as $record) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($record['uid']) . "</td>";
            echo "<td>" . htmlspecialchars($record['userid']) . "</td>";
            echo "<td>" . htmlspecialchars($record['state']) . "</td>";
            echo "<td>" . htmlspecialchars(date("M d, Y", strtotime($record['timestamp']))) . "</td>";
            echo "<td>" . htmlspecialchars(date("H:i:s A", strtotime($record['timestamp']))) . "</td>";
            echo "<td>" . htmlspecialchars(date("g:i:s A", strtotime($record['timestamp']))) . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
<?php
} else {

    $folder = __DIR__ . "/files"; // 👈 your folder

    // get all files inside folder
    $files = glob($folder . "/*.*");

    if (empty($files)) {
        die("❌ No files found in $folder");
    }

    foreach ($files as $file) {
        echo "<h3>📄 File: " . basename($file) . "</h3>";

        echo '<a href="get_file_data.php?file=' . pathinfo($file, PATHINFO_FILENAME) . '" target="">View Decrypted Data</a><br><br>';
    }
}
