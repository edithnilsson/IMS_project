function checkUsername() {
    const input = document.getElementById("username");
    const error = document.getElementById("username_error");
    const username = input.value.trim();

    if (username === ""){
        input.classList.add("red_border");
        error.textContent = "Username can not be empty";
    } else if (!/^[A-Za-z0-9]+$/.test(username)) {
        input.classList.add("red_border");
        error.textContent = 'Username can only contain letters and numbers';
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("username").addEventListener("focus", checkUsername);
document.getElementById("username").addEventListener("input", checkUsername);

function checkPassword() {
    const input = document.getElementById("password");
    const error = document.getElementById("password_error");
    const password = input.value;

    if (password === ""){
        input.classList.add("red_border");
        error.textContent = "Password can not be empty";
    } else if (
        password.length < 8 ||
        !/[A-Z]/.test(password) ||
        !/[a-z]/.test(password) ||
        !/[0-9]/.test(password) ||
        !/[^A-Za-z0-9]/.test(password)
    ) {
        input.classList.add("red_border");
        error.textContent = "Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character";
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("password").addEventListener("focus", checkPassword);
document.getElementById("password").addEventListener("input", checkPassword);

function checkFirstName() {
    const input = document.getElementById("first_name");
    const error = document.getElementById("first_name_error");
    if (input.value.trim() === ""){
        input.classList.add("red_border");
        error.textContent = "First name can not be empty";
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("first_name").addEventListener("focus", checkFirstName);
document.getElementById("first_name").addEventListener("input", checkFirstName);

function checkLastName() {
    const input = document.getElementById("last_name");
    const error = document.getElementById("last_name_error");
    if (input.value.trim() === ""){
        input.classList.add("red_border");
        error.textContent = "Last name can not be empty";
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("last_name").addEventListener("focus", checkLastName);
document.getElementById("last_name").addEventListener("input", checkLastName);

function checkRole() {
    const input = document.getElementById("role");
    const error = document.getElementById("role_error");
    if (input.value.trim() === ""){
        input.classList.add("red_border");
        error.textContent = "Please pick a role";
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("role").addEventListener("focus", checkRole);
document.getElementById("role").addEventListener("input", checkRole);

function checkEmail() {
    const input = document.getElementById("email");
    const error = document.getElementById("email_error");
    if (input.value.trim() === ""){
        input.classList.add("red_border");
        error.textContent = "Email can not be empty";
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("email").addEventListener("focus", checkEmail);
document.getElementById("email").addEventListener("input", checkEmail);

function checkPhoneNumber() {
    const input = document.getElementById("phone_number");
    const error = document.getElementById("phone_number_error");
    if (input.value.trim() === ""){
        input.classList.add("red_border");
        error.textContent = "Phone number can not be empty";
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("phone_number").addEventListener("focus", checkPhoneNumber);
document.getElementById("phone_number").addEventListener("input", checkPhoneNumber);

function checkHospital() {
    const input = document.getElementById("hospital_id");
    const error = document.getElementById("hospital_error");
    if (input.value.trim() === ""){
        input.classList.add("red_border");
        error.textContent = "Please pick a hospital";
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("hospital_id").addEventListener("focus", checkHospital);
document.getElementById("hospital_id").addEventListener("input", checkHospital);


// Handle how "I agree" and "Close" button work in the popup/modal
const termsDialog = document.getElementById("terms_dialog");
// Open the popup
document.getElementById("open_terms").addEventListener("click", function () {
    termsDialog.showModal();
});
// "I agree" 
document.getElementById("agree_terms").addEventListener("click", function () {
    document.getElementById("terms").checked = true;
    termsDialog.close();
});
// "Close"
document.getElementById("close_terms").addEventListener("click", function () {
    termsDialog.close();
});

// Disable the Register button after submit so the form can't be sent twice
document.getElementById("register_form").addEventListener("submit", function () {
    const button = document.getElementById("register_button");
    button.disabled = true;
    button.value = "Registering...";
});