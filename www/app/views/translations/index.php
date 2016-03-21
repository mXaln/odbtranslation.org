<?php
use \Helpers\Session;
?>

<h1><?php echo $data['title'] ?></h1>

<?php
if(isset($data['bookProject'])) {
    echo "Books<br><br>";

    foreach ($data['bookProject'] as $bookProject) {
        echo "<a href=\"" . DIR . "translations/" . $bookProject . "\">" . strtoupper($bookProject) . "</a><br>";
    }
}

if(isset($data['books'])) {
    echo '<a href="'.DIR.'translations">Books</a>->'.strtoupper($data['books'][0]->bookProject).'<br><br>';

    foreach ($data['books'] as $books) {
        echo "<a href=\"" . DIR . "translations/" . $books->bookProject . "/" . $books->bookID . "\">" . $books->bookName . "</a><br>";
    }
}

if(isset($data['chapters'])) {
    echo '<a href="'.DIR.'translations">Books</a>->';
    echo '<a href="'.DIR.'translations/'.$data['chapters'][0]->bookProject.'">'.strtoupper($data['chapters'][0]->bookProject).'</a>->';
    echo $data['chapters'][0]->bookName.'</a><br><br>';

    foreach ($data['chapters'] as $chapter) {
        echo "<a href=\"" . DIR . "translations/".$chapter->bookProject. "/" . $chapter->bookID . "/" . $chapter->chapter . "\">Chapter " . $chapter->chapter . "</a><br>";
    }
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