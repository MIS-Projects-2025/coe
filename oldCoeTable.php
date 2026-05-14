<?php
$c = 1;
$EMPLOYID = $_settings->userdata('EMPLOYID');
$DEPT = $_settings->userdata('DEPARTMENT');
$POSITION = $_settings->userdata('EMPPOSITION');
$STATION = $_settings->userdata('STATION');
$EMPCLASS = $_settings->userdata('EMPCLASS');
// echo $EMPCLASS;
$stmtAdmin = $conn->prepare("SELECT admin_id FROM admin_list WHERE admin_id = ?");
$stmtAdmin->bind_param("s", $EMPLOYID); // Assuming admin_id is a string; change type if necessary
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();

// Check if the user is an admin
if ($resultAdmin->num_rows > 0) {
    $is_admin = true;
} else {
    $is_admin = false; // Not an admin
}
$empClass = [
    1 => 'Direct',
    2 => 'Non-Exempt',
    3 => 'Exempt',
    4 => 'Section Head',
    5 => 'Manager',
    6 => 'Senior Management'
];
$coeTypes = [
    1 => 'Without Compensation',
    2 => 'COE Inactive',
    3 => 'COE With Compensation'
];
?>

<link rel="stylesheet" href="../admin/COE/COETable/coeTable.css">
<h3 class="font-italic">COE Records</h3>
<?php if ($is_admin && $POSITION != 5) : ?>
    <button id="exportButton" class="btn btn-success btn-sm m-2 float-right"><i class="fas fa-file-excel mr-2"></i>Export to Excel</button>
