<?php 
  require __DIR__ . '/php_vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
 class Controller
    {
        private $filename;

        public function __construct($filename = "db.txt")
        {
            $this->filename = $filename;

            // Create empty JSON array if not exists
            if (!file_exists($this->filename)) {
                file_put_contents($this->filename, "[]");
            }
        }

        // Insert new record (skip if userid+timestamp already exists)
        public function insert($accessNo, $status = 1, $datetime = null)
        {
            $all = $this->getAll();

            // Check for duplicate (same userid & timestamp)
            foreach ($all as $row) {
                if ($row['AccessNo'] == $accessNo && $row['Datetime'] == $datetime) {
                    return [
                        "error" => true,
                        "message" => "❌ Duplicate record skipped (userid + timestamp already exists)",
                        "record" => $row
                    ];
                }
            }

            // Auto-increment ID
            $lastId = 0;
            if (!empty($all)) {
                $lastId = end($all)['id'];
            }

            $record = [
                "id"       => $lastId + 1,
                "AccessNo" => $accessNo,
                "Status"   => $status,
                "Datetime" => $datetime ?? date("Y-m-d H:i:s")
            ];

            // Add new record
            $all[] = $record;

            // Save back to file
            file_put_contents($this->filename, json_encode($all, JSON_PRETTY_PRINT));

            return [
                "error" => false,
                "message" => "✅ Record inserted successfully",
                "record" => $record
            ];
        }

        // Get all records
        public function getAll()
        {
            if (!file_exists($this->filename)) {
                return [];
            }

            $json = file_get_contents($this->filename);
            $data = json_decode($json, true);

            if (!is_array($data)) {
                return []; // fallback if JSON is invalid
            }

            return $data;
        }

        public function sendMail($filename,$savedCompany,$savedBranchCode,$email_body_date='')
        {
            
            $mail = new PHPMailer(true);
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = '192.168.100.22';
                $mail->Port       = 25; // or 587 if configured
                $mail->SMTPAuth   = true;
                $mail->Username   = 'noreply';
                $mail->Password   = 'noreplynga@1234';
                $mail->SMTPSecure = 'tls';
                $mail->SMTPAutoTLS = true;

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];
                // Sender
                $mail->setFrom('no-reply@cmc.com', 'Motortrade Notification');
                $attachmentName = "attendance_".$savedCompany."_". $savedBranchCode."_" . date("Ymd_His") . ".json";
                // Recipient
                //$mail->addAddress('erick.adriano@cmc.com', 'DEV');
                $mail->addAddress('dhalyn.dioleste@cmc.com','PAYROLL');
                $mail->addAddress('kier.amar@cmc.com', 'NETWORK');
                $mail->addAddress('rodney.brian@cmc.com', 'MNGR');
                $mail->addAttachment(__DIR__ . '/' . $filename, $attachmentName);
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Attendance Data ' . date("Y-m-d H:i:s") .'| ' . $email_body_date;
                $mail->Body    = '<p>Good day!,</p>
                                <p>Please find the attached encrypted attendance data file.</p>
                                <p>Company: <strong>' . htmlspecialchars($savedCompany) . '</strong></p>
                                <p>Branch Code: <strong>' . htmlspecialchars($savedBranchCode) . '</strong></p>
                                <p>Date: <strong>' . ($email_body_date) . '</strong></p>
                                <p>Best regards,<br>Motortrade Notification System</p> 
                                <p style="font-size: small; color: gray;">This is an automated message. Please do not reply.</p>';
                $mail->AltBody = 'do not reply to this email.';

                $mail->send();
                return  1;
            } catch (Exception $e) {
                echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
                return 0;
            }
        }

    public function stagingAPI($data)
    {
        $targetUrl = "http://172.0.0.22:8080/payroll-bio-converter/api/store-data";

        $postFields = [
            'data' => json_encode($data)
        ];

        $ch = curl_init($targetUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return json_encode(['error' => $error_msg]);
        }

        curl_close($ch);
        return $response;
    }
}

    ?>