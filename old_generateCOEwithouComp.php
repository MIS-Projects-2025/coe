<head>
    <style>
        body {
            font-family: 'Times New Roman';

        }

        #coeCard img {
            display: block;
            /* Ensure the logo is displayed as a block */
            margin: 0 auto;
            /* Center the logo */
            max-width: 150px;
            /* Adjust the logo size */
            height: auto;
            /* Maintain aspect ratio */
        }

        #coeCard h1 {
            margin-top: 40px;
            text-align: center;
            font-size: 24px;
            margin-bottom: 40px;
        }

        #coeCard .card-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10;
            margin-bottom: 40px;
        }



        @media print {

            #form-card {
                display: none;
                /* Hide the form card when printing */
            }

            #generateCOE {
                display: none;
                /* Hide the form card when printing */
            }

            #note_info {
                display: none;
                /* Hide the form card when printing */
            }


            #coeCard {
                font-family: 'Times New Roman';
                display: block;
                max-width: 850px;
                /* Adjust as necessary */
                margin: 0 auto;
                /* Center the card */
                padding: 20px;
                /* Add padding for better spacing */
            }

            .card-body {
                text-align: center;
                /* Center the title */
            }

            /* Target all paragraphs in #coeCard except for the header and note */
            .card-body p {
                text-align: justify;
                /* Justify the text */
                max-width: 900px;
                /* Set a max width for the paragraph */
                margin: 0 auto;
                /* Center the paragraph */

            }

            #coeCard .signature {
                width: 100%;
                /* Full width of the container */
                max-width: 280px;
                /* or any desired size */
                margin-left: none;
            }


            /* Exclude the note paragraph */
            .note {
                text-align: justify;
                /* Justify the text */
                max-width: 900px;
                /* Set a max width for the paragraph */
                margin: 0 auto;
                /* Center the paragraph */
                font-family: times, "Times New Roman", ;
                font-size: 26px;
            }

            .justify-content-center {
                display: flex;
                justify-content: center;
                /* Center the columns */
            }

            #image_header {
                border: none;
            }

            #image_header img {
                width: 100%;
                /* Full width of the container */
                max-width: 160px;
            }

            .main-footer {

                /* Justify the text */
                border: none;
                /* Remove border from footer */
            }

            .main-footer .top {
                display: flex;
                width: 100%;
                margin-left: 370px;
                /* Ensure no margin */
                padding: 0;
                /* Ensure no padding */
            }

            .main-footer .footer {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                text-align: justify;
                text-align: center;
                border: none;
                margin: 0;
                /* Ensure no margin */
                padding: 0;
                /* Ensure no padding */
                font-size: 15px;
                z-index: 0;
                /* Footer behind the top */
            }

            .main-footer .icon {
                font-family: times, "Times New Roman";
                margin: 0;
                /* Ensure no margin */
                padding: 0;
                /* Ensure no padding */
            }

            .main-footer .top .img-fluids {
                max-width: 50px;
                /* Limit image width */
                height: auto;
                /* Maintain aspect ratio */
                margin: 0;
                /* Ensure no margin */
                padding: 0;
                /* Ensure no padding */
            }

            .main-footer .footer #img-right {
                width: 100%;
                /* Full width of the container */
                max-width: 250px;
                /* Maximum width */
                height: auto;
                /* Maintain aspect ratio */
                object-fit: cover;
                /* Prevent distortion */
                margin-left: 15px;
            }


            .text-lg-plus {
                font-size: 1.75em;
                /* Adjust this value as needed */
                line-height: 1.5;
                /* Optional: Adjust line height for better readability */
            }

        }
    </style>

</head>


<?php

$print_id = isset($_GET['id']) ? $_GET['id'] : '';
// $approv = isset($_GET['appr']) ? $_GET['appr'] : '';
// $view = isset($_GET['view']) ? ($_GET['view'])  : '';
// $readonly = isset($_GET['view']) ? 'readonly' : '';
// Debugging: Print the IDs being used in the query
// echo "Fetching data for IDs: Nurse ID = $print_id, Approv ID = $approv, View ID = $view<br>";
if ($print_id) {
    $print_id = (int)$print_id;

    $qry = "SELECT * FROM coe_record WHERE ID = $print_id ";
    // echo "Query: $qry<br>";

    $calldata = $conn->query($qry);
    while ($callrow = $calldata->fetch_assoc()):
        foreach ($callrow as $k => $v) {
            $coe_data[$k] = $v;
        }
    endwhile;
}
// // Print the $coe_data array
// echo "<pre>";
// print_r($coe_data);
// echo "</pre>";
?>

