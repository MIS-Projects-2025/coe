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
            body {
                font-family: 'Times New Roman';

            }

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

            #coeCard .signature {
                width: 100%;
                /* Full width of the container */
                max-width: 280px;
                /* or any desired size */
                margin-left: none;
            }

            .card-body {
                text-align: center;
                /* Center the title */
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                width: 100%;
                /* Ensure it takes full width of the parent card */
            }

            #coeCard p {
                text-align: justify;
                /* Justify the text */

            }

            .note {
                text-align: justify;
                /* Justify the text */
                max-width: 900px;
                /* Set a max width for the paragraph */
                margin: 20px auto;
                /* Center the paragraph with some vertical spacing */
                font-family: times, "Times New Roman";
                /* Corrected font-family */
                font-size: 28px;
                /* Adjusted font size */
                line-height: 1.5;
                /* Added line height for better readability */
            }

            .justify-content-center {
                display: flex;
                justify-content: center;
                /* Center the columns */
            }

            .pay-container {
                font-size: 1.75em;
                /* Adjust this value as needed */
                line-height: 1.5;
                /* Optional: Adjust line height for better readability */
                display: flex;
                justify-content: flex-start;
                /* Align items closely */
                margin-left: 20px;
                width: 100%;
                max-width: 600px;
                margin-bottom: 10px;
            }



            .label,
            .dash,
            .value {
                flex: none;
                /* Prevent flex items from stretching */
            }

            .label {
                width: 200px;
                margin-right: 5px;
            }

            .dash {
                margin-left: 150px;
            }

            .value {
                margin-left: 20px;
            }

            #image_header {
                border: none;
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
                    <input type="text" id="coe_type" name="coe_type" class="form-control form-control-sm bg-body-tertiary rounded" value="3" hidden>
                    <input type="text" id="gender" name="gender" class="form-control form-control-sm bg-body-tertiary rounded" hidden>
                    <input type="text" id="prodline" name="prodline" class="form-control form-control-sm bg-body-tertiary rounded" hidden>
                    <input type="text" id="empclass" name="empclass" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo $empclass; ?>" hidden>
                    <input type="text" id="pcn_status" name="pcn_status" class="form-control form-control-sm bg-body-tertiary rounded" value='0' hidden>
                    <label class="control-label">Employee Name:</label>
                    <?php if ($isAdmin): ?>
                        <select id="employee_name" name="employee_name" class="form-control select2 form-control-sm bg-body-tertiary rounded" required>
                            <option value="">Select an option</option>
                            <?php
                            $lst_emp = [];

                            // Query to fetch distinct pcn_emp_no from pcn_salary
                            $qry1 = $dbPcnconn->query("SELECT DISTINCT pcn_emp_no FROM `pcn_salary`");

                            // Check if the query was successful
                            if (!$qry1) {
                                echo "Error in query: " . $dbPcnconn->error;
                            } else {
                                // Collecting employee numbers into the $lst_emp array
                                while ($row1 = $qry1->fetch_assoc()) {
                                    $lst_emp[] = $row1['pcn _emp_no'];
                                }
                                // Update pcn_status based on the success of qry1
                                echo '<script>document.getElementById("pcn_status").value = 1;</script>';
                            }
                            // Check if there are any employee numbers
                            if (!empty($lst_emp)) {
                                // Create a comma-separated list of employee numbers for the SQL IN clause
                                $empNumbersList = implode(",", array_map('intval', $lst_emp)); // Sanitize input

                                // Query to fetch employee details from employee_masterlist
                                $qry2 = $empListConnection->query("SELECT * FROM `employee_masterlist` WHERE `EMPLOYID` IN ($empNumbersList) AND `EMPLOYID` != 0 AND `ACCSTATUS` = 1 OR EMPLOYID = '{$_settings->userdata('EMPLOYID')}'");

                                // Check if the second query was successful
                                if (!$qry2) {
                                    echo "Error in query: " . $empListConnection->error;
                                } else {
                                    // Populate the select options
                                    while ($row_endorse = $qry2->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row_endorse['EMPNAME']) . '">' . htmlspecialchars($row_endorse['EMPLOYID']) . " - " . htmlspecialchars($row_endorse['EMPNAME']) . '</option>';
                                    }
                                }
                            } else {
                                echo '<option value="">No employees found</option>';
                            }
                            ?>
                        </select>
                    <?php else: ?>
                        <input type="text" id="employee_name" name="employee_name" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo isset($coe_data['EMPNAME']) ? $coe_data['EMPNAME'] : $_settings->userdata('EMPNAME'); ?>" readonly>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label class="control-label">Position:</label>
                    <input type="text" id="position" name="position" class="form-control form-control-sm bg-body-tertiary rounded" readonly>
                </div>
                <div class="col-md-3">
                    <label class="control-label">Date Hired:</label>
                    <input type="text" id="date_hired" name="date_hired" class="form-control form-control-sm bg-body-tertiary rounded" readonly>
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
            <input type="text" id="emp_no" name="emp_no" class="form-control form-control-sm bg-body-tertiary rounded" hidden>
            <!-- <div class="row">
                        <div class="col-md-3">
                            <label class="control-label">Sample:</label>
                            <input type="text" id="formula_product" name="formula_product" class="form-control form-control-sm bg-body-tertiary rounded">
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Wage Order:</label>
                            <input type="number" id="wage_order" name="wage_order" class="form-control form-control-sm bg-body-tertiary rounded">
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Basic Pay:</label>
                            <input type="number" id="basic_pay" name="basic_pay" class="form-control form-control-sm bg-body-tertiary rounded">
                        </div>
                    </div> -->

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
    </div>
    <div class="card-body">
        <h1 class="text-center text-xl"><b>CERTIFICATE OF EMPLOYMENT</b></h1><br>
        <div class="row justify-content-center">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <p class="text-lg-plus text-justify">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that <span id="gender_card"></span></b> <span id="emp_name"></span> is an employee of Telford
                    Svc. Phils. Inc. from <b><span id="date_hired_card"></span></b> up to <b>present</b> with the position of
                    <b><span id="position_card"></span></b>. <span id="hisher_card"></span> gross annual compensation is <span id="annual_salary_words"></span> (Php
                    <span id="annual_salary"></span>)
                    including the 13th Month Pay.
                </p>
                <br>
                <p class="text-lg-plus text-justify">Breakdown of Gross Monthly Compensation is as follows:</p>

                <br>
                <div class="pay-container">
                    <p class="label">Basic Pay</p>
                    <p class="dash">-</p>
                    <p class="value">Php <span id="wage_order_card"></span></p>
                </div>
                <div class="pay-container">
                    <p class="label">13th Month Pay</p>
                    <p class="dash">-</p>
                    <p class="value">Php <span id="basic_pay_card"></span></p>
                </div>

                <br>
                <p class="text-lg-plus text-justify">This certification is being issued upon the request of <i><span id="gender_card1"></span> <span id="emp_name_request"></span></i>
                    for <span id="purpose_card"></span>.</p>
                <br>
                <p class="text-lg-plus text-justify">Done this <span id="date_of_request_card"></span> at Telford Svc Phils., Inc. Gateway
                    Business Park, Brgy. Javalera, General Trias, Cavite.
                </p>

                <br><br>
                <img src="<?php echo base_url ?>uploads/signature1.png" alt="Store Logo" class="signature" style="float: left;">
            </div>
            <div class="col-md-3"></div>
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
    $('#employee_name').on('change', function() {
        var employeeName = $(this).val();
        fetchEmployeeDetails(employeeName); // Call the function to fetch employee details
    });
    $(document).ready(function() {
        console.log('Document is ready.'); // Log when the document is ready

        // Automatically fetch employee details for non-admin users
        var employeeName = $('#employee_name').val();
        console.log('Employee Name on Page Load:', employeeName); // Log the employee name

        if (employeeName) {
            console.log('Fetching employee details for:', employeeName); // Log before fetching details
            fetchEmployeeDetails(employeeName); // Fetch details using the current employee ID
        } else {
            console.log('No employee name found on page load.'); // Log if no employee name is found
        }
    });

    function adjustInputWidth(element) {
        // Check if element has textContent (which spans have)
        if (element.textContent) {
            element.style.width = `${element.textContent.length + 1}ch`; // Adjust width based on the length of the text content
        }
    } // Function to fetch employee details
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
                    $('#emp_class').val(response.EMPCLASS);
                    $('#prodline').val(response.PRODLINE);
                    console.log(response.EMPCLASS);
                    $('#emp_no').val(response.EMPLOYID);
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

    $('#generateCOE').on('click', function() {
        if (!$('#purpose').val().trim()) {
            alert_toast('Please enter a purpose.', 'warning');
            return;
        }
        var employeeName = $('#employee_name').val();
        var empNo = $('#emp_no').val();
        var empClass = $('#emp_class').val();
        var genderRaw = $('#gender').val();
        var position = $('#position').val();
        var prodline = $('#prodline').val();
        var dateHiredRaw = $('#date_hired').val();
        var coeType = $('#coe_type').val();
        var dateHired = dateHiredRaw ? new Date(dateHiredRaw).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';
        var sepDateRaw = $('#sep_date').val();
        var sepDate = sepDateRaw ? new Date(sepDateRaw).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';
        var purpose = $('#purpose').val();
        var currentDate = new Date();
        var dateOfRequest = new Date().toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        // Determine company name based on prodline
        var companyName = (prodline === 'PL5 (TPMI)') ? 'Telford Property Management Inc.' : 'Telford Svc. Philippines Inc.';
        // Debugging statement
        console.log("Gender Prefix determined:", genderPrefix); // Debugging statement

        // Set the company name in the COE
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

        // Get the day of the month
        var day = currentDate.getDate();
        var ordinalDay = day + getOrdinalSuffix(day);

        // Combine the formatted date with the ordinal day
        var formattedDate = ordinalDay + ' day of ' + dateOfRequest.split(' ')[0] + ' ' + dateOfRequest.split(' ')[2];

        // Set gender card to display "Mr." or "Ms." based on the stored genderRaw value
        var genderPrefix = '';
        var genderPrefix1 = '';
        if (genderRaw == 1) {
            genderPrefix = 'Mr.';
        } else if (genderRaw == 2) {
            genderPrefix = 'Ms.';
        }
        if (genderRaw == 1) {
            genderPrefix1 = 'His';
        } else if (genderRaw == 2) {
            genderPrefix1 = 'Her';
        }

        // Process the employee name from the form input
        var nameResult = formatEmployeeName(employeeName); // Get name from input and format it

        $('#emp_name').text(nameResult.formattedName);
        $('#gender_card').text(genderPrefix);
        $('#gender_card1').text(genderPrefix);
        $('#hisher_card').text(genderPrefix1);
        $('#position_card').text(position);
        $('#date_hired_card').text(dateHired);
        $('#purpose_card').text(purpose);
        $('#sep_date_hired').text(sepDate);
        $('#emp_name_request').text(nameResult.lastName);
        $('#date_of_request_card').text(formattedDate);

        // Fetch basic pay and proceed after it's fetched
        fetchBasicPay(empNo, function(response) {
            // Check if the response is valid (i.e., employee has a basic pay record)
            if (response && response.status === 'success') {
                if (empClass == 1) {
                    $('#annual_salary_words').text(response.monthly_salary_words); // Use the response object
                    $('#annual_salary').text(response.monthly_salary_annual); // Use the response object
                    $('#basic_pay_card').text(response.salary_to_1); // Use the basic pay from the response
                    $('#wage_order_card').text(response.salary_to_1); // Use the basic pay from the response
                } else if (empClass == 2) {
                    $('#annual_salary_words').text(response.annual_salary_words); // Use the response object
                    $('#annual_salary').text(response.annual_salary); // Use the response object
                    $('#basic_pay_card').text(response.salary_to); // Use the basic pay from the response
                    $('#wage_order_card').text(response.salary_to); // Use the basic pay from the response
                }

                // Make the COE card visible for printing
                $('#coeCard span').each(function() {
                    adjustInputWidth(this);
                });

                $('#coeCard').removeAttr('hidden'); // Make it visible for printing
                $('.main-footer .first').attr('hidden', true);
                $('.main-footer .second').attr('hidden', true);
                $('.main-footer .top').removeAttr('hidden');
                $('.main-footer img').removeAttr('hidden');
                $('.main-footer img').addClass('float-right d-none d-sm-inline-block');
                $('.main-footer .footer').removeAttr('hidden');
                // Trigger the print dialog
                window.print();

                updateRecordStatus(recordId, 'generated');
            } else {
                // If no basic pay record is found, alert the user
                alert_toast('No basic pay record found for this employee. COE cannot be generated.', 'warning');
            }
        });
    });

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
    $('#requestCOE').on('click', function() {
        if (!$('#purpose').val().trim()) {
            alert_toast('Please enter a purpose.', 'warning');
            return;
        }

        const fileInput = $('#attachmentFile')[0];
        if (fileInput.files.length === 0) {
            alert_toast('Please upload a file before submitting.', 'warning');
            return;
        }

        var empNo = $('#emp_no').val();
        var employeeName = $('#employee_name').val();
        var position = $('#position').val();
        var prodline = $('#prodline').val();
        var empClass = $('#empclass').val();
        var dateHiredRaw = $('#date_hired').val();
        var genderRaw = $('#gender').val();
        var coeType = $('#coe_type').val();
        var purpose = $('#purpose').val();

        var dateHired = dateHiredRaw ? new Date(dateHiredRaw).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';

        var currentDate = new Date();
        var dateOfRequest = currentDate.toISOString().replace('T', ' ').replace('Z', '');

        var companyName = (prodline === 'PL5 (TPMI)') ? 'Telford Property Management Inc.' : 'Telford Svc. Philippines Inc.';

        if (empNo) {
            $.ajax({
                type: 'POST',
                url: '../getFunction/check_record.php',
                data: {
                    emp_no: empNo
                },
                dataType: 'json',
                success: function(response) {
                    var pcnStatus = (response.status === 'success' && empClass == 1) ? 1 : 0;
                    $('#pcn_status').val(pcnStatus);

                    // Build formData with all necessary fields and file
                    var formData = new FormData();
                    formData.append('EMPNAME', employeeName);
                    formData.append('EMPPOSITION', position);
                    formData.append('EMPCLASS', empClass);
                    formData.append('DATE_HIRED', dateHired);
                    formData.append('PURPOSE', purpose);
                    formData.append('COE_TYPE', coeType);
                    formData.append('DATE_REQUEST', dateOfRequest);
                    formData.append('PCN_STATUS', pcnStatus);
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
                },
                error: function(xhr, status, error) {
                    console.error('Error checking record:', error);
                    console.log(xhr.responseText);
                }
            });
        } else {
            alert('No employee number provided.');
        }
    });

    $('#employee_name').on('change', function() { // Change to 'change' event
        var employeeName = $(this).val();
        $.ajax({
            type: 'POST',
            url: '../getFunction/get_emp.php',
            data: {
                employee_name: employeeName
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#position').val(response.JOB_TITLE);
                    $('#emp_no').val(response.EMPLOYID);
                    $('#date_hired').val(response.DATEHIRED);
                    $('emp_class').val(response.EMPCLASS);
                    $('#prodline').val(response.PRODLINE);
                    console.log(response.EMPCLASS);
                    // // Fetch separation date immediately after setting emp_no
                    fetchBasicPay(response.EMPLOYID);
                } else {
                    alert('Employee not found in the database');
                    $('#emp_no').val(''); // Clear emp_no if employee not found
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching employee details:', error);
                console.log(xhr.responseText);
            }
        });
    });
    // Modify the fetchBasicPay function to accept a callback
    function fetchBasicPay(empNo, callback) {
        if (empNo) {
            $.ajax({
                type: 'POST',
                url: '../getFunction/get_salary.php',
                data: {
                    emp_no: empNo
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response data:', response);
                    var basicPay = response.salary_to_1; // Assuming this is the value you want
                    $('#basic_pay_card').text(basicPay);
                    $('#wage_order_card').text(basicPay);
                    $('#annual_salary').text(response.monthly_salary_annual);
                    $('#annual_salary_words').text(response.monthly_salary_words);
                    console.log(response.annual_salary_words);
                    console.log(response.annual_salary);
                    // Call the callback with the basicPay value
                    if (callback) {
                        callback(response);
                    }

                    // Adjust input widths after setting the values
                    $('#coeCard input[type="text"]').each(function() {
                        adjustInputWidth(this);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching salary:', error);
                    console.log(xhr.responseText);
                }
            });
        } else {
            alert('No salary record available in the PCN.');
        }
    }
</script>