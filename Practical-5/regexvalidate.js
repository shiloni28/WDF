
function validateLogin() {

    let username = document.getElementById("username").value.trim();
    let password = document.getElementById("password").value.trim();


    let usernamePattern = /^25DCE\d{3}$/;
   let emailvalidation = /^[A-Za-z0-9._+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
    let passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=(?:.*[a-z]){2,}).{6,}$/;

    document.getElementById("username").style.border = "";
    document.getElementById("password").style.border = "";


    if (username == "") {

        alert("Please enter Student ID / E-mail.");

        document.getElementById("username").style.border = "2px solid red";
        document.getElementById("username").focus();

        return false;
    }


    if (username.length <= 8) {
        alert("Username is invalid.(according to length)");
        document.getElementById("username").focus();
        return false;
    }

  
    if (!usernamePattern.test(username) && !emailvalidation.test(username)) {

        alert("Invalid Student ID / E-mail.");

        document.getElementById("username").style.border = "2px solid red";
        document.getElementById("username").focus();

        return false;
    }


    if (password == "") {

        alert("Please enter Password.");

        document.getElementById("password").style.border = "2px solid red";
        document.getElementById("password").focus();

        return false;
    }


     if (password.length < 6) {
        alert("Password must contain at least 6 characters.");
        document.getElementById("password").focus();
        return false;
    }


    if (!passwordPattern.test(password)) {

        alert("Invalid Password. Password must contain at least 6 characters, 1 capital letter, 1 digit and 2 small letters.");

        document.getElementById("password").style.border = "2px solid red";
        document.getElementById("password").focus();

        return false;
    }
    alert("Login Successful!");
    return true;
}

function validateProfile() {

    let age = document.getElementById("age").value.trim();
    let contact = document.getElementById("contact").value.trim();

    let agePattern = /^(?:[1-9]|[1-9][0-9]|100)$/;
    let contactPattern = /^[789]\d{9}$/;


    if (age == "") {
        alert("Please enter Age.");
        document.getElementById("age").focus();
        return false;
    }

    if (!agePattern.test(age)) {
        alert("Invalid Age. Age must be between 1 and 100.");
        document.getElementById("age").focus();
        return false;
    }


    if (contact == "") {
        alert("Please enter Contact Number.");
        document.getElementById("contact").focus();
        return false;
    }

    if (!contactPattern.test(contact)) {
        alert("Invalid Contact Number. It must contain 10 digits and start with 7, 8 or 9.");
        document.getElementById("contact").focus();
        return false;
    }

    alert("Profile Saved Successfully!");
    return true;
}