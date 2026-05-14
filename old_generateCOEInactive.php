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
                margin-left: 40px;
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

?>
<div class="card" id="form-card">
    <div class="card-header"></div>
    <div class="card-body">
        <form id="coe-form">
            <div class="alert alert-light mt-2" role="alert">
                <strong>Note:</strong> Please ensure the following settings when printing:
                <ul class="mb-0">
                    <li>Margins are set to the default.</li>
                    <li>Headers, and footers along with the background graphics are unchecked.</li>
                </ul>
            </div>
            <div class="callout callout-success">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" id="coe_type" name="coe_type" class="form-control form-control-sm bg-body-tertiary rounded" value="2" hidden>
                            <input type="text" id="gender" name="gender" class="form-control form-control-sm bg-body-tertiary rounded" hidden>
                            <input type="text" id="prodline" name="prodline" class="form-control form-control-sm bg-body-tertiary rounded" hidden>
                            <input type="text" id="empclass" name="empclass" class="form-control form-control-sm bg-body-tertiary rounded" value="<?php echo $empclass; ?>" hidden>
                            <label class="control-label">Employee Name:</label>
                            <select id="employee_name" name="employee_name" class="form-control select2 form-control-sm bg-body-tertiary rounded" required>
                                <option value="">Select an option</option>
                                <?php
                                $qry = $empListConnection->query("SELECT * FROM `employee_masterlist` WHERE `EMPLOYID` != 0 AND `ACCSTATUS` = 2");
                                while ($row_endorse = $qry->fetch_assoc()) :
                                ?>
                                    <option value="<?php echo $row_endorse['EMPNAME'] ?>"><?php echo $row_endorse['EMPLOYID'] . " - " . $row_endorse['EMPNAME']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <input type="text" id="emp_no" name="emp_no" class="form-control form-control-sm bg-body-tertiary rounded" hidden>
                        <div class="col-md-3">
                            <label class="control-label">Position:</label>
                            <input type="text" id="position" name="position" class="form-control form-control-sm bg-body-tertiary rounded" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Date Hired:</label>
                            <input type="text" id="date_hired" name="date_hired" class="form-control form-control-sm bg-body-tertiary rounded" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Seperation Date:</label>
                            <input type="text" id="sep_date" name="sep_date" class="form-control form-control-sm bg-body-tertiary rounded" readonly>
                        </div>
                    </div>

                </div>
            </div>
            <button type="button" class="btn btn-primary float-right" id="generateCOE">Generate COE</button>
        </form>
    </div>
</div>

<div class="card" id="coeCard" hidden>
    <div class="card-header text-center" id="image_header">
        <img src="<?php echo base_url ?>uploads/logo.png" alt="Store Logo" class="">
        <p class="text-center text-xl">Telford Svc. Philippines Inc.</p>
    </div><br><br><br>
    <div class="card-body">
        <h1 class="text-center text-xl"><b>CERTIFICATE OF EMPLOYMENT</b></h1><br>
        <div class="row justify-content-center">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <p class="text-lg-plus text-justify"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that <span id="gender_card"></span></b> <span id="emp_name"></span> is an employee of Telford
                    Svc. Phils. Inc. from <b><span id="date_hired_card"></span></b> up to <b><span id="sep_date_hired"></span> </b>with the position of
                    <b><span id="position_card"></span></b>.
                </p>
                <br>
                <p class="text-lg-plus text-justify">This certification is being issued upon the request of <i><span id="gender_card1"></span> <span id="emp_name_request"></span></i>
                    for whatever legal purpose(s) it may serve.</p>
                <br>
                <p class="text-lg-plus text-justify">Done this <span id="date_of_request_card"></span> at Telford Svc Phils., Inc. Gateway
                    Business Park, Brgy. Javalera, General Trias, Cavite.
                </p> <br><br><br><br><br><br><br>
                <img src="<?php echo base_url ?>uploads/signature1.png" alt="Store Logo" class="signature" style="float: left;">
                <br><br><br><br><br><br><br> <br><br><br>
                <p class="note">This is a system generated certificate. An official seal is required.</p>
            </div>
            <div class="col-md-3"></div>
        </div>
    </div>

