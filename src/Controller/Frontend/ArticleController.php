<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Form\SearchArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use App\Search\SearchArticle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles', name: 'app.articles')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $repo,
        private CommentaireRepository $repoComment
    ) {
    }

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(Request $request): Response|JsonResponse
    {
        // instance
        $filter = new SearchArticle();

        // page 1 par default
        $filter->setPage($request->query->get('page', 1));

        // objet -> $filter
        $form = $this->createForm(SearchArticleType::class, $filter);
        $form->handleRequest($request);

        // function custom 自定义功能->findSearch检查是否是ajax ou pas
        $articles = $this->repo->findSearch($filter);

        // sur le ajax-> return json
        if ($request->query->get('ajax')) {
            /*
             * on envoie la réponse en JSON avec le nouveau code HTML de chaque composants de la page
             */
            // class de symfony repondre le json par ajax
            return new JsonResponse([
                // chemin vers le content/sorting/pagination/count
                'content' => $this->renderView('Components/_articleList.html.twig', [
                    // recuperer avec findSearch
                    'articles' => $articles,
                ]),
                'sorting' => $this->renderView('Components/_sorting.html.twig', [
                    'articles' => $articles,
                ]),
                'pagination' => $this->renderView('Components/_pagination.html.twig', [
                    'articles' => $articles,
                ]),
                'count' => $this->renderView('Components/_count.html.twig', [
                    'articles' => $articles,
                ]),
                // nombre de total de page  => 1 / 2
                // -> 1.nombre de article total
                // -> 2. nombre de article par page
                'totalPage' => ceil($articles->getTotalItemCount() / $articles->getItemNumberPerPage()),
            ]);
        }

        return $this->render('Frontend/Article/index.html.twig', [
            'articles' => $articles,
            'form' => $form,
        ]);
    }

    #[Route('/details/{slug}', name: '.show', methods: ['GET', 'POST'])]
    public function show(?Article $article, Request $request): Response|RedirectResponse
    {
        if (!$article instanceof Article || !$article->isEnable()) {
            $this->addFlash('error', 'Article non trouvé');

            return $this->redirectToRoute('app.articles.index');
        }

        $commentaire = new Commentaire();

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire
                ->setUser($this->getUser())
                ->setEnable(true)
                ->setArticle($article);

            $this->repoComment->save($commentaire);

            $this->addFlash('success', 'Votre commentaire a bien été pris en compte');

            return $this->redirectToRoute('app.articles.show', [
                'slug' => $article->getSlug(),
            ], Response::HTTP_FOUND);
        }

        return $this->render('Frontend/Article/show.html.twig', [
            'article' => $article,
            'commentaires' => $this->repoComment->findActiveByArticle($article),
            'form' => $form,
        ]);
    }
}
