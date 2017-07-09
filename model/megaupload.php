<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 09-07-17
 * Time: 14:53
 */
class megaupload extends assignment {

    /**
     * Handles repetitive new uploads via POST requests. Only used in development.
     */
    function upload(){

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
            $this->setSubmission($_POST["student"], $_POST["assignment"], $originalfilename, $db_filename);
            echo "Done!";
        } catch (\Exception $e) {
            // Display reason for failure
            print_r($e);
        }
    }
}