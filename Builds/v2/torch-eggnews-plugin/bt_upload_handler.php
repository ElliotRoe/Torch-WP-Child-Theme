<?php
$success_array = array('Fatal Error' => '', 'Posted Stories' => array(), 'Failed Stories' => array());

$target_dir = wp_upload_dir()['basedir'] . "stories";
$target_file = $target_dir . "/" . basename($_FILES["fileToUpload"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if file already exists
if (!file_exists($target_file)) {
    // Allow certain file formats
    if ($imageFileType == "zip") {
        // Move file to correct location
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            // File extraction
            $zip = new ZipArchive;
            if ($zip->open('test.zip') === true) {
                $zip->extractTo(untrailingslashit($target_dir) . '/unzipped/');
                $zip->close();
                // Reading docx file
                $dir = new DirectoryIterator(untrailingslashit($target_dir) . '/unzipped/');
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
                                    $success_array['Posted Stories'][$headline][] = $category + " category could not be found with slug: ". $cat_slug .". Posted under uncategorized";
                                    $cat_ID = 1;
                                }
                                // Gets authors last name in order to use it as a key word in a user query search
                                $keyword = substr($author, strlen($author)-strpos(strrev($author), " "));

                                $user_query = new WP_User_Query(array( 'search' => $keyword ));

                                if (!empty($user_query->get_results())) {
                                    $auth_ID = $user_query->get_results()[0]->ID;
                                } else {
                                    $success_array['Posted Stories'][$headline][] = "No author found. Searched with keyword: " . $keyword . ". Will post under defualt staff reporter (Bexley.StaffReporter)";
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
                                    $success_array['Failed Stories'][$headline] = $success_array['Posted Stories'][$headline];
                                    unset($success_array['Posted Stories'][$headline]);
                                    $success_array['Failed Stories'][$headline][] = "Failed to post the story: " . $headline . ". wp_insert_post failed.";
                                }
                            }
                        } else {
                            $success_array['Failed Stories'][$headline] = array("Not .docx file");
                        }
                    }
                }
            } else {
                $success_array['Fatal Error'] = "Could not extract file";
            }
        } else {
            $success_array['Fatal Error'] = "Could not move temp file to target file";
        }
    } else {
        $success_array['Fatal Error'] = "Only zip files are allowed.";
    }
} else {
    $success_array['Fatal Error'] = "File already exists";
}

echo $success_array;

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
