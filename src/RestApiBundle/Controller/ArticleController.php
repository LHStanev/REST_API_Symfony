<?php

namespace RestApiBundle\Controller;

use RestApiBundle\Entity\Article;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RestApiBundle\Form\ArticleType;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class ArticleController
 * @package RestApiBundle\Controller
 * @Route("/articles")
 */

class ArticleController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    /**
     * @Route("/all",name="all_articles")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAllAction() {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($articles, 'json');
        return new Response($json,Response::HTTP_OK,
                     array('content-type' => 'application/json'));
    }

    /**
     * @Route("/{id}", name="article_id", requirements={"id"="\d+"})
     * @param int $id
     * @return Response
     */

    public function showOneAction(int $id)
    {
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($article, 'json');
        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);

    }

    /**
     * @Route("/create_article",name="create_article")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */

    public function createAction(Request $request) {
        try {
            $this->createArticle($request);
            return new Response(null, Response::HTTP_CREATED);
        }catch (\Exception $e) {
            return new Response(json_encode(['error' => $e->getMessage()]), Response::HTTP_BAD_REQUEST, ['content-type' =>'application/json']);
        }

    }

    protected function createArticle(Request $request) {
        $parameters = $request->getContent();

        $serializer = $this->container->get('jms_serializer');
        $article = $serializer->deserialize($parameters, Article::class, 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();
        return $article;
    }
}
