<?php
use \Helpers\Session;
use \Core\Language;
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
        echo "<a href=\"" . DIR . "translations/" . $bookProject->targetLang . "/". $bookProject->bookProject . "\">" . strtoupper($bookProject->bookProject) . " (".Language::show($bookProject->bookProject, "Events").")</a><br>";
    }
}

if(isset($data['books'])) {
    echo '<a href="'.DIR.'translations">Bible</a> → ';
    echo '<a href="'.DIR.'translations/'.$data['books'][0]->targetLang.'">'.$data['books'][0]->angName . ' ('.$data['books'][0]->langName.')</a> → ';
    echo Language::show($data['books'][0]->bookProject, "Events").'</a><br><br>';

    foreach ($data['books'] as $book) {
        echo "<a href=\"" . DIR . "translations/" . $book->targetLang . "/" .$book->bookProject . "/" . $book->bookCode . "\">" . $book->bookName . "</a><br>";
    }
}

if(isset($data['book'])) {
    echo '<a href="'.DIR.'translations">Bible</a> → ';
    echo '<a href="'.DIR.'translations/'.$data['data']->targetLang.'">'.$data['data']->angName . ' ('.$data['data']->langName.')</a> → ';
    echo '<a href="'.DIR.'translations/'.$data['data']->targetLang.'/'.$data['data']->bookProject.'">'.Language::show($data["data"]->bookProject, "Events").'</a> → ';
    echo $data['data']->bookName.'</a><br><br>';

    echo '<h1>'.$data['data']->bookName.'</h1>';

    echo '<div class="bible_book">'.$data["book"].'</div>';
}