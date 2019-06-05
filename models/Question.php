<?php
    /**
     * Class that is responsible for managing active question of test session
     */
    class Question {
        private $id;
        private $conn;
        private $text;
        private $answers;
        private $next_id;
        private $correct;

        function __construct($id, $text, $next_id){
            $this->id = $id;
            $this->text = $text;
            $this->next_id = $next_id;
            $this->answers = $this->load_answers();
            $this->correct = $this->load_correct();
        }

        /**
         * Function for retreival of answers from database
         * @return array
         */
        private function load_answers(){
            $sql = "SELECT * FROM answers WHERE questions_id=:id";
            $args = array(
                ":id" => $this->id
            );
            $conn = new Connection();
            $result = $conn->get($sql, $args);
            $prepared_answers = array();
            foreach ($result as $answer_data){
                $id = $answer_data['answers_id'];
                $text = $answer_data['answer_text'];
                $prepared_answers[$id]=$text;
            }
            return $prepared_answers;
        }

        /**
         * Function for retreival of correct answer from database
         * @return int
         */
        private function load_correct(){
            $sql = "SELECT answers_id FROM answers WHERE questions_id=:id AND correct=TRUE";
            $args = array(
                ":id" => $this->id
            );
            $conn = new Connection();
            $result = $conn->get($sql, $args);
            return $result[0]['answers_id'];
        }

        /**
         * Function returns question text
         * @return string
         */
        public function get_question_text(){
            return $this->text;
        }

        /**
         * Function returns answers of current question
         * @return array
         */
        public function get_answers(){
            return $this->answers;
        }
        /**
         * Function returns current question id
         * @return int
         */
        public function get_question_id(){
            return $this->id;
        }
        /**
         * Function returns id of question that should follow after current question'
         * @return int
         */
        public function get_next_id(){
            return $this->next_id;
        }
        /**
         * Function returns answer id for current question
         * @return int
         */
        public function get_correct(){
            return $this->correct;
        }
}

?>