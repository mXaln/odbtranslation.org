<?php
use \Helpers\Session;
?>

<?php
if(isset($data['languages'])) {
    echo "Bible<br><br>";

    foreach ($data['languages'] as $language) {
        echo "<a href=\"" . DIR . "translations/" . $language->targetLang . "\">" . $language->angName . " (".$language->langName.")</a><br>";
    }
}

if(isset($data['bookProjects'])) {
    echo '<a href="'.DIR.'translations">Bible</a> → '.$data['bookProjects'][0]->angName . ' ('.$data['bookProjects'][0]->langName.')<br><br>';

    foreach ($data['bookProjects'] as $bookProject) {
        echo "<a href=\"" . DIR . "translations/" . $bookProject->targetLang . "/". $bookProject->bookProject . "\">" . strtoupper($bookProject->bookProject) . "</a><br>";
    }
}

if(isset($data['books'])) {
    echo '<a href="'.DIR.'translations">Bible</a> → ';
    echo '<a href="'.DIR.'translations/'.$data['books'][0]->targetLang.'">'.$data['books'][0]->angName . ' ('.$data['books'][0]->langName.')</a> → ';
    echo $data['books'][0]->bookProject.'</a><br><br>';

    foreach ($data['books'] as $book) {
        echo "<a href=\"" . DIR . "translations/" . $book->targetLang . "/" .$book->bookProject . "/" . $book->bookCode . "\">" . $book->bookName . "</a><br>";
    }
}

if(isset($data['book'])) {
    echo '<a href="'.DIR.'translations">Bible</a> → ';
    echo '<a href="'.DIR.'translations/'.$data['data']->targetLang.'">'.$data['data']->angName . ' ('.$data['data']->langName.')</a> → ';
    echo '<a href="'.DIR.'translations/'.$data['data']->targetLang.'/'.$data['data']->bookProject.'">'.\Core\Language::show($data["data"]->bookProject, "Events").'</a> → ';
    echo $data['data']->bookName.'</a><br><br>';

    echo '<h1>'.$data['data']->bookName.'</h1>';

    echo $data["book"];
}

if(isset($data['verses'])) {
    echo '<a href="'.DIR.'translations">Books</a>->';
    echo '<a href="'.DIR.'translations/'.$data['verses'][0]->bookProject.'">'.strtoupper($data['verses'][0]->bookProject).'</a>->';
    echo '<a href="'.DIR.'translations/'.$data['verses'][0]->bookProject.'/'.$data['verses'][0]->bookID.'">'.$data['verses'][0]->bookName.'</a>->';
    echo 'Chapter '.$data['verses'][0]->chapter.'<br><br>';

    $verses = array();

    foreach ($data['verses'] as $verse) {
        $verses = json_decode($verse->translatedVerses, true);

        echo '<div class="verses_segment">';

        if(Session::get('memberID') == $verse->memberID) {
            echo '<a href="'.DIR.'translations/'.$verse->bookProject.'/'.$verse->bookID.'/'.$verse->chapter.'/edit">';
        }
        elseif(Session::get('userType') == "checker" || Session::get('userType') == "both")
        {
            echo '<a href="'.DIR.'translations/'.$verse->bookProject.'/'.$verse->bookID.'/'.$verse->chapter.'/view">';
        }
        else
        {
            echo '<a href="'.DIR.'translations/'.$verse->bookProject.'/'.$verse->bookID.'/'.$verse->chapter.'">';
        }

        foreach ($verses as $verse => $verseData)
        {
            echo '<div class="form-group">';
            echo $verse.". ".$verseData['text']."<label>";
            echo '</div>';
        }
        echo '</a>';
        echo '</div>';
    }
}