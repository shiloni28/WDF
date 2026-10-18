
const API_URL = "api.php";

const form = document.getElementById("studentForm");
const idInput = document.getElementById("studentId");
const nameInput = document.getElementById("name");
const emailInput = document.getElementById("email");
const courseInput = document.getElementById("course");
const tableBody = document.getElementById("studentTable");
const messageBox = document.getElementById("message");
const loading = document.getElementById("loading");
const submitBtn = document.getElementById("submitBtn");
const cancelBtn = document.getElementById("cancelBtn");
const formTitle = document.getElementById("formTitle");
const searchInput = document.getElementById("search");

let students = [];
let debounceTimer;
let requestNumber = 0;

function showMessage(message, type = "info") {
    messageBox.textContent = message;
    messageBox.className = type;
}

function clearMessage() {
    messageBox.textContent = "";
    messageBox.className = "";
}
async function requestAPI(url, options = {}) {
    let response;

    try {
        response = await fetch(url, options);
    } catch (error) {
        throw new Error(
            "Network error. Check that Apache and MySQL are running."
        );
    }

    let result;
    try {
        result = await response.json();
    } catch (error) {
        throw new Error("Server returned invalid JSON.");
    }

    if (!response.ok || result.success !== true) {
        throw new Error(
            result.message || `Request failed: ${response.status}`
        );
    }

    return result;
}
function postAction(data) {
    return requestAPI(API_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(data)
    });
}
async function loadStudents() {
    const currentRequest = ++requestNumber;

    loading.hidden = false;
    loading.textContent = "Loading students...";
    try {
        const result = await requestAPI(API_URL, {
            method: "GET",
            headers: {
                "Accept": "application/json"
            }
        });

        if (currentRequest !== requestNumber) return;

        students = result.data;
        renderStudents();
        loading.hidden = true;

    } catch (error) {
        if (currentRequest !== requestNumber) return;

        loading.hidden = false;
        loading.textContent = error.message;
        showMessage(error.message, "error");
    }
}
function renderStudents() {
    tableBody.replaceChildren();

    const query = searchInput.value.trim().toLowerCase();

    const filtered = students.filter(student =>
        [student.name, student.email, student.course]
            .some(value => value.toLowerCase().includes(query))
    );

    if (filtered.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");

        cell.colSpan = 5;
        cell.textContent = query
            ? "No matching students found."
            : "No student records found.";

        row.appendChild(cell);
        tableBody.appendChild(row);
        return;
    }

    filtered.forEach(student => {
        const row = document.createElement("tr");

        [student.id, student.name, student.email, student.course]
            .forEach(value => {
                const cell = document.createElement("td");
                cell.textContent = value;
                row.appendChild(cell);
            });

        const actions = document.createElement("td");

        const editBtn = document.createElement("button");
        editBtn.type = "button";
        editBtn.className = "edit";
        editBtn.textContent = "Edit";
        editBtn.addEventListener("click", () => {
            editStudent(student.id);
        });

        const deleteBtn = document.createElement("button");
        deleteBtn.type = "button";
        deleteBtn.className = "delete";
        deleteBtn.textContent = "Delete";
        deleteBtn.addEventListener("click", () => {
            deleteStudent(student.id);
        });

        actions.append(editBtn, deleteBtn);
        row.appendChild(actions);
        tableBody.appendChild(row);
    });
}
form.addEventListener("submit", async event => {
    event.preventDefault();
    clearMessage();

    const id = idInput.value;

    const data = {
        action: id ? "update" : "create",
        name: nameInput.value.trim(),
        email: emailInput.value.trim(),
        course: courseInput.value.trim()
    };

    if (id) {
        data.id = Number(id);
    }

    submitBtn.disabled = true;

    try {
        const result = await postAction(data);

        showMessage(result.message, "success");
        resetForm();
        await loadStudents();

    } catch (error) {
        showMessage(error.message, "error");

    } finally {
        submitBtn.disabled = false;
    }
});
function editStudent(id) {
    const student = students.find(
        item => Number(item.id) === Number(id)
    );

    if (!student) {
        showMessage("Student not found.", "error");
        return;
    }

    idInput.value = student.id;
    nameInput.value = student.name;
    emailInput.value = student.email;
    courseInput.value = student.course;

    formTitle.textContent = "Edit Student";
    submitBtn.textContent = "Update Student";
    cancelBtn.hidden = false;

    form.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
}
async function deleteStudent(id) {
    const student = students.find(
        item => Number(item.id) === Number(id)
    );

    if (!student) {
        showMessage("Student not found.", "error");
        return;
    }

    if (!confirm(`Delete student "${student.name}"?`)) {
        return;
    }

    clearMessage();

    try {
        const result = await postAction({
            action: "delete",
            id: Number(id)
        });

        showMessage(result.message, "success");

        if (idInput.value === String(id)) {
            resetForm();
        }

        await loadStudents();

    } catch (error) {
        showMessage(error.message, "error");
    }
}
function resetForm() {
    form.reset();
    idInput.value = "";

    formTitle.textContent = "Add New Student";
    submitBtn.textContent = "Add Student";
    cancelBtn.hidden = true;
}

cancelBtn.addEventListener("click", () => {
    resetForm();
    clearMessage();
});
searchInput.addEventListener("input", () => {
    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        renderStudents();
    }, 300);
});
loadStudents();
