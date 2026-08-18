function validateLogin() {

    let username = document.getElementById("username").value.trim();
    let password = document.getElementById("password").value.trim();


    document.getElementById("username").style.border = "";
    document.getElementById("password").style.border = "";

    if (username == "") {
        alert("Please enter Username.");
        document.getElementById("username").style.border = "2px solid red";
        document.getElementById("username").focus();
        return false;
    }

    if (username.length != 8) {
        alert("Username is invalid.");
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
        document.getElementById("password").style.border = "2px solid red";
        document.getElementById("password").focus();
        return false;
    }

    alert("Login Successful!");
    return true;
}

let themeButton = document.createElement("button");

themeButton.innerHTML = "🌙 Dark Mode";

themeButton.style.position = "fixed";
themeButton.style.top = "20px";
themeButton.style.right = "20px";
themeButton.style.padding = "10px 15px";
themeButton.style.border = "none";
themeButton.style.borderRadius = "5px";
themeButton.style.cursor = "pointer";

document.body.appendChild(themeButton);

let loginTexts = [];

document.querySelectorAll("*").forEach(function(element) {

    let text = element.textContent.trim();

    if (
        text === "Enter Student ID / E-mail" ||
        text === "Enter Password" ||
        text === "Remember Me"
    ) {
        loginTexts.push(element);
    }

});


themeButton.addEventListener("click", function() {

    if (document.body.style.backgroundColor == "rgb(18, 18, 18)") {

        document.body.style.backgroundColor = "white";
        document.body.style.color = "black";

        loginTexts.forEach(function(element) {
            element.style.color = "black";
        });

        themeButton.innerHTML = "🌙 Dark Mode";

    } else {

        document.body.style.backgroundColor = "#121212";
        document.body.style.color = "black";

        loginTexts.forEach(function(element) {
            element.style.color = "black";
        });
        themeButton.innerHTML = "☀️ Light Mode";
    }
});