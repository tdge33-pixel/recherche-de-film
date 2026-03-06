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

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$movieId = intval($_GET['id']);
$movie = fetchFromApi("/movie/$movieId");
$videos = fetchFromApi("/movie/$movieId/videos");
$trailer = null;
if (!empty($videos['results'])) {
    foreach ($videos['results'] as $video) {
        if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
            $trailer = 'https://www.youtube.com/watch?v=' . $video['key'];
            break;
        }
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($movie['title']) ?> - Détail</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .movie-detail { max-width: 700px; margin: 40px auto; background: #222; border-radius: 8px; padding: 30px; color: #fff; }
        .movie-detail img { width: 300px; float: left; margin-right: 30px; border-radius: 8px; }
        .movie-detail h2 { margin-top: 0; }
        .movie-detail .overview { margin: 20px 0; }
        .movie-detail .trailer { margin-top: 20px; }
        .back-link { display: block; margin-bottom: 20px; color: #e50914; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">&larr; Retour à la liste</a>
        <div class="movie-detail">
            <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
            <h2><?= htmlspecialchars($movie['title']) ?></h2>
            <div class="movie-date">Sortie : <?= htmlspecialchars($movie['release_date']) ?></div>
            <div class="overview"><strong>Résumé :</strong> <?= htmlspecialchars($movie['overview']) ?></div>
            <?php if ($trailer): ?>
                <div class="trailer">
                    <a href="<?= $trailer ?>" target="_blank" style="color:#e50914;">Voir la bande annonce sur YouTube</a>
                </div>
            <?php else: ?>
                <div class="trailer">Bande annonce non disponible.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
