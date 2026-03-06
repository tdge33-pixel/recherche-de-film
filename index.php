<?php
// 1. On inclut le fichier de connexion
require_once 'db.php';

// 2. On prépare une petite requête pour tester (ex: lire des articles)
// Note : Tu dois avoir créé une table "articles" dans phpMyAdmin avant !
$stmt = $pdo->query("SELECT * FROM articles");
$articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>ce site web est pour voir des films</title>
</head>
<body>
    <h1>Bienvenue sur mon site relié à MySQL ghtttttt</h1>

    <ul>
        <?php foreach ($articles as $article): ?>
            <li><?php echo htmlspecialchars($article['titre']); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
<?php
// Clé API TMDb
$apiKey = '0750563d4bace906f8c9e7c959f3f65d';
$apiUrl = 'https://api.themoviedb.org/3';

function fetchFromApi($endpoint, $params = []) {
    global $apiKey, $apiUrl;
    $params['api_key'] = $apiKey;
    $params['language'] = 'fr-FR';
    $url = $apiUrl . $endpoint . '?' . http_build_query($params);
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Récupérer les genres
$genres = fetchFromApi('/genre/movie/list')["genres"];

// Gestion de la recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';

function fetchAllPages($endpoint, $params = []) {
    $allResults = [];
    $page = 1;
    do {
        $params['page'] = $page;
        $data = fetchFromApi($endpoint, $params);
        if (isset($data['results'])) {
            $allResults = array_merge($allResults, $data['results']);
        }
        $totalPages = isset($data['total_pages']) ? $data['total_pages'] : 1;
        $page++;
    } while ($page <= $totalPages && $page <= 10); // Limite à 10 pages pour éviter les lenteurs
    return $allResults;
}

$now = date('Y-m-d');
$oneYearLater = date('Y-m-d', strtotime('+1 year'));

// Prochainement : films à sortir dans moins d'un an
$upcoming = fetchAllPages('/movie/upcoming', ['region' => 'FR', 'release_date.gte' => $now, 'release_date.lte' => $oneYearLater]);
$upcoming = array_filter($upcoming, function($m) use ($now, $oneYearLater) {
    return isset($m['release_date']) && $m['release_date'] > $now && $m['release_date'] <= $oneYearLater;
});

// Films déjà sortis
if ($search) {
    $movies = fetchAllPages('/search/movie', ['query' => $search]);
    $movies = array_filter($movies, function($m) use ($now) {
        return isset($m['release_date']) && $m['release_date'] <= $now;
    });
} elseif ($genre) {
    $movies = fetchAllPages('/discover/movie', ['with_genres' => $genre, 'sort_by' => 'release_date.desc']);
    $movies = array_filter($movies, function($m) use ($now) {
        return isset($m['release_date']) && $m['release_date'] <= $now;
    });
} else {
    $movies = fetchAllPages('/discover/movie', ['sort_by' => 'release_date.desc', 'region' => 'FR', 'release_date.lte' => $now]);
    $movies = array_filter($movies, function($m) use ($now) {
        return isset($m['release_date']) && $m['release_date'] <= $now;
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Film</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Recherche de Film</h1>
    </header>
    <div class="container">
        <div class="menu" style="display:flex;gap:30px;justify-content:center;margin-bottom:30px;">
            <a href="index.php" class="active">Déjà sortis</a>
            <a href="#prochainement" onclick="document.getElementById('prochainement').scrollIntoView({behavior:'smooth'});return false;">Prochainement</a>
        </div>
        <form class="search-bar" method="get">
            <input type="text" name="search" placeholder="Rechercher un film..." value="<?= htmlspecialchars($search) ?>">
            <select name="genre">
                <option value="">Toutes les catégories</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= $genre == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Rechercher</button>
        </form>
        <div class="movies">
            <?php if ($movies): ?>
                <?php foreach ($movies as $movie): ?>
                    <a href="film.php?id=<?= $movie['id'] ?>" class="movie" style="text-decoration:none;color:inherit;">
                        <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                        <div class="movie-info">
                            <div class="movie-title"><?= htmlspecialchars($movie['title']) ?></div>
                            <div class="movie-date">Sortie : <?= htmlspecialchars($movie['release_date']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun film trouvé.</p>
            <?php endif; ?>
        </div>
        <h2 id="prochainement" style="margin-top:50px;">Prochainement (moins d'un an)</h2>
        <div class="movies">
            <?php if ($upcoming): ?>
                <?php foreach ($upcoming as $movie): ?>
                    <a href="film.php?id=<?= $movie['id'] ?>" class="movie" style="text-decoration:none;color:inherit;">
                        <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                        <div class="movie-info">
                            <div class="movie-title"><?= htmlspecialchars($movie['title']) ?></div>
                            <div class="movie-date">Sortie prévue : <?= htmlspecialchars($movie['release_date']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun film prochainement.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>