<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

require __DIR__.'/src/config/ConfigLoader.php';

require __DIR__.'/src/database/DatabaseConnection.php';

require __DIR__.'/src/core/SpotifyAuthenticator.php';

require __DIR__.'/src/core/SpotifyPlaylistFetcher.php';

use GuzzleHttp\Client;

$configLoader = new ConfigLoader();
$configLoader->load();

$dsn = $_ENV['DATABASE_DSN'];
$databaseUser = $_ENV['DATABASE_USER'];
$databasePassword = $_ENV['DATABASE_PASSWORD'];

try {
    $database = new DatabaseConnection($dsn, $databaseUser, $databasePassword);
    $pdo = $database->getPdo();
} catch (RuntimeException $e) {
    echo 'Fehler: '.$e->getMessage();

    exit;
}

$spotifyRepository = new PdoSpotifyRepository($database);
$client = new Client();
$spotifyApi = new SpotifyAuthenticator($client, $spotifyRepository);

$accessToken = $spotifyRepository->getLatestAccessToken() ?? $spotifyApi->createAccesstoken();

$headers = [
    'Authorization' => 'Bearer '.$accessToken,
];
$offset = 0;

$response = null;
$artists = [];
$trackitems = [];

$playlistFetcher = new SpotifyPlaylistFetcher($client, $accessToken);

$data = $playlistFetcher->fetchTracks($_ENV['PLAYLIST_ID']);

$artists = $data['artists'];
$trackitems = $data['tracks'];
$genres = [];

// for debugging purposes (duplicate genre count)

echo "<table style='borderwidth: 2px; borderstyle: solid; width: 100%'>\n";
echo "<tr>
<th>Song</th>
<th>Künstler</th>
<th></th>
<th>Album</th>
<th>Weitere Künstler</th>
</tr>";
foreach ($trackitems as $item) {
    echo '<tr>';
    echo '<td>';
    if ($item->track->explicit) {
        echo '&#x26A0; ';
    }
    echo $item->track->name;
    echo '</td>';
    echo '<td>';
    echo $item->track->artists[0]->name;
    echo '</td>';
    echo "<td><img src='";
    echo $item->track->album->images[2]->url;
    echo "'></td>";
    echo '<td>';
    echo $item->track->album->name;
    echo '</td>';
    foreach (array_slice($item->track->artists, 1) as $artist) {
        echo "<td>{$artist->name}</td>";
    }
    echo "</tr>\n";
}
echo '</table>';

foreach ($artists as $key => $artist) {
    foreach ($artist['genres'] as $genre) {
        if (array_key_exists($genre, $genres)) {
            $genres[$genre][] = $artist;
        } else {
            $genres[$genre] = [$artist];
        }
        $spotifyRepository->saveGenre($genre);
        $spotifyRepository->saveArtistGenre($key, $genre);
    }
    $spotifyRepository->saveArtist($key, $artist);
}

uasort($genres, static fn ($a, $b) => sizeof($a) < sizeof($b) ? 1 : -1);

foreach($genres as $genre => $genreartists) {
    $paragraph = '';
    $genresongcount = 0;
    foreach($genreartists as $artist) {
        $paragraph .= $artist['name'].': '.$artist['count'].' Songs</br>';
// Hier werden Songs doppelt für das Genre gezählt!
        $genresongcount += $artist['count'];
    }
    $paragraph = '<b>'.$genre.'</b>: '.sizeof($genreartists).' Künstler, '.$genresongcount.' Songs<br/>'
    .$paragraph.'<br/>';
    echo $paragraph;
}
