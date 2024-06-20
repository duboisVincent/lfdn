<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticlesController extends AbstractController
{
    #[Route('/allArticles', name: 'app_articles')]
    public function getAll(EntityManagerInterface $em): Response
    {

        return $this->render('articles/index.html.twig', [
            'articles' => $em->getRepository(Articles::class)->findAll(),
        ]);
    }

    #[Route('/readArticle/{id}', name: 'app_read_article')]
    public function read(EntityManagerInterface $em, int $id): Response
    {
        $article = $em->getRepository(Articles::class)->findOneBy(['id' => $id]);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $article->setViews($article->getViews() + 1);
        $em->persist($article);
        $em->flush();
        return $this->render('articles/read.html.twig', [
            'article' => $article,
        ]);
    }


    
    #[Route('/createArticle', name: 'app_create_article')]
    public function create(EntityManagerInterface $em, Request $request): Response{
        $article = new Articles();
        $article->setViews(0);

        $form = $this->createForm(ArticlesType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($article);
            $em->flush();

            return $this->render('articles/index.html.twig', [
                'articles' => $em->getRepository(Articles::class)->findAll(),
            ]);
        }


        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deleteArticle/{id}', name: 'app_delete_article')]
    public function deleteArticle(EntityManagerInterface $em, int $id){
        $article = $em->getRepository(Articles::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $em->remove($article);
        $em->flush();

        return $this->render('homepage/index.html.twig', [
            'controller_name' => 'Page D\'accueil',
        ]);
    }

    #[Route('/editArticle/{id}', name: 'app_edit_article')]
    public function edit(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $article = $em->getRepository(Articles::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $form = $this->createForm(ArticlesType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_articles');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
