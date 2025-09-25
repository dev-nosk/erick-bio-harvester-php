<?php
  function zkconnect($self) {
    $command       = CMD_CONNECT;
    $commandString = '';
    $chksum        = 0;
    $session_id    = 0;
    $reply_id      = 0xFFFF; // safer than -1 + USHRT_MAX

    // Build packet
    $buf = $self->createHeader($command, $chksum, $session_id, $reply_id, $commandString);

    // --- SOCKET FIXES ---
    // make sure socket exists
    if (!$self->zkclient) {
        $self->zkclient = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$self->zkclient) {
            echo "❌ socket_create failed: " . socket_strerror(socket_last_error()) . PHP_EOL;
            return false;
        }
        // bind + timeout
        @socket_bind($self->zkclient, "0.0.0.0");
        socket_set_option($self->zkclient, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 5, "usec" => 0]);
    }

    // Send connect command
    $sent = @socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);
    if ($sent === false) {
        echo "❌ socket_sendto failed: " . socket_strerror(socket_last_error($self->zkclient)) . PHP_EOL;
        return false;
    }

    // Receive response
    $from = '';
    $port = 0;
    $bytes = @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $from, $port);
    if ($bytes === false) {
        echo "❌ socket_recvfrom failed: " . socket_strerror(socket_last_error($self->zkclient)) . PHP_EOL;
        return false;
    }

    if ($bytes > 0) {
        // Debug response
        echo "✅ Data received ($bytes bytes) from $from:$port -> " . bin2hex($self->data_recv) . PHP_EOL;

        // Extract session id (bytes 4–5 in little-endian)
        if (strlen($self->data_recv) >= 8) {
            $u = unpack('vcommand/vchecksum/vsession/vreply', substr($self->data_recv, 0, 8));
            $self->session_id = $u['vsession'] ?? 0;
            echo "✅ Session ID: {$self->session_id}" . PHP_EOL;
        }

        // Validate
        if ($self->checkValid($self->data_recv)) {
            echo "✅ Device connected successfully!" . PHP_EOL;
            return true;
        } else {
            echo "❌ checkValid failed!" . PHP_EOL;
            return false;
        }
    }

    echo "❌ No data received from device." . PHP_EOL;
    return false;
}



function zkdisconnect($self) {
    $command       = CMD_EXIT;
    $command_string = '';
    $chksum        = 0;
    $session_id    = $self->session_id;

    // Default reply_id
    $reply_id = 0;

    if (!empty($self->data_recv) && strlen($self->data_recv) >= 8) {
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($self->data_recv, 0, 8));
        if ($u !== false) {
            $reply_id = hexdec($u['h8'] . $u['h7']);
        }
    }

    $buf = $self->createHeader($command, $chksum, $session_id, $reply_id, $command_string);

    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);

    try {
        if (@socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port) === false) {
            return false;
        }
        return $self->checkValid($self->data_recv);
    } catch (ErrorException $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
}

?>
