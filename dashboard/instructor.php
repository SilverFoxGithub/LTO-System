<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/styles/admin-dash.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Teacher Dashboard</h2>
            <button id="logoutBtn">Logout</button>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Enrolled Students</h1>
            </div>
            <div class="student-list">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Lesson Progress</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <tr>
                            <td>Harry Potter</td>
                            <td>Lesson 5 of 10</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 50%;">50%</div>
                                </div>
                            </td>
                            <td>
                                <button id="deleteUserBtn">Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../assets/instructor.js"></script>
</body>
</html>