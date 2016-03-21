<h1><?php echo $data['title'] ?></h1>



<?php
echo \Core\Error::display($error);

if(isset($data['gl'])) {
    echo "Gateway Language<br><br>";

    foreach ($data['gl'] as $gl) {
        echo "<a href=\"" . DIR . "books/" . $gl . "\">" . strtoupper($gl) . "</a><br>";
    }
}

if(isset($data['bookProject'])) {
    echo "Books<br><br>";

    foreach ($data['bookProject'] as $bookProject) {
        echo "<a href=\"" . DIR . "books/" . $bookProject . "\">" . strtoupper($bookProject) . "</a><br>";
    }
}

if(isset($data['books'])) {
    echo '<a href="'.DIR.'books">Books</a>->'.strtoupper($data['books'][0]->bookProject).'<br><br>';

    foreach ($data['books'] as $book) {
        echo "<a href=\"" . DIR . "books/" . $book->bookProject . "/" . $book->bookID . "\">" . $book->bookName . "</a><br>";
    }
}

if(isset($data['verses'])) {
    echo '<a href="'.DIR.'books">Books</a>->';
    echo '<a href="'.DIR.'books/'.$data['verses'][0]->bookProject.'">'.strtoupper($data['verses'][0]->bookProject).'</a>->';
    echo $data['verses'][0]->bookName.'<br><br>';

    $verses = json_decode($data['verses'][0]->verses);

    echo '<form action="" method=\'post\' style="width: 700px">';

    foreach ($verses as $verse => $text)
    {
        $disabled = isset($data['translatedVerses'][$verse]) ? 'disabled' : '';

        echo '<div class="form-group">';
        echo '<label><input type="checkbox" name="verses[]" id="verses" value="'.$verse.'" '.$disabled.' /> ';
        echo $verse.". ".$text."<label>";
        echo '</div>';
    }

    echo '<button type="submit" name="submit" class="btn btn-primary">Start translation</button>';

    echo '</form>';
}