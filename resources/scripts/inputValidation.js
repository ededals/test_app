
document.getElementById("begin").addEventListener("click", validateSessionStart);

/**
 * Function validates user input fields and stops request to server if necessary
 * @param {event} event 
 */
function validateSessionStart(event){
    removeErrors(); //from Utils.js
    var username = document.getElementById('username').value;
    var testValue = document.getElementById('test').value;
    if (username.length < 1 || testValue == 0){
        var message="Lai turpinātu nepieciešams ievadīt vārdu un izvēlēties testu";
        appendError(message, event);//from Utils.js
    } else if (username.length > 255){
        var message = "Pārāk garš lietotājvārds, ievadiet lietotājvārdu, kas īsāks par 255 zīmēm";
        appendError(message, event);//from Utils.js
    } 
}


