
document.getElementById("next").addEventListener("click", handleNextClick);

/**
 * Function handles click on "Next" button. It:
 *  -removes all errors currently seen on screen
 *  -checks if answer has been selected and shows error if not
 *  -In case of no errors it retreives data from server and updates DOM;  
 * @param {event} event 
 */
function handleNextClick(event){
    removeErrors(); //from Utils.js
    var answers = document.getElementsByClassName('answer');
    var answer_selected = false;
    for (var i=0; i<answers.length; i++){
        if (answers[i].checked==true){
            answer_selected = answers[i].value;
            break;
        };
    }
    var progress = document.getElementById('progress-marker');
    var style = progress.getAttribute('style');
    if (answer_selected == false){
        var message="Lai turpinātu nepieciešams izvēlēties atbildi";
        appendError(message, event);//from Utils.js
    } else if (style !== "width:100%"){
        var xhttp = new XMLHttpRequest();
        xhttp.onloadend = handleResponse
        xhttp.open("GET", "index.php?answer="+answer_selected, true);
        xhttp.send();
        event.preventDefault();
    }
}

/**
 * Function responsible for DOM update during 
 */

function handleResponse() {
    if (this.readyState==4 && this.status == 200) { 
        var questionData = JSON.parse(this.responseText);
        //heading update
        document.getElementById('question').innerHTML = questionData.question;
        //Answers update
        var answerList = document.getElementById('answer-list');
        answerList.innerHTML = "";
        let answers = questionData.answers;
        Object.keys(answers).forEach(key => {
            var listItem = document.createElement("li");
            var input = document.createElement("input");
            input.setAttribute("type", "radio");
            input.setAttribute("class", "answer");
            input.setAttribute("name", "answer");
            input.setAttribute("value", key);
            input.setAttribute("id", key);
            var label = document.createElement("label");
            label.setAttribute("for", key);
            label.innerHTML=answers[key];
            listItem.appendChild(input);
            listItem.appendChild(label);
            answerList.appendChild(listItem);
        })
        //progress bar update
        var progress_marker = document.getElementById("progress-marker");
        progress_marker.setAttribute("style", "width:"+questionData.progress+"%");
    } else {
        console.log("Error: "+xhttp.statusText);
        console.log("s");
    };
}


