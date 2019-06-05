<?php 

require_once "Question.php";
require_once "Connection.php";
/**
 * Class is responsible for managing test session and related data
 */
class Test_Session {
    private $test_id;
    private $user;
    private $question;
    private $current_question = 1;
    private $question_count;
    private $correct_answers = 0;
    private $session_id;
    private $status = true;

    function __construct($test_id, $user){
        $this->test_id = $test_id;
        $this->user = $user;
        $this->question = $this->init_question();
        $this->question_count = $this->count_questions();
        $this->session_id = uniqid();
    }

    /**
     * Function retreives data for initial question from database.
     * It is the one who has NULL value in prev_id field.
     * @return Question
     */
    private function init_question(){
        $sql = "SELECT questions_id, question, next_question FROM questions WHERE test_id=:tid and prev_question IS NULL";
        $args = array(
            ":tid" => $this->test_id
        );
        $conn = new Connection();
        $result = $conn->get($sql, $args);
        return new Question( 
            $result[0]['questions_id'],
            $result[0]['question'],
            $result[0]['next_question']);
    }
    /**
     * Function retreives total question count of selected test from database
     * @return int
     */
    private function count_questions(){
        $sql = "SELECT COUNT(questions_id) FROM questions WHERE test_id=:tid";
        $args = array(
            ":tid" => $this->test_id
        );
        $conn = new Connection();
        $result = $conn->get($sql, $args);
        return $result[0]["COUNT(questions_id)"];
    }
    /**
     * Function increments correct answer count if user answer has been correct
     */
    private function inc_if_correct($answers_id){
        if ($answers_id == $this->question->get_correct()){
            $this->correct_answers++;
        }
    }
    /**
     * Function records user answer in database
     * @param int
     */
    private function save_answer($answers_id){
        $sql = "INSERT INTO answers_history (session_id, test_id, questions_id, answers_id, username) ";
        $sql .="VALUES (:sid, :tid, :qid, :aid, :username)";
        $args = array(
            ":sid" => $this->session_id,
            ":tid" => $this->test_id,
            ":qid" => $this->question->get_question_id(),
            ":aid" => $answers_id,
            ":username" => $this->user
        );
        $conn = new Connection();
        $conn->get($sql, $args);
        
    }
    /**
     * Function either loads next test question and returns true if it exists or
     * uploads tests results to database and returns false and sets session status
     * to false if current question was last in the test
     * @param boolean
     */
    private function load_next_question(){
        $next_id = $this->question->get_next_id();
        if (is_null($next_id)) {
            $this->upload_result();
            $this->status = false;
            return false;
        } else {
            $sql = "SELECT questions_id, question, next_question FROM questions WHERE test_id=:tid and questions_id=:nid";
            $args = array(
                ":tid" => $this->test_id,
                ":nid" => $next_id
            );
            $conn = new Connection();
            $result = $conn->get($sql, $args);
            $this->question = new Question(
                $result[0]['questions_id'],
                $result[0]['question'],
                $result[0]['next_question']);
            $this->current_question++;
            return true;
        }
    }
    /**
     * Function uploads test results to database
     */
    private function upload_result(){
        $sql = "INSERT INTO results (test_id, username, session_id, correct_answers, total_answers) ";
        $sql .="VALUES(:tid, :username, :sid, :ca, :ta)";
        $args = array(
            ":tid" => $this->test_id,
            ":username" => $this->user,
            ":sid" => $this->session_id,
            ":ca" => $this->correct_answers,
            ":ta" => $this->question_count,
        );
        $conn = new Connection();
        $conn->get($sql, $args);
    }
    /**
     * Function returns current question text
     * @return string
     */
    public function get_current_question(){
        return $this->question->get_question_text();
    }

    /**
     * Function returns answers to current question
     * @return array
     */
    public function get_answers(){
        return $this->question->get_answers();
    }

    /**
     * Function returns current session progress in percents
     * @return int
     */
    public function get_session_progress(){
        $progress = $this->current_question / $this->question_count;
        $progress = floor($progress*100);
        return $progress;
        
    }

    /**
     * Functon responsible for processing of user answer. It
     *     -increments correct answer count if needed
     *     -saves user answer to database
     *     -loads next question
     * Returns true if there is next question in test, otherwise returns false
     * @return boolean
     */
    public function process_user_action($answers_id){
        $this->inc_if_correct($answers_id);
        $this->save_answer($answers_id);
        return $this->load_next_question();
    }
    /**
     * Function returns message to be displayed to user after the test
     * @return string
     */
    public function get_result_message(){
        return "Tu atbildēji pareizi uz ".$this->correct_answers." no ".$this->question_count." jautājumiem.";
    }
    /**
     * Function returns username of person doing the test
     * @return string
     */
    public function get_username(){
        return $this->user;
    }

    /**
     * Function returns session status
     * @return boolean
     */

    public function get_status(){
        return $this->status;
    }
}

?>