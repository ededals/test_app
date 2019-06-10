<?php 

require_once "Test.php";

/**
 * Class is responsible for managing test session and related data
 */
class Test_Session extends Test {
    private $test_id;
    private $user;
    private $session_id;

    function __construct($test_id, $user, $session_id, $conn){
        $this->test_id = $test_id;
        $this->user = $user;
        $this->session_id = $session_id;
        $question_id = $this->init_question($conn);
        parent:: __construct($question_id, $conn);
        $this->question_count = $this->count_questions();
    }

    public static function from_id($id, $conn){
        $sql = "SELECT test_id, username FROM answers_history WHERE session_id = :id";
        $args = array(
            ":id" => $id
        );
        $result = $conn->get($sql, $args);
        if (empty($result)){
            return new self($_SESSION['test_id'], $_SESSION['user_id'], $_SESSION['id'], $conn);
        } else {
            return new self($result[0]['test_id'], $result[0]['username'], $id, $conn);
        }
    }

    
    public static function new_session($test_id, $username){
        session_start();
        $session_id = uniqid();
        $_SESSION['id'] = $session_id;
        $_SESSION['test_id'] = $test_id;
        $_SESSION['user_id'] = $username;
    }

    /**
     * Function responsible for checking if user has allready started new session
     * Return session id if yes, otherwise return null
     * @return string|null
     */

    public static function is_init(){
        session_start();
        if (isset($_SESSION['id'])){
            return $_SESSION['id'];
        } else {
            return null;
        }
    }

    /**
     * Function returns all tests for which it is possible to create session
     */
    public static function get_tests($conn){
        return $conn->get("SELECT * FROM test");
    }

    /**
     * Function retreives data for initial question from database.
     * It is the one who has NULL value in prev_id field.
     * @return Question
     */
    public function init_question($conn){
        $sql = "SELECT answers_history.session_id, questions.next_question, questions.questions_id  FROM answers_history JOIN questions ";
        $sql .= "ON answers_history.questions_id = questions.questions_id WHERE answers_history.session_id = :id";
        $args = array(
            ":id" => $this->session_id
        );

        $result = $conn->get($sql, $args);
        if (empty($result)){
            $sql = "SELECT questions_id FROM questions WHERE test_id = :tid AND prev_question IS NULL";
            $args = array(
                ":tid" => $this->test_id
            );
            $result = $conn->get($sql, $args);
            return end($result)['questions_id'];
        }else{
            if (is_null(end($result)['next_question'])){
                return end($result)['questions_id'];
            } else {
                return end($result)['next_question'];
            }
        }
        
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

        $result = $this->conn->get($sql, $args);
        return $result[0]["COUNT(questions_id)"];
    }


    /**
     * Function responsible for counting correct answers in the session
     */

    private function count_answers(){
        $sql = "SELECT COUNT(questions_id) FROM answers_history WHERE session_id=:id";
        $args = array(
            ":id" => $this->session_id
        );
        $result = $this->conn->get($sql, $args);
        return $result[0]["COUNT(questions_id)"];
    }

    /**
     * Function responsible for counting how many correct answers has user
     * answered in the current session     
     * */

    private function count_correct_answers(){
        $sql = "SELECT COUNT(answers_history_id) FROM answers_history JOIN answers ";
        $sql .= "ON answers_history.answers_id = answers.answers_id ";
        $sql .= "WHERE answers_history.session_id = :id AND answers.correct=1";
        $args = array(
            ":id" => $this->session_id
        );
        $result = $this->conn->get($sql, $args);
        return $result[0]["COUNT(answers_history_id)"];
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
            ":qid" => $this->get_question_id(),
            ":aid" => $answers_id,
            ":username" => $this->user
        );
        $this->conn->get($sql, $args);
        
    }
    /**
     * Function either loads next test question and returns true if it exists or
     * uploads tests results to database and returns false if current question was last in the test
     * @return boolean
     */
    private function load_next_question(){
        $next_id = $this->get_next_id();
        if (is_null($next_id)) {
            $this->upload_result();
            return false;
        } else {
            $sql = "SELECT questions_id FROM questions WHERE test_id=:tid and questions_id=:nid";
            $args = array(
                ":tid" => $this->test_id,
                ":nid" => $next_id
            );
            $result = $this->conn->get($sql, $args);
            $this->setNewQuestion($result[0]['questions_id']);
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
            ":ca" => $this->count_correct_answers(),
            ":ta" => $this->count_questions()
        );
        $this->conn->get($sql, $args);
    }

    /**Function clears the session information */

    public function clear_session(){
        session_destroy();
    }
    /**
     * Function returns current session progress in percents
     * @return int
     */
    public function get_session_progress(){
        $progress = ($this->count_answers()+1) / $this->count_questions();
        $progress = floor($progress*100);
        return $progress;
        

    }

    /**
     * Functon responsible for processing of user answer. It
     *     -saves user answer to database
     *     -loads next question
     * Returns true if there is next question in test, otherwise returns false
     * @return boolean
     */
    public function process_user_action($answers_id){
        $this->save_answer($answers_id);
        return $this->load_next_question();
    }
    /**
     * Function returns message to be displayed to user after the test
     * @return string
     */
    public function get_result_message(){
        return "Tu atbildēji pareizi uz ".$this->count_correct_answers()." no ".$this->question_count." jautājumiem.";
    }
    /**
     * Function returns username of person doing the test
     * @return string
     */
    public function get_username(){
        return $this->user;
    }

    /**
     * Function returns test session id
     * @return string
     */
    public function get_session_id(): string{
        return $this->session_id();
    }
}

?>