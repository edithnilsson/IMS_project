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

function checkEmail() {
    const input = document.getElementById("email");
    const error = document.getElementById("email_error");
    if (input.value.trim() === ""){
        input.classList.add("red_border");
        error.textContent = "Email can not be empty";
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) {
        input.classList.add("red_border");
        error.textContent = "Please enter a valid email address";
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
    } else if (!/^[0-9+ -]+$/.test(input.value.trim())) {
        input.classList.add("red_border");
        error.textContent = "Phone number can only contain numbers";
    } else {
        input.classList.remove("red_border");
        error.textContent = "";
    }
}

document.getElementById("phone_number").addEventListener("focus", checkPhoneNumber);
document.getElementById("phone_number").addEventListener("input", checkPhoneNumber);