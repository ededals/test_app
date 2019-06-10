<?php
    require_once "models/Connection.php";
    require_once "models/Test_Session.php";
    /**
     *This class is responsible for top level routing during testing
     */
    class TestController{

        private $error;
        private $test_session;
        private $conn;

        public function __construct($conn){
            $this->conn = $conn;
        }
        /**
         * Function extracts requested document name from URI
         * and then decides to which helper function to delegate further execution
         */
        public function handleRequest(){
            $document_list = explode("/", $_SERVER['REQUEST_URI']);
            $document = array_pop($document_list);
            $sid = Test_Session::is_init();
            if (!is_null($sid)){
                $this->test_session = Test_Session::from_id($sid, $this->conn);
                if ($document == "result.php"){
                    $this->showResults();
                }else if (isset($_REQUEST['answer'])){
                    $this->nextAnswer();
                } else if ($document == "session.php"){
                    $this->loadQuestions();
                } 
            } else {
                $this->startSession();
            }
        }
        /**
         * Controller responsible for session starting: user input validation 
         * and new test session creation
         */
        private function startSession(){
            if (isset($_POST['begin'])){
                if (!isset($_POST['username']) || 
                    strlen($_POST['username']) < 1 ||
                    $_POST['test_selection'] == 0){
                        $this->error = "Lai turpinātu nepieciešams ievadīt vārdu un izvēlēties testu";
                        header("Location: index.php");
                        return;
                } else if (strlen($_POST['username']) > 255){
                    $this->error = "Lietotājvārdam jābūt īsākam par 255 zīmēm.";
                    header("Location: index.php");
                    return;
                } else {
                    $username=trim(htmlspecialchars($_POST['username']));
                    $this->test_session = Test_Session::new_session(
                        $_POST['test_selection'],
                        $username
                    );
                    header("Location: session.php");
                    return;
                }
        
            }
            include "views/startSession.php";
        }

        /**
         * This controller function is responsible for routing during test session
         */
        private function loadQuestions(){
            include "views/session.php";
        }

        /**
         * Function
         */
        private function nextAnswer(){

            $next_question_loaded = $this->test_session->process_user_action($_REQUEST["answer"]);
            if ($next_question_loaded){
                $response = new stdClass;
                $response->question = $this->test_session->get_question_text();
                $response->answers = $this->test_session->get_answers();
                $response->progress = $this->test_session->get_session_progress();
                $encoded = json_encode($response);
                echo $encoded;
            } else {
                header('Location: result.php');
                return;
            }
        }
        /**
         * This function is responsible for routing during test result display.
         */
        private function showResults(){
            $user=$this->test_session->get_username();
            $message=$this->test_session->get_result_message();
            $this->test_session->clear_session();
            include "views/result.php";
        }

    
    }

?> 