<?php

// Just for debugging
error_reporting(-1); // reports all errors
ini_set("display_errors", 1); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/tmp-error.log");
error_log("Test");
$path = preg_replace('/wp-content.*$/', '', __DIR__);
require_once($path.'wp-load.php');

$success_array = array('fatalError' => '', 'postedStories' => array(), 'postedWarningStories' => array(), 'failedStories' => array());

$target_dir = __DIR__ . "/stories";
$target_dir = "/tmp";
$target_file = $target_dir . "/" . basename($_FILES["fileToUpload"]["name"]);
$target_file = $target_dir . "/torchStory.zip";
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

$success_array['errors']['fileUpload'] = $_FILES["fileToUpload"]['error'];

    // Allow certain file formats
    if ($fileType == "zip") {
        if (is_uploaded_file($_FILES['fileToUpload']['tmp_name'])) {
            // Move file to correct location
            $success_array['errors']['fileMove'] = move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
            if ($success_array['errors']['fileMove']) {
                // File extraction
                $zip = new ZipArchive();
                if ($zip->open($target_file) === true) {
                    $zip->extractTo(untrailingslashit($target_dir) . '/torchStoryUnzipped/');
                    $zip->close();
                    // Reading docx file
                    $dir = new DirectoryIterator(untrailingslashit($target_dir) . '/torchStoryUnzipped/');
                    foreach ($dir as $fileinfo) {
                        if (!$fileinfo->isDot()) {
                            if (!strpos($fileinfo->getFilename(), ".docx")) {
                                $unfiltered_content = bt_read_docx($fileinfo->getFilename());
                                if (!$unfiltered_content) {
                                    $nl_index = strpos($unfiltered_content, '\n');
                                    $category = substr($unfiltered_content, 0, $nl_index);
                                    $filtered_content = substr($unfiltered_content, $nl_index);
                                    $nl_index = strpos($filtered_content, '\n');
                                    $headline = substr($unfiltered_content, 0, $nl_index);
                                    $author = substr($unfiltered_content, $nl_index, strpos($filtered_content, '\n', $nl_index));
                                    $author = trim($author);

                                    $cat_slug = str_replace(' ', '-', trim(strtolower($category)));
                                    $cat_ID = get_category_by_slug($cat_slug);
                                    if ($cat_ID==0) {
                                        $success_array['postedWarningStories'][$headline][] = $category + " category could not be found with slug: ". $cat_slug .". Posted under uncategorized";
                                        $cat_ID = 1;
                                    }
                                    // Gets authors last name in order to use it as a key word in a user query search
                                    $keyword = substr($author, strlen($author)-strpos(strrev($author), " "));

                                    $user_query = new WP_User_Query(array( 'search' => $keyword ));

                                    if (!empty($user_query->get_results())) {
                                        $auth_ID = $user_query->get_results()[0]->ID;
                                    } else {
                                        $success_array['postedWarningStories'][$headline][] = "No author found. Searched with keyword: " . $keyword . ". Will post under defualt staff reporter (Bexley.StaffReporter)";
                                        $auth_ID = get_user_by('login', 'Bexley.StaffReporter')->ID;
                                    }

                                    $post_arr = array(
                                      'post_author' => $author,
                                      'post_content' => $filtered_content,
                                      'post_title' => $headline,
                                      'comment_status' => 'closed',
                                      'post_category' => $cat_ID,
                                    );

                                    if (wp_insert_post($post_arr)==0) {
                                        $success_array['failedStories'][$headline] = $success_array['postedWarningStories'][$headline];
                                        unset($success_array['postedWarningStories'][$headline]);
                                        $success_array['failedStories'][$headline][] = "Failed to post the story: " . $headline . ". wp_insert_post failed.";
                                    } else {
                                        if (!isset($success_array['postedWarningStories'][$headline])) {
                                            $success_array['postedStories'][] = $headline;
                                        }
                                    }
                                }
                            } else {
                                $success_array['failedStories'][$headline] = array("Not .docx file");
                            }
                        }
                    }
                } else {
                    $success_array['fatalError'] = "Could not extract file";
                }
            } else {
                $success_array['fatalError'] = "Could not move ".$_FILES["fileToUpload"]["tmp_name"]." to " . $target_file;
            }
        } else {
            $success_array['fatalError'] = $_FILES["fileToUpload"]["tmp_name"]." is uploaded successfully";
        }
    } else {
        $success_array['fatalError'] = "Only zip files are allowed.";
    }
echo json_encode($success_array);

function bt_read_docx($filename)
{
    $striped_content = '';
    $content = '';

    $zip = zip_open($filename);

    if (!$zip || is_numeric($zip)) {
        return false;
    }

    while ($zip_entry = zip_read($zip)) {
        if (zip_entry_open($zip, $zip_entry) == false) {
            continue;
        }

        if (zip_entry_name($zip_entry) != "word/document.xml") {
            continue;
        }

        $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

        zip_entry_close($zip_entry);
    }

    zip_close($zip);

    $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
    $content = str_replace('</w:r></w:p>', "\r\n", $content);
    $striped_content = strip_tags($content);

    return $striped_content;
}
