<?php

// --- top of file: always run this before any HTML output ---
$filename = __DIR__ . '/ips.txt';
$savedIp = '';    // <- initialize so it's always defined
$message = '';    // <- initialize message too
$savedCompany = '';
$savedBranchCode = '';

// Handle save button
if (isset($_POST['save_ip'])) {
    $ip = trim($_POST['ipaddress'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $branchCode = trim($_POST['branch_code'] ?? '');

    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        // store IP, company, and branch code (overwrite) and use LOCK_EX
        $data = $ip . PHP_EOL . $company . PHP_EOL . $branchCode . PHP_EOL;
        file_put_contents($filename, $data, LOCK_EX);

        $savedIp = $ip;
        $savedCompany = $company;
        $savedBranchCode = $branchCode;

        $message = "✅ IP address, Company, and Branch Code saved!";
    } else {
        $message = "❌ Invalid IP address.";
    }
} else {
    // load saved ip, company, and branch code if file exists
    if (file_exists($filename)) {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        $savedIp = isset($lines[0]) ? trim($lines[0]) : '';
        $savedCompany = isset($lines[1]) ? trim($lines[1]) : '';
        $savedBranchCode = isset($lines[2]) ? trim($lines[2]) : '';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Logs</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="">
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Material Design Bootstrap -->
    <link rel="stylesheet" href="assets/css/mdb.min.css">
    <!-- Your custom styles (optional) -->
    <!-- <link rel="stylesheet" href="https://mdbootstrap.com/previews/free_templates/mdbootstrap-standard/css/style.css"> -->
    <style>
        .tab-content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }

        #file-data-frame {
            width: 100%;
            height: 400px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Logs</h1>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="logs-tab" data-toggle="tab" href="#logs" role="tab" aria-controls="logs" aria-selected="true">ALL DATA</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="filedata-tab" data-toggle="tab" href="#filedata" role="tab" aria-controls="filedata" aria-selected="false">FILE DATA</a>
            </li>
             <li class="nav-item">
                <a class="nav-link" id="config-tab" data-toggle="tab" href="#config" role="tab" aria-controls="config" aria-selected="false">CONFIG</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="myTabContent">

            <!-- Logs Tab -->
            <div class="tab-pane fade show active" id="logs" role="tabpanel" aria-labelledby="logs-tab">  
                <h3>All Logs</h3>
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>AccessNo</th>
                            <th>Status</th>
                            <th>Datetime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include("Controller.php");
                        $db = new Controller("db.txt");
                        $logs = $db->getAll();
                        $logs = array_reverse($logs);
                        foreach ($logs as $log) {
                        ?>
                            <tr>
                                <td><?= $log['id']; ?></td>
                                <td><?= $log['AccessNo']; ?></td>
                                <td><?= $log['Status']; ?></td>
                                <td><?= $log['Datetime']; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- File Data Tab -->
            <div class="tab-pane fade" id="filedata" role="tabpanel" aria-labelledby="filedata-tab">
                <div class="container">
                    <h3>File Data</h3>

                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Filename</th>
                                <th>Datetime Execute</th>
                                <th>View</th>
                                <th>Resend to Payroll</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $folder = __DIR__ . "/files"; // 👈 your folder
                            // get all files inside folder
                            $files = glob($folder . "/*.*");
                            $files = array_reverse($files); // Show latest files first
                            if (empty($files)) {
                                echo "<tr><td colspan='3'>❌ No files found in $folder</td></tr>";
                            } else {
                                foreach ($files as $file) {
                                    $filename = basename($file);
                                    $file_no_ext = pathinfo($file, PATHINFO_FILENAME);
                                    $parts = explode("_", $file_no_ext);
                                    $datePart = $parts[3]; // 20250924
                                    $timePart = $parts[4]; // 113219
                                    $datetimeStr = $datePart . $timePart;
                                    $exedatetime = DateTime::createFromFormat("YmdHis", $datetimeStr);

                                    echo "<tr>";
                                    echo "<td>" . $filename . "</td>";
                                    echo "<td>" . ($exedatetime instanceof DateTime ? $exedatetime->format('F d, Y H:i:s A') : 'Invalid Date') . "</td>";
                                    echo "<td><a href='get_file_data.php?file=" . $file_no_ext . "' type='button' class='btn btn-success' target='_blank'>View</a></td>";
                                    echo "<td><button class='btn btn-primary btn-sm resend'  data-filename='" . $filename . "'>Resend to payroll</button></td>"; // You can add functionality to this button
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="config" role="tabpanel" aria-labelledby="config-tab">
                <form method="post">
                    <div class="form-group">
                        <input type="text" name="ipaddress" id="ipaddress" class="form-control"
                            placeholder="Enter Device IP"
                            value="<?php echo htmlspecialchars($savedIp); ?>">
                    </div>
                    <div class="form-group">
                        <select name="company" id="company" class="form-control">
                            <option value="MNC" <?php echo ($savedCompany == 'MNC') ? 'selected' : ''; ?>>MNC</option>
                            <option value="HPTI" <?php echo ($savedCompany == 'HPTI') ? 'selected' : ''; ?>>HPTI</option>
                            <option value="MTI" <?php echo ($savedCompany == 'MTI') ? 'selected' : ''; ?>>MTI</option>
                            <option value="MDI" <?php echo ($savedCompany == 'MDI') ? 'selected' : ''; ?>>MDI</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="branch_code" id="branch_code" class="form-control"
                            placeholder="Enter Branch Code"
                            value="<?php echo htmlspecialchars($savedBranchCode); ?>">
                    </div>
                    <button type="submit" name="save_ip" class="btn btn-success">💾 Save INFO</button>
                </form>
                <hr>
                <label for="">Add date if manual date <i>Note : cron run every 4 days getting 4 days before today</i></label>
                <input type="text" name="date_from" id="date_from" class="form-control" value="" placeholder="Date From" onfocus="(this.type='date')" onblur="(this.type='text')">
                <input type="text" name="date_to" id="date_to" class="form-control" value="" placeholder="Date To" onfocus="(this.type='date')" onblur="(this.type='text')">
                <!-- FETCH button still works separately -->
                <button id="fetch-bio" class="btn btn-primary">FETCH BIO</button>
                <br>

                <input type="checkbox" name="send-email" id="send-email">
                <label for="send-email">force send email if record exist your local pc</label>
                <p id="message"><?php echo isset($message) ? $message : ''; ?></p>
            </div>                
        </div>
    </div>

    <!-- jQuery -->
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
    <!-- Bootstrap tooltips -->
    <script type="text/javascript" src="assets/js/popper.min.js"></script>
    <!-- Bootstrap core JavaScript -->
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
    <!-- MDB core JavaScript -->
    <script type="text/javascript" src="assets/js/mdb.min.js"></script>

    <script>
        $(document).ready(function() {
            $(document).on('click', '#fetch-bio', function() {
               var send_email = $('#send-email').is(':checked') ? '?send_email=true' : '';
               var and = send_email ? '&' : '?';
               var date_from = $('#date_from').val().trim();
                var date_to = $('#date_to').val().trim();
                if(date_from != '' || date_to != ''){
                    var date = and+'date_from='+date_from+'&date_to='+date_to;
                }

                const ip = $('#ipaddress').val().trim();
                if (!ip) {
                    alert('Please enter a valid IP address.');
                    return;
                }
                $('#message').text('Your request is being processed, please check the new tab');
                window.open('get-logs.php' + send_email+date, 'frame');
            });

        });

        $(document).on('click', '.resend', function() {
            const filename = $(this).data('filename');
            alert('Resend functionality is not implemented yet for ' + filename);
            return;
            if (confirm('Are you sure you want to resend ' + filename + ' to payroll?')) {
                // AJAX request to resend the file
                // $.post('resend_to_payroll.php', { filename: filename }, function(response) {
                //     alert(response.message);
                // }, 'json');

                alert('Resend functionality is not implemented yet for ' + filename);
            }
        });
    </script>
</body>

</html>