<form id="coe-form">
    <?php if (isset($_GET['id'])) : ?>
        <div class="alert alert-light mt-2" role="alert" id="note_info">
            <strong>Note:</strong> Please ensure the following settings when printing:
            <ul class="mb-0">
                <li>Margins are set to the default.</li>
                <li>Headers, and footers along with the background graphics are unchecked.</li>
            </ul>
        </div> <?php endif; ?>
    <div class="callout callout-success" id="form-card">
        <div class="container-fluid">
            <div class="row">


                <?php
                // Assuming $userdataEmployid is defined and contains the current user's employ ID
                $userdataEmployid = $_settings->userdata('EMPNAME'); // Replace with actual ID
                $empclass = $_settings->userdata('EMPCLASS');
                // Step 2: Check if the user is also an admin
                $stmtAdmin = $conn->prepare("SELECT admin_name FROM admin_list WHERE admin_name = ?");
                $stmtAdmin->bind_param("s", $userdataEmployid); // Assuming admin_id is a string; change type if necessary
                $stmtAdmin->execute();
                $resultAdmin = $stmtAdmin->get_result();

                // Check if the user is in the admin list
                $isAdmin = $resultAdmin->num_rows > 0;
                // Query to get all employees from master list
                $stmtEmployees = $empListConnection->prepare("SELECT EMPNAME,EMPLOYID FROM employee_masterlist WHERE ACCSTATUS != 2");
                $stmtEmployees->execute();
                $resultEmployees = $stmtEmployees->get_result();
                ?>

                <div class="col-md-3">
                    <input type="text" id="coe_type" name="coe_type" class="form-control form-control-sm bg-body-tertiary rounded" value="1" hidden>
                    <input type="text" id="gender" name="gender" class="form-control form-control-sm bg-body-tertiary rounded" hidden>
                    <input type="text" id="prodline" name="prodline" class="form-control form-control-sm bg-body-tertiary rounded" hidden>
                    <input type="text" id="empclass" name="empclass" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo $empclass; ?>" hidden>
                    <input type="text" id="dateRequest" name="dateRequest" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo isset($coe_data['DATE_REQUEST']) ? $coe_data['DATE_REQUEST'] : '' ?>" hidden>
                    <label class="control-label">Employee Name:</label>

                    <?php if ($isAdmin) { ?>
                        <select id="employee_name" name="employee_name" class="form-control form-control-sm select2 bg-body-tertiary rounded">
                            <option value="<?php echo $userdataEmployid; ?>"><?php echo $userdataEmployid; ?></option>
                            <?php while ($row = $resultEmployees->fetch_assoc()) { ?>
                                <option value="<?php echo $row['EMPNAME']; ?>"><?php echo $row['EMPLOYID'] . " - " . $row['EMPNAME']; ?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <input type="text" id="employee_name" name="employee_name" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo isset($coe_data['EMPNAME']) ? $coe_data['EMPNAME'] : $_settings->userdata('EMPNAME'); ?>" readonly>
                    <?php } ?>

                </div>

                <?php

                ?>
                <div class="col-md-3">
                    <label class="control-label">Position:</label>
                    <input type="text" id="position" name="position" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo isset($coe_data['EMPPOSITION']) ? $coe_data['EMPPOSITION'] : '' ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="control-label">Date Hired:</label>
                    <input type="text" id="date_hired" name="date_hired" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo isset($coe_data['DATE_HIRED']) ? $coe_data['DATE_HIRED'] : '' ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="control-label">Purpose:</label>
                    <?php if (isset($coe_data['PURPOSE'])) : ?>
                        <!-- Display a read-only input if purpose is set -->
                        <input type="text" id="purpose" name="purpose" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo $coe_data['PURPOSE']; ?>" readonly>
                    <?php else : ?>
                        <!-- Display the dropdown if purpose is not set -->
                        <select id="purpose" name="purpose" class="form-control select2 form-control-sm bg-body-tertiary rounded" required>
                            <option value="">Select an option</option>
                            <?php
                            $qry = $conn->query("SELECT * FROM `purpose`");
                            while ($row_endorse = $qry->fetch_assoc()) :
                            ?>
                                <option value="<?php echo $row_endorse['PURPOSE']; ?>">
                                    <?php echo $row_endorse['PURPOSE']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="form-group col-md-3">
                    <label for="attachmentFile" class="mt-3">Proof of request</label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input class="custom-file-input" type="file" id="attachmentFile">

                            <label class="custom-file-label" for="attachmentFile">Choose file</label>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <div class="col-md-12 text-right">
        <?php if (isset($_GET['id'])) : ?>
            <!-- Display Generate COE button if ID is present -->
            <button type="button" class="btn btn-primary" id="generateCOE">Generate COE</button>
        <?php else : ?>
            <!-- Display Request COE button if ID is not present -->
            <button type="button" class="btn btn-primary" id="requestCOE">Request COE</button>
        <?php endif; ?>
    </div>
</form>




<div class="card" id="coeCard" hidden>
    <div class="card-header text-center" id="image_header">
        <img src="<?php echo base_url ?>uploads/logo.png" alt="Store Logo" class="">
        <p class="text-center text-xl">Telford Svc. Philippines Inc.</p>
    </div><br><br><br>
    <div class="card-body">
        <h1 class="text-center text-xl"><b>CERTIFICATE OF EMPLOYMENT</b></h1><br>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <p class="text-lg-plus text-justify">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that <b><span id="gender_card"></span></b> <b><span id="emp_name"></span></b> is an employee of Telford
                    Svc. Phils. Inc. from <b><span id="date_hired_card"></span></b> up to present with the position of&nbsp;
                    <b><span id="position_card"></span></b>.
                </p>
                <br>
                <p class="text-lg-plus text-justify">This certification is being issued upon the request of <i><span id="gender_card1"></span> <span id="emp_name_request"></span></i>
                    for <span id="purpose_card"></span>.
                </p>
                <br>
                <p class="text-lg-plus text-justify">Done this <span id="date_of_request_card"></span> at <span id="company_bottom"></span> Gateway
                    Business Park, Brgy. Javalera, General Trias, Cavite.
                </p>
                <br><br><br><br><br><br><br>
                <img src="<?php echo base_url ?>uploads/signature1.png" alt="Store Logo" class="signature" style="float: left;">
                <br><br><br><br><br><br><br> <br><br><br>
            </div>
        </div>
    </div>
</div>
<?php if ($print_id): ?>
    <script>
        var recordId = <?php echo $print_id; ?>;
    </script>
<?php endif; ?>
<script>
    $('#attachmentFile').on('change', function() {
        // Get the file name
        var fileName = $(this).val().split('\\').pop();
        // Replace the "Choose a file" label
        $(this).next('.custom-file-label').html(fileName);
    });

    function adjustInputWidth(element) {
        // Check if element has textContent (which spans have)
        if (element.textContent) {
            element.style.width = `${element.textContent.length + 1}ch`; // Adjust width based on the length of the text content
        }
    }

    // Function to fetch employee details
    function fetchEmployeeDetails(employeeName) {
        $.ajax({
            type: 'POST',
            url: '../getFunction/get_emp.php',
            data: {
                employee_name: employeeName
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#position').val(response.JOB_TITLE);
                    $('#date_hired').val(response.DATEHIRED);
                    $('#gender').val(response.EMPSEX);
                    $('#prodline').val(response.PRODLINE);
                    // Determine the prefix based on EMPSEX
                    var prefix = '';
                    if (response.EMPSEX == 1) {
                        prefix = 'Mr.';
                    } else if (response.EMPSEX == 2) {
                        prefix = 'Ms.';
                    }

                    // Set the prefix in the gender card
                    $('#gender_card').text(prefix);
                } else {
                    alert('Employee not found in the database');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching employee details:', error);
                console.log(xhr.responseText);
            }
        });
    }

    // Function to format the employee name from "Last, First Middle" to "First Middle Last"
    function formatEmployeeName(name) {
        var nameParts = name.split(','); // Split by comma
        if (nameParts.length === 2) {
            var lastName = nameParts[0].trim(); // Last name
            var firstMiddleName = nameParts[1].trim(); // First and middle names
            var formattedName = firstMiddleName + ' ' + lastName; // Format as "First Middle Last"

            // Return an object containing both the formatted name and the last name
            return {
                formattedName: formattedName,
                lastName: lastName
            };
        }
        // Return original name if format is unexpected
        return {
            formattedName: name,
            lastName: ''
        };
    }

    function generateCOE() {
        const dateReq = $('#dateRequest').val();
        const dateParts = dateReq.split(' ')[0].split('-');
        const day = parseInt(dateParts[2]);
        const month = dateParts[1];
        const year = dateParts[0];
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const monthName = months[month - 1];
        const ordinalDay = day + getOrdinalSuffix(day);
        const formattedDate = ordinalDay + ' day of ' + monthName + ' ' + year;
        console.log(formattedDate);
        const employeeName = $('#employee_name').val();
        const position = $('#position').val();
        const prodline = $('#prodline').val();
        const dateHiredRaw = $('#date_hired').val();
        const genderRaw = $('#gender').val();
        const coeType = $('#coe_type').val();
        const purpose = $('#purpose').val();

        console.log("Employee Name:", employeeName); // Debugging statement
        console.log("Position:", position); // Debugging statement
        console.log("Prodline:", prodline); // Debugging statement
        console.log(dateReq);

        const dateHired = dateHiredRaw ? new Date(dateHiredRaw).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';

        const currentDate = new Date();
        const dateOfRequest = currentDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const companyName = (prodline === 'PL5 (TPMI)') ? 'Telford Property Management Inc.' : 'Telford Svc. Philippines Inc.';
        const companyBottomName = (prodline === 'PL5 (TPMI)') ? 'Telford Property Management Inc.' : 'Telford Svc. Philippines Inc.';
        $('#image_header p').text(companyName);

        function getOrdinalSuffix(day) {
            if (day > 3 && day < 21) return 'th'; // Catch 11th-13th
            switch (day % 10) {
                case 1:
                    return 'st';
                case 2:
                    return 'nd';
                case 3:
                    return 'rd';
                default:
                    return 'th';
            }
        }

        // const day = currentDate.getDate();
        // const ordinalDay = dateReq + getOrdinalSuffix(dateReq);
        // const formattedDate = ordinalDay + ' day of ' + dateOfRequest.split(' ')[0] + ' ' + dateOfRequest.split(' ')[2];
        console.log(formattedDate);

        let genderPrefix = '';
        if (genderRaw == 1) {
            genderPrefix = 'Mr.';
        } else if (genderRaw == 2) {
            genderPrefix = 'Ms.';
        }

        const nameResult = formatEmployeeName(employeeName); // Assuming formatEmployeeName is defined elsewhere
        $('#emp_name').text(nameResult.formattedName);
        $('#position_card').text(position);
        $('#date_hired_card').text(dateHired);
        $('#gender_card').text(genderPrefix);
        $('#gender_card1').text(genderPrefix);
        $('#emp_name_request').text(nameResult.lastName);
        $('#purpose_card').text(purpose);
        $('#date_of_request_card').text(formattedDate);
        $('#company_bottom').text(companyBottomName);
        $('#coeCard span').each(function() {
            adjustInputWidth(this); // Assuming adjustInputWidth is defined elsewhere
        });

        $('#coeCard').removeAttr('hidden');
        $('.main-footer .first').attr('hidden', true);
        $('.main-footer .second').attr('hidden', true);
        $('.main-footer .top').removeAttr('hidden');
        $('.main-footer img').removeAttr('hidden').addClass('float-right d-none d-sm-inline-block');
        $('.main-footer .footer').removeAttr('hidden');

        window.print();

        $('#coeCard').attr('hidden', true);

        console.log({
            EMPNAME: employeeName,
            EMPPOSITION: position,
            DATE_HIRED: dateHired,
            PURPOSE: purpose,
            DATE_REQUEST: dateOfRequest
        });
        updateRecordStatus(recordId, 'generated');

    }

    function updateRecordStatus(id, status) {
        $.ajax({
            url: _base_url_ + "classes/Record.php?f=update_status",
            method: "POST",
            data: {
                ID: id,
                STATUS: status
            },
            dataType: "json",
            success: function(resp) {
                if (resp.status == 'success') {
                    alert_toast(resp.msg);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert_toast("An error occurred while updating the status.", 'error');
                }
            },
            error: function(err) {
                console.log(err);
                alert_toast("An error occurred.", 'error');
            }
        });
    }
    // Attach the function to the click event
    $('#generateCOE').on('click', generateCOE);
    $('#requestCOE').on('click', function() {
        var dateHiredRaw = $('#date_hired').val();
        var dateHired = dateHiredRaw ? new Date(dateHiredRaw).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';
        if (!$('#purpose').val().trim()) {
            alert_toast('Please enter a purpose.', 'warning');
            return;
        }

        const fileInput = $('#attachmentFile')[0];
        if (fileInput.files.length === 0) {
            alert_toast('Please upload a file before submitting.', 'warning');
            return;
        }

        var formData = new FormData();
        formData.append('EMPNAME', $('#employee_name').val());
        formData.append('EMPPOSITION', $('#position').val());
        formData.append('EMPCLASS', $('#empclass').val());
        formData.append('DATE_HIRED', dateHired);
        formData.append('PURPOSE', $('#purpose').val());
        formData.append('COE_TYPE', $('#coe_type').val());

        var currentDate = new Date();
        var dateOfRequest = currentDate.toISOString().replace('T', ' ').replace('Z', '');
        formData.append('DATE_REQUEST', dateOfRequest);

        formData.append('ATTACHMENT', fileInput.files[0]);

        $.ajax({
            url: '../classes/Record.php?f=save_record',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert_toast(response.msg);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert_toast(response.msg || 'Submission failed.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Upload error:', error);
                alert_toast('An error occurred while saving. Please try again.', 'error');
            }
        });
    });

    $('#employee_name').on('change', function() {
        var employeeName = $(this).val();
        fetchEmployeeDetails(employeeName); // Call the function to fetch employee details
    });

    $(document).ready(function() {
        // Automatically fetch employee details for non-admin users
        var employeeName = $('#employee_name').val();
        console.log(employeeName);

        if (employeeName) {
            fetchEmployeeDetails(employeeName); // Fetch details using the current employee ID
        }
    });
</script>