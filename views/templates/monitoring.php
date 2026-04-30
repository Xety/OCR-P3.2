<?php
    /**
     * Affichage de la partie monitoring : liste des articles et leurs statistiques (nombre de vues, nombre de commentaires, date de création).
     */
    $sort = $_GET['sort'] ?? 'date_creation';
    $order = $_GET['order'] ?? 'desc';
    $newOrder = ($order === 'asc') ? 'desc' : 'asc';
?>

<div class="adminHeader">
    <h2>
        Dashboard
    </h2>
    <a class="submit" href="index.php?action=admin">Voir les articles</a>
</div>


<div class="adminMonitoring">
    <table>
        <thead>
            <tr>
                <th class="col-title">
                    <a href="index.php?action=monitoring&sort=title&order=<?= $newOrder ?>">
                        Titre <?= $sort === 'title' ? ($order === 'asc' ? '↑' : '↓') : '↕' ?>
                    </a>
                </th>
                <th class="col-stat">
                    <a href="index.php?action=monitoring&sort=views&order=<?= $newOrder ?>">
                        Vues <?= $sort === 'views' ? ($order === 'asc' ? '↑' : '↓') : '↕' ?>
                    </a>
                </th>
                <th class="col-stat">
                    <a href="index.php?action=monitoring&sort=comments_count&order=<?= $newOrder ?>">
                        Commentaires <?= $sort === 'comments_count' ? ($order === 'asc' ? '↑' : '↓') : '↕' ?>
                    </a>
                </th>
                <th class="col-date">
                    <a href="index.php?action=monitoring&sort=date_creation&order=<?= $newOrder ?>">
                        Date de publication <?= $sort === 'date_creation' ? ($order === 'asc' ? '↑' : '↓') : '↕' ?>
                    </a>
                </th>
                <th class="col-action">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article) { ?>
                <tr>
                    <td>
                        <a class="articleTitle" href="index.php?action=showArticle&id=<?= $article->getId() ?>">
                            <?= Utils::format($article->getTitle()) ?>
                        </a>
                    </td>
                    <td><?= $article->getViews() ?></td>
                    <td><?= $article->getCommentsCount() ?></td>
                    <td><?= Utils::convertDateToFrenchFormat($article->getDateCreation()) ?></td>
                    <td>
                        <a class="submit" href="index.php?action=showComments&idArticle=<?= $article->getId() ?>">
                            Gérer les commentaires
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>