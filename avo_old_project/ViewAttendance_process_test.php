<?php
session_start();
include("config.php"); // Ensure this file contains the database connection code
include("access.php");

// Set default session variables if not already set
if (!isset($_SESSION['user'])) { 
    $_SESSION['user'] = 'Guest'; // Default user if session is not set
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>View Attendance - <?php echo htmlspecialchars($_SESSION['user']); ?></title>
    <link href="style.css" rel="stylesheet" type="text/css" />
    <link href="menu.css" rel="stylesheet" type="text/css" />
    <link href="../datepicker/date_css.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="../datepicker/datepick_js.js" type="text/javascript"></script>
    <style>
        .space {
            margin-left: 80px;
        }

        .addcolor {
            font-size: 20px;
            color: #C60000;
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
        }
    </style>
    <script>
        function tableToExcel(table, name) {
            var uri = 'data:application/vnd.ms-excel;base64,',
                template =
                '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
                base64 = function(s) {
                    return window.btoa(unescape(encodeURIComponent(s)))
                },
                format = function(s, c) {
                    return s.replace(/{(\w+)}/g, function(m, p) {
                        return c[p];
                    })
                };
            if (!table.nodeType) table = document.getElementById(table);
            var ctx = {
                worksheet: name || 'Worksheet',
                table: table.innerHTML
            };
            window.location.href = uri + base64(format(template, ctx));
        }

        function fetchAttendance(page, perpg) {
            var Branch = document.getElementById("Branch").value;
            var Department = document.getElementById("Department").value;
            var Employee_name = document.getElementById("Employee_name").value;
            var Employee_code = document.getElementById("Employee_code").value;
            var from = document.getElementById("from").value;
            var to = document.getElementById("to").value;

            var Page = page || '';
            var perp = perpg || '25';

            document.getElementById("show").innerHTML = "<center><img src='loader.gif' /></center>";

            $.ajax({
                type: 'POST',
                url: 'ViewAttendance_process_test.php',
                data: {
                    Branch: Branch,
                    Department: Department,
                    Employee_name: Employee_name,
                    Employee_code: Employee_code,
                    from: from,
                    to: to,
                    Page: Page,
                    perpg: perp
                },
                success: function(response) {
                    document.getElementById("show").innerHTML = response;
                },
                error: function() {
                    document.getElementById("show").innerHTML = "Error fetching data.";
                }
            });
        }

        function printPage() {
            window.print();
        }
    </script>
</head>

<body>
    <?php include("menubar.php"); ?>

    <h2 class="h2color" align="center">View Attendance</h2>
    <div>
        <table border="0" align="center">
            <tr>
                <th>Branch:</th>
                <th>Department:</th>
                <th>Employee name:</th>
                <th>Employee code:</th>
                <th>From date:</th>
                <th>To date:</th>
            </tr>
            <tr>
                <td>
                    <select name="Branch" id="Branch">
                        <option value="">Select</option>
                        <?php 
                        $qry = "SELECT * FROM avo_branch";
                        $result = mysqli_query($con1, $qry);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . htmlspecialchars($row['branch_name']) . "'>" . htmlspecialchars($row['branch_name']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Error loading branches</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select name="Department" id="Department">
                        <option value="">Select</option>
                        <?php 
                        $qry = "SELECT * FROM department";
                        $result = mysqli_query($con1, $qry);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . htmlspecialchars($row['dep_name']) . "'>" . htmlspecialchars($row['dep_name']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Error loading departments</option>";
                        }
                        ?>
                    </select>
                </td>
                <td><input type="text" name="Employee_name" id="Employee_name" /></td>
                <td><input type="text" name="Employee_code" id="Employee_code" /></td>
                <td><input type="date" name="from" id="from" /></td>
                <td><input type="date" name="to" id="to" /></td>
                <td><input type="button" value="Search" onclick="fetchAttendance('', '')" /></td>
            </tr>
        </table>

        <table align="right">
            <tr>
                <td>
                    <input type="button" onclick="tableToExcel('show', 'Attendance Data')" value="Export to Excel" />
                    <button onclick="printPage()">Print this page</button>
                </td>
            </tr>
        </table>
    </div>

    <div id="show"></div>
</body>

</html>
