<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZKTeco Attendance - Fetch Bio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-header">

            <h4>ZKTeco Attendance - Fetch Bio</h4>
        </div>
        <div class="card-body">
            <form class="row g-2 align-items-center mb-3" id="ipForm" autocomplete="off">
                <label class="col-auto col-form-label" for="ip1">Device IP:</label>
                <div class="col-auto">
                    <input type="number" min="0" max="255" maxlength="3" class="form-control" id="ip1" name="ip1" placeholder="192" required 
                        oninput="this.value=this.value.slice(0,3); if(this.value.length==3) document.getElementById('ip2').focus();">
                </div>
                <span class="col-auto">.</span>
                <div class="col-auto">
                    <input type="number" min="0" max="255" maxlength="3" class="form-control" id="ip2" name="ip2" placeholder="168" required 
                        oninput="this.value=this.value.slice(0,3); if(this.value.length==3) document.getElementById('ip3').focus();">
                </div>
                <span class="col-auto">.</span>
                <div class="col-auto">
                    <input type="number" min="0" max="255" maxlength="3" class="form-control" id="ip3" name="ip3" placeholder="1" required 
                        oninput="this.value=this.value.slice(0,3); if(this.value.length==3) document.getElementById('ip4').focus();">
                </div>
                <span class="col-auto">.</span>
                <div class="col-auto">
                    <input type="number" min="0" max="255" maxlength="3" class="form-control" id="ip4" name="ip4" placeholder="201" required 
                        oninput="this.value=this.value.slice(0,3);">
                </div>
                </div>
            </form>
            <button id="fetchBioBtn" class="btn btn-primary">Fetch Bio</button>
            <div id="bioResult" class="mt-4"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#fetchBioBtn').click(function(){
        $('#bioResult').html('<div class="spinner-border text-primary" role="status"></div> Fetching...');
        $.ajax({
            url: './ZKController.php?action=fetchBio',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'fetchBio',
                ip: $('#ip1').val() + '.' + $('#ip2').val() + '.' + $('#ip3').val() + '.' + $('#ip4').val()
            },
            success: function(response) {
                if(response.success) {
                    $('#bioResult').html(
                        '<pre>' + response.message + '</pre>'
                    );
                } else {
                    $('#bioResult').html(
                        '<div class="alert alert-danger">Failed to fetch bio data.</div>'
                    );
                }
            },
            error: function() {
                $('#bioResult').html(
                    '<div class="alert alert-danger">AJAX request failed.</div>'
                );
            }
        });
    });
});
</script>
</body>
</html>