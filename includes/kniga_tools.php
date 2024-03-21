<?php
	function getBookGenres($bookId){
		global $conn;
		$fetchGenres = $conn->query("SELECT genres.genreName, genres.id FROM `books-genres`
			INNER JOIN genres ON genreId = genres.id
			WHERE bookId = $bookId")->fetch_all(MYSQLI_ASSOC);
		$genresList = array();

		foreach($fetchGenres as $genreItem){
			$genresList[ $genreItem['id'] ] = $genreItem['genreName'];
		}

		return $genresList;
	}

	function getBookAuthors($bookId){
		global $conn;
		$fetchGenres = $conn->query("SELECT authors.authorName, authors.id FROM `books-authors`
			INNER JOIN authors ON authorId = authors.id
			WHERE bookId = $bookId")->fetch_all(MYSQLI_ASSOC);
		$authorsList = array();

		foreach($fetchGenres as $authorItem){
			$authorsList[ $authorItem['id'] ] = $authorItem['authorName'];
		}

		return $authorsList;
	}