<?php endif; ?>
<br>
&nbsp;&nbsp;&nbsp;
<div class="card card-success card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="statusTabs" role="tablist">
            <li class="pt-2 px-3">
                <h3 class="card-title"><i><strong>Status</strong></i></h3>
            </li>
            <li class="nav-item">
                <a class="nav-link active" id="pending-tab" data-status="pending" data-toggle="tab" href="#pending" role="tab">
                    <?php if ($POSITION == 4 && $DEPT != 'Human Resource') { ?>
                        <i class="fas fa-hourglass-start"></i> COE Request Records
                    <?php } else { ?>
                        <i class="fas fa-hourglass-start"></i> Pending
                    <?php } ?>
                </a>
            </li>
            <?php if ($POSITION == 4 && $DEPT != 'Human Resource') { ?>
            <?php } else { ?>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="history-tab" data-status="history" data-toggle="tab" href="#history" role="tab">
                        <i class="fas fa-history nav-icon"></i> History <span class="badge badge-warning" id="count-status-1">0</span>
                    </a> <?php } ?>
                </li>
        </ul>
    </div>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <div class="container-fluid overflow-auto">
                <?php if ($is_admin && $POSITION != 5) : ?>
                    <br>
                    <button class="btn btn-danger btn-sm float-right m-2" id="disapproveSelected">Disapprove Selected</button>
                    <button class="btn btn-primary btn-sm float-right m-2" id="approveSelected">Approve Selected</button>
                    <br>
                    <br>
                    &nbsp;
                <?php endif; ?>
                <br>
                <table id="coe_pending" class="table table-borderless text-left text-xs m-2">
                    <thead>
                        <tr class="bg-gradient-light">
                            <td>
                                <?php if ($is_admin) : ?>
                                    <input type="checkbox" id="selectAll" />
                                <?php endif; ?>
                            </td>
                            <td>#</td>
                            <td>Employee ID</td>
                            <td>Employee Name</td>
                            <td>Job Title</td>
                            <td>Rate Code</td>
                            <td>Purpose</td>
                            <td>Date Requested</td>
                            <th>Type</th>
                            <td>Status</td>
                            <?php if (!($POSITION == 4 && $DEPT != 'Human Resource')) : ?>
                                <td>Action</td>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $employID = $_settings->userdata('EMPLOYID');
                        // Initialize an array to hold employee IDs based on the department
                        $employeeIds = [];
                        // Modify the employee query based on the department
                        if ($DEPT !== 'Human Resource') {
                            $empqry = $empListConnection->query("SELECT EMPLOYID FROM employee_masterlist WHERE APPROVER1 = '{$EMPLOYID}' OR APPROVER2 = '{$EMPLOYID}' OR EMPLOYID = '{$EMPLOYID}' ");
                        } else {
                            $empqry = $empListConnection->query("SELECT EMPLOYID FROM employee_masterlist WHERE DEPARTMENT ='Human Resource' OR APPROVER1 = '{$EMPLOYID}' OR APPROVER2 = '{$EMPLOYID}' OR EMPLOYID = '{$EMPLOYID}' ");
                        }
                        // Fetch employee IDs based on the department and position
                        // if ($DEPT === 'Human Resource' && $POSITION == 1) {
                        //     // HR with position 1 can see all records with status 1
                        //     $coeqry = "SELECT * FROM coe_record WHERE STATUS = 0 AND EMPID = '{$EMPLOYID}' OR (EMPID = '{$EMPLOYID}' AND STATUS = 0) ORDER BY ID DESC";
                        // }
                        if ($DEPT === 'Human Resource') {
                            // HR with position not 1 can see status 0 and 1 records from HR department,
                            // and status 1 records from other departments
                            // $coeqry = "SELECT * FROM coe_record WHERE STATUS IN(0,1) UNION SELECT * FROM coe_record WHERE EMPID = '{$EMPLOYID}' AND STATUS IN(0,1) ORDER BY ID DESC";
                            $coeqry = "SELECT * FROM coe_record WHERE STATUS = 0 AND EMPID != '{$EMPLOYID}' AND EMPCLASS IN(1,2) UNION SELECT * FROM coe_record WHERE STATUS IN(0,1) AND EMPCLASS != 1 AND COE_TYPE =3 UNION SELECT * FROM coe_record WHERE STATUS IN(0,1) ORDER BY ID DESC";
                        } elseif ($DEPT !== 'Human Resource' && $POSITION == 1) {
                            // Non-HR with position 1 can see their own records with status 0 and 1
                            $coeqry = "SELECT * FROM coe_record WHERE EMPID = '{$EMPLOYID}' AND STATUS IN(0,1) ORDER BY ID DESC";
                        } elseif ($POSITION == 5) {
                            $coeqry = "SELECT * FROM coe_record WHERE STATUS IN(0,1) ORDER BY ID DESC";
                        } else {
                            // Non-HR Department with Position Not 1
                            // They can see all records with STATUS = 0 for employees under their supervision
                            // Exclude their own records
                            $empqry = $empListConnection->query("SELECT EMPLOYID FROM employee_masterlist WHERE APPROVER1 = '{$EMPLOYID}' OR APPROVER2 = '{$EMPLOYID}'");

                            while ($emp_list = $empqry->fetch_assoc()) {
                                $employeeIds[] = $emp_list['EMPLOYID'];
                            }

                            // Include the current employee only if they are also an approver for someone else
                            $selfCheckQry = $empListConnection->query("SELECT EMPLOYID FROM employee_masterlist WHERE APPROVER1 = '{$EMPLOYID}' OR APPROVER2 = '{$EMPLOYID}'");
                            if ($selfCheckQry->num_rows > 0) {
                                $employeeIds[] = $EMPLOYID; // Add self if they have employees under them
                            }

                            // Fetch records with STATUS = 0, ensuring to exclude their own records
                            $coeqry = "SELECT * FROM coe_record WHERE EMPID IN ('" . implode("','", $employeeIds) . "') AND STATUS = 0
                                UNION
                                SELECT * FROM coe_record WHERE EMPID ='{$EMPLOYID}' AND STATUS IN (0,1) ORDER BY ID DESC";
                        }
                        // echo $coeqry;
                        $magnadata = $conn->query($coeqry);
                        while ($row = $magnadata->fetch_assoc()) : ?>
                            <tr>
                                <td>
                                    <?php if ($is_admin) : ?>
                                        <input type="checkbox" name="selected_ids[]" value="<?php echo $row['ID']; ?>" />
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['ID'] ?></td>
                                <td><?php echo $row['EMPID'] ?></td>
                                <td><?php echo $row['EMPNAME'] ?></td>
                                <td><?php echo $row['EMPPOSITION'] ?></td>
                                <td>
                                    <?php
                                    $empClassText = isset($empClass[$row['EMPCLASS']]) ? $empClass[$row['EMPCLASS']] : 'Unknown EMPCLASS';
                                    echo $empClassText;
                                    ?>
                                </td>
                                <td><?php echo $row['PURPOSE'] ?></td>
                                <td><?php echo date('F, d Y', strtotime($row['DATE_REQUEST'])); ?></td>
                                <td>
                                    <?php
                                    $coeText = isset($coeTypes[$row['COE_TYPE']]) ? $coeTypes[$row['COE_TYPE']] : 'Unknown COE_TYPE';
                                    echo $coeText;
                                    ?>

                                </td>
                                <td>
                                    <?php
                                    $status = $row['STATUS'];
                                    $badgeClass = '';
                                    $statusText = '';

                                    if ($status == 0) {
                                        $badgeClass = 'badge bg-warning text-dark'; // Yellow for "For Approval"
                                        $statusText = 'For Approval';
                                    } elseif ($status == 1 & $row['COE_TYPE'] != 3) {
                                        $badgeClass = 'badge bg-success'; // Green for "Approved"
                                        $statusText = 'Approved';
                                    } elseif ($status == 1 && $row['EMPCLASS'] == 1 && $row['PCN_STATUS'] != 0 && $row['COE_TYPE'] == 3) {
                                        $badgeClass = 'badge bg-success'; // Green for "Approved"
                                        $statusText = 'Approved';
                                    } elseif ($status == 1 && $row['EMPCLASS'] != 1) {
                                        $badgeClass = 'badge bg-success'; // Green for "Approved"
                                        $statusText = 'For Processing';
                                    } elseif ($status == 1 && $row['EMPCLASS'] == 1 && $row['PCN_STATUS'] == 0 & $row['COE_TYPE'] == 3) {
                                        $badgeClass = 'badge bg-success'; // Green for "Approved"
                                        $statusText = 'For Processing';
                                    } elseif ($status == 2) {
                                        $badgeClass = 'badge bg-info'; // Green for "Approved"
                                        $statusText = 'Generated';
                                    } elseif ($status == 3) {
                                        $badgeClass = 'badge bg-danger'; // Red for "Disapproved"
                                        $statusText = 'Disapproved';
                                    } elseif ($status == 5) {
                                        $badgeClass = 'badge bg-danger'; // Red for "Disapproved"
                                        $statusText = 'Available for Claim';
                                    } else {
                                        $badgeClass = 'badge bg-secondary'; // Gray for "Unknown Status"
                                        $statusText = 'Unknown Status';
                                    }

                                    echo "<span class='$badgeClass'>$statusText</span>";
                                    ?>
                                </td>
                                <?php if (!($POSITION == 4 && $DEPT != 'Human Resource')) : ?>
                                    <td align="left">
                                        <?php
                                        // Collect all actions in an array
                                        $actions = [];

                                        // HR Actions
                                        if ($DEPT == 'Human Resource') {
                                            if ($row['STATUS'] == 0 && $EMPLOYID != $row['EMPID']) {
                                                // Approve and Disapprove options for HR
                                                $actions[] = '<a class="dropdown-item approve-btn" href="javascript:void(0)" data-id="' . $row['ID'] . '">
                <span class="fa fa-check"></span> Approve
            </a>';
                                                $actions[] = '<a class="dropdown-item disapprove-btn" href="javascript:void(0)" data-id="' . $row['ID'] . '">
                <span class="fa fa-times"></span> Disapprove
            </a>';
                                                $actions[] = '<a class="dropdown-item view-attachments-btn" href="javascript:void(0)" data-id="' . $row['ID'] . '">
    <span class="fa fa-paperclip"></span> View Attachment
</a>';
                                            }
                                            if ($row['STATUS'] == 1) {
                                                // Generate File option for employees with status 1
                                                $generateLink = '';
                                                if ($row['COE_TYPE'] == 1) {
                                                    $generateLink = "?page=COE/generateCOE&id=" . $row['ID'];
                                                } elseif ($row['COE_TYPE'] == 3 && $row['EMPCLASS'] == 1 && $row['PCN_STATUS'] == 1 && $row['EMPID'] != $employID) {
                                                    $generateLink = "?page=COE/generateCOEWithComp&id=" . $row['ID'];
                                                } elseif ($row['COE_TYPE'] == 3 && $row['EMPCLASS'] != 1 && $row['PCN_STATUS'] != 1) {
                                                    $actions[] = '<a class="dropdown-item claim-btn" href="javascript:void(0)" data-id="' . $row['ID'] . '">
                                                  <span class="fa fa-hand-pointer"></span> Ready for Claim
                                            </a>';
                                                }
                                                // Only add the "Generate File" option if $generateLink is not empty
                                                if (!empty($generateLink)) {
                                                    $actions[] = '<a class="btn btn-xs btn-light generate-btn" href="' . $generateLink . '">
        <span class="fa fa-file"></span> Generate File
    </a>';
                                                }
                                            }
                                        } else {
                                            // Employee Actions
                                            if ($row['STATUS'] == 1) {
                                                // Generate File option for employees with status 1
                                                $generateLink = '';
                                                if ($row['COE_TYPE'] == 1) {
                                                    $generateLink = "?page=COE/generateCOE&id=" . $row['ID'];
                                                } elseif ($row['COE_TYPE'] == 3 && $row['EMPCLASS'] == 1 && $row['PCN_STATUS'] == 1) {
                                                    $generateLink = "?page=COE/generateCOEWithComp&id=" . $row['ID'];
                                                } elseif ($row['COE_TYPE'] == 3 && $row['EMPCLASS'] != 1 && $row['PCN_STATUS'] != 1) {
                                                }
                                                // Only add the "Generate File" option if $generateLink is not empty
                                                if (!empty($generateLink)) {
                                                    $actions[] = '<a class="btn btn-xs btn-light generate-btn" href="' . $generateLink . '">
        <span class="fa fa-file"></span> Generate File
    </a>';
                                                }
                                            }
                                        }

                                        // // Delete option for all users
                                        // $actions[] = '<a class="dropdown-item delete_data" href="javascript:void(0)" data-id="' . $row['ID'] . '">
                                        //     <span class="fa fa-trash text-danger"></span> Delete
                                        // </a>';

                                        // Check the number of actions
                                        if (count($actions) >= 1) {
                                            // Render as dropdown
                                            echo '<a href="#" class="text-dark dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
        </a>
        <div class="dropdown-menu" role="menu">';
                                            foreach ($actions as $action) {
                                                echo $action;
                                            }
                                            echo '</div>';
                                        } elseif (count($actions) == 1) {
                                            // Render as a single button
                                            echo $actions[0];
                                        } else {
                                            echo 'No Action';
                                        }
                                        ?>
                                    </td> <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div><!-- End of table-responsive -->
        </div><!-- End of card -->

        <div class="tab-pane fade" id="history" role="tabpanel">
            <div class="container-fluid overflow-auto">
                <br>
                <!-- Filter Section -->
                <div class="row">
                    <div class="col-md-2 m-2">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <button class="btn btn-light btn-sm dropdown-toggle" type="button">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                                <select id="status-filter" class="form-control form-control-sm">
                                    <option value="all">All Status</option>
                                    <option value="disapproved">Disapproved</option>
                                    <option value="approved">Approved</option>
                                </select>

                            </div>
                        </div>
                    </div>
                </div>
                <table id="coe_record_history" class="table table-borderless text-left text-xs" style="width: 100%;">
                    <thead>
                        <tr class="bg-gradient-light">
                            <td>#</td>
                            <td>Employee ID</td>
                            <td>Employee Name</td>
                            <td>Job Title</td>
                            <td>Rate Code</td>
                            <td>Purpose</td>
                            <td>Date Requested</td>
                            <th>Type</th>
                            <td>Status</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $c = 1;
                        $EMPLOYID = $_settings->userdata('EMPLOYID');
                        $DEPT = $_settings->userdata('DEPARTMENT');
                        $POSITION = $_settings->userdata('EMPPOSITION');
                        $STATION = $_settings->userdata('STATION');
                        // echo  $EMPLOYID;
                        // echo $POSITION;
                        // echo $DEPT;
                        // Initialize an array to hold employee IDs based on the department
                        $employeeIds = [];

                        // Modify the employee query based on the department
                        if ($DEPT !== 'Human Resource') {
                            $empqry = $empListConnection->query("SELECT EMPLOYID FROM employee_masterlist WHERE APPROVER1 = '{$EMPLOYID}' OR APPROVER2 = '{$EMPLOYID}' OR EMPLOYID = '{$EMPLOYID}' ");
                        } else {
                            $empqry = $empListConnection->query("SELECT EMPLOYID FROM employee_masterlist WHERE DEPARTMENT ='Human Resource' OR APPROVER1 = '{$EMPLOYID}' OR APPROVER2 = '{$EMPLOYID}' OR EMPLOYID = '{$EMPLOYID}' ");
                        }

                        if ($DEPT === 'Human Resource') {
                            // HR with position not 1 can see status 0 and 1 records from HR department,
                            // and status 1 records from other departments
                            $coeqry1 = "SELECT * FROM coe_record WHERE EMPID != '{$EMPLOYID}'AND STATUS != 0 UNION SELECT * FROM coe_record WHERE EMPID = '{$EMPLOYID}' AND STATUS NOT IN(0,1) ORDER BY ID DESC
                             ";
                        } elseif ($DEPT !== 'Human Resource' && $POSITION == 1) {
                            // Non-HR with position 1 can see their own records with status 0 and 1
                            $coeqry1 = "SELECT * FROM coe_record WHERE EMPID = '{$EMPLOYID}' AND STATUS NOT IN(0,1) ORDER BY ID DESC";
                        } elseif ($POSITION == 5) {
                            $coeqry1 = "SELECT * FROM coe_record WHERE STATUS NOT IN(0,1) ORDER BY ID DESC";
                        } else {
                            // Non-HR Department with Position Not 1
                            // They can see all records with STATUS = 0 for employees under their supervision
                            // Exclude their own records
                            $empqry = $empListConnection->query("SELECT EMPLOYID FROM employee_masterlist WHERE APPROVER1 = '{$EMPLOYID}' OR APPROVER2 = '{$EMPLOYID}'");

                            while ($emp_list = $empqry->fetch_assoc()) {
                                $employeeIds[] = $emp_list['EMPLOYID'];
                            }

                            // Include the current employee only if they are also an approver for someone else
                            $selfCheckQry = $empListConnection->query("SELECT EMPLOYID FROM employee_masterlist WHERE APPROVER1 = '{$EMPLOYID}' OR APPROVER2 = '{$EMPLOYID}'");
                            if ($selfCheckQry->num_rows > 0) {
                                $employeeIds[] = $EMPLOYID; // Add self if they have employees under them
                            }

                            // Fetch records with STATUS = 0, ensuring to exclude their own records
                            $coeqry1 = "SELECT * FROM coe_record WHERE EMPID IN ('" . implode("','", $employeeIds) . "') AND STATUS != 0 AND EMPID !='{$EMPLOYID}'
                                UNION
                                SELECT * FROM coe_record WHERE EMPID ='{$EMPLOYID}' AND STATUS NOT IN (0,1) ORDER BY ID DESC";
                        }
                        // echo $coeqry1;
                        $magnadata = $conn->query($coeqry1);
                        while ($row = $magnadata->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['ID'] ?></td>
                                <td><?php echo $row['EMPID'] ?></td>
                                <td><?php echo $row['EMPNAME'] ?></td>
                                <td><?php echo $row['EMPPOSITION'] ?></td>
                                <td>
                                    <?php
                                    $empClassText = isset($empClass[$row['EMPCLASS']]) ? $empClass[$row['EMPCLASS']] : 'Unknown EMPCLASS';
                                    echo $empClassText;
                                    ?>
                                </td>
                                <td><?php echo $row['PURPOSE'] ?></td>
                                <td><?php echo date('F, d Y', strtotime($row['DATE_REQUEST'])); ?></td>
                                <td>
                                    <?php
                                    $coeText = isset($coeTypes[$row['COE_TYPE']]) ? $coeTypes[$row['COE_TYPE']] : 'Unknown COE_TYPE';
                                    echo $coeText;
                                    ?>

                                </td>
                                <td>
                                    <?php
                                    $status = $row['STATUS'];
                                    $badgeClass = '';
                                    $statusText = '';

                                    if ($status == 0) {
                                        $badgeClass = 'badge bg-warning text-dark'; // Yellow for "For Approval"
                                        $statusText = 'For Approval';
                                    } elseif ($status == 1) {
                                        $badgeClass = 'badge bg-success'; // Green for "Approved"
                                        $statusText = 'Approved';
                                    } elseif ($status == 2) {
                                        $badgeClass = 'badge bg-info'; // Green for "Approved"
                                        $statusText = 'Generated';
                                    } elseif ($status == 3) {
                                        $badgeClass = 'badge bg-danger'; // Red for "Disapproved"
                                        $statusText = 'Disapproved';
                                    } elseif ($status == 5) {
                                        $badgeClass = 'badge bg-warning'; // Red for "Disapproved"
                                        $statusText = 'Available for Claim';
                                    } else {
                                        $badgeClass = 'badge bg-secondary'; // Gray for "Unknown Status"
                                        $statusText = 'Unknown Status';
                                    }

                                    echo "<span class='$badgeClass'>$statusText</span>";
                                    ?>
                                </td>
                                <td align="left">
                                    <?php
                                    // Check if the record is equal to the user's data (EMPLOYID) and the status is equal to 2
                                    if ($row['EMPID'] == $EMPLOYID && $row['STATUS'] == 2) {
                                        // Generate File option for employees with status 2
                                        $generateLink = '';
                                        if ($row['COE_TYPE'] == 1) {
                                            $generateLink = "?page=COE/generateCOE&id=" . $row['ID'];
                                        } elseif ($row['COE_TYPE'] == 3 && $row['EMPCLASS'] == 1) {
                                            $generateLink = "?page=COE/generateCOEWithComp&id=" . $row['ID'];
                                        }
                                        if (!empty($generateLink)) {
                                            echo '<a class="btn btn-xs btn-light generate-btn" href="' . $generateLink . '">
        <span class="fa fa-file"></span> Generate File
    </a>';
                                        }
                                    } elseif ($row['STATUS'] == 3) {
                                        // View Remarks option for employees with status 3
                                        echo '<button class="btn btn-xs btn-light view-remarks-btn" data-toggle="modal" data-target="#viewRemarksModal-' . $row['ID'] . '">
                                            <span class="fa fa-eye"></span> Remarks
                                        </button>';
                                    ?>
                                        <!-- Modal -->
                                        <div class="modal fade" id="viewRemarksModal-<?php echo $row['ID']; ?>" tabindex="-1" role="dialog" aria-labelledby="remarksModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="remarksModalLabel">View Remarks</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php echo $row['REMARKS']; ?>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    } else {
                                        echo 'No Action';
                                    }

                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <br>
            </div><!-- End of table-responsive -->
        </div>
    </div>
</div><!-- End of card-success card-tabs -->
<!-- Modal for Remarks -->
<div class="modal fade" id="remarksModal" tabindex="-1" role="dialog" aria-labelledby="remarksModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="remarksModalLabel">Disapprove Record</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form id="remarksForm">
                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitRemarks">Submit</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="attachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentModalLabel">Attachments</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="attachmentContent">
                <!-- Attachments will load here -->
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>


<script src="<?php echo base_url; ?>admin/COE/COETable/function.js"></script>