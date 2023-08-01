<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/articles', name: 'app.articles')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $repo,
        private CommentaireRepository $repoComment
    ) {
    }

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Frontend/Article/index.html.twig', [
            'articles' => $this->repo->findLatest(),
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
            'form' => $form
        ]);
    }
}
