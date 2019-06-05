/**
 * Function appednds error message at the top of form and prevents default submit behavior
 * @param {string} message 
 * @param {event} event 
 */

function appendError (message, event){
    event.preventDefault();
    var error = document.createElement("p")
    error.innerHTML=message;
    error.setAttribute('class', 'error');
    var form= document.getElementsByTagName('form')[0];
    document.body.insertBefore(error, form);
}

/**
 * Function removes all elements of class error from DOM
 */
function removeErrors(){
    var errors = document.getElementsByClassName('error');
    for (var i=0; i<errors.length; i++){
        errors[i].remove();
    }
}