</div>
<script>
    start_loader(); //
    $(document).ready(function() {
        end_loader();
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
    $('#generateCOE').on('click', function() {
        var employeeName = $('#employee_name').val();
        var position = $('#position').val();
        var empclass = $('#empclass').val();
        var prodline = $('#prodline').val();
        var dateHiredRaw = $('#date_hired').val();
        var coeType = $('#coe_type').val();
        var genderRaw = $('#gender').val();
        console.log("Employee Name:", employeeName); // Debugging statement
        console.log("Position:", position); // Debugging statement
        console.log("Prodline:", prodline); // Debugging statement
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
        var dateOfRequest1 = currentDate.toISOString().replace('T', ' ').replace('Z', ''); // Add the ordinal suffix (st, nd, rd, th)
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
        if (genderRaw == 1) {
            genderPrefix = 'Mr.';
        } else if (genderRaw == 2) {
            genderPrefix = 'Ms.';
        }
        // Process the employee name from the form input
        var nameResult = formatEmployeeName(employeeName); // Get name from input and format it
        $('#emp_name').text(nameResult.formattedName);
        $('#gender_card').text(genderPrefix);
        $('#gender_card1').text(genderPrefix);
        $('#position_card').text(position);
        $('#date_hired_card').text(dateHired);
        $('#sep_date_hired').text(sepDate);
        $('#emp_name_request').text(nameResult.lastName);
        $('#purpose_card').text(purpose);
        $('#date_of_request_card').text(formattedDate);
        // $('#form-card').attr('hidden', true);
        // $('#coeCard').removeAttr('hidden');
        // Optional: Adjusting the width of dynamically inserted content if needed
        $('#coeCard span').each(function() {
            // $(this).addClass('input-custom');
            adjustInputWidth(this);
        });

        // $('#coeCard input').addClass('border-left-0 border-right-0 border-top-0 border-info text-center ')
        $('#coeCard').removeAttr('hidden'); // Make it visible for printing
        $('.main-footer .first').attr('hidden', true);
        $('.main-footer .second').attr('hidden', true);
        $('.main-footer .top').removeAttr('hidden');
        $('.main-footer img').removeAttr('hidden');
        $('.main-footer img').addClass('float-right d-none d-sm-inline-block');
        $('.main-footer .footer').removeAttr('hidden');
        // Trigger the print dialog
        window.print();

        // Optionally hide the COE card again after printing
        $('#coeCard').attr('hidden', true);
        console.log({
            EMPNAME: employeeName,
            EMPPOSITION: position,
            DATE_HIRED: dateHired,
            PURPOSE: purpose,
            DATE_REQUEST: dateOfRequest
        });

        // AJAX call to save the data to the database
        $.ajax({
            url: '../classes/Record.php?f=save_record',
            type: 'POST',
            data: {
                EMPNAME: employeeName,
                EMPPOSITION: position,
                EMPCLASS: empclass,
                DATE_HIRED: dateHired,
                PURPOSE: purpose,
                COE_TYPE: coeType,
                DATE_REQUEST: dateOfRequest1
            },
            success: function(response) {
                if (response.status === 'success') {

                    $('#coeCard').attr('hidden', true); // Hide the COE card
                    location.reload(); // Reload the page
                } else {
                    location.reload(); // Reload the page

                }
            },
            error: function(xhr, status, error) {
                console.error('Error saving data:', error);
                alert('An error occurred while saving data. Please try again.');
            }
        });
    });
    // Event listener for when the print dialog is closed
    // window.onafterprint = function() {
    //     location.reload(); // Reload the page
    // };
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
                    $('#prodline').val(response.PRODLINE);
                    // Fetch separation date immediately after setting emp_no
                    fetchSeparationDate(response.EMPLOYID);
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

    // Function to fetch the separation date
    function fetchSeparationDate(empNo) {
        if (empNo) {
            $.ajax({
                type: 'POST',
                url: '../getFunction/get_sep_date.php',
                data: {
                    emp_no: empNo
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Parse the date from the response
                        const date = new Date(response.date_resigned);

                        // Define options for formatting the date
                        const options = {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        };

                        // Format the date to "Month Day Number, Year Number"
                        const formattedDate = date.toLocaleDateString('en-US', options);

                        // Set the formatted date in the input field
                        $('#sep_date').val(formattedDate);
                    } else {
                        alert('Separation date not found for this employee number');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching separation date:', error);
                    console.log(xhr.responseText);
                }
            });
        } else {
            alert('Please enter a valid employee number.');
        }
    }
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