<?php
/**
 * Template de la page de monitoring des commentaires.
 */
?>

<div class="adminHeader">
    <h2>
        Commentaires de l'article : <?= Utils::format($article->getTitle()) ?>
    </h2>
    <a class="submit" href="index.php?action=monitoring">Dashboard</a>
</div>

<div class="adminComments">
    <table>
        <thead>
            <tr>
                <th class="col-title">Auteur</th>
                <th class="col-stat">Contenu</th>
                <th class="col-date">Date de publication</th>
                <th class="col-action">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($comments)) { ?>
                <tr>
                    <td colspan="4" class="noComments">Aucun commentaire pour cet article.</td>
                </tr>
            <?php } else { ?>
                <?php foreach ($comments as $comment) { ?>
                    <tr>
                        <td><?= Utils::format($comment->getPseudo()) ?></td>
                        <td><?= Utils::format($comment->getContent()) ?></td>
                        <td><?= Utils::convertDateToFrenchFormat($comment->getDateCreation()) ?></td>
                        <td>
                            <a class="submit" href="index.php?action=deleteComment&id=<?= $comment->getId() ?>" <?= Utils::askConfirmation("Êtes-vous sûr de vouloir supprimer ce commentaire ?") ?> >
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>
<div class="pagination">
    <?php if ($page > 1) { ?>
        <a class="submit" href="index.php?action=showComments&idArticle=<?= $article->getId() ?>&page=<?= $page - 1 ?>">
            Page précédente
        </a>
    <?php } ?>
    <?php if ($page < $totalPages) { ?>
        <a class="submit" href="index.php?action=showComments&idArticle=<?= $article->getId() ?>&page=<?= $page + 1 ?>">
            Page suivante
        </a>
    <?php } ?>
</div>