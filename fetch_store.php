<html>
<head>
    <title>ZK Test</title>
</head>
<body>
<?php
set_time_limit(0);
include("zklib/zklib.php");

// DB Connection
$servername = "172.0.0.22";  // just the IP/hostname
$port       = 32775;         // port goes here
$username   = "dev";
$password   = "root";
$dbname     = "zk";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!mysqli_set_charset($conn, "utf8")) {
    printf("Error loading character set utf8: %s\n", mysqli_error($conn));
    exit();
} else {
    printf("Current character set: %s\n<br>", mysqli_character_set_name($conn));
}
echo 'connected to database<br>';

// Connect to device
$ip = "192.168.1.5";
$zk = new ZKLib($ip, 4370);
$data = [];
$ret = $zk->connect();
 $data = array_reverse($zk->getAttendance());
// var_dump($ret);die;
// if ($ret) {
//     echo "connected to device";
//     echo '<br>';
//     if (isset($_GET['date_fetch'])) {
//          $data = $zk->getAttendance($_GET['date_fetch']);
//     }else{
//         $data = array_reverse($zk->getAttendance());
//     } 
// } else {
//     die("Device connection failed");
// }
var_dump($data);
// Insert fetched data
if (!empty($data) && isset($_GET['store_data'])) {
    foreach ($data as $d) {
        $userId      = $d['fingerprintid']; // adjust field name if needed
        $logDateTime = $d['timestamp'];     // must be Y-m-d H:i:s string

        // Prepare once per row
        $stmt = $conn->prepare("INSERT INTO fetchLog 
            (access_no, time_logs, ip_address) 
            VALUES (?, ?, ?)");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters: userId = string, logDateTime = string, ip = string
        $stmt->bind_param("sss", $userId, $logDateTime, $ip);

        // Execute
        if ($stmt->execute()) {
            echo "Inserted OK: User $userId at $logDateTime<br>";
        } else {
            echo "Insert failed: " . $stmt->error . "<br>";
        }

        $stmt->close();
    }
}

$conn->close();
?>
</body>
</html>
