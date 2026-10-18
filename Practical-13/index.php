
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 13 - Student Management System</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 25px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            text-align: center;
            color: #1d4ed8;
        }

        .panel {
            background: white;
            padding: 22px;
            border-radius: 12px;
            margin: 20px 0;
            box-shadow: 0 3px 12px #0000000d;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
        }

        input {
            display: block;
            width: 100%;
            padding: 11px;
            margin-top: 7px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button {
            padding: 10px 14px;
            margin: 10px 5px 0 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            background: #2563eb;
        }

        button:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .secondary {
            background: #64748b;
        }

        .edit {
            background: #0f766e;
        }

        .delete {
            background: #dc2626;
        }

        #message {
            padding: 12px;
            margin-top: 12px;
            border-radius: 6px;
        }

        #message:empty {
            display: none;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .info {
            background: #dbeafe;
            color: #1e40af;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border-bottom: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #eff6ff;
        }

        @media (max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            body {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Student Management System</h1>
    <p style="text-align:center">
        PHP JSON API | Fetch API | MySQL CRUD
    </p>

    <div class="panel">
        <h2 id="formTitle">Add New Student</h2>

        <form id="studentForm">
            <input type="hidden" id="studentId">

            <div class="form-grid">
                <label>
                    Student Name
                    <input type="text" id="name"
                           maxlength="100" required>
                </label>

                <label>
                    Email Address
                    <input type="email" id="email"
                           maxlength="150" required>
                </label>

                <label>
                    Course
                    <input type="text" id="course"
                           maxlength="100" required>
                </label>
            </div>

            <button type="submit" id="submitBtn">
                Add Student
            </button>

            <button type="button" class="secondary"
                    id="cancelBtn" hidden>
                Cancel Edit
            </button>
        </form>

        <div id="message" role="status"
             aria-live="polite"></div>
    </div>

    <div class="panel">
        <h2>Student Records</h2>

        <label for="search">Search Students</label>
        <input type="search" id="search"
               placeholder="Search name, email or course">

        <p id="loading" role="status">Loading students...</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentTable"></tbody>
            </table>
        </div>
    </div>

</div>

<script src="app.js"></script>
</body>
</html>
