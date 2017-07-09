<?php use Carrooi\DocxExtractor\DocxExtractor;

/**
 * Handles assignments, including submissions by students.
 */

class assignment extends model {

    /*
     * Page functions
     */

    /**
     * Renders the assignment overview.
     */
    function overview(){
        $this->get_session();

        // Get data
        $data = $this->getAssignments($_SESSION['user']);
        $columns = [["Titel", "title"], ["Status", "status"], ["Uiterste inleverdatum", "end_date"]];

        // Generate menu
        $menu = $this->menu($this->bootstrap, ["active" => "Opdrachten", "align" => "stacked"]);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["name"] => "#", "Opdrachten" => "#"]);
        $link = '<a href="%s/">%s</a>';
        $options = [
            ["<a class='' href='%s/'><i class='glyphicon glyphicon-pencil'></i> Inleveren</a>"],
        ];

        // Generate page
        echo $this->templates->render("assignments::index", [
            "title" => "Tekstmijn | Opdrachten",
            "page_title" => "Opdrachten",
            "table" => $this->table($this->bootstrap, $columns, $data, $options, $link),
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
        ]);
    }

    /**
     * Renders the individual assignment page, including any earlier submissions.
     *
     * @param $assignment_id String containing the assignment's UUID
     */
    function individualAssignment($assignment_id){
        $this->get_session();

        // Get assignment and submission info
        $data = $this->getAssignment($assignment_id, $_SESSION["class"]);
        $submission = $this->getSubmission($_SESSION["user"], $assignment_id);

        // Generate navigation items
        $menu = $this->menu($this->bootstrap, ["active" => "Opdrachten", "align" => "stacked"]);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["name"] => "#", "Opdrachten" => "../", $data['title'] => "#"]);
        $tabs = $this->bootstrap->tabs(array(
            'Info' => '#info',
            'Inzending' => '#submission',
        ), array(
            'active' => 'Info',
            'toggle' => "tab",
        ));

        // Show approriate warnings when there is an assignment to be overwritten
        $page_js = "";
        $overwrite = 0;
        if($submission != null){
            $page_js = "/vendor/application/assignment_submitted.js";
            $overwrite = 1;
        }

        echo $this->templates->render("assignments::assignment",
            [
                "title" => sprintf("Tekstmijn | Opdracht: %s", strtolower($data['title'])),
                "breadcrumbs" => $breadcrumbs,
                "menu" => $menu,
                "page_title" => $data['title'],
                "status" => $data['status'],
                "deadline" => $data["deadline"],
                "submission" => $submission,
                "tabs" => $tabs,
                "page_js" => $page_js,
                "overwrite" => $overwrite
            ]
        );
    }

    /**
     * Saves submissions to the database.
     *
     * @param $assignment_id String containing the assignment's UUID
     */
    function submitAssignment($assignment_id){
        $this->get_session();

        // Check if there is a previous submission
        $previous_submission = $this->getSubmissionFile($_SESSION["user"], $assignment_id);

        // Setup file storage
        $storage = new \Upload\Storage\FileSystem($_SERVER['DOCUMENT_ROOT'] . '/assets/submissions/');
        $file = new \Upload\File('file', $storage);

        // Get original file name for display purposes
        $originalfilename = $file->getNameWithExtension();

        // Generate and set new, unique file name for server storage
        $new_filename = uniqid();
        $db_filename = $new_filename . "." . $file->getExtension();
        $file->setName($new_filename);

        // Validate file: check if it isn't bigger than 5MB and is a Word document (.docx)
        $file->addValidations(array(
            new \Upload\Validation\Mimetype(array('application/vnd.openxmlformats-officedocument.wordprocessingml.document')),
            new \Upload\Validation\Size('5M')
        ));

        // Try to upload file
        try {
            // Save file to server
            $file->upload();

            // Either update an existing submission or insert a new submission, in case there isn't a previous submission
            if (strlen($previous_submission) > 5){
                $rows_affected = $this->updateSubmission($_SESSION['user'], $assignment_id, $originalfilename, $db_filename, $previous_submission);
            } else {
                $rows_affected = $this->setSubmission($_SESSION['user'], $assignment_id, $originalfilename, $db_filename);
            }

            // Give the user appropriate feedback
            if ($rows_affected){
                $this->redirect("/assignment/".$assignment_id."/?upload=success");
            } else {
                $this->redirect("/assignment/".$assignment_id."/?upload=failed");
            }
        } catch (\Exception $e) {
            // Explain the upload wasn't successful
            $this->redirect("/assignment/".$assignment_id."/?upload=failed");
        }
    }

    /*
     * Supporting functions
     */

    /**
     * Grabs all assignments available for a student from the database.
     *
     * @param $studentid Int containing the student's ID
     * @return array containing the relevant assignments, with title and status attributes
     */
    function getAssignments($studentid){
        $quoted_id = $this->database->quote($studentid);
        $querystring = "SELECT id, title, status, DATE_FORMAT(end_date, '%d %M %Y %H:%i') AS end_date
                        FROM (
                            SELECT assignments.id AS id, assignments.title AS title,
                              IF(NOW() BETWEEN assignments_class.start_date AND assignments_class.end_date,
                                'Open', 'Gesloten') AS status,
                                 assignments_class.end_date AS end_date
                            FROM assignments, assignments_class, students
                            WHERE students.id = $quoted_id
                            AND students.class_id = assignments_class.class_id
                            AND assignments_class.assignment_id = assignments.id
                            AND assignments_class.start_date <= NOW()
                        ) AS classwork";

        return $this->database->query($querystring)->fetchAll();
    }

    /**
     * Grabs all information concerning an individual assignment from the database.
     *
     * @param $assignment_id String containing the assignment's UUID
     * @param $class_id Int containing the class ID of the student
     * @return array containing the assignment details: title, status and deadline
     */
    function getAssignment($assignment_id, $class_id){
        $quoted_id = $this->database->quote($assignment_id);
        $quoted_class = $this->database->quote($class_id);

        $querystring = "SELECT title, IF(NOW() BETWEEN assignments_class.start_date AND assignments_class.end_date,
            'Open', 'Gesloten') AS status, DATE_FORMAT(end_date, '%d %M %Y %H:%i') as deadline
                        FROM assignments, assignments_class
                        WHERE assignments.id = assignments_class.assignment_id
                        AND class_id = $quoted_class
                        AND assignments.id = $quoted_id";

        return $this->database->query($querystring)->fetchAll()[0];
    }

    /**
     * Returns an existing submission for an assignment, if any exists
     *
     * @param $student_id Int containing the student's ID
     * @param $assignment_id String containing the assignment's UUID
     * @return mixed In case there is a submission, an array containing submission time, the name of the submission file
     * on server and the original file name used at submission time
     */
    function getSubmission($student_id, $assignment_id){
        $quoted_student = $this->database->quote($student_id);
        $quoted_assignment = $this->database->quote($assignment_id);
        $query = "SELECT DATE_FORMAT(time, '%d %M %Y, %H:%i') AS time, file, original_file
              FROM submissions
              WHERE student_id = $quoted_student
              AND assignment_id = $quoted_assignment";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * Checks if an earlier assignment submission exists for a particular student and returns the file name if it exists.
     *
     * @param $student_id Int containing the student's ID
     * @param $assignment_id String containing the assignment's UUID
     * @return mixed In case there is a submission, a String containing the file name is returned. If not, False will be
     * returned
     */
    function getSubmissionFile($student_id, $assignment_id){
        $quoted_student = $this->database->quote($student_id);
        $quoted_assignment = $this->database->quote($assignment_id);
        $query = "SELECT file
              FROM submissions
              WHERE student_id = $quoted_student
              AND assignment_id = $quoted_assignment";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['file'];
    }

    /**
     * Inserts new submission into database.
     *
     * @param $student_id Int containing the student's ID
     * @param $assignment_id String containing the assignment's UUID
     * @param $originalfilename String containing the original file name at submission time
     * @param $file String containing the file name on server
     * @return bool|int False in case the insert query fails or an int containing the number of rows affected by the insert
     */
    function setSubmission($student_id, $assignment_id, $originalfilename, $file){
        // Extract raw text from Word file
        $extractor = new DocxExtractor;
        $text = $extractor->extractText(getcwd() . '/assets/submissions/' . $file);

        // Insert submission details including raw text to database
        $rows_affected = $this->database->insert("submissions",
            ["student_id" => $student_id,
                "assignment_id" => $assignment_id,
                "file" => $file,
                "original_file" => $originalfilename,
                "text" => $text
            ]
        );
        return $rows_affected;
    }

    /**
     * @param $student_id Int containing the student's ID
     * @param $assignment_id String containing the assignment's UUID
     * @param $originalfilename String containing the original file name at submission time
     * @param $file String containing the file name on server
     * @param $previous String containing the file name of the previous file on server
     * @return bool|int False in case the update query fails or an int containing the number of rows affected by the insert
     */
    function updateSubmission($student_id, $assignment_id, $originalfilename, $file, $previous){
        // Remove previous submission file
        $is_deleted = unlink(getcwd() . "/assets/submissions/" . $previous);

        // Extract raw text from Word file
        $extractor = new DocxExtractor;
        $text = $extractor->extractText(getcwd() . '/assets/submissions/' . $file);

        // Update submission details in database
        $rows_affected = $this->database->update("submissions",
            ["file" => $file,
                "original_file" => $originalfilename,
                "text" => $text,
                "submission_count[+]" => 1
            ], ["AND" => [
                "student_id" => $student_id,
                "assignment_id" => $assignment_id
            ]]
        );

        return $rows_affected;
    }


}