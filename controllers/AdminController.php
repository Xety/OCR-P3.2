<?php
/**
 * Contrôleur de la partie admin.
 */

class AdminController {

    /**
     * Nombre d'articles par page.
     *
     * @var int
     */
    private const ARTICLES_PER_PAGE = 3;

    /**
     * Affiche la page des articles.
     * @return void
     */
    public function showArticles() : void
    {
        // On vérifie que l'utilisateur est connecté.
        $this->checkIfUserIsConnected();

        // On récupère les articles.
        $articleManager = new ArticleManager();
        $articles = $articleManager->getAllArticles();

        // On affiche la page des articles.
        $view = new View("Articles");
        $view->render("admin/articles", [
            'articles' => $articles
        ]);
    }

    /**
     * Affiche la page de monitoring.
     *
     * @return void
     */
    public function showMonitoring() : void
    {
        // On vérifie que l'utilisateur est connecté.
        $this->checkIfUserIsConnected();

        $sort = $_GET['sort'] ?? 'date_creation';
        $order = $_GET['order'] ?? 'desc';

        // On vérifie que les paramètres de tri sont valides pour éviter les injections SQL.
        $allowedSort = ['title', 'views', 'comments_count', 'date_creation'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'date_creation';
        }

        if ($order !== 'asc' && $order !== 'desc') {
            $order = 'desc';
        }

        $articleManager = new ArticleManager();
        $articles = $articleManager->getMonitoringArticles($sort, $order);

        // On affiche la page de monitoring.
        $view = new View("Monitoring");
        $view->render("admin/monitoring", [
            'articles' => $articles,
            'sort' => $sort,
            'order' => $order
        ]);
    }

    /**
     * Vérifie que l'utilisateur est connecté.
     * @return void
     */
    private function checkIfUserIsConnected() : void
    {
        // On vérifie que l'utilisateur est connecté.
        if (!isset($_SESSION['user'])) {
            Utils::redirect("connectionForm");
        }
    }

    /**
     * Affichage du formulaire de connexion.
     * @return void
     */
    public function displayConnectionForm() : void
    {
        $view = new View("Connexion");
        $view->render("connectionForm");
    }

    /**
     * Connexion de l'utilisateur.
     * @return void
     */
    public function connectUser() : void
    {
        // On récupère les données du formulaire.
        $login = Utils::request("login");
        $password = Utils::request("password");

        // On vérifie que les données sont valides.
        if (empty($login) || empty($password)) {
            throw new Exception("Tous les champs sont obligatoires. 1");
        }

        // On vérifie que l'utilisateur existe.
        $userManager = new UserManager();
        $user = $userManager->getUserByLogin($login);
        if (!$user) {
            throw new Exception("L'utilisateur demandé n'existe pas.");
        }

        // On vérifie que le mot de passe est correct.
        if (!password_verify($password, $user->getPassword())) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            throw new Exception("Le mot de passe est incorrect : $hash");
        }

        // On connecte l'utilisateur.
        $_SESSION['user'] = $user;
        $_SESSION['idUser'] = $user->getId();

        // On redirige vers la page de monitoring.
        Utils::redirect("monitoring");
    }

    /**
     * Déconnexion de l'utilisateur.
     * @return void
     */
    public function disconnectUser() : void
    {
        // On déconnecte l'utilisateur.
        unset($_SESSION['user']);

        // On redirige vers la page d'accueil.
        Utils::redirect("home");
    }

    /**
     * Affichage du formulaire d'ajout d'un article.
     * @return void
     */
    public function showUpdateArticleForm() : void
    {
        $this->checkIfUserIsConnected();

        // On récupère l'id de l'article s'il existe.
        $id = Utils::request("id", -1);

        // On récupère l'article associé.
        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($id);

        // Si l'article n'existe pas, on en crée un vide.
        if (!$article) {
            $article = new Article();
        }

        // On affiche la page de modification de l'article.
        $view = new View("Edition d'un article");
        $view->render("admin/updateArticleForm", [
            'article' => $article
        ]);
    }

    /**
     * Ajout et modification d'un article.
     * On sait si un article est ajouté car l'id vaut -1.
     * @return void
     */
    public function updateArticle() : void
    {
        $this->checkIfUserIsConnected();

        // On récupère les données du formulaire.
        $id = Utils::request("id", -1);
        $title = Utils::request("title");
        $content = Utils::request("content");

        // On vérifie que les données sont valides.
        if (empty($title) || empty($content)) {
            throw new Exception("Tous les champs sont obligatoires. 2");
        }

        // On crée l'objet Article.
        $article = new Article([
            'id' => $id, // Si l'id vaut -1, l'article sera ajouté. Sinon, il sera modifié.
            'title' => $title,
            'content' => $content,
            'id_user' => $_SESSION['idUser']
        ]);

        // On ajoute l'article.
        $articleManager = new ArticleManager();
        $articleManager->addOrUpdateArticle($article);

        // On redirige vers la page des articles.
        Utils::redirect("showArticles");
    }


    /**
     * Suppression d'un article.
     * @return void
     */
    public function deleteArticle() : void
    {
        $this->checkIfUserIsConnected();

        $id = Utils::request("id", -1);

        // On supprime l'article.
        $articleManager = new ArticleManager();
        $articleManager->deleteArticle($id);

        // On redirige vers la page des articles.
        Utils::redirect("showArticles");
    }

    /**
     * Affiche les commentaires d'un article.
     *
     * @throws Exception
     *
     * @return void
     */
    public function showComments() : void
    {
        $this->checkIfUserIsConnected();

        // Récupération de l'id de l'article demandé.
        $id = Utils::request("idArticle", -1);

        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleByIdWithCount($id);

        if (!$article) {
            throw new Exception("L'article n'existe pas.");
        }

        $page = (int)Utils::request("page", 1);

        // Calcul du nombre total de pages.
        $limit = self::ARTICLES_PER_PAGE;
        $totalPages = ceil($article->getCommentsCount() / $limit);

        // Si la page demandée n'est pas valide, on redirige vers la page 1.
        if ($page < 1 || ($page > $totalPages && $totalPages > 0)) {
            Utils::redirect("showComments&idArticle=$id&page=1");
        }

        // On récupère les commentaires de l'article avec pagination.
        $commentManager = new CommentManager();
        $comments = $commentManager->getCommentsPaginatedByArticleId($id, $limit, ($page - 1) * $limit);

        // On affiche la page de gestion des commentaires.
        $view = new View("Gestion des commentaires");
        $view->render("admin/comments", [
            'comments' => $comments,
            'article' => $article,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Supprime un commentaire.
     *
     * @return void
     */
    public function deleteComment() : void
    {
        $this->checkIfUserIsConnected();

        $id = Utils::request("id", -1);

        // On vérifie que le commentaire existe.
        $commentManager = new CommentManager();
        $comment = $commentManager->getCommentById($id);
        if ($comment) {
            // On supprime le commentaire.
            $commentManager->deleteComment($comment);
        }

        // On redirige vers la page de gestion des commentaires.
        Utils::redirect("showComments&idArticle=" . $comment->getIdArticle());
    }
}