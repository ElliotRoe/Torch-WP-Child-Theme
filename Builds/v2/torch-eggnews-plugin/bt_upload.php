<?php
$target_dir = wp_upload_dir()['basedir'] . "stories";
$target_file = $target_dir . "/" . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

// Allow certain file formats
if ($imageFileType != "zip") {
    echo "Sorry, only zip files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars(basename($_FILES["fileToUpload"]["name"])). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// File extraction
$zip = new ZipArchive;
if ($zip->open('test.zip') === true) {
    $zip->extractTo(untrailingslashit($target_dir) . '/unzipped/');
    $zip->close();
    echo 'Extracting successful';
} else {
    echo 'Failed to extract';
}

// Reading docx file
$dir = new DirectoryIterator(untrailingslashit($target_dir) . '/unzipped/');
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
      if (strpos($fileinfo->getFilename(),".docx") === false) {
        $unfiltered_content = bt_read_docx($fileinfo->getFilename());
        $nl_index = strpos($unfiltered_content, '\n');
        $category = substr($unfiltered_content,0,$nl_index);
        $filtered_content = substr($unfiltered_content, $nl_index);
        $nl_index = strpos($filtered_content, '\n');
        $headline = substr($unfiltered_content,0,$nl_index);
        $author = substr($unfiltered_content,$nl_index, strpos($filtered_content, '\n', $nl_index));
        $author = trim($author);

        $cat_slug = str_replace(' ', '-', trim(strtolower($category)));
        $cat_ID = get_category_by_slug($cat_slug);
        if($cat_ID==0) {
          echo $category + " category could not be found with slug: ". $cat_slug .". Please double check spelling";
        }

        // Gets authors last name in order to use it as a key word in a user query search
        $keyword = substr($author, strlen($author)-strpos(strrev($author)," "));

        $user_query = new WP_User_Query( array( 'search' => $keyword ) );

        if (!empty($user_query->get_results())) {
          $auth_ID = $user_query->get_results()[0]->ID;
        } else {
          echo "No author found. Searched with keyword: " . $keyword;
        }

        $post_arr = array(
          'post_author' => $author,
          'post_content' => $filtered_content,
          'post_title' => $headline,
          'comment_status' => 'closed',
          'post_category' => $cat_ID,
        );

        if(wp_insert_post($post_arr)==0) {
          echo "Failed to post the story: " . $headline . " please check story format and try again";
        } else {
          echo "Posted " . $headline;
        }
    } else {
      echo "Failed to post the story: " . $headline . " please check to make sure you are using .docx files and try again";
    }
  }
}

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
?